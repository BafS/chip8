<?php declare(strict_types=1);

namespace BafS\Chip8;

/**
 * Standard Chip-8 Instructions.
 *
 * Based on http://devernay.free.fr/hacks/chip8/C8TECH10.HTM#3.0
 */
class Opcodes
{
    /** System call (ignored) */
    public const SYS_0NNN = '0NNN';
    /** Clear the screen */
    public const CLS_00E0 = '00E0';
    /** Return from subroutine */
    public const RET_00EE = '00EE';
    /** Jump to address nnn */
    public const JP_1NNN = '1NNN';
    /** Call routine at address nnn */
    public const CALL_2NNN = '2NNN';
    /** Skip next instruction if register Vx equals nn */
    public const SE_3XNN = '3XNN';
    /** Do not skip next instruction if register Vx equals nn */
    public const SNE_4XNN = '4XNN';
    /** Skip if register Vx equals register Vy */
    public const SE_5XY0 = '5XY0';
    /** Load register Vx with value nn */
    public const LD_6XNN = '6XNN';
    /** Add value nn to register Vx */
    public const ADD_7XNN = '7XNN';
    /** Move value from register Vx to register Vy */
    public const LD_8XY0 = '8XY0';
    /** Perform logical OR on register Vx and Vy and store in Vy */
    public const OR_8XY1 = '8XY1';
    /** Perform logical AND on register Vx and Vy and store in Vy */
    public const AND_8XY2 = '8XY2';
    /** Perform logical XOR on register Vx and Vy and store in Vy */
    public const XOR_8XY3 = '8XY3';
    /** Add Vx to Vy and store in Vx - register F set on carry */
    public const ADD_8XY4 = '8XY4';
    /** Subtract Vx from Vy and store in Vy - register F set on !borrow */
    public const SUB_8XY5 = '8XY5';
    /** Shift bits in Vx 1 bit right, store in Vy - bit 0 shifts to register F */
    public const SHR_8XY6 = '8XY6';
    /** Set Vx = Vy - Vx, set VF = NOT borrow */
    public const SUBN_8XY7 = '8XY7';
    /** Shift bits in Vx 1 bit left, store in Vy - bit 7 shifts to register F */
    public const SHL_8XYE = '8XYE';
    /** Skip next instruction if register Vx not equal register Vy */
    public const SNE_9XY0 = '9XY0';
    /** The value of register I is set to nnn */
    public const LD_ANNN = 'ANNN';
    /** Jump to location nnn + V0 */
    public const JP_BNNN = 'BNNN';
    /** Set Vx = random byte AND kk */
    public const RND_CXNN = 'CXNN';
    /** Display n-byte sprite starting at memory location I at (Vx, Vy), set VF = collision */
    public const DRW_DXYN = 'DXYN';
    /** Skip next instruction if key with the value of Vx is pressed */
    public const SKP_EX9E = 'EX9E';
    /** Skip next instruction if key with the value of Vx is not pressed */
    public const SKNP_EXA1 = 'EXA1';
    /** Set Vx = delay timer value */
    public const LD_FX07 = 'FX07';
    /** Wait for a key press, store the value of the key in Vx */
    public const LD_FX0A = 'FX0A';
    /** Set delay timer = Vx */
    public const LD_FX15 = 'FX15';
    /** Set sound timer = Vx */
    public const LD_FX18 = 'FX18';
    /** Set I = I + Vx */
    public const ADD_FX1E = 'FX1E';
    /** Set I = location of sprite for digit Vx */
    public const LD_FX29 = 'FX29';
    /** Store BCD representation of Vx in memory locations I, I+1, and I+2 */
    public const LD_FX33 = 'FX33';
    /** Store registers V0 through Vx in memory starting at location I */
    public const LD_FX55 = 'FX55';
    /** Read registers V0 through Vx from memory starting at location I */
    public const LD_FX65 = 'FX65';
}
