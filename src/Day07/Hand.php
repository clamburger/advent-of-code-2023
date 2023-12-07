<?php

namespace App\Day07;

use Stringable;

class Hand implements Stringable
{
    public const array STRENGTHS = ['A' => 50, 'K' => 40, 'Q' => 30, 'J' => 20, 'T' => 10, '9' => 9, '8' => 8, '7' => 7, '6' => 6, '5' => 5, '4' => 4, '3' => 3, '2' => 2];

    public array $cards;

    public int $bid;

    public int $strength;

    public function __construct(public string $line)
    {
        [$hand, $this->bid] = explode(' ', $line);
        $this->cards = str_split($hand);

        $this->strength = $this->strength();
    }

    public function strength(): int
    {
        $cards = $this->cards;
        sort($cards);

        $strength = 0;

        $unique = array_values(array_unique($cards));
        $values = array_count_values($cards);

        if (count($unique) === 1) {
            // Five of a kind
            $strength = 9_00_00_00_00_00;
        } elseif (count($unique) === 2) {
            if ($values[$unique[0]] === 4 || $values[$unique[1]] === 4) {
                // Four of a kind
                $strength = 8_00_00_00_00_00;
            } elseif ($values[$unique[0]] === 3 || $values[$unique[1]] === 3) {
                // Full house
                $strength = 7_00_00_00_00_00;
            }
        } elseif (count($unique) === 3) {
            if ($values[$unique[0]] === 3 || $values[$unique[1]] === 3 || $values[$unique[2]] === 3) {
                // Three of a kind
                $strength = 6_00_00_00_00_00;
            } else {
                // Two pair
                $strength = 5_00_00_00_00_00;
            }
        } elseif (count($unique) === 4) {
            // One pair
            $strength = 4_00_00_00_00_00;
        } else {
            // High card
            $strength = 3_00_00_00_00_00;
        }

        foreach ($this->cards as $index => $card) {
            $magnitude = pow(100, 4 - $index);
            $cardStrength = self::STRENGTHS[$card];

            $strength += $magnitude * $cardStrength;
        }

        return $strength;
    }

    public function __toString(): string
    {
        return implode(' ', $this->cards);
    }
}
