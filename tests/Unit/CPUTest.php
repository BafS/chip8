<?php declare(strict_types=1);

namespace Test\Unit;

use BafS\Chip8\CPU;
use BafS\Chip8\IO\Terminal\Screen;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Output\BufferedOutput;

final class CPUTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array{0:CPU, 1:Screen}
     */
    private static function createCpu(string $data = '', array $options = []): array
    {
        $bufferOutput = new BufferedOutput();
        $screen = new Screen($bufferOutput);
        $cpu = new CPU($screen, options: $options);
        $cpu->loadData($data);

        return [$cpu, $screen];
    }

    private function getRegisters(CPU $cpu): \ArrayAccess
    {
        return (fn () => $this->registers)->call($cpu);
    }

    private function getPc(CPU $cpu): int
    {
        return (fn () => $this->pc)->call($cpu);
    }

    private function getI(CPU $cpu): int
    {
        return (fn () => $this->i)->call($cpu);
    }

    public function testOpcode3XNN(): void
    {
        [$cpu] = self::createCpu();
        $pc = $this->getPc($cpu);
        $cpu->tick(0x3100); // yes
        Assert::assertSame($this->getPc($cpu), $pc + 4);
        $cpu->tick(0x3122); // no
        Assert::assertSame($this->getPc($cpu), $pc + 4 + 2);
        $cpu->tick(0x6122); // 22 in v1
        $cpu->tick(0x3122); // check if v1 == 22
        Assert::assertSame($this->getPc($cpu), $pc + 4 + 2 + 2 + 4);
        $cpu->tick(0x3133); // check if v1 == 33
        Assert::assertSame($this->getPc($cpu), $pc + 4 + 2 + 2 + 4 + 2);
    }

    public function testOpcode6XNN(): void
    {
        [$cpu] = self::createCpu(hex2bin('6012'));
        $cpu->tick();
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x12);

        [$cpu] = self::createCpu(hex2bin('62ff'));
        $cpu->tick();
        Assert::assertSame($this->getRegisters($cpu)[0x2], 0xff);
    }

    public function testAdd7XNN(): void
    {
        [$cpu] = self::createCpu(hex2bin('7abc'));

        $this->getRegisters($cpu)[0xa] = 0x10;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0xa], 0x10 + 0xbc);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        [$cpu] = self::createCpu(hex2bin('7afd'));

        $this->getRegisters($cpu)[0xa] = 0x10;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0xa], (0x10 + 0xfd) & 0xff);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        [$cpu] = self::createCpu(hex2bin('7aff'));

        $this->getRegisters($cpu)[0xa] = 0x10;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0xa], 0xf);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);
    }

    public function testOpcode8XY1(): void
    {
        [$cpu] = self::createCpu();

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8011); // V0 | V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33 | 0x22);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        $cpu->tick(0x60dd);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0xdd);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8011); // V0 | V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0xdd | 0x22);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);
    }

    public function testOpcode8XY2(): void
    {
        [$cpu] = self::createCpu();

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8012); // V0 & V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33 & 0x22);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        $cpu->tick(0x60dd);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0xdd);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8012); // V0 & V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0xdd & 0x22);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);
    }

    public function testOpcode8XY3(): void
    {
        [$cpu] = self::createCpu();

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8013); // V0 ^ V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33 ^ 0x22);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        $cpu->tick(0x60dd);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0xdd);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8013); // V0 ^ V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0xdd ^ 0x22);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);
    }

    public function testOpcode8XY4(): void
    {
        [$cpu] = self::createCpu();

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8014); // V0 + V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33 + 0x22);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);
        $cpu->tick(0x61dd);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0xdd);
        $cpu->tick(0x8014); // V0 + V1 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], (0x33 + 0xdd) & 0xff);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x1);
    }

    public function testSubtract8XY5(): void
    {
        [$cpu] = self::createCpu(hex2bin('8115'));

        $this->getRegisters($cpu)[0x1] = 0x10;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x0);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0); // we test the borrow flag

        [$cpu] = self::createCpu(hex2bin('8125'));

        $this->getRegisters($cpu)[0x1] = 0x3;
        $this->getRegisters($cpu)[0x2] = 0x1;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x2);
        Assert::assertSame($this->getRegisters($cpu)[0x2], 0x1);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x1);

        [$cpu] = self::createCpu(hex2bin('8125'));

        $this->getRegisters($cpu)[0x1] = 0x1;
        $this->getRegisters($cpu)[0x2] = 0x2;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0x1], 0xff);
        Assert::assertSame($this->getRegisters($cpu)[0x2], 0x2);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);
    }

    public function testOpcode8XY6(): void
    {
        [$cpu] = self::createCpu('', [
            'shift_quirks' => false,
        ]);

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);
        $cpu->tick(0x8016);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33 >> 1);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x1);

        [$cpu] = self::createCpu('', [
            'shift_quirks' => true,
        ]);

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);

        $cpu->tick(0x8016);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33 >> 1);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);
    }

    public function testOpcode8XY7(): void
    {
        [$cpu] = self::createCpu();
        $cpu->tick(0x6010);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x10);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8017); // V1 - V0 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x22 - 0x10);

        $cpu->tick(0x6033);
        Assert::assertSame($this->getRegisters($cpu)[0x0], 0x33);
        $cpu->tick(0x6122);
        Assert::assertSame($this->getRegisters($cpu)[0x1], 0x22);
        $cpu->tick(0x8017); // V1 - V0 in V0
        Assert::assertSame($this->getRegisters($cpu)[0x0], (0x22 - 0x33) & 0xff);
    }

    public function testOpcode8XYE(): void
    {
        [$cpu] = self::createCpu(hex2bin('8cde'));

        $this->getRegisters($cpu)[0xc] = 0x2;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0xc], 0x4);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        [$cpu] = self::createCpu(hex2bin('8abe'), [
            'shift_quirks' => true,
        ]);

        $this->getRegisters($cpu)[0xa] = 0xce;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0xc], 0x0);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x0);

        [$cpu] = self::createCpu(hex2bin('8abe'), [
            'shift_quirks' => false,
        ]);

        $this->getRegisters($cpu)[0xa] = 0xce;
        $cpu->tick();

        Assert::assertSame($this->getRegisters($cpu)[0xc], 0x0);
        Assert::assertSame($this->getRegisters($cpu)[0xf], 0x1);
    }

    public function testOpcode9XY0(): void
    {
        [$cpu] = self::createCpu(hex2bin('9230'));

        $this->getRegisters($cpu)[0x2] = 0x3;
        $this->getRegisters($cpu)[0x3] = 0xa;
        $cpu->tick();
        Assert::assertSame($this->getPc($cpu), 0x204);

        $cpu->tick(0x9230);
        Assert::assertSame($this->getPc($cpu), 0x208);
    }

    public function testOpcodeANNN(): void
    {
        [$cpu] = self::createCpu();
        $cpu->tick(0xa222);
        Assert::assertSame($this->getI($cpu), 0x222);

        $cpu->tick(0xa103);
        Assert::assertSame($this->getI($cpu), 0x103);
    }

    public function testOpcodeBNNN(): void
    {
        [$cpu] = self::createCpu();
        $this->getRegisters($cpu)[0x0] = 0x2;
        $cpu->tick(0xbaa0);
        Assert::assertSame($this->getPc($cpu), 0xaa2);

        $cpu->tick(0xb001);
        Assert::assertSame($this->getPc($cpu), 0x003);

        // Overflow test
        $cpu->tick(0xbfff);
        Assert::assertSame($this->getPc($cpu), 0x001);
    }

    public function testScreenFramebuffer(): void
    {
        $data = file_get_contents(__DIR__ . '/../resource/test_opcode.ch8');
        $bufferOutput = new BufferedOutput();
        $screen = new Screen($bufferOutput);
        $cpu = new CPU($screen);
        $cpu->loadData($data);

        // Simulate the loading of the screen

        for ($i = 0; $i < 5; ++$i) {
            $cpu->tick();
        }

        $frameBuffer = (fn () => $this->frameBuffer)->call($screen);
        Assert::assertTrue(assert($frameBuffer instanceof \SplFixedArray));

        Assert::assertSame(0, $frameBuffer[0]);
        Assert::assertSame(0, $frameBuffer[63]);
        Assert::assertSame(0, $frameBuffer[64]);
        Assert::assertSame(0, $frameBuffer[65]);
        Assert::assertSame(0, $frameBuffer[66]);
        Assert::assertSame(0, $frameBuffer[78]);
        Assert::assertSame(0, $frameBuffer[79]);
        Assert::assertSame(0, $frameBuffer[80]);
        Assert::assertSame(0, $frameBuffer[2047]);

        for ($i = 0; $i < 8; ++$i) {
            $cpu->tick();
        }

        $frameBuffer = (fn () => $this->frameBuffer)->call($screen);
        Assert::assertTrue(assert($frameBuffer instanceof \SplFixedArray));

        Assert::assertSame(0, $frameBuffer[0]);
        Assert::assertSame(0, $frameBuffer[63]);
        Assert::assertSame(0, $frameBuffer[64]);
        Assert::assertSame(1, $frameBuffer[65]);
        Assert::assertSame(1, $frameBuffer[66]);
        Assert::assertSame(0, $frameBuffer[78]);
        Assert::assertSame(0, $frameBuffer[79]);
        Assert::assertSame(0, $frameBuffer[80]);
        Assert::assertSame(0, $frameBuffer[2047]);

        for ($i = 0; $i < 8; ++$i) {
            $cpu->tick();
        }
        $frameBuffer = (fn () => $this->frameBuffer)->call($screen);
        Assert::assertTrue(assert($frameBuffer instanceof \SplFixedArray));

        Assert::assertSame(0, $frameBuffer[0]);
        Assert::assertSame(0, $frameBuffer[1]);
        Assert::assertSame(0, $frameBuffer[2]);
        Assert::assertSame(0, $frameBuffer[63]);
        Assert::assertSame(0, $frameBuffer[64]);
        Assert::assertSame(1, $frameBuffer[65]);
        Assert::assertSame(1, $frameBuffer[66]);
        Assert::assertSame(1, $frameBuffer[67]);
        Assert::assertSame(0, $frameBuffer[68]);
        Assert::assertSame(1, $frameBuffer[69]);
        Assert::assertSame(0, $frameBuffer[70]);
        Assert::assertSame(1, $frameBuffer[71]);
        Assert::assertSame(0, $frameBuffer[72]);
        Assert::assertSame(0, $frameBuffer[73]);
        Assert::assertSame(1, $frameBuffer[74]);
        Assert::assertSame(1, $frameBuffer[75]);
        Assert::assertSame(1, $frameBuffer[76]);
        Assert::assertSame(0, $frameBuffer[77]);
        Assert::assertSame(1, $frameBuffer[78]);
        Assert::assertSame(0, $frameBuffer[79]);
        Assert::assertSame(1, $frameBuffer[80]);
        Assert::assertSame(0, $frameBuffer[81]);
        Assert::assertSame(0, $frameBuffer[2045]);
        Assert::assertSame(0, $frameBuffer[2046]);
        Assert::assertSame(0, $frameBuffer[2047]);
    }
}
