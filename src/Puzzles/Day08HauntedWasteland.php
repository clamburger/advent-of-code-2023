<?php

namespace App\Puzzles;

use Override;

class Day08HauntedWasteland extends AbstractPuzzle
{
    protected static int $day_number = 8;

    private array $map;
    private array $instructions;

    private array $cycles = [];

    private function parseInput()
    {
        $this->instructions = str_split($this->input->lines_by_block->first()->first());
        $this->map = [];

        foreach ($this->input->lines_by_block->get(1) as $line) {
            [$from, $to] = $line->explode(' = ');
            [$left, $right] = explode(', ', trim($to, '()'));

            $this->map[$from] = ['L' => $left, 'R' => $right];
        }
    }

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->parseInput();

        $count = 0;

        $at = 'AAA';
        $steps = $this->instructions;
        while ($at !== 'ZZZ') {
            if (empty($steps)) {
                $steps = $this->instructions;
            }
            $step = array_shift($steps);
            $at = $this->map[$at][$step];
            $count++;
        }

        return $count;
    }

    private function findCycleLength(string $start, int $index)
    {
        $at = $start;

        $last = -1;

        $instances = [];

        $i = 0;
        while (count($instances) < 10) {
            $step = $this->instructions[$i % count($this->instructions)];
            $at = $this->map[$at][$step];

            if (str_ends_with($at, 'Z')) {
//                echo "[$index] Found Z: $at, $i (diff " . ($i - $last) . ")\n";
                $instances[] = $i;
                $last = $i;
            }

            $i++;
        }

        // no particular reason to do it this way other than it was easy with what I already had
        return $instances[9] - $instances[8];
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $this->parseInput();

        $starts = collect($this->map)->keys()->filter(fn ($s) => str_ends_with($s, 'A'))->values()->toArray();

        $cycleLengths = [];

        foreach ($starts as $index => $start) {
            $cycleLengths[] = $this->findCycleLength($start, $index);
        }

        return array_reduce($cycleLengths, fn (int $carry, int $value) => self::lcm($carry, $value), 1);
    }

    public static function lcm(int $a, int $b): int
    {
        return $a * $b / self::gcd($a, $b);
    }

    /**
     * Euclid's algorithm
     *
     * Not entirely sure how this works, copied from Wikipedia
     */
    private static function gcd(int $a, int $b): int
    {
        while ($b !== 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }

        return $a;
    }
}
