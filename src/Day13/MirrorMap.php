<?php

namespace App\Day13;

use Illuminate\Support\Collection;

class MirrorMap implements \Stringable
{
    public string $dimension = 'row';
    public bool $reversed = false;
    public ?int $parentValue = null;

    public function __construct(public Collection $grid)
    {
    }

    public static function createFromBlock(Collection $block)
    {
        $block = $block->map(fn ($row) => collect(str_split($row)));
        return new self($block);
    }

    public function __toString(): string
    {
        return $this->grid
            ->map(fn ($row) => $row->implode(''))
            ->implode("\n");
    }

    /**
     * @return Collection<MirrorMap>
     */
    public function getSmudgedMaps(): Collection
    {
        $value = $this->getValue();
        $grid = $this->grid->toArray();

        $maps = collect();

        $opposite = [
            '#' => '.',
            '.' => '#',
        ];

        foreach ($grid as $y => $row) {
            foreach ($row as $x => $char) {
                $newGrid = $grid;
                $newGrid[$y][$x] = $opposite[$char];

                $map = new self(collect($newGrid)->map(fn ($row) => collect($row)));
                $map->parentValue = $value;
                $maps[] = $map;
            }
        }

        return $maps;
    }

    public function toArray(): array
    {
        return $this->grid->map(fn ($row) => $row->implode(''))->toArray();
    }

    public function transpose(): MirrorMap
    {
        $width = count($this->grid->first());

        $transposed = collect();

        for ($i = 0; $i < $width; $i++) {
            $transposed[] = $this->grid->pluck($i);
        }

        $map = new self($transposed);
        $map->dimension = $this->dimension === 'row' ? 'col' : 'row';
        return $map;
    }

    public function invert(): MirrorMap
    {
       $map = new self($this->grid->reverse()->values());
       $map->reversed = !$this->reversed;
       return $map;
    }

    public function checkForReflection(): ?int
    {
        $rows      = $this->grid->map(fn($row) => $row->implode(''));
        $instances = [];

        foreach ($rows as $index => $row) {
            $instances[$row][] = $index;
        }

        $reflectionTracker = null;

        foreach ($rows as $index => $row) {
            if ($reflectionTracker === null) {
                $reverseIndex = count($rows) - 1;
            } else {
                $reverseIndex = count($rows) - ($index - $reflectionTracker) - 1;
            }

            if ($reflectionTracker !== null && $rows[$reverseIndex] !== $row) {
//                echo "    Reflection broken at $this->dimension " . ($index + 1) . "\n";
                $reflectionTracker = null;
                $reverseIndex      = count($rows) - 1;
            }

            if (($index % 2) === ($reverseIndex % 2)) {
                continue;
            }

            if ($reflectionTracker === null) {
                // Reached the end of the array with no reflections found
                if ($reverseIndex === $index) {
                    break;
                }
                if ($rows[$reverseIndex] === $row) {
//                    echo "    Potential reflection found at $this->dimension " . ($index + 1) . " (matches reverse index " . ($reverseIndex + 1) . ")\n";
                    $reflectionTracker = $index;
                }
                continue;
            }

//            echo "    Reflection continues at $this->dimension " . ($index + 1) . " (matches reverse index " . ($reverseIndex + 1) . ")\n";
        }

        if ($reflectionTracker === null) {
//            echo "    No reflection found.\n";
        } else {
            $firstLine = $reflectionTracker + 1;
            $lastLine = count($rows);
            $midpoint = ($lastLine - $firstLine + 1) / 2 + $firstLine - 1;
//            echo "    First line of reflection: " . ($firstLine) . "\n";
//            echo "    Last line of reflection: " . ($lastLine) . "\n";
//            echo "    Reflection found, after $this->dimension $midpoint\n";

            if ($this->reversed) {
                $midpoint = count($rows) - $midpoint;
//                echo "    Reflection found, after $this->dimension $midpoint (corrected for reversal)\n";
            }
        }

        return $reflectionTracker === null ? null : $midpoint;
    }

    public function getValue(): ?int
    {
        // Rows (standard)
//        echo "  Rows (standard)\n";
        $resultA = $this->checkForReflection();
        if ($resultA !== null) {
            $resultA *= 100;
        }

        // Rows (reversed)
//        echo "  Rows (reversed)\n";
        $mapB = $this->invert();
        $resultB = $mapB->checkForReflection();
        if ($resultB !== null) {
            $resultB *= 100;
        }

        // Cols (standard)
//        echo "  Cols (standard)\n";
        $mapC = $this->transpose();
        $resultC = $mapC->checkForReflection();

        // Cols (reversed)
//        echo "  Cols (reversed)\n";
        $mapD = $this->transpose()->invert();
        $resultD = $mapD->checkForReflection();

        $results = collect([$resultA, $resultB, $resultC, $resultD])
            ->filter(fn($result) => $result !== null && ($this->parentValue === null || $this->parentValue !== $result));

        if (count($results) === 0) {
            if ($this->parentValue !== null) {
                return null;
            }
            throw new \Exception("No reflection detected");
        } elseif (count($results) > 1) {
            throw new \Exception("This shouldn't happen: multiple reflections detected");
        }

        return $results->first();
    }
}
