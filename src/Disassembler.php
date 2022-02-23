<?php declare(strict_types=1);

namespace BafS\Chip8;

class Disassembler
{
    private array $opcodeInfo;

    public function __construct(string $opcodeFqcn = Opcodes::class)
    {
        $reflectionClass = new \ReflectionClass($opcodeFqcn);
        $consts = $reflectionClass->getConstants();

        $opcodeInfo = [];
        foreach ($consts as $const => $opcode) {
            $mask = 0;
            $args = [];
            for ($i = 0; $i < 4; ++$i) {
                $char = $opcode[$i];
                $charMask = (0xF << ((3 - $i) * 4));
                if (!in_array($char, ['N', 'X', 'Y'], true)) {
                    $mask = $mask | $charMask;
                    continue;
                }

                if ($char === 'X' || $char === 'Y') {
                    $args[$char] = [
                        'mask' => $charMask,
                        'mask_DEBUG' => sprintf("%X\n", $charMask),
                        'shift' => (3 - $i) * 4,
                    ];
                }

                if ($char === 'N') {
                    if (isset($args['N'])) {
                        $args['N']['mask'] = $args['N']['mask'] | $charMask;
                    } else {
                        $args['N'] = ['mask' => $charMask];
                    }

                    $args['N']['mask_DEBUG'] = sprintf("%X", $args['N']['mask']);
                    $args['N']['shift'] = (3 - $i) * 4;
                }
            }

            $opcodeOnly = hexdec(str_replace(['X', 'Y', 'N'], '0', $opcode));

            $opcodeInfo[$opcode] = [
                'name' => $const,
                'opcodeOnly' => $opcodeOnly,
                'opcodeOnly_DEBUG' => sprintf("%X", $opcodeOnly),
                'opcode' => $opcode,
                'mask' => $mask,
                'mask_DEBUG' => sprintf("%X", $mask),
                'args' => $args,
            ];
        }

        $this->opcodeInfo = $opcodeInfo;
    }

    public function disassemble(int $opcode): ?array
    {
        $bestScore = 0;
        $best = null;
        foreach ($this->opcodeInfo as $name => $opcodeInfo) {
            if (($opcode & $opcodeInfo['mask']) === $opcodeInfo['opcodeOnly']) {
                $args = $opcodeInfo['args'];

                $score = strlen(str_replace('_', '', dechex($opcode & $opcodeInfo['mask'])));

                if ($best !== null && $score < $bestScore) {
                    continue;
                }

                foreach ($args as $argName => $arg) {
                    $args[$argName] = ($opcode & $arg['mask']) >> $arg['shift'];
                }

                $bestScore = $score;

                $best = [
                    'pattern' => $name,
                    'name' => $opcodeInfo['name'],
                    'args' => $args ?? [],
                ];
            }
        }

        return $best ?? null;
    }
}
