<?php declare(strict_types=1);

namespace Test\Integration;

use BafS\Chip8\CPU;
use BafS\Chip8\IO\Terminal\Screen;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class EmulationScreenTest extends TestCase
{
    private static function assertRange(int $from, int $to, mixed $expected, array|\ArrayAccess $buffer): void {
        for ($i = $from; $i < $to; ++$i) {
            $current = $buffer[$i];
            Assert::assertSame($expected, $current, "$expected is not $current (index $i).");
        }
    }

    private static function assertScreenBuffer(string $stringScreen, \SplFixedArray $frameBuffer): void
    {
        $lines = explode("\n", $stringScreen);
        $lines = preg_replace(['/█/u', '/ /'], ['1', '0'], $lines);

        $lines = array_map(
            callback: static fn (string $l) => str_pad(
                $l,
                64,
                '0',
            ),
            array: $lines,
        );

        $bufferExpected = implode('', $lines);

        $strLen = strlen($bufferExpected);
        for ($i = 0; $i < $strLen; ++$i) {
            Assert::assertSame((int) $bufferExpected[$i], $frameBuffer[$i], "Pixel comparison failed (position: $i)");
        }
    }

    public function testScreenFramebufferWithTestOpcode(): void
    {
        $frameBuffer = new \SplFixedArray(64 * 32);
        $screen = new Screen(new BufferedOutput(), options: [
            'frame_buffer' => $frameBuffer,
        ]);
        $cpu = new CPU($screen);
        $cpu->loadData(file_get_contents(__DIR__ . '/resource/test_opcode.ch8'));

        // Simulate the loading of the screen
        for ($i = 0; $i < 5; ++$i) {
            $cpu->tick();
        }

        // Screen should be fully black
        AssertionUtils::assertRange(0, 2048, 0, $frameBuffer);

        for ($i = 0; $i < 8; ++$i) {
            $cpu->tick();
        }

        AssertionUtils::assertRange(0, 64, 0, $frameBuffer);
        Assert::assertSame(1, $frameBuffer[65]);
        Assert::assertSame(1, $frameBuffer[66]);
        Assert::assertSame(1, $frameBuffer[67]);
        Assert::assertSame(0, $frameBuffer[68]);
        Assert::assertSame(1, $frameBuffer[69]);
        Assert::assertSame(0, $frameBuffer[70]);
        Assert::assertSame(1, $frameBuffer[71]);
        Assert::assertSame(0, $frameBuffer[72]);
        Assert::assertSame(0, $frameBuffer[73]);
        Assert::assertSame(0, $frameBuffer[74]);
        Assert::assertSame(1, $frameBuffer[261]);
        Assert::assertSame(0, $frameBuffer[262]);
        Assert::assertSame(1, $frameBuffer[263]);
        AssertionUtils::assertRange(264, 2048, 0, $frameBuffer);

        for ($i = 0; $i < 8; ++$i) {
            $cpu->tick();
        }

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

    public function testScreenFramebufferWithCorax(): void
    {
        $frameBuffer = new \SplFixedArray(64 * 32);
        $screen = new Screen(new BufferedOutput(), options: [
            'frame_buffer' => $frameBuffer,
        ]);
        $cpu = new CPU($screen);
        $cpu->loadData(file_get_contents(__DIR__ . '/resource/3-corax+.ch8'));

        // Simulate the loading of the screen
        for ($i = 0; $i < 5; ++$i) {
            $cpu->tick();
        }

        // Screen should start fully black
        AssertionUtils::assertRange(0, 2048, 0, $frameBuffer);

        for ($i = 0; $i < 5; ++$i) {
            $cpu->tick();
        }

        // "v4.0" is the first thing displayed
        $screen = <<<SCREEN


























                                                              █ █   ███
                                                          █ █ ███   █ █
                                                          █ █   █   █ █
                                                           █    █ █ ███


        SCREEN;

        AssertionUtils::assertScreenBuffer($screen, $frameBuffer);

        for ($i = 0; $i < 280; ++$i) {
            $cpu->tick();
        }

        $screen = <<<SCREEN

          ███ █ █         ███ █ █         ███ █ █         ███ ███
           ██  █   █ █      █  █   █ █    ███ ███  █ █    █   ██   █ █
            █ █ █  ██     ██  █ █  ██     █ █   █  ██     ██    █  ██
          ███ █ █  █      ███ █ █  █      ███   █  █      █   ██   █

          █ █ █ █         ███ ███         ███ ███         ███ ███
          ███  █   █ █    █ █ ██   █ █    ███ ██   █ █    █    ██  █ █
            █ █ █  ██     █ █ █    ██     █ █   █  ██     ██    █  ██
            █ █ █  █      ███ ███  █      ███ ██   █      █   ███  █

          ███ █ █         ███ ███         ███ ███         ███ ███
          ██   █   █ █    ███ █ █  █ █    ███   █  █ █    █   ██   █ █
            █ █ █  ██     █ █ █ █  ██     █ █  █   ██     ██  █    ██
          ██  █ █  █      ███ ███  █      ███  █   █      █   ███  █

          ███ █ █         ███ ██          ███  ██             █ █
            █  █   █ █    ███  █   █ █    ███ █    █ █    █ █  █   █ █
           █  █ █  ██     █ █  █   ██     █ █ ███  ██     █ █ █ █  ██
           █  █ █  █      ███ ███  █      ███ ███  █       █  █ █  █

          ███ █ █         ███ ███         ███ ███
          ███  █   █ █    ███   █  █ █    ███ ██   █ █
            █ █ █  ██     █ █ ██   ██     █ █ █    ██
          ██  █ █  █      ███ ███  █      ███ ███  █

          ██  █ █         ███ ███         ███  ██             █ █   ███
           █   █   █ █    ███  ██  █ █    █   █    █ █    █ █ ███   █ █
           █  █ █  ██     █ █   █  ██     ██  ███  ██     █ █   █   █ █
          ███ █ █  █      ███ ███  █      █   ███  █       █    █ █ ███

        SCREEN;

        AssertionUtils::assertScreenBuffer($screen, $frameBuffer);

        // Screen should not change anymore, we can keep ticking
        for ($i = 0; $i < 100; ++$i) {
            $cpu->tick();
        }

        AssertionUtils::assertScreenBuffer($screen, $frameBuffer);
    }

    public function testScreenFramebufferWithFlags(): void
    {
        $frameBuffer = new \SplFixedArray(64 * 32);
        $screen = new Screen(new BufferedOutput(), options: [
            'frame_buffer' => $frameBuffer,
        ]);
        $cpu = new CPU($screen);
        $cpu->loadData(file_get_contents(__DIR__ . '/resource/4-flags.ch8'));

        // Simulate the loading of the screen
        for ($i = 0; $i < 5; ++$i) {
            $cpu->tick();
        }

        // Screen should start fully black
        AssertionUtils::assertRange(0, 2048, 0, $frameBuffer);

        $screen = <<<SCREEN
        █ █  █  ██  ██  █ █   ██                    ███
        ███ █ █ █ █ █ █ █ █    █   █ █ █ █ █ █        █  █ █ █ █ █ █
        █ █ ███ ██  ██   █     █   ██  ██  ██       ██   ██  ██  ██
        █ █ █ █ █   █    █    ███  █   █   █        ███  █   █   █

        ███                   █ █                   ███
         ██  █ █ █ █ █ █      ███  █ █ █ █ █ █ █ █  ██   █ █ █ █ █ █ █ █
          █  ██  ██  ██         █  ██  ██  ██   █     █  ██  ██   █   █
        ███  █   █   █          █  █   █   █   █ █  ██   █   █   █ █ █ █

        ███                   ███                   ███
        █    █ █ █ █ █ █        █  █ █ █ █ █ █ █ █  ██   █ █ █ █ █ █
        ███  ██  ██  ██         █  ██  ██   █   █   █    ██  ██  ██
        ███  █   █   █          █  █   █   █ █ █ █  ███  █   █   █


        ███  █  ██  ██  █ █   █ █                   ███
        █   █ █ █ █ █ █ █ █   ███  █ █ █ █ █ █ █ █  ██   █ █ █ █ █ █ █ █
        █   ███ ██  ██   █      █  ██  ██  ██   █     █  ██  ██   █   █
        ███ █ █ █ █ █ █  █      █  █   █   █   █ █  ██   █   █   █ █ █ █

        ███                   ███                   ███
        █    █ █ █ █ █ █        █  █ █ █ █ █ █ █ █  ██   █ █ █ █ █ █
        ███  ██  ██   █         █  ██  ██   █   █   █    ██  ██   █
        ███  █   █   █ █        █  █   █   █ █ █ █  ███  █   █   █ █


        ███ ███ █ █ ███ ██    ███ ███                         █ █   ███
        █ █  █  ███ ██  █ █   █   ██   █ █ █ █            █ █ ███   █ █
        █ █  █  █ █ █   ██    ██  █    ██  ██             █ █   █   █ █
        ███  █  █ █ ███ █ █   █   ███  █   █               █    █ █ ███
        SCREEN;

        for ($i = 0; $i < 1000; ++$i) {
            $cpu->tick();
        }

        AssertionUtils::assertScreenBuffer($screen, $frameBuffer);

        // Screen should not change anymore, we can keep ticking
        for ($i = 0; $i < 1000; ++$i) {
            $cpu->tick();
        }

        AssertionUtils::assertScreenBuffer($screen, $frameBuffer);
    }
}
