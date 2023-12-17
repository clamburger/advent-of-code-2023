<?php

namespace App\Puzzles;

use App\Puzzles\AbstractPuzzle;
use Illuminate\Support\Collection;
use Override;

class Day14ParabolicReflectorDish extends AbstractPuzzle
{
    protected static int $day_number = 14;

    private int $width;
    private int $height;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->width = $this->input->grid->first()->count();
        $this->height = $this->input->grid->count();

        $grid = $this->shiftGrid($this->input->grid->toArray(), 'y', true); // Up

        return $this->calculateLoad($grid);
    }

    private function calculateLoad(array $grid): int
    {
        $load = 0;

        foreach ($grid as $y => $row) {
            foreach ($row as $x => $char) {
                if ($char === 'O') {
                    $load += $this->height - $y;
                }
            }
        }

        return $load;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $this->width = $this->input->grid->first()->count();
        $this->height = $this->input->grid->count();

        $grid = $this->input->grid->toArray();

//        echo "Original: {$this->getGridHash($grid)}\n";

        $hashes = [];
        $hashHistory = [];
        $history = [];
        $cycleLength = $cycleStart = null;

        for ($i = 1; $i <= 1000; $i++) {
            $grid = $this->cycleGrid($grid);
            $hash = $this->getGridHash($grid);
            $history[$i] = $grid;
            $hashHistory[$i] = $hash;
//            echo "Cycle $i: {$hash}\n";

            if (isset($hashes[$hash])) {
//                echo "Seen this hash before at cycle {$hashes[$hash]}\n";
                if ($cycleLength === null) {
                    $cycleLength = $i - $hashes[$hash];
                    $cycleStart = $hashes[$hash];
//                    echo "Cycle length is $cycleLength, starting at $cycleStart\n";
                }
                break;
            } else {
                $hashes[$hash] = $i;
            }
        }

        if ($cycleLength === null) {
            throw new \Exception("Couldn't find cycle length");
        }

//        echo "Cycle length is $cycleLength, starting at $cycleStart\n";

        $futureCycle = 1000000000;
        $mod = $futureCycle % $cycleLength;
//
//        $fullCycles = ($futureCycle - $cycleStart) / $cycleLength;
//        $iter = $cycleStart + floor($cycleLength * $fullCycles);

        $expectedCycle = ($futureCycle - $cycleStart) % $cycleLength + $cycleStart;
//        echo "Full cycles: $fullCycles\n";
//        echo "Iter: $iter\n";
//        echo "Modulus: $mod\n";
//        echo "Expected match for cycle $futureCycle is $expectedCycle {$hashHistory[$expectedCycle]}\n";

        $grid = $history[$expectedCycle];
        return $this->calculateLoad($grid);
    }

    private function cycleGrid(array $grid): array
    {
        $grid = $this->shiftGrid($grid, 'y', true);  // U
        $grid = $this->shiftGrid($grid, 'x', true);  // L
        $grid = $this->shiftGrid($grid, 'y', false); // D
        $grid = $this->shiftGrid($grid, 'x', false); // R

        return $grid;
    }

    private function printGrid(array $grid): void
    {
        echo $this->gridToString($grid);
    }

    private function gridToString(array $grid): string
    {
        return implode("\n", array_map(fn ($row) => implode('', $row), $grid)) . "\n\n";
    }

    private function getGridHash(array $grid): string
    {
        return crc32($this->gridToString($grid));
    }

    private function shiftGrid(array $grid, string $axis, bool $backwards): array
    {
        $newGrid = $grid;

        for ($j = 0; $j < $this->width; $j++) {
            if ($backwards) {
                $nextSpot = 0;
                for ($i = 0; $i < $this->width; $i++) {
                    if ($axis === 'x') {
                        $char = $grid[$j][$i];
                    } else {
                        $char = $grid[$i][$j];
                    }

                    if ($char === 'O') {
                        if ($axis === 'x') {
                            $newGrid[$j][$i] = '.';
                            $newGrid[$j][$nextSpot] = $char;
                        } else {
                            $newGrid[$i][$j] = '.';
                            $newGrid[$nextSpot][$j] = $char;
                        }
                        $nextSpot++;
                    } elseif ($char === '#') {
                        if ($axis === 'x') {
                            $newGrid[$j][$i] = '#';
                        } else {
                            $newGrid[$i][$j] = '#';
                        }
                        $nextSpot = $i + 1;
                    } else {
                        if ($axis === 'x') {
                            $newGrid[$j][$i] = '.';
                        } else {
                            $newGrid[$i][$j] = '.';
                        }
                    }
                }
            } else {
                $nextSpot = $this->width - 1;
                for ($i = $this->width - 1; $i >= 0; $i--) {
                    if ($axis === 'x') {
                        $char = $grid[$j][$i];
                    } else {
                        $char = $grid[$i][$j];
                    }

                    if ($char === 'O') {
                        if ($axis === 'x') {
                            $newGrid[$j][$i] = '.';
                            $newGrid[$j][$nextSpot] = $char;
                        } else {
                            $newGrid[$i][$j] = '.';
                            $newGrid[$nextSpot][$j] = $char;
                        }
                        $nextSpot--;
                    } elseif ($char === '#') {
                        if ($axis === 'x') {
                            $newGrid[$j][$i] = '#';
                        } else {
                            $newGrid[$i][$j] = '#';
                        }
                        $nextSpot = $i - 1;
                    } else {
                        if ($axis === 'x') {
                            $newGrid[$j][$i] = '.';
                        } else {
                            $newGrid[$i][$j] = '.';
                        }
                    }
                }
            }
        }

        return $newGrid;
    }
}
