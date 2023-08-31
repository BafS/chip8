<?php

declare(strict_types=1);

namespace BafS\Chip8;

/**
 * Standard Chip-8 Instructions.
 *
 * Based on http://devernay.free.fr/hacks/chip8/C8TECH10.HTM#3.0
 */
enum Opcodes: string
{
    /** System call (ignored) */
    case SYS_0NNN = '0NNN';
    /** Clear the screen */
    case CLS_00E0 = '00E0';
    /** Return from subroutine */
    case RET_00EE = '00EE';
    /** Jump to address nnn */
    case JP_1NNN = '1NNN';
    /** Call routine at address nnn */
    case CALL_2NNN = '2NNN';
    /** Skip next instruction if register Vx equals nn */
    case SE_3XNN = '3XNN';
    /** Do not skip next instruction if register Vx equals nn */
    case SNE_4XNN = '4XNN';
    /** Skip if register Vx equals register Vy */
    case SE_5XY0 = '5XY0';
    /** Load register Vx with value nn */
    case LD_6XNN = '6XNN';
    /** Add value nn to register Vx */
    case ADD_7XNN = '7XNN';
    /** Move value from register Vx to register Vy */
    case LD_8XY0 = '8XY0';
    /** Perform logical OR on register Vx and Vy and store in Vy */
    case OR_8XY1 = '8XY1';
    /** Perform logical AND on register Vx and Vy and store in Vy */
    case AND_8XY2 = '8XY2';
    /** Perform logical XOR on register Vx and Vy and store in Vy */
    case XOR_8XY3 = '8XY3';
    /** Add Vx to Vy and store in Vx - register F set on carry */
    case ADD_8XY4 = '8XY4';
    /** Subtract Vx from Vy and store in Vy - register F set on !borrow */
    case SUB_8XY5 = '8XY5';
    /** Shift bits in Vx 1 bit right, store in Vy - bit 0 shifts to register F */
    case SHR_8XY6 = '8XY6';
    /** Set Vx = Vy - Vx, set VF = NOT borrow */
    case SUBN_8XY7 = '8XY7';
    /** Shift bits in Vx 1 bit left, store in Vy - bit 7 shifts to register F */
    case SHL_8XYE = '8XYE';
    /** Skip next instruction if register Vx not equal register Vy */
    case SNE_9XY0 = '9XY0';
    /** The value of register I is set to nnn */
    case LD_ANNN = 'ANNN';
    /** Jump to location nnn + V0 */
    case JP_BNNN = 'BNNN';
    /** Set Vx = random byte AND kk */
    case RND_CXNN = 'CXNN';
    /** Display n-byte sprite starting at memory location I at (Vx, Vy), set VF = collision */
    case DRW_DXYN = 'DXYN';
    /** Skip next instruction if key with the value of Vx is pressed */
    case SKP_EX9E = 'EX9E';
    /** Skip next instruction if key with the value of Vx is not pressed */
    case SKNP_EXA1 = 'EXA1';
    /** Set Vx = delay timer value */
    case LD_FX07 = 'FX07';
    /** Wait for a key press, store the value of the key in Vx */
    case LD_FX0A = 'FX0A';
    /** Set delay timer = Vx */
    case LD_FX15 = 'FX15';
    /** Set sound timer = Vx */
    case LD_FX18 = 'FX18';
    /** Set I = I + Vx */
    case ADD_FX1E = 'FX1E';
    /** Set I = location of sprite for digit Vx */
    case LD_FX29 = 'FX29';
    /** Store BCD representation of Vx in memory locations I, I+1, and I+2 */
    case LD_FX33 = 'FX33';
    /** Store registers V0 through Vx in memory starting at location I */
    case LD_FX55 = 'FX55';
    /** Read registers V0 through Vx from memory starting at location I */
    case LD_FX65 = 'FX65';
}
