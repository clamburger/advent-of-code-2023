<?php

namespace App\Day07;

use Stringable;

class Hand2 implements Stringable
{
    public const array STRENGTHS = ['A' => 50, 'K' => 40, 'Q' => 30, 'T' => 10, '9' => 9, '8' => 8, '7' => 7, '6' => 6, '5' => 5, '4' => 4, '3' => 3, '2' => 2, 'J' => 1];
    public const int FIVE_OF_A_KIND = 9_00_00_00_00_00;
    public const int FOUR_OF_A_KIND = 8_00_00_00_00_00;
    public const int FULL_HOUSE = 7_00_00_00_00_00;
    public const int THREE_OF_A_KIND = 6_00_00_00_00_00;
    public const int TWO_PAIR = 5_00_00_00_00_00;
    public const int ONE_PAIR = 4_00_00_00_00_00;
    public const int HIGH_CARD = 3_00_00_00_00_00;

    public const array RESULTS = [9 => '5 of a kind', 8 => '4 of a kind', 7 => 'Full house', 6 => 'three of a kind', 5 => 'two pair', 4 => 'One pair', 3 => 'High card'];

    public array $cards;

    public int $bid;

    public int $strength;

    public int $betterStrength;
    public string $bestHand;
    public string $bestCard;


    public function __construct(public string $line)
    {
        [$hand, $this->bid] = explode(' ', $line);
        $this->cards = str_split($hand);

        $this->strength = $this->strength();
        $this->betterStrength = $this->betterStrength();
    }

    public function betterStrength()
    {
        $cards = $this->cards;

        $replace = [
            0 => 'x',
            1 => 'y',
            2 => 'z',
            3 => 'v',
            4 => 'w',
        ];

        foreach ($replace as $index => $char) {
            if ($cards[$index] === 'J') {
                $cards[$index] = $char;
            }
        }

        $baseStrength = self::baseStrength($cards);
        $bestCard = $baseStrength['best card'];
        $this->bestCard = $bestCard;

        $newCards = implode('', $this->cards);
        $newCards = str_replace('J', $bestCard, $newCards);
        $newCards = str_split($newCards);
        $this->bestHand = implode(' ', $newCards);

        return self::baseStrength($newCards)['strength'] + self::tiebreakerStrength($this->cards);
    }

    public static function baseStrength(array $cards): array
    {
        sort($cards);

        $strength = 0;

        $unique = array_values(array_unique($cards));
        $values = array_count_values($cards);

        $bestCard = null;

        if (count($unique) === 1) {
            // Five of a kind
            $strength = self::FIVE_OF_A_KIND;
            $bestCard = $unique[0];
        } elseif (count($unique) === 2) {
            if ($values[$unique[0]] === 4 || $values[$unique[1]] === 4) {
                // Four of a kind
                $strength = self::FOUR_OF_A_KIND;

                if ($values[$unique[0]] === 4) {
                    $bestCard = $unique[0];
                } elseif ($values[$unique[1]] === 4) {
                    $bestCard = $unique[1];
                }

            } elseif ($values[$unique[0]] === 3 || $values[$unique[1]] === 3) {
                // Full house
                $strength = self::FULL_HOUSE;

                if ($values[$unique[0]] === 3) {
                    $bestCard = $unique[0];
                } elseif ($values[$unique[1]] === 3) {
                    $bestCard = $unique[1];
                }
            }
        } elseif (count($unique) === 3) {
            if ($values[$unique[0]] === 3 || $values[$unique[1]] === 3 || $values[$unique[2]] === 3) {
                // Three of a kind
                $strength = self::THREE_OF_A_KIND;

                if ($values[$unique[0]] === 3) {
                    $bestCard = $unique[0];
                } elseif ($values[$unique[1]] === 3) {
                    $bestCard = $unique[1];
                } elseif ($values[$unique[2]] === 3) {
                    $bestCard = $unique[2];
                }
            } else {
                // Two pair
                $strength = self::TWO_PAIR;

                if ($values[$unique[0]] === 2) {
                    $bestCard = $unique[0];
                } elseif ($values[$unique[1]] === 2) {
                    $bestCard = $unique[1];
                } elseif ($values[$unique[2]] === 2) {
                    $bestCard = $unique[2];
                }
            }
        } elseif (count($unique) === 4) {
            // One pair
            $strength = self::ONE_PAIR;

            if ($values[$unique[0]] === 2) {
                $bestCard = $unique[0];
            } elseif ($values[$unique[1]] === 2) {
                $bestCard = $unique[1];
            } elseif ($values[$unique[2]] === 2) {
                $bestCard = $unique[2];
            } elseif ($values[$unique[3]] === 2) {
                $bestCard = $unique[3];
            }
        } else {
            // High card
            $strength = self::HIGH_CARD;

            // Doesn't matter lol
            foreach ($cards as $card) {
                if (strtoupper($card) === $card) {
                    $bestCard = $card;
                }
            }

            if (!isset($bestCard)) {
                $bestCard = 'J';
            }
        }

        return ['strength' => $strength, 'best card' => $bestCard];
    }

    public static function tiebreakerStrength(array $cards): int
    {
        $strength = 0;

        foreach ($cards as $index => $card) {
            $magnitude = pow(100, 4 - $index);
            $cardStrength = self::STRENGTHS[$card];

            $strength += $magnitude * $cardStrength;
        }

        return $strength;
    }

    public function strength(): int
    {
        return self::baseStrength($this->cards)['strength'] + self::tiebreakerStrength($this->cards);
    }

    public function __toString(): string
    {
        $strength0 = ((string)$this->betterStrength)[0];
        $text = implode(' ', $this->cards) . ' (as ' . $this->bestHand . ')' . ', best card ' . $this->bestCard . '     [' . self::RESULTS[$strength0] .']';
        if (!collect($this->cards)->contains('J')) {
            $text .= '  no J';
        }

        $text = str_replace(['A', 'K', 'Q', 'T', 'J'], ['D', 'C', 'B', 'A', '1'], $text);

        return $text;
    }
}
