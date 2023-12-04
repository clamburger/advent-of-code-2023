<?php

namespace App\Puzzles;

use App\Day04\Card;
use App\Puzzles\AbstractPuzzle;
use Override;

class Day04Scratchcards extends AbstractPuzzle
{
    protected static int $day_number = 4;

    private array $cards;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $total = 0;

        foreach ($this->input->lines as $line) {
            $card = Card::createFromLine($line);
            $total += $card->getScore();
        }
        return $total;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $this->cards = [];

        foreach ($this->input->lines as $line) {
            $card = Card::createFromLine($line);
            $this->cards[$card->id] = $card;
        }

        $cardCounts = array_fill_keys(array_keys($this->cards), 1);

        foreach ($this->cards as $card) {
            $copies = $card->getCopiedCards($this->cards);
            foreach ($copies as $copy) {
                $cardCounts[$copy->id] += $cardCounts[$card->id];
            }
        }

        return array_sum($cardCounts);
    }
}
