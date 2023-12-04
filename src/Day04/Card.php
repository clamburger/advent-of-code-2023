<?php

namespace App\Day04;

use Illuminate\Support\Stringable;

class Card implements \Stringable
{
    public function __construct(public int $id, public array $numbers, public array $winners)
    {
    }

    public static function createFromLine(Stringable $line): self
    {
        preg_match('/Card +([0-9]+): ([0-9 ]+)\|([0-9 ]+)/', $line, $matches);

        $numbers = explode(' ', trim($matches[3]));
        $numbers = array_filter(array_map(trim(...), $numbers));

        $winners = explode(' ', trim($matches[2]));
        $winners = array_filter(array_map(trim(...), $winners));

        return new Card((int)$matches[1], $numbers, $winners);
    }

    public function __toString(): string
    {
        return "Card " . $this->id;
    }

    public function getScore(): int
    {
        $intersect = count(array_intersect($this->numbers, $this->winners));
        if ($intersect === 0) {
            return 0;
        }

        return pow(2, count(array_intersect($this->numbers, $this->winners)) - 1);
    }

    public function getMatches(): int
    {
        return count(array_intersect($this->numbers, $this->winners));
    }

    public function getCopiedCards(array $cards): array
    {
        $count = $this->getMatches();

        $newCards = [];

        for ($i = 1; $i <= $count; $i++) {
            $newCards[] = $cards[$this->id + $i];
        }

        return $newCards;
    }
}
