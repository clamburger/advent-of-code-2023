<?php

namespace App\Puzzles;

use App\Day13\MirrorMap;
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
            $map = MirrorMap::createFromBlock($block);

//            echo "=====================\n";
//            echo "Checking block $index\n";

            $total += $map->getValue();
        }

        return $total;
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $blocks = $this->input->lines_by_block;

        $total = 0;

        foreach ($blocks as $index => $block) {
            $map = MirrorMap::createFromBlock($block);

//            echo "=====================\n";
//            echo "Checking block $index\n";

            $originalValue = $map->getValue();
            $maps = $map->getSmudgedMaps();

            $valid = collect();

            foreach ($maps as $subIndex => $subMap) {
//                echo " Submap $subIndex\n";
                $value = $subMap->getValue();
                if ($value !== null) {
                    $total += $value;
//                    echo " Found value $value on block $index submap $subIndex\n";
//                    echo implode("\n", $subMap->toArray()) . "\n";
//                    echo " Original value was $originalValue\n";
                    $valid[] = ['subindex' => $subIndex, 'value' => $value];
                    break;
                }
            }

            if ($valid->pluck('value')->unique()->count() > 1) {
                throw new \Exception("Too many valid");
            }
        }

        return $total;
    }
}
