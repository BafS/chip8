# CHIP-8 Emulator

<p align="center">
    <img width="400" src="https://i.imgur.com/vVZhncX.png">
</p>

> Chip 8 emulator coded in PHP 8, using Symfony "Console" package.

## Run in the terminal

- `composer i`
- `./bin/chip8 <yourgame.ch8>`

The default keyboard layout was mapped as follows:
```
native:      emulator:

1|2|3|C  ->  1|2|3|4
4|5|6|D  ->  Q|W|E|R
7|8|9|E  ->  A|S|D|F
A|0|B|F  ->  Z|X|C|V
```

## Tests

- `./vendor/bin/phpunit tests/ --testdox`

## Quality

### Static Analysis

- `./vendor/bin/phpstan analyse src/ -l 7`

### Syntax lint

- `./vendor/bin/phpcs --standard=PSR12 src/`

#### Documentation

- https://chip-8.github.io/
- https://github.com/mattmikolay/chip-8/wiki/CHIP%E2%80%908-Technical-Reference
- http://devernay.free.fr/hacks/chip8/C8TECH10.HTM

<p align="center">
    <img width="400" src="https://i.imgur.com/sZHA7r8.gif">
</p>
