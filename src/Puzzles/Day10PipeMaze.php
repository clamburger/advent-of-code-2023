<?php

namespace App\Puzzles;

use Override;

class Day10PipeMaze extends AbstractPuzzle
{
    protected static int $day_number = 10;

    private array $grid;
    private array $start;
    private string $startType;

    private array $fakeGrid;
    private array $fakeToRealCoords;

    private array $partOfMainLoop;

    private array $partOfFakeMainLoop;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->parseInput();
        $this->partOfMainLoop = [];

        $position = $this->start;
        $type = $this->startType;

        $visited = [];
        $count = 0;

        while (true) {
            if ($type === 'S') {
                break;
            }
            $directions = self::PIPE_TYPES[$type];

            $candidateFound = null;
            $count++;

            foreach ($directions as $direction) {
                $coords = self::DIRECTIONS[$direction];
                $candidateCoords = [
                    'x' => $position['x'] + $coords['x'],
                    'y' => $position['y'] + $coords['y'],
                ];

                if (isset($visited[json_encode($candidateCoords)])) {
                    continue;
                }

                $position = $candidateCoords;
                $type = $this->grid[$candidateCoords['y']][$candidateCoords['x']];
                $visited[json_encode($position)] = $count;
                $this->partOfMainLoop[$candidateCoords['y']][$candidateCoords['x']] = true;
                $candidateFound = true;
                break;
            }


            if (!$candidateFound) {
                break;
            }
        }

        return $count / 2;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
//        $this->parseInput();
        $this->getPartOneAnswer();

        $this->drawMap($this->grid);

        $this->expandGrid();

        $this->drawMap($this->fakeGrid, 'Expanded Map', true);

        $this->floodFill();

        $this->drawMap($this->fakeGrid, 'Filled', true);

        $inside = 0;

        foreach ($this->fakeGrid as $y => $row) {
            foreach ($row as $x => $symbol) {
                if ($symbol === 'O') {
                    continue;
                }
                if (isset($this->fakeToRealCoords[$y][$x]) && !$this->partOfLoop($x, $y)) {
                    $inside++;
                }
            }
        }

        return $inside;
    }

    public const LEFT = ['x' => -1, 'y' => 0];
    public const RIGHT = ['x' => 1, 'y' => 0];
    public const UP = ['x' => 0, 'y' => -1];
    public const DOWN = ['x' => 0, 'y' => 1];

    public const DIRECTIONS = [
        'left' => self::LEFT,
        'right' => self::RIGHT,
        'up' => self::UP,
        'down' => self::DOWN,
    ];

    public const PIPE_TYPES = [
        '|' => ['down', 'up'],
        '-' => ['left', 'right'],
        'L' => ['right', 'up'],
        'J' => ['left', 'up'],
        '7' => ['down', 'left'],
        'F' => ['down', 'right'],
    ];

    private function parseInput()
    {
        $start = [];
        $grid = [];

        // Surround the grid with blanks
        $processedGrid = $this->input->grid->map(fn ($line) => $line->prepend('.')->push('.'));
        $processedGrid = $processedGrid
            ->prepend(array_fill(0, count($processedGrid->first()), '.'))
            ->push(array_fill(0, count($processedGrid->first()), '.'));

        // Find the start
        foreach ($processedGrid as $y => $row) {
            foreach ($row as $x => $symbol) {
                // Copy grid so we can modify it
                $grid[$y][$x] = $symbol;
                if ($symbol === 'S') {
                    $start = ['x' => $x, 'y' => $y];
                    break 2;
                }
            }
        }

        $startType = null;
        $startConnections = [];

        // Determine which connections the start pipe can have with its neighbours
        foreach (self::DIRECTIONS as $direction => $relativeCoords) {
            $neighbourX = $start['x'] + $relativeCoords['x'];
            $neighbourY = $start['y'] + $relativeCoords['y'];

            $neighbour = $processedGrid[$neighbourY][$neighbourX] ?? null;
            if (!$neighbour || $neighbour === '.') {
                continue;
            }

            $type = self::PIPE_TYPES[$neighbour];
            foreach ($type as $connection) {
                $coords = self::DIRECTIONS[$connection];

                if ($neighbourY + $coords['y'] === $start['y'] && $neighbourX + $coords['x'] === $start['x']) {
                    $startConnections[] = $direction;
                }
            }
        }

        assert(count($startConnections) === 2, 'startConnections must have exactly 2 values');

        // Identify the type of the start pipe
        sort($startConnections);
        foreach (self::PIPE_TYPES as $type => $directions) {
            if (json_encode($directions) === json_encode($startConnections)) {
                $startType = $type;
            }
        }

        assert($startType, 'startType must have a value');

        $this->grid = $processedGrid->toArray();
        $this->start = $start;
        $this->startType = $startType;
    }

    /**
     * Create an expanded grid that's 2x the width and 2x the height
     */
    public function expandGrid()
    {
        $fakeWidth = count($this->grid[0]) * 2 - 1;
        $fakeGrid = [];
        $fakeToRealMap = [];

        $fakeY = -1;

        $realGrid = $this->grid;

        foreach ($this->grid as $y => $row) {
            if ($y !== 0) {
                // no expansion needed for first row
                $fakeY++;

                // Slightly confusing here as we want to use the fake X but the real Y
                for ($fakeX = 0; $fakeX < $fakeWidth; $fakeX++) {
                    // Odd numbers are never connected
                    if ($fakeX % 2 === 1) {
                        $toPlace = '.';
                    } else {
                        $x = $fakeX / 2;

                        $position  = ['x' => $x, 'y' => $y];
                        $neighbour = ['x' => $x, 'y' => $y - 1];
                        if ($this->arePipesConnected($realGrid, $position, $neighbour)) {
                            $toPlace = '|';
                            if (isset($this->partOfMainLoop[$position['y']][$position['x']])) {
                                $this->partOfFakeMainLoop[$fakeY][$fakeX] = true;
                            }
                        } else {
                            $toPlace = '.';
                        }

                        if ($fakeX % 2 === 0 && $fakeY % 2 === 0) {
                            $fakeToRealMap[$fakeY][$fakeX] = ['x' => $x, 'y' => $y];
                        }
                    }

                    $fakeGrid[$fakeY][$fakeX] = $toPlace;
                }
            }

            $fakeY++;
            $fakeX = -1;

            // Expand the row horizontally
            // Expanded tiles will only ever be - or .

            foreach ($row as $x => $symbol) {
                if ($x !== 0) {
                    // no expansion needed for first column
                    $fakeX++;
                    $position  = ['x' => $x, 'y' => $y];
                    $neighbour = ['x' => $x - 1, 'y' => $y];

                    if ($this->arePipesConnected($realGrid, $position, $neighbour)) {
                        $toPlace = '-';
                        if (isset($this->partOfMainLoop[$position['y']][$position['x']])) {
                            $this->partOfFakeMainLoop[$fakeY][$fakeX] = true;
                        }
                    } else {
                        $toPlace = '.';
                    }

                    $fakeGrid[$fakeY][$fakeX] = $toPlace;
                }

                // Place the original symbol
                $fakeX++;
                $fakeGrid[$fakeY][$fakeX] = $symbol;
                if ($fakeX % 2 === 0 && $fakeY % 2 === 0) {
                    $fakeToRealMap[$fakeY][$fakeX] = ['x' => $x, 'y' => $y];
                }
            }
        }

        $this->fakeGrid = $fakeGrid;
        $this->fakeToRealCoords = $fakeToRealMap;
    }

    public function drawMap(array $grid, string $label = 'Map', bool $fake = false): void
    {
//        echo "$label:\n";
//        foreach ($grid as $y => $row) {
//            foreach ($row as $x => $symbol) {
//                if ($fake) {
//                    $realCoords = $this->fakeToRealCoords[$y][$x] ?? null;
//                } else {
//                    $realCoords = ['x' => $x, 'y' => $y];
//                }
//
//                if ($fake && $this->partOfFakeLoop($x, $y)) {
//                    // MAIN LOOP (fake only) - light green
//                    echo $this->colour2($symbol, 30, 104);
//                } elseif ($fake && $this->partOfMainLoop($x, $y)) {
//                    echo $this->colour2($symbol, 30, 104);
//                } elseif (!$fake && $this->partOfMainLoop($x * 2, $y * 2)) {
//                    // MAIN LOOP (real only) - light blue
//                    echo $this->colour2($symbol, 30, 104);
//                } elseif ($symbol === 'O') {
//                    // Dark grey
//                    echo $this->colour($symbol, 90);
//                } elseif (!$realCoords) {
//                    echo $this->colour($symbol, 37);
//                } else {
//                    echo $this->colour2($symbol, 103, 30);
//                }
//            }
//            echo "\n";
//        }
    }

    public function colour(string $string, int $colour): string
    {
        return "\033[{$colour}m" . $string . "\033[0m";
    }

    public function colour2(string $string, int $colour1, int $colour2): string
    {
        return "\033[{$colour1}m\033[{$colour2}m" . $string . "\033[0m";
    }

    public function arePipesConnected(array $grid, array $a, array $b): bool
    {
        $symbolA = $grid[$a['y']][$a['x']];
        $symbolB = $grid[$b['y']][$b['x']];

        if ($symbolA === 'S') {
            $symbolA = $this->startType;
        } elseif ($symbolA === '.') {
            return false;
        }

        if ($symbolB === 'S') {
            $symbolB = $this->startType;
        } elseif ($symbolB === '.') {
            return false;
        }

        $directionsA = self::PIPE_TYPES[$symbolA];
        $directionsB = self::PIPE_TYPES[$symbolB];

        $connectionA = false;
        $connectionB = false;

        foreach ($directionsA as $direction) {
            $relativeCoords = self::DIRECTIONS[$direction];

            $neighbourX = $relativeCoords['x'] + $a['x'];
            $neighbourY = $relativeCoords['y'] + $a['y'];
            $neighbour = ['x' => $neighbourX, 'y' => $neighbourY];
            if (json_encode($neighbour) === json_encode($b)) {
                $connectionA = true;
            }
        }

        foreach ($directionsB as $direction) {
            $relativeCoords = self::DIRECTIONS[$direction];

            $neighbourX = $relativeCoords['x'] + $b['x'];
            $neighbourY = $relativeCoords['y'] + $b['y'];
            $neighbour = ['x' => $neighbourX, 'y' => $neighbourY];
            if (json_encode($neighbour) === json_encode($a)) {
                $connectionB = true;
            }
        }

        return $connectionA && $connectionB;
    }

    private function partOfMainLoop(int $fakeX, int $fakeY): bool
    {
        if ($fakeX % 2 === 0 && $fakeY % 2 === 0) {
            return isset($this->partOfMainLoop[$fakeY / 2][$fakeX / 2]);
        }

        return false;
    }

    private function partOfFakeLoop(int $fakeX, int $fakeY): bool
    {
        return isset($this->partOfFakeMainLoop[$fakeY][$fakeX]);
    }

    private function partOfLoop(int $fakeX, int $fakeY): bool
    {
        return $this->partOfMainLoop($fakeX, $fakeY) || $this->partOfFakeLoop($fakeX, $fakeY);
    }

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
                $neighbour = $this->fakeGrid[$neighbourCoords['y']][$neighbourCoords['x']] ?? null;
//                if ($neighbour === '.') {
                    $queue[] = $neighbourCoords;
//                }
            }
        }
    }
}
