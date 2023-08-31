<?php declare(strict_types=1);

namespace BafS\Chip8;

use BafS\Chip8\IO\AudioInterface;
use BafS\Chip8\IO\DisplayInterface;
use BafS\Chip8\IO\GamepadInterface;
use SplFixedArray;

class CPU
{
    private const MEMORY_SIZE = 4096;
    /** 16 x 16-bit values */
    private const STACK_SIZE = 16;
    /** 16 x 8-bit registers */
    private const REGISTER_NUMBER = 16;
    private const PC_START_ADDR = 0x200;
    private const FONTS = [
        0xF0, 0x90, 0x90, 0x90, 0xF0, // 0
        0x20, 0x60, 0x20, 0x20, 0x70, // 1
        0xF0, 0x10, 0xF0, 0x80, 0xF0, // 2
        0xF0, 0x10, 0xF0, 0x10, 0xF0, // 3
        0x90, 0x90, 0xF0, 0x10, 0x10, // 4
        0xF0, 0x80, 0xF0, 0x10, 0xF0, // 5
        0xF0, 0x80, 0xF0, 0x90, 0xF0, // 6
        0xF0, 0x10, 0x20, 0x40, 0x40, // 7
        0xF0, 0x90, 0xF0, 0x90, 0xF0, // 8
        0xF0, 0x90, 0xF0, 0x10, 0xF0, // 9
        0xF0, 0x90, 0xF0, 0x90, 0x90, // A
        0xE0, 0x90, 0xE0, 0x90, 0xE0, // B
        0xF0, 0x80, 0x80, 0x80, 0xF0, // C
        0xE0, 0x90, 0x90, 0x90, 0xE0, // D
        0xF0, 0x80, 0xF0, 0x80, 0xF0, // E
        0xF0, 0x80, 0xF0, 0x80, 0x80, // F
    ];

    /** @var int<1, max> */
    private readonly int $frequency;
    /** 0x000-0x1FF is reserved */
    private int $pc;
    /** Index register (16-bit register) */
    private int $i = 0;
    /** Stack pointer */
    private int $sp = 0;
    private int $soundTimer = 0;
    private int $deltaTimer = 0;
    /** @var SplFixedArray<int> */
    private SplFixedArray $memory;
    /** @var SplFixedArray<int> Registers (V) */
    private SplFixedArray $registers;
    /** @var SplFixedArray<int> */
    private SplFixedArray $stack;
    private Disassembler $disassembler;
    /** @var \Closure(self, int, array<string, mixed>):void|null  */
    private ?\Closure $debugCallback;
    /** @var array<string, bool> */
    private array $quirks;
    private bool $halted = false;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly ?DisplayInterface $screen = null,
        private readonly ?AudioInterface $audio = null,
        private readonly ?GamepadInterface $gamepad = null,
        array $options = [],
    ) {
        $frequency = $options['frequency'] ?? 300;
        if ($frequency <= 0) {
            throw new \InvalidArgumentException('Frequency must be bigger than zero.');
        }
        $this->frequency = $frequency;
        $this->debugCallback = ($options['debug_callback'] ?? null)?->bindTo($this, $this);
        unset($options['frequency'], $options['debug_callback']);
        $this->quirks = array_merge([
            'shift_quirks' => false,
        ], $options);
        $this->disassembler = new Disassembler();

        $this->reset();
    }

    private function reset(): void
    {
        $this->memory = new SplFixedArray(self::MEMORY_SIZE);
        $this->registers = new SplFixedArray(self::REGISTER_NUMBER);
        $this->stack = new SplFixedArray(self::STACK_SIZE);

        foreach ($this->registers as $i => $_) {
            $this->registers[$i] = 0;
        }

        $this->pc = self::PC_START_ADDR;
        $this->stack = new SplFixedArray(16);

        // 0-80 (5 * 16) reserved for font set
        for ($i = 0; $i < 80; ++$i) {
            $this->memory[$i] = self::FONTS[$i];
        }
    }

    public function loadData(string $data): void
    {
        $this->reset();

        $length = strlen($data);
        for ($i = 0, $max = $length; $i < $max; $i++) {
            $this->memory[0x200 + $i] = (ord($data[$i]) & 0xFF);
        }
    }

    public function toggleHalted(): void
    {
        $this->halted = !$this->halted;
    }

    public function run(): void
    {
        $this->screen?->clear();

        /** @phpstan-ignore-next-line */
        while (true) {
            $this->gamepad?->read();

            if ($this->halted === false) {
                $this->tick();
            }

            usleep((int) (1000000 / $this->frequency));
        }
    }

    private function setRegister(int $n, int $value): void
    {
        $this->registers[$n] = $value; // % 0xff;
    }

    private function fetchCurrentOpcode(): int
    {
        return $this->memory[$this->pc] << 8 | $this->memory[$this->pc + 1];
    }

    public function tick(int $opcode = null): void
    {
        // Fetch opcode
        $opcode = $opcode ?? $this->fetchCurrentOpcode();

        $opcodeInfo = $this->disassembler->disassemble($opcode);
        $pattern = $opcodeInfo['pattern'];
        $args = $opcodeInfo['args'];

        if ($this->debugCallback) {
            ($this->debugCallback)($this, $opcode, $opcodeInfo);
        }

        $this->pc = ($this->pc + 2) & 0x0FFF;

        $draw = false;

        switch ($pattern) {
//            case Opcodes::SYS_NNN->value:
//                break;
//
            case Opcodes::CLS_00E0->value:
                $this->screen?->clear();
                $draw = true;
                break;

            case Opcodes::RET_00EE->value:
                $this->pc = $this->stack[$this->sp];
                $this->sp--;
                break;

            case Opcodes::JP_1NNN->value:
                $this->pc = $args['N'];
                break;

            case Opcodes::CALL_2NNN->value:
                $this->sp++;
                $this->stack[$this->sp] = $this->pc;
                $this->pc = $args['N'];
                break;

            case Opcodes::SE_3XNN->value:
                if ($this->registers[$args['X']] === $args['N']) {
                    $this->pc += 2; // we skip one instruction
                }
                break;

            case Opcodes::SNE_4XNN->value:
                if ($this->registers[$args['X']] !== $args['N']) {
                    $this->pc += 2;
                }
                break;

            case Opcodes::SE_5XY0->value:
                if ($this->registers[$args['X']] === $this->registers[$args['Y']]) {
                    $this->pc += 2;
                }
                break;

            case Opcodes::LD_6XNN->value:
                $this->setRegister($args['X'], $args['N']);
                break;

            case Opcodes::ADD_7XNN->value:
                $sum = $this->registers[$args['X']] + $args['N'];
                $this->setRegister($args['X'], $sum & 0xff);
                break;

            case Opcodes::LD_8XY0->value:
                $this->setRegister($args['X'], $this->registers[$args['Y']]);
                break;

            case Opcodes::OR_8XY1->value:
                $this->setRegister($args['X'], $this->registers[$args['X']] | $this->registers[$args['Y']]);
                break;

            case Opcodes::AND_8XY2->value:
                $this->setRegister($args['X'], $this->registers[$args['X']] & $this->registers[$args['Y']]);
                break;

            case Opcodes::XOR_8XY3->value:
                $this->setRegister($args['X'], $this->registers[$args['X']] ^ $this->registers[$args['Y']]);
                break;

            case Opcodes::ADD_8XY4->value:
                $sum = $this->registers[$args['X']] + $this->registers[$args['Y']];
                $this->setRegister(0xf, $sum > 0xff ? 1 : 0);
                $this->setRegister($args['X'], $sum & 0xff);
                break;

            case Opcodes::SUB_8XY5->value:
                $this->registers[0xf] = $this->registers[$args['X']] > $this->registers[$args['Y']] ? 1 : 0;
                $this->setRegister($args['X'], ($this->registers[$args['X']] - $this->registers[$args['Y']]) & 0xff);
                break;

            case Opcodes::SHR_8XY6->value:
                if (!$this->quirks['shift_quirks']) {
                    $args['Y'] = $args['X'];
                }

                $this->registers[0xf] = $this->registers[$args['Y']] & 0x1;
                $this->setRegister($args['X'], $this->registers[$args['X']] >> 1);
                break;

            case Opcodes::SUBN_8XY7->value:
                $this->registers[0xF] = $this->registers[$args['Y']] > $this->registers[$args['X']] ? 1 : 0;

                $this->setRegister($args['X'], $this->registers[$args['Y']] - $this->registers[$args['X']]);

                if ($this->registers[$args['X']] < 0) {
                    $this->registers[$args['X']] += 256;
                }
                break;

            case Opcodes::SHL_8XYE->value:
                if (!$this->quirks['shift_quirks']) {
                    $args['Y'] = $args['X'];
                }

                $this->registers[0xf] = $this->registers[$args['Y']] >> 7;
                $this->setRegister($args['X'], ($this->registers[$args['X']] << 1) & 0xff);
                break;

            case Opcodes::SNE_9XY0->value:
                if ($this->registers[$args['X']] !== $this->registers[$args['Y']]) {
                    $this->pc += 2;
                }
                break;

            case Opcodes::LD_ANNN->value:
                $this->i = $args['N'];
                break;

            case Opcodes::JP_BNNN->value:
                $this->pc = ($args['N'] + $this->registers[0]) & 0xfff;
                break;

            case Opcodes::RND_CXNN->value:
                $this->setRegister($args['X'], rand(0, 255) & $args['N']);
                break;

            case Opcodes::DRW_DXYN->value:
                $regX = $this->registers[$args['X']];
                $regY = $this->registers[$args['Y']];
                $n = $args['N'];
                $carry = false;

                for ($b = 0; $b < $n; ++$b) {
                    $byte = $this->memory[$this->i + $b];
                    for ($i = 0; $i < 8; ++$i) {
                        $mask = 0b1000_0000 >> $i;

                        if (($byte & $mask) !== 0) {
                            [$width, $height] = $this->screen?->resolution() ?? [64, 32];
                            $x = ($regX + $i) % $width;
                            $y = ($regY + $b) % $height;
                            $value = $this->screen?->togglePixel($x, $y);
                            $carry = $carry || !$value;
                        }
                    }
                }

                $this->registers[0xF] = $carry ? 1 : 0;
                $draw = true;
                break;

            case Opcodes::SKP_EX9E->value:
                if ($this->gamepad?->getPressedKey() === $this->registers[$args['X']]) {
                    $this->pc += 2;
                }
                break;

            case Opcodes::SKNP_EXA1->value:
                if ($this->gamepad?->getPressedKey() !== $this->registers[$args['X']]) {
                    $this->pc += 2;
                }
                break;

            case Opcodes::LD_FX07->value:
                $this->setRegister($args['X'], $this->deltaTimer);
                break;

            case Opcodes::LD_FX0A->value:
                $key = $this->gamepad?->getPressedKey();

                // Halted
                // We loop on the same instruction until a key is pressed
                if ($key === null) {
                    usleep(1000);
                    $this->pc -= 2;
                } else {
                    $this->setRegister($args['X'], $key);
                }
                break;

            case Opcodes::LD_FX15->value:
                $this->deltaTimer = $this->registers[$args['X']];
                break;

            case Opcodes::LD_FX18->value:
                $this->soundTimer = $this->registers[$args['X']];
                break;

            case Opcodes::ADD_FX1E->value:
                $this->i += $this->registers[$args['X']];
                break;

            case Opcodes::LD_FX29->value:
                $this->i = (int) ($this->registers[$args['X']] * 5);
                break;

            case Opcodes::LD_FX33->value:
                $x = $this->registers[$args['X']];

                $this->memory[$this->i] = (int) floor($x / 100);
                $this->memory[$this->i + 1] = (int) floor($x / 10) % 10;
                $this->memory[$this->i + 2] = ($x % 100) % 10;
                break;

            case Opcodes::LD_FX55->value:
                for ($i = 0; $i <= $args['X']; $i++) {
                    $this->memory[$this->i + $i] = $this->registers[$i];
                }
                $this->i += $args['X'] + 1;
                break;

            case Opcodes::LD_FX65->value:
                for ($i = 0; $i <= $args['X']; $i++) {
                    $this->registers[$i] = $this->memory[$this->i + $i] & 0xff;
                }
                $this->i += $args['X'] + 1;
                break;

            default:
                echo sprintf("Unknown opcode: 0x%X\n", $opcode);
                exit;
        }

        // Update timers
        if ($this->deltaTimer > 0) {
            --$this->deltaTimer;
        }

        if ($this->soundTimer > 0) {
            if ($this->soundTimer === 1) {
                $this->audio?->beep();
            }
            --$this->soundTimer;
        }

        if ($draw) {
            $this->screen?->draw();
        }
    }
}
