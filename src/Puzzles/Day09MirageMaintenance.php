<?php

namespace App\Puzzles;

use App\Puzzles\AbstractPuzzle;
use Override;

class Day09MirageMaintenance extends AbstractPuzzle
{
    protected static int $day_number = 9;
    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $sum = 0;

        foreach ($this->input->lines as $line) {
            $values = $line->explode(' ')->map(fn ($i) => (int)$i)->toArray();

            $allZero = false;

            $history = [
                $values
            ];

            while (!$allZero) {
//                echo implode(" ", $values) . "\n";
                $values = $this->getSequenceDiff($values);
                $history[] = $values;
                $allZero = count(array_filter($values, fn ($v) => $v !== 0)) === 0;
            }

            $result = array_reverse($this->extrapolate($history));
            $sum += $result[0][array_key_last($result[0])];
        }

        return $sum;
    }

    private function getSequenceDiff(array $nums): array
    {
        $result = [];

        for ($i = 0; $i < count($nums) - 1; $i++) {
            $result[] = $nums[$i + 1] - $nums[$i];
        }

        return $result;
    }

    private function extrapolate(array $history): array
    {
        $history = array_reverse($history);
        $history[0][] = 0;

        for ($i = 1; $i < count($history); $i++) {
            $history[$i][] = $history[$i][array_key_last($history[$i])] + $history[$i-1][array_key_last($history[$i])];
        }

        return $history;
    }

    private function extrapolateBackwards(array $history): array
    {
        $history = array_reverse($history);
        $history[0][-1] = 0;
        ksort($history[0]);

        for ($i = 1; $i < count($history); $i++) {
            $history[$i][-1] = $history[$i][array_key_first($history[$i])] + -$history[$i-1][array_key_first($history[$i-1])];
            ksort($history[$i]);
        }

        return $history;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $sum = 0;

        foreach ($this->input->lines as $line) {
            $values = $line->explode(' ')->map(fn ($i) => (int)$i)->toArray();

            $allZero = false;

            $history = [
                $values
            ];

            while (!$allZero) {
//                echo implode(" ", $values) . "\n";
                $values = $this->getSequenceDiff($values);
                $history[] = $values;
                $allZero = count(array_filter($values, fn ($v) => $v !== 0)) === 0;
            }

            $result = array_reverse($this->extrapolateBackwards($history));
            $sum += $result[0][array_key_first($result[0])];
        }

        return $sum;
    }
}
