<?php

namespace App\Puzzles;

use Override;

class Day02CubeConundrum extends AbstractPuzzle
{
    protected static int $day_number = 2;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $limits = [
            'red' => 12,
            'green' => 13,
            'blue' => 14,
        ];

        $ids = 0;

        foreach ($this->input->lines as $line) {
            [$game, $moves] = $line->split('/: /');
            $game = (int)str_replace("Game ", "", $game);
            $sets = explode(";", $moves);
            $sets = collect($sets)->map(function ($set) {
                $pulls = collect(explode(', ', trim($set)))->map(function ($pull) {
                    $ex = explode(" ", $pull);
                    $pull = [
                        'count' => (int)$ex[0],
                        'colour' => $ex[1]
                    ];
                    return $pull;
                });
                return $pulls;
            });


            $result = true;
            foreach ($sets as $pulls) {
                foreach ($pulls as $pull) {
                    if ($pull['count'] > $limits[$pull['colour']]) {
//                        echo "Game $game impossible\n";
                        $result = false;
                        break 2;
                    }
                }
            }

            if ($result) {
                $ids += $game;
            }
        }

        return $ids;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $total = 0;

        foreach ($this->input->lines as $line) {
            [$game, $moves] = $line->split('/: /');
            $sets = explode(";", $moves);
            $sets = collect($sets)->map(function ($set) {
                $pulls = collect(explode(', ', trim($set)))->map(function ($pull) {
                    $ex = explode(" ", $pull);
                    $pull = [
                        'count' => (int)$ex[0],
                        'colour' => $ex[1]
                    ];
                    return $pull;
                });
                return $pulls;
            });

            $max = [
                'red' => 0,
                'green' => 0,
                'blue' => 0,
            ];

            foreach ($sets as $pulls) {
                foreach ($pulls as $pull) {
                    if ($pull['count'] > $max[$pull['colour']]) {
                        $max[$pull['colour']] = $pull['count'];
                    }
                }
            }

            $power = $max['red'] * $max['green'] * $max['blue'];
            $total += $power;
        }
        return $total;
    }
}
