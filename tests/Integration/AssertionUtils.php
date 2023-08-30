<?php

declare(strict_types=1);

namespace Test\Integration;

use PHPUnit\Framework\Assert;

class AssertionUtils
{
    private function __construct()
    {
    }

    public static function assertRange(int $from, int $to, mixed $expected, array|\ArrayAccess $buffer): void {
        for ($i = $from; $i < $to; ++$i) {
            $current = $buffer[$i];
            Assert::assertSame($expected, $current, "$expected is not $current (index $i).");
        }
    }

    public static function assertScreenBuffer(string $stringScreen, \SplFixedArray $frameBuffer): void
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
}
