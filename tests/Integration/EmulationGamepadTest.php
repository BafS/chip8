<?php declare(strict_types=1);

namespace Test\Integration;

use BafS\Chip8\CPU;
use BafS\Chip8\IO\Terminal\Gamepad;
use BafS\Chip8\IO\Terminal\Screen;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Test\Integration\AssertionUtils;

final class EmulationGamepadTest extends TestCase
{
    public function testGamepadWithKeypadRom(): void
    {
        $frameBuffer = new \SplFixedArray(64 * 32);
        $screen = new Screen(new BufferedOutput(), options: [
            'frame_buffer' => $frameBuffer,
        ]);

        $handle = fopen("php://memory", 'rwb+');

        $gamepad = new Gamepad($handle);
        $cpu = new CPU($screen, gamepad: $gamepad);
        $cpu->loadData(file_get_contents(__DIR__ . '/resource/6-keypad.ch8'));

        // Simulate the loading of the screen
        for ($i = 0; $i < 5; ++$i) {
            $cpu->tick();
        }

        // Screen should be fully black
        AssertionUtils::assertRange(0, 2048, 0, $frameBuffer);

        for ($i = 0; $i < 500; ++$i) {
            $cpu->tick();
        }

        $screen = <<<SCREEN


                  ██  ███ ███ █ █     ███ ██  ███ ███ ██  ███
                  █ █  █  █   ██      █ █ █ █ █   █ █ █ █ ██
                  ██   █  █   █ █     █ █ ██  █   █ █ █ █ █
                  █   ███ ███ █ █     ███ █   ███ ███ ██  ███




                ██      ███ █ █ ███ ███     ██  ███ █ █ ██
            ██   █      ██   █  ███ ██      █ █ █ █ █ █ █ █
            ██   █      █   █ █   █ █       █ █ █ █ ███ █ █
                ███     ███ █ █ ███ ███     ██  ███ ███ █ █

                ███     ███ █ █  █  ██      █ █ ██
                  █     ██   █  █ █  █      █ █ █ █
                ██      █   █ █ ███  █      █ █ ██
                ███     ███ █ █ █ █ ███      ██ █

                ███     ███ █ █ ███  █       ██ ███ ███ █ █ ███ █ █
                 ██     █    █  █ █ █ █     █   ██   █  ██  ██  █ █
                  █     ██  █ █ █ █ ███     █ █ █    █  █ █ █    █
                ███     █   █ █ ███ █ █      ██ ███  █  █ █ ███  █



                                                              █ █   ███
                                                          █ █ ███   █ █
                                                          █ █   █   █ █
                                                           █    █ █ ███

        SCREEN;

        AssertionUtils::assertScreenBuffer($screen, $frameBuffer);

        fwrite($handle, '1');
        rewind($handle);
        for ($i = 0; $i < 500; ++$i) {
            $gamepad->read();
            $cpu->tick();
        }
        rewind($handle);

        $screenKeysBase = <<<SCREEN



                          ██      ███     ███     ███
                           █        █      ██     █
                           █      ██        █     █
                          ███     ███     ███     ███



                          █ █     ███     ███     ██
                          ███     ██      █       █ █
                            █       █     ███     █ █
                            █     ██      ███     ██



                          ███     ███     ███     ███
                            █     ███     ███     ██
                            █     █ █       █     █
                            █     ███     ███     ███



                           █      ███     ██      ███
                          █ █     █ █     ███     █
                          ███     █ █     █ █     ██
                          █ █     ███     ███     █

        SCREEN;

        AssertionUtils::assertScreenBuffer($screenKeysBase, $frameBuffer);

        // We keep "2" pressed
        for ($i = 0; $i < 200; ++$i) {
            fwrite($handle, '2');
            rewind($handle);
            $gamepad->read();
            $cpu->tick();
        }

        $screen2Pressed = <<<SCREEN


                                ███████
                          ██    ██   ██   ███     ███
                           █    ████ ██    ██     █
                           █    ██  ███     █     █
                          ███   ██   ██   ███     ███
                                ███████


                          █ █     ███     ███     ██
                          ███     ██      █       █ █
                            █       █     ███     █ █
                            █     ██      ███     ██



                          ███     ███     ███     ███
                            █     ███     ███     ██
                            █     █ █       █     █
                            █     ███     ███     ███



                           █      ███     ██      ███
                          █ █     █ █     ███     █
                          ███     █ █     █ █     ██
                          █ █     ███     ███     █

        SCREEN;

        AssertionUtils::assertScreenBuffer($screen2Pressed, $frameBuffer);

        // We release it
        for ($i = 0; $i < 200; ++$i) {
            $gamepad->read();
            $cpu->tick();
        }

        AssertionUtils::assertScreenBuffer($screenKeysBase, $frameBuffer);
    }
}
