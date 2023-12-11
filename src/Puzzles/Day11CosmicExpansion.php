<?php

namespace App\Puzzles;

use Illuminate\Support\Collection;
use Override;

class Day11CosmicExpansion extends AbstractPuzzle
{
    protected static int $day_number = 11;

    private array $emptyRows;
    private array $emptyColumns;
    private Collection $joins;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->parseInput();

        $total = 0;

        foreach ($this->joins as $pair) {
            $total += $this->calculateDiff($pair[0], $pair[1], 2);
        }

        return $total;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $this->parseInput();

        $total = 0;

        foreach ($this->joins as $pair) {
            $total += $this->calculateDiff($pair[0], $pair[1], 1000000);
        }

        return $total;
    }

    private function parseInput(): void
    {
        /** @var Collection<Collection<string>> $galaxy */
        $galaxy = unserialize(serialize($this->input->grid));

        $this->emptyRows = [];
        $this->emptyColumns = [];

        $flippedGalaxy = collect();
        foreach ($galaxy as $y => $row) {
            foreach ($row as $x => $character) {
                if (!isset($flippedGalaxy[$x])) {
                    $flippedGalaxy[$x] = collect();
                }
                $flippedGalaxy[$x][$y] = $character;
            }
        }

        foreach ($galaxy as $y => $row) {
            if ($row->search('#') === false) {
                $this->emptyRows[] = $y;
            }
        }

        foreach ($flippedGalaxy as $x => $row) {
            if ($row->search('#') === false) {
                $this->emptyColumns[] = $x;
            }
        }

        $stars = collect();

        foreach ($galaxy as $y => $row) {
            foreach ($row as $x => $char) {
                if ($char === '#') {
                    $stars[] = ['x' => $x, 'y' => $y];
                }
            }
        }

        $this->joins = collect();

        for ($i = 0; $i < count($stars); $i++) {
            for ($j = $i + 1; $j < count($stars); $j++) {
                $this->joins[] = [$stars[$i], $stars[$j]];
            }
        }
    }

    private function calculateDiff(array $a, array $b, int $emptySpace)
    {
        $xs = [$a['x'], $b['x']];
        sort($xs);

        $xDiff = 0;
        for ($x = $xs[0]; $x < $xs[1]; $x++) {
            if (in_array($x, $this->emptyColumns)) {
                $xDiff += $emptySpace;
            } else {
                $xDiff += 1;
            }
        }

        $ys = [$a['y'], $b['y']];
        sort($ys);

        $yDiff = 0;
        for ($y = $ys[0]; $y < $ys[1]; $y++) {
            if (in_array($y, $this->emptyRows)) {
                $yDiff += $emptySpace;
            } else {
                $yDiff += 1;
            }
        }

        return $xDiff + $yDiff;
    }
}
