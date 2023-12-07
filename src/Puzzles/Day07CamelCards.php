<?php

namespace App\Puzzles;

use App\Day07\Hand;
use App\Day07\Hand2;
use Override;

class Day07CamelCards extends AbstractPuzzle
{
    protected static int $day_number = 7;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $hands = [];
        foreach ($this->input->lines as $line) {
            $hand = new Hand($line);
            $hands[] = $hand;
        }

        usort($hands, fn ($a, $b) => $a->strength <=> $b->strength);

        $total = 0;

        foreach ($hands as $index => $hand) {
            $rank = $index + 1;
            $total += $hand->bid * $rank;
        }

        return $total;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $hands = [];
        foreach ($this->input->lines as $line) {
            $hand = new Hand2($line);
            $hands[] = $hand;
        }

        usort($hands, fn ($a, $b) => $a->betterStrength <=> $b->betterStrength);

        $total = 0;

        foreach ($hands as $index => $hand) {
//            echo str_pad($index, 5) . $hand . "\n";
            $rank = $index + 1;
            $total += $hand->bid * $rank;
        }

        return $total;
    }
}
