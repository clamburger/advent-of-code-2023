<?php

namespace App\Puzzles;

use Override;

class Day06WaitForIt extends AbstractPuzzle
{
    protected static int $day_number = 6;

    private array $races = [];

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->parseInput();

        $ways = [];

        foreach ($this->races as $race) {
            $time = $race['time'];
            $record = $race['record'];

            $waysToWin = 0;

            for ($holdTime = 1; $holdTime < $time; $holdTime++) {
                $moveTime = $time - $holdTime;
                $distance = $moveTime * $holdTime;

                if ($distance > $record) {
                    $waysToWin++;
                }
            }

            $ways[] = $waysToWin;
        }

        return collect($ways)->reduce(fn ($init, $carry) => $init * $carry, 1);
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $time = (int)(string)$this->input->lines->get(0)->remove('Time:')->replace(' ', '');
        $record = (int)(string)$this->input->lines->get(1)->remove('Distance:')->replace(' ', '');

        $first = 0;

        for ($holdTime = 1; $holdTime < $time; $holdTime++) {
            $moveTime = $time - $holdTime;
            $distance = $moveTime * $holdTime;

            if ($distance > $record) {
                $first = $holdTime;
                break;
            }
        }

        $last = $time - $first;

        return $last - $first + 1;
    }

    private function parseInput(): void
    {
        $this->races = [];

        $line1 = $this->input->lines->get(0)->matchAll('/(\d+)/');
        $line2 = $this->input->lines->get(1)->matchAll('/(\d+)/');

        foreach ($line1 as $index => $time) {
            $this->races[] = ['time' => (int)$time, 'record' => (int)$line2[$index]];
        }
    }
}
