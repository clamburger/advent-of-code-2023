<?php

namespace App\Puzzles;

use Ds\Set;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Override;

class Day12HotSprings extends AbstractPuzzle
{
    protected static int $day_number = 12;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $total = 0;

        foreach ($this->input->lines as $index => $line) {
            [$springs, $damage] = $line->explode(' ');
            $damage = collect(explode(',', $damage))->map(fn ($d) => intval($d));

//            echo "$index. ";
            $result = $this->solveRow($springs, $damage);
//            echo "$result\n";
            $total += $result;
//            echo "$springs [$result]\n";
        }

        return $total;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        return 0;
    }

    private function solveRow(string $springs, Collection $damage): int
    {

        $chars = str_split($springs);

        $unknowns = array_keys(array_filter($chars, fn ($char) => $char === '?'));

//        echo "$unknown bits  ";

        $validCombinations = 0;

        $unknown = Str::substrCount($springs, '?');
        $max = (1 << $unknown) - 1;

        for ($i = 0; $i <= $max; $i++) {
            $charsCopy = $chars;

            for ($bit = 1; $bit <= $unknown; $bit++) {
                $bitValue = ($i >> $bit - 1) & 1;
                $charsCopy[$unknowns[$bit - 1]] = $bitValue ? '#' : '.';
            }

            $filledIn = implode('', $charsCopy);
            $result = $this->doesRowMatchCondition($filledIn, $damage);

            if ($result) {
                $validCombinations++;
            }

//            echo "$i/$max: $filledIn: " . ($result ? 'true' : 'false') . "\n";
        }

        return $validCombinations;
//        dump($this->doesRowMatchCondition($springs, $damage));
    }

    private function doesRowMatchCondition(string $springs, Collection $damage): bool
    {
        $springs = Str::of($springs);

        if ($springs->contains('?')) {
            throw new \Exception('$springs must not contain any unknown springs.');
        }

        $runs = $springs
            ->replaceMatches('/\.+/', '.')
            ->trim('.')
            ->explode('.')
            ->map(fn ($run) => strlen($run));

        foreach ($runs as $i => $value) {
            if (!isset($damage[$i]) || $damage[$i] !== $value) {
                return false;
            }
        }

        return true;
    }
}
