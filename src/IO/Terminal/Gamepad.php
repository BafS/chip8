<?php declare(strict_types=1);

namespace BafS\Chip8\IO\Terminal;

use BafS\Chip8\IO\GamepadInterface;

final class Gamepad implements GamepadInterface
{
    private int $lastPressedIndex = 0;
    private ?int $lastPressed = null;
    /** @var non-empty-array<string, int> */
    private readonly array $keymap;

    /**
     * @param resource $inputStream
     * @param non-empty-list<string>|null $keymap
     */
    public function __construct(private readonly mixed $inputStream, ?array $keymap = null)
    {
        if (!is_resource($this->inputStream)) {
            $actualType = get_debug_type($this->inputStream);
            throw new \InvalidArgumentException("\$inputStream must be of type resource ($actualType given).");
        }

        $this->keymap = array_flip($keymap ?? [
            'x', '1', '2', '3', 'q', 'w', 'e', 'a', 's', 'd', 'z', 'c', '4', 'r', 'f', 'v',
        ]);
    }

    private function getChar(): string|false
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
