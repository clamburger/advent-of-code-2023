<?php

namespace App\Puzzles;

use Override;

class Day08HauntedWasteland extends AbstractPuzzle
{
    protected static int $day_number = 8;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $instructions = str_split($this->input->lines_by_block->first()->first());

        $map = [];

        foreach ($this->input->lines_by_block->get(1) as $line) {
            [$from, $to] = $line->explode(' = ');
            [$left, $right] = explode(', ', trim($to, '()'));

            $map[$from] = ['L' => $left, 'R' => $right];
        }

        $count = 0;

        $at = 'AAA';
        $steps = $instructions;
        while ($at !== 'ZZZ') {
            if (empty($steps)) {
                $steps = $instructions;
            }
            $step = array_shift($steps);
            $at = $map[$at][$step];
            $count++;
        }

        return $count;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $instructions = str_split($this->input->lines_by_block->first()->first());

        $map = [];

        foreach ($this->input->lines_by_block->get(1) as $line) {
            [$from, $to] = $line->explode(' = ');
            [$left, $right] = explode(', ', trim($to, '()'));

            $map[$from] = ['L' => $left, 'R' => $right];
        }

        $count = 0;

        $ats = collect($map)->keys()->filter(fn ($s) => str_ends_with($s, 'A'))->values()->toArray();
        $steps = $instructions;

        $cycleDetector = [];
        $cycleFound = array_fill(0, count($ats), false);

        do {
            $count++;

            if (empty($steps)) {
                $steps = $instructions;
            }
            $step = array_shift($steps);


            foreach ($ats as $i =>  &$at) {
                $at = $map[$at][$step];
                if (!isset($cycleDetector[$i][$at])) {
                    $cycleDetector[$i][$at] = [];
                }

                if (count($cycleDetector[$i][$at]) === 1 && !$cycleFound[$i]) {
                    $start = $cycleDetector[$i][$at][0];
                    echo "[$i] Cycle detected starting at $count, first seen at $start / $at\n";
                    $cycleFound[$i] = ['start' => $start, 'end' => $count, 'at' => $at, 'length' => $count - $start + 1];
                }
                $cycleDetector[$i][$at][] = $count;
            }

            if (count(array_filter($cycleFound)) === count($ats)) {
                break;
            }

            $conditionsMet = collect($ats)->filter(fn ($s) => str_ends_with($s, 'Z'))->count() === count($ats);

//            if ($count % 100000 === 0) {
                echo $count . ". " . implode(", ", $ats) . "\n";
//            }

        } while (!$conditionsMet);

        dump($cycleFound);

        return $count;
    }
}
