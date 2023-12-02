<?php

namespace App\Puzzles;

class Day01Trebuchet extends AbstractPuzzle
{
    protected static int $day_number = 1;

    /**
     * @return int
     */
    public function getPartOneAnswer(): int
    {
        $lines = $this->input->lines;

        $lines = $lines->map(function ($line) {
            return preg_replace('/[^1-9]/', '', $line);
        } )->map(function ($line) {
            if (empty($line)) {
                return 0;
            }
            return (int)($line[0] .$line[strlen($line) - 1]);
        });

        return array_sum($lines->toArray());
    }

    /**
     * @return int
     */
    public function getPartTwoAnswer(): int
    {
        $lines = $this->input->lines;

        $words = [
            'one' => '1',
            'two' => '2',
            'three' => '3',
            'four' => '4',
            'five' => '5',
            'six' => '6',
            'seven' => '7',
            'eight' => '8',
            'nine' => '9',
        ];

        $lines = $lines->map(function ($line) use ($words) {
            $str = clone $line;

//            echo "Original: $line\n";
            $len = strlen($line);

            // Forward
            for ($i = 0; $i < $len; $i++) {
                if (preg_match('/[1-9]/', $str->__toString()[$i])) {
                    break;
                }
                foreach ($words as $word => $number) {
                    if (substr($str, $i, strlen($word)) === $word) {
                        $str = preg_replace("/$word/", $number, $str, 1);
                        break 2;
                    }
                }
            }

//            echo "$str\n";

            // Backwards
            $len = strlen($str);
            $rev = strrev($str);
            for ($i = 0; $i < $len; $i++) {
                if (preg_match('/[1-9]/', $rev[$i])) {
                    break;
                }
                foreach ($words as $word => $number) {
                    $revwrod = strrev($word);
                    if (substr($rev, $i, strlen($word)) === $revwrod) {
                        $rev = preg_replace("/$revwrod/", $number, $rev, 1);
                        break 2;
                    }
                }
            }
            $str = strrev($rev);

//            echo "$str\n";

            $str = preg_replace('/[^0-9]/', '', $str);
//            echo "$str\n";

            $num = (int)($str[0] .$str[strlen($str) - 1]);
//            echo "$num\n\n";

            return $num;
        });

//        dump($lines);

        return array_sum($lines->toArray());
    }
}
