<?php

namespace App\Puzzles;

use App\Puzzles\AbstractPuzzle;
use Override;

class Day14ParabolicReflectorDish extends AbstractPuzzle
{
    protected static int $day_number = 14;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $width = $this->input->grid->first()->count();
        $maxLoad = $this->input->grid->count();

        $totalLoad = 0;

        for ($x = 0; $x < $width; $x++) {
            $col = $this->input->grid->pluck($x);

            $rockPositions = collect();
            $nextSpot = 0;

            foreach ($col as $y => $char) {
                if ($char === 'O') {
                    $rockPositions[] = $nextSpot;
                    $nextSpot++;
                }

                if ($char === '#') {
                    $nextSpot = $y + 1;
                }
            }

            $load = $rockPositions->reduce(fn ($carry, $value) => $carry + ($maxLoad - $value), 0);
            $totalLoad += $load;
        }

        return $totalLoad;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        return 0;
    }
}
