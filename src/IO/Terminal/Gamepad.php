<?php declare(strict_types=1);

namespace BafS\Chip8\IO\Terminal;

use BafS\Chip8\IO\GamepadInterface;

final class Gamepad implements GamepadInterface
{
    private int $lastPressedIndex = 0;
    private ?int $lastPressed = null;
    private readonly array $keymap;

    public function __construct(private $inputStream, ?array $keymap = null)
    {
        $this->keymap = array_flip($keymap ?? [
            '1', '2', '3', '4',
            'q', 'w', 'e', 'r',
            'a', 's', 'd', 'f',
            'z', 'x', 'c', 'v',
        ]);
    }

    private function getChar(): ?string
    {
        return fread($this->inputStream, 1);
    }

    public function read(): void
    {
        // Simulate a "keep pressed" key
        $this->lastPressedIndex++;
        $nextChar = $this->getChar();

        $pressed = $this->keymap[$nextChar] ?? null;

        if ($pressed !== null && $this->lastPressed === $pressed) {
            $this->lastPressedIndex = 0;
        }

        if ($this->lastPressedIndex > 40) {
            $this->lastPressedIndex = 0;
            $this->lastPressed = null;
        }

        $this->lastPressed = $pressed ?? $this->lastPressed;
    }

    public function getPressedKey(): ?int
    {
        return $this->lastPressed;
    }
}
