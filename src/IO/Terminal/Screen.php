<?php declare(strict_types=1);

namespace BafS\Chip8\IO\Terminal;

use BafS\Chip8\IO\AudioInterface;
use BafS\Chip8\IO\DisplayInterface;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;

final class Screen implements DisplayInterface, AudioInterface
{
    private const DISPLAY_WIDTH = 64;
    private const DISPLAY_HEIGHT = 32;
    private const PIXEL_ON = '█';
    private const PIXEL_OFF = ' ';

    private const ASCII_BEL = "\x07";

    private readonly ?string $color;
    private readonly Cursor $cursor;
    private \SplFixedArray $frameBuffer;

    public function __construct(private readonly OutputInterface $output, array $options = [])
    {
        $this->color = $options['color'] ?? null;
        $this->cursor = $options['cursor'] ?? new Cursor($this->output);

        $this->frameBuffer = new \SplFixedArray(self::DISPLAY_HEIGHT * self::DISPLAY_WIDTH);
        $this->cursor->hide();
        $this->reset();
    }

    public function beep(): void
    {
        fwrite(STDOUT, self::ASCII_BEL);
    }

    public function reset(): void
    {
        foreach ($this->frameBuffer as $i => $_) {
            $this->frameBuffer[$i] = 0;
        }
    }

    public function togglePixel(int $x, int $y): bool
    {
        $idx = $x + $y * self::DISPLAY_WIDTH;

        $col = $this->frameBuffer[$idx];
        $this->frameBuffer[$idx] ^= 1;

        $char = $col === 0 ? self::PIXEL_ON : self::PIXEL_OFF;

        $this->cursor->moveToPosition($x + 1, $y);
        if ($this->color) {
            $char = '<fg=' . $this->color . '>' . $char . '</>';
        }

        $this->output->write($char);

        return (bool) $this->frameBuffer[$idx];
    }

    public function draw(): void
    {
        // For the terminal we draw directly when "toggle pixel"
    }

    public function clear(): void
    {
        $this->reset();
        $this->cursor->clearScreen();
    }

    public function resolution(): array
    {
        return [self::DISPLAY_WIDTH, self::DISPLAY_HEIGHT];
    }
}
