<?php declare(strict_types=1);

namespace BafS\Chip8\IO;

interface GamepadInterface
{
    /**
     * This method is called for every CPU "tick".
     */
    public function read(): void;

    /**
     * Get pressed key.
     */
    public function getPressedKey(): ?int;
}
