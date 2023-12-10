<?php

namespace App\Puzzles;

use Illuminate\Support\Collection;
use Override;

class Day10PipeMaze extends AbstractPuzzle
{
    public const array DIRECTIONS = [
        'down'  =>  ['x' =>  0, 'y' =>  1],
        'left'  =>  ['x' => -1, 'y' =>  0],
        'right' =>  ['x' =>  1, 'y' =>  0],
        'up'    =>  ['x' =>  0, 'y' => -1],
    ];

    public const array PIPE_TYPES = [
        'S' => [],
        '.' => [],
        '|' => ['down', 'up'],
        '-' => ['left', 'right'],
        'L' => ['right', 'up'],
        'J' => ['left', 'up'],
        '7' => ['down', 'left'],
        'F' => ['down', 'right'],
    ];

    protected static int $day_number = 10;

    /**
     * @var Collection<Collection<string>>
     */
    private Collection $grid;

    /**
     * @var Collection<Collection<string>>
     */
    private Collection $fakeGrid;

    /**
     * @var array{x: int, y: int}
     */
    private array $start;

    /**
     * @var array<int, array<int, true>>
     */
    private array $mainLoop;

    /**
     * @var array<int, array<int, true>>
     */
    private array $fakeLoop;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->parseInput();
        $this->findMainLoop();

        return collect($this->mainLoop)->flatten()->count() / 2;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $this->parseInput();
        $this->findMainLoop();
//        $this->drawMap($this->grid);
        $this->expandGrid();
//        $this->drawMap($this->fakeGrid, 'Expanded Map', true);
        $this->floodFill();
//        $this->drawMap($this->fakeGrid, 'Filled', true);

        $inside = 0;

        foreach ($this->fakeGrid as $y => $row) {
            foreach ($row as $x => $symbol) {
                if ($symbol === 'O') {
                    continue;
                }
                if ($x % 2 === 0 && $y % 2 === 0 && !$this->partOfLoop($x, $y)) {
                    $inside++;
                }
            }
        }

        return $inside;
    }

    private function parseInput(): void
    {
        // Surround the grid with blanks (needed for the flood fill in part 2)
        $width = $this->input->grid->first()->count();

        $grid = unserialize(serialize($this->input->grid));

        $this->grid = $grid
            // Top
            ->prepend(collect()->pad($width, '.'))
            // Bottom
            ->push(collect()->pad($width, '.'))
            // Left and right
            ->map(fn ($line) => $line->prepend('.')->push('.'));

        // Find the start position.
        // Two foreach loops would be much simpler and easier to understand,
        // but sometimes it's nice to be a little too 'clever'.
        $this->start = $this->grid
            ->map->search('S')
            ->filter()
            ->mapWithKeys(fn ($x, $y) => [['x' => $x, 'y' => $y]])
            ->first();

        // Check to see which of the start's neighbours have a connection to the start.
        // This will allow the actual pipe type of the start to be identified.
        $connections = [];
        $neighbours = $this->getNeighbours($this->start);
        foreach ($neighbours as $direction => $neighbour) {
            if ($this->doesPipeLeadToAnother($neighbour, $this->start)) {
                $connections[] = $direction;
            }
        }

        $type = collect(self::PIPE_TYPES)->search($connections);

        // Once we've identified the type, replace it in the grid.
        $this->grid[$this->start['y']][$this->start['x']] = $type;
    }

    /**
     * Finds which pipes are part of the main loop, storing the result.
     */
    private function findMainLoop(): void
    {
        $this->mainLoop = [];

        $position = $this->start;

        while (true) {
            $this->mainLoop[$position['y']][$position['x']] = true;
            $symbol = $this->grid[$position['y']][$position['x']];
            $directions = self::PIPE_TYPES[$symbol];

            $candidateFound = false;

            foreach ($directions as $direction) {
                $coords = self::DIRECTIONS[$direction];
                $candidateCoords = [
                    'x' => $position['x'] + $coords['x'],
                    'y' => $position['y'] + $coords['y'],
                ];

                if (isset($this->mainLoop[$candidateCoords['y']][$candidateCoords['x']])) {
                    continue;
                }

                $position = $candidateCoords;
                $this->mainLoop[$candidateCoords['y']][$candidateCoords['x']] = true;
                $candidateFound = true;
                break;
            }

            if (!$candidateFound) {
                break;
            }
        }
    }

    /**
     * @param array{x: int, y: int} $position
     *
     * @return array<string, array{x: int, y:int}>
     */
    private function getNeighbours(array $position): array
    {
        $neighbours = [];
        foreach (self::DIRECTIONS as $direction => $relativeCoords) {
            $x = $position['x'] + $relativeCoords['x'];
            $y = $position['y'] + $relativeCoords['y'];

            $neighbour = $this->grid[$y][$x] ?? null;
            if ($neighbour) {
                $neighbours[$direction] = ['x' => $x, 'y' => $y];
            }
        }

        return $neighbours;
    }

    /**
     * Create an expanded grid that's 2x the width and 2x the height.
     */
    private function expandGrid(): void
    {
        // Multiple all X and Y coordinates by 2.
        // This will leave gaps between all rows and columns.
        $this->fakeGrid = $this->grid->mapWithKeys(fn (Collection $row, int $y) => [
            $y * 2 => $row->mapWithKeys(fn (string $symbol, int $x) => [
                $x * 2 => $symbol,
            ])
        ]);

        $fakeWidth = $this->fakeGrid->first()->keys()->last();
        $fakeHeight = $this->fakeGrid->keys()->last();

        // To populate the gaps we've added in the grid, we check to see if the pipes on each
        // side of the gap would be connecting without the gap. If so, we add a fake connection
        // to join them, otherwise the space is left blank.

        // First pass: connect the columns horizontally.
        // Iterate through the original rows but the gaps between the original columns.

        for ($fakeY = 0; $fakeY <= $fakeHeight; $fakeY += 2) {
            for ($fakeX = 1; $fakeX <= $fakeWidth; $fakeX += 2) {
                // Real coordinates
                $left  = ['x' => ($fakeX - 1) / 2, 'y' => $fakeY / 2];
                $right = ['x' => ($fakeX + 1) / 2, 'y' => $fakeY / 2];

                if ($this->arePipesConnected($left, $right)) {
                    $this->fakeGrid[$fakeY][$fakeX] = '-';

                    if (isset($this->mainLoop[$left['y']][$left['x']])) {
                        $this->fakeLoop[$fakeY][$fakeX] = true;
                    }
                } else {
                    $this->fakeGrid[$fakeY][$fakeX] = '.';
                }
            }
        }

        $this->fakeGrid = $this->fakeGrid->map->sortKeys();

        // Second pass: connect the rows vertically.
        // Iterate through the gaps between the rows and the original columns.
        for ($fakeY = 1; $fakeY <= $fakeHeight; $fakeY += 2) {
            $this->fakeGrid[$fakeY] = collect()->pad($fakeWidth + 1, '.');

            for ($fakeX = 0; $fakeX <= $fakeWidth; $fakeX += 2) {
                // Real coordinates
                $above = ['x' => $fakeX / 2, 'y' => ($fakeY - 1) / 2];
                $below = ['x' => $fakeX / 2, 'y' => ($fakeY + 1) / 2];

                if ($this->arePipesConnected($above, $below)) {
                    $this->fakeGrid[$fakeY][$fakeX] = '|';

                    if (isset($this->mainLoop[$above['y']][$above['x']])) {
                        $this->fakeLoop[$fakeY][$fakeX] = true;
                    }
                }
            }
        }

        $this->fakeGrid = $this->fakeGrid->sortKeys();
    }

    private function drawMap(Collection $grid, string $label = 'Map', bool $fake = false): void
    {
        $boxChars = [
            '.' => '·',
            '-' => '─',
            '|' => '│',
            'L' => '└',
            'J' => '┘',
            '7' => '┐',
            'F' => '┌',
            'S' => '⊕',
            'O' => ' ',
        ];

        echo "$label:\n";
        foreach ($grid as $y => $row) {
            foreach ($row as $x => $symbol) {
                $drawSymbol = $boxChars[$symbol] ?? $symbol;

                if ($fake && $this->partOfFakeLoop($x, $y)) {
                    // MAIN LOOP (fake only) - light green
                    echo $this->colour($drawSymbol, 30, 104);
                } elseif ($fake && $this->partOfMainLoop($x, $y)) {
                    echo $this->colour($drawSymbol, 30, 104);
                } elseif (!$fake && $this->partOfMainLoop($x * 2, $y * 2)) {
                    // MAIN LOOP (real only) - light blue
                    echo $this->colour($drawSymbol, 30, 104);
                } elseif ($symbol === 'O') {
                    // Dark grey
                    echo $this->colour($drawSymbol, 30);
                } elseif ($fake && $x % 2 === 0 && $y % 2 === 0) {
                    echo $this->colour($drawSymbol, 101, 30, 1);
                } else {
                    echo $this->colour($drawSymbol, 103, 33);
                }
            }
            echo "\n";
        }
    }

    private function colour(string $string, int ...$colours): string
    {
        $colours = array_map(fn ($colour) => "\033[{colour}m", $colours);

        return implode('', $colours) . $string . "\033[0m";
    }

    /**
     * Checks if pipe A has a connection toward pipe B. Does not check the inverse.
     */
    private function doesPipeLeadToAnother(array $a, array $b): bool
    {
        // Check connection from A to B
        $symbol = $this->grid[$a['y']][$a['x']];
        $directionsA = self::PIPE_TYPES[$symbol];

        foreach ($directionsA as $direction) {
            $relativeCoords = self::DIRECTIONS[$direction];

            $neighbourX = $relativeCoords['x'] + $a['x'];
            $neighbourY = $relativeCoords['y'] + $a['y'];
            $neighbour = ['x' => $neighbourX, 'y' => $neighbourY];
            if (json_encode($neighbour) === json_encode($b)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if two pipes are connected to each other.
     */
    public function arePipesConnected(array $a, array $b): bool
    {
        return $this->doesPipeLeadToAnother($a, $b) && $this->doesPipeLeadToAnother($b, $a);
    }

    private function partOfMainLoop(int $fakeX, int $fakeY): bool
    {
        if ($fakeX % 2 === 0 && $fakeY % 2 === 0) {
            return isset($this->mainLoop[$fakeY / 2][$fakeX / 2]);
        }

        return false;
    }

    private function partOfFakeLoop(int $fakeX, int $fakeY): bool
    {
        return isset($this->fakeLoop[$fakeY][$fakeX]);
    }

    private function partOfLoop(int $fakeX, int $fakeY): bool
    {
        return $this->partOfMainLoop($fakeX, $fakeY) || $this->partOfFakeLoop($fakeX, $fakeY);
    }

    /**
     * Replaces all empty space and pipes outside the main loop with the O symbol (for 'outside').
     */
    private function floodFill(): void
    {
        $queue = [['x' => 0, 'y' => 0]];

        while (!empty($queue)) {
            $node = array_shift($queue);
            $symbol = $this->fakeGrid[$node['y']][$node['x']] ?? null;

            if (!$symbol || $symbol === 'O' || $this->partOfLoop($node['x'], $node['y'])) {
                continue;
            }

            $this->fakeGrid[$node['y']][$node['x']] = 'O';

            foreach (self::DIRECTIONS as $direction) {
                $neighbourCoords = ['x' => $node['x'] + $direction['x'], 'y' => $node['y'] + $direction['y']];
                // Doesn't matter if it's not valid, it'll get filtered out by the if statement later
                $queue[] = $neighbourCoords;
            }
        }
    }
}
