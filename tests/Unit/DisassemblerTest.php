<?php declare(strict_types=1);

namespace Test\Unit;

use BafS\Chip8\Disassembler;
use PHPUnit\Framework\Assert;

final class DisassemblerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider opcodePatternData
     */
    public function testOpcodePatterns(int $opcode, string $pattern): void
    {
        $disassembler = new Disassembler();

        Assert::assertSame($pattern, $disassembler->disassemble($opcode)['pattern']);
    }

    public function opcodePatternData(): iterable
    {
        yield '0x00e0' => [0x00e0, '00E0'];
        yield '0x00ee' => [0x00ee, '00EE'];
        yield '0x0eee' => [0x0eee, '0NNN'];
        yield '0x0123' => [0x0123, '0NNN'];
        yield '0x1333' => [0x1333, '1NNN'];
        yield '0x6555' => [0x6555, '6XNN'];
        yield '0x8000' => [0x8000, '8XY0'];
        yield '0x800e' => [0x800e, '8XYE'];
        yield '0xa000' => [0xa000, 'ANNN'];
        yield '0xaaaa' => [0xaaaa, 'ANNN'];
        yield '0xafff' => [0xafff, 'ANNN'];
        yield '0xa123' => [0xa123, 'ANNN'];
        yield '0x4444' => [0x4444, '4XNN'];
        yield '0xe09e' => [0xe09e, 'EX9E'];
        yield '0xef9e' => [0xef9e, 'EX9E'];
        yield '0xf018' => [0xf018, 'FX18'];
        yield '0xf118' => [0xf118, 'FX18'];
        yield '0xf00a' => [0xf00a, 'FX0A'];
        yield '0xff0a' => [0xff0a, 'FX0A'];
        yield '0xf055' => [0xf055, 'FX55'];
        yield '0xf555' => [0xf555, 'FX55'];
        yield '0xff55' => [0xff55, 'FX55'];
    }

    /**
     * @dataProvider opcodeArgsData
     */
    public function testOpcodeArgs(int $opcode, array $args): void
    {
        $disassembler = new Disassembler();

        $argsDis = $disassembler->disassemble($opcode)['args'];

        Assert::assertEquals($args, $argsDis);
    }

    public function opcodeArgsData(): iterable
    {
        yield '0x00e0' => [0x00e0, []];
        yield '0x00ee' => [0x00ee, []];
        yield '0x0eee' => [0x0eee, [
            'N' => 0xeee,
        ]];
        yield '0x0123' => [0x0123, [
            'N' => 0x123,
        ]];
        yield '0x1333' => [0x1333, [
            'N' => 0x333,
        ]];
        yield '0x6555' => [0x6555, [
            'X' => 0x5,
            'N' => 0x55,
        ]];
        yield '0x8000' => [0x8000, [
            'X' => 0x0,
            'Y' => 0x0,
        ]];
        yield '0x8f10' => [0x8f10, [
            'X' => 0xf,
            'Y' => 0x1,
        ]];
    }
}
