<?php declare(strict_types=1);

namespace BafS\Chip8\IO;

interface DisplayInterface
{
    /**
     * @return bool Return true if there is a collision
     */
    public function togglePixel(int $x, int $y): bool;

    /**
     * This method is called to draw the current screen after all pixels are toggled.
     *
     * Useful to not toggle a pixel directly and use a buffer.
     */
    public function draw(): void;

    /**
     * Clear display.
     */
    public function clear(): void;
}
