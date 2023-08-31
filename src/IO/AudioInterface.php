<?php

declare(strict_types=1);

namespace BafS\Chip8\IO;

interface AudioInterface
{
    public function beep(): void;
}
