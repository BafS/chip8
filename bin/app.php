<?php declare(strict_types=1);

require './vendor/autoload.php';

use BafS\Chip8\CPU;
use BafS\Chip8\IO\Terminal\Gamepad;
use BafS\Chip8\IO\Terminal\Screen;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

/**
 * @internal
 */
final class StringHelper
{
    public static function registersToString(\SplFixedArray $registers): string
    {
        return implode(
            ' ',
            array_map(
                static fn (int $v) => self::prettyHex(dechex($v)),
                array_values($registers->toArray()),
            ),
        );
    }

    public static function prettyHex(string|int $val, int $length = 2): string
    {
        if (is_int($val)) {
            $val = dechex($val);
        }

        return str_pad($val, $length, '0', STR_PAD_LEFT);
    }
}

final class Chip8Command
{
    private function execute(InputInterface $input, OutputInterface $output): int
    {
        // Taken from
        // https://github.com/dbu/php-snake/blob/73ceeb5a8ae6771989588cc0dd6e9415ff0e5741/src/Command/SnakeCommand.php
        if ($input instanceof StreamableInputInterface && $stream = $input->getStream()) {
            $inputStream = $stream;
        } else {
            $inputStream = STDIN;
        }

        stream_set_blocking($inputStream, false);
        $sttyMode = shell_exec('stty -g');
        shell_exec('stty -icanon -echo');

        $filename = $input->getArgument('file');
        $data = file_get_contents($filename);

        $frequency = $input->getOption('frequency');

        $cursor = new Cursor($output);

        $screen = new Screen($output, [
            'cursor' => $cursor,
        ]);
        $gamepad = new Gamepad($inputStream);

        $debugCallback = null;
        if ($output->isVerbose()) {
            $debugCallback = function (CPU $cpu, int $opcode, array $opcodeInfo) use ($output, $cursor) {
                $cursor->moveToPosition(1, 34);
                $output->write([
                    "<info>PC</info>: " . StringHelper::prettyHex($this->pc, 4),
                    "    <info>SP</info>: " . StringHelper::prettyHex($this->sp & 0xffff),
                    "    <info>I</info>: " . StringHelper::prettyHex($this->i, 4),
                    "    <info>Opcode</info>: " . StringHelper::prettyHex($opcode),
                    " (" . $opcodeInfo['pattern'] . ")      ",
                ]);
                $output->writeln('');
                $output->write([
                    "<info>Registers</info>: " . StringHelper::registersToString($this->registers),
                ]);
                if ($output->isVeryVerbose()) {
                    $output->writeln('');
                    $output->write([
                        "<info>DeltaTimer</info>: " . StringHelper::prettyHex($this->deltaTimer, 4),
                        "    <info>SoundTimer</info>: " . StringHelper::prettyHex($this->soundTimer, 4),
                    ]);
                }
            };
        }

        $cpu = new CPU($screen, $screen, $gamepad, [
            'frequency' => $frequency ? +$frequency : null,
            'debug_callback' => $debugCallback,
        ]);
        $cpu->loadData($data);
        $cpu->run();

        stream_set_blocking($inputStream, true);
        shell_exec(sprintf('stty %s', $sttyMode));

        return SingleCommandApplication::SUCCESS;
    }

    public function run(): int
    {
        return (new SingleCommandApplication())
            ->addArgument('file', InputArgument::OPTIONAL, 'The rom')
            ->addOption('frequency', 'f', InputOption::VALUE_REQUIRED)
            ->setCode($this->execute(...))
            ->run();
    }
}

$ret = (new Chip8Command)->run();
exit($ret);
