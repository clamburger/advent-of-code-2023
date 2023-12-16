<?php

namespace App\Puzzles;

use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Override;

class Day13PointOfIncidence extends AbstractPuzzle
{
    protected static int $day_number = 13;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $blocks = $this->input->lines_by_block;

        $total = 0;

        foreach ($blocks as $index => $block) {
            $block = $block->map(fn(Stringable $str) => str_split($str));

            echo "=====================\n";
            echo "Checking block $index\n";

            echo "  Rows (standard)\n";
            $resultA = $this->checkRowsForReflection($block->toArray(), "row", false);

            echo "  Rows (reversed)\n";
            $resultB = $this->checkRowsForReflection($block->reverse()->values()->toArray(), "row", true);

            echo "  Cols (standard)\n";
            $cols = collect($this->transposeArray($block->toArray()));

            $resultC = $this->checkRowsForReflection($cols->toArray(), "col", false);

            echo "  Cols (reversed)\n";
            $resultD = $this->checkRowsForReflection($cols->reverse()->values()->toArray(), "col", true);

            $results = count(array_filter([$resultA, $resultB, $resultC, $resultD], fn($result) => $result !== null));
            if ($results === 0) {
                throw new \Exception("No reflection detected");
            } elseif ($results > 1) {
                throw new \Exception("This shouldn't happen: multiple reflections detected");
            }

            if ($resultA !== null || $resultB !== null) {
                $total += (max($resultA, $resultB) * 100);
                continue;
            }

            if ($resultC !== null || $resultD !== null) {
                $total += max($resultC, $resultD);
            }
        }

        // 38344 = too high
        return $total;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        return 0;
    }

    private function checkRowsForReflection(array $block, string $type, bool $reversed): ?int
    {
        $rows = array_map(fn ($row) => implode('', $row), $block);
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
                echo "    Reflection broken at $type " . ($index + 1) . "\n";
                $reflectionTracker = null;
                $reverseIndex = count($rows) - 1;
            }

            if ($reflectionTracker === null) {
                // Reached the end of the array with no reflections found
                if ($reverseIndex === $index) {
                    break;
                }
                if ($rows[$reverseIndex] === $row) {
                    echo "    Potential reflection found at $type " . ($index + 1) . " (matches reverse index " . ($reverseIndex + 1) . ")\n";
                    $reflectionTracker = $index;
                }
                continue;
            }

            echo "    Reflection continues at $type " . ($index + 1) . " (matches reverse index " . ($reverseIndex + 1) . ")\n";
        }

        if ($reflectionTracker === null) {
            echo "    No reflection found.\n";
        } else {
            $firstLine = $reflectionTracker + 1;
            $lastLine = count($rows);
            $midpoint = ($lastLine - $firstLine + 1) / 2 + $firstLine - 1;
            echo "    First line of reflection: " . ($firstLine) . "\n";
            echo "    Last line of reflection: " . ($lastLine) . "\n";
            echo "    Reflection found, after $type $midpoint\n";

            if ($reversed) {
                $midpoint = count($rows) - $midpoint;
                echo "    Reflection found, after $type $midpoint (corrected for reversal)\n";
            }
        }

        return $reflectionTracker === null ? null : $midpoint;
    }

    private function transposeArray(array $rows): array
    {
        $width = count($rows[array_key_first($rows)]);

        $transposed = [];

        for ($i = 0; $i < $width; $i++) {
            $transposed[] = array_column($rows, $i);
        }

        return $transposed;
    }
}
