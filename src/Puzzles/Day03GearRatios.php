<?php

namespace App\Puzzles;

use App\Day03\Number;
use Override;

class Day03GearRatios extends AbstractPuzzle
{
    protected static int $day_number = 3;

    private array $numbers;
    private array $numbersByCoord;
    private array $symbolsByCoord;

    private int $width;
    private int $height;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->parseInput();

        $sum = 0;

        foreach ($this->numbers as $number) {
            if ($this->hasAdjacentSymbol($number)) {
                $sum += $number->number;
//                echo $number['number']  . " has symbol\n";
            } else {
//                echo $number['number']  . " does not have symbol\n";
            }
        }

        return $sum;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $this->parseInput();

        $ratios = 0;

        foreach ($this->symbolsByCoord as $y => $line) {
            foreach ($line as $x => $symbol) {
                if ($symbol !== '*') {
                    continue;
                }
                $numbers = array_values($this->getSurroundingNumbers($x, $y));

                if (count($numbers) === 2) {
                    $ratios += ($numbers[0]->number * $numbers[1]->number);
                }
            }
        }

        return $ratios;
    }

    private function parseInput()
    {
        $this->height = count($this->input->lines);
        $this->width = count($this->input->grid->first());

        foreach ($this->input->lines as $index => $line) {
            $matches = $this->findNumbers($line);
            foreach ($matches as $match) {
                $number = new Number($match[1], $index, (int)$match[0]);
                $this->numbers[] = $number;

                for ($x = $number->x1; $x <= $number->x2; $x++) {
                    $this->numbersByCoord[$number->y][$x] = $number;
                }
            }

            $matches = $this->findSymbols($line);
            foreach ($matches as $match) {
                $this->symbolsByCoord[$index][$match[1]] = $match[0];
            }
        }
    }

    private function findNumbers(string $line): array
    {
        $pattern = '/[0-9]+/';
        preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE);

        return $matches[0];
    }

    private function findSymbols(string $line): array
    {
        $pattern = '/[^0-9.]/';
        preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE);

        return $matches[0];
    }

    private function hasAdjacentSymbol(Number $number): bool
    {
        $neighbours = collect();
        for ($x = $number->x1; $x <= $number->x2; $x++) {
            $neighbours->push(...$this->getSurroundingSquares($x, $number->y));
        }

        $neighbours = $neighbours->unique();

        foreach ($neighbours as $neighbour) {
            if (isset($this->symbolsByCoord[$neighbour['y']][$neighbour['x']])) {
                return true;
            }
        }

        return false;
    }

    private function getSurroundingSquares(int $x, int $y): array
    {
        $squares = [
            ['x' => $x - 1, 'y' => $y - 1],
            ['x' => $x    , 'y' => $y - 1],
            ['x' => $x + 1, 'y' => $y - 1],
            ['x' => $x - 1, 'y' => $y    ],
            ['x' => $x    , 'y' => $y    ],
            ['x' => $x + 1, 'y' => $y    ],
            ['x' => $x - 1, 'y' => $y + 1],
            ['x' => $x    , 'y' => $y + 1],
            ['x' => $x + 1, 'y' => $y + 1],
        ];

        return array_filter($squares, fn ($square) => isset($this->input->grid[$y][$x]));
    }

    private function getSurroundingNumbers(int $x, int $y): array
    {
        $neighbours = collect($this->getSurroundingSquares($x, $y));

        $numbers = [];

        foreach ($neighbours as $neighbour) {
            if (isset($this->numbersByCoord[$neighbour['y']][$neighbour['x']])) {
                $number = $this->numbersByCoord[$neighbour['y']][$neighbour['x']];
                $numbers[$number->id] = $number;
            }
        }

        return $numbers;
    }
}
