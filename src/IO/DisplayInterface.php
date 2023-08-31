<?php

declare(strict_types=1);

namespace BafS\Chip8\IO;

interface DisplayInterface
{
    /**
     * Toggle ON or OFF a given pixel.
     *
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

    /**
     * @return array{int, int} [width, height] resolution
     */
    public function resolution(): array;
}
