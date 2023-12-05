<?php

namespace App\Puzzles;

use Brick\Math\BigInteger;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Override;

class Day05IfYouGiveASeedAFertilizer extends AbstractPuzzle
{
    protected static int $day_number = 5;

    private array $seeds;

    private array $seedRanges;

    private array $maps = [];
    private array $bigMaps = [];

    private bool $break = false;

    #[Override]
    public function getPartOneAnswer(): int|string
    {
        $this->parseInput();

        $locations = [];

        foreach ($this->seeds as $seed) {
            $soil = $this->findMapping('seed', $seed);
            $fertilizer = $this->findMapping('soil', $soil);
            $water = $this->findMapping('fertilizer', $fertilizer);
            $light = $this->findMapping('water', $water);
            $temperature = $this->findMapping('light', $light);
            $humidity = $this->findMapping('temperature', $temperature);
            $location = $this->findMapping('humidity', $humidity);

            $locations[] = $location;
        }

        return min($locations);
    }

    #[Override]
    public function getPartTwoAnswer(): int|string
    {
        $this->parseInput();

        $locations = [];

//        echo "SOIL\n=====\n";
        $soilRanges = $this->findRangeMapping('seed', $this->seedRanges);

//        echo "FERTILIZER\n=====\n";
        $fertilizerRanges = $this->findRangeMapping('soil', $soilRanges);

//        echo "WATER\n=====\n";
        $waterRanges = $this->findRangeMapping('fertilizer', $fertilizerRanges);

//        echo "LIGHT\n=====\n";
        $lightRanges = $this->findRangeMapping('water', $waterRanges);

//        echo "TEMPERATURE\n=====\n";
        $temperatureRanges = $this->findRangeMapping('light', $lightRanges);

//        echo "HUMIDITY\n=====\n";
        $humidityRanges = $this->findRangeMapping('temperature', $temperatureRanges);

//        echo "LOCATION\n=====\n";
        $locationRanges = $this->findRangeMapping('humidity', $humidityRanges);

        usort($locationRanges, fn ($a, $b) => $a['start'] <=> $b['start']);

        return $locationRanges[0]['start'];
    }

    private function parseInput(): void
    {
        $this->seeds = [];
        $this->seedRanges = [];
        $this->maps = [];

        $blocks = unserialize(serialize($this->input->lines_by_block));
        $blocks->shift();

        /** @var Stringable $line */
        $line = $this->input->lines->first();
        $seeds = $line->remove('seeds: ')->explode(' ');
        $this->seeds = $seeds->toArray();

        $pairs = $seeds->chunk(2);

        foreach ($pairs as $index => $pair) {
            $start = BigInteger::of($pair->values()[0]);
            $range = BigInteger::of($pair->values()[1]);
            $this->seedRanges[] = [
                'start' => $start,
                'end' => $start->plus($range)->minus(1),
                'range' => $range,
            ];
        }

        foreach ($blocks as $block) {
            [$typeA, , $typeC] = $block->shift()->remove(' map:')->explode('-');

            $this->maps[$typeA] = [];
            $this->bigMaps[$typeA] = [];
            foreach ($block as $line) {
                [$destinationId, $sourceId, $range] = $line->explode(' ');
                $this->maps[$typeA][] = [
                    'source' => (int)$sourceId,
                    'source end' => (int)$sourceId + (int)$range - 1,
                    'destination' => (int)$destinationId,
                    'destination end' => (int)$destinationId + (int)$range - 1,
                    'range' => (int)$range,
                ];

                $this->bigMaps[$typeA][] = [
                    'source' => BigInteger::of($sourceId),
                    'source end' => BigInteger::of($sourceId)->plus($range)->minus(1),
                    'destination' => BigInteger::of($destinationId),
                    'destination end' => BigInteger::of($destinationId)->plus($range)->minus(1),
                    'range' => BigInteger::of($range),
                    'delta' => BigInteger::of($destinationId)->minus($sourceId),
                ];
            }

            usort($this->maps[$typeA], fn ($a, $b) => $a['source'] <=> $b['source']);
        }
    }

    private function findMapping(string $typeA, int $number): int
    {
        $map = $this->maps[$typeA];

        foreach ($map as $mapping) {
            if ($number >= $mapping['source'] && $number <= $mapping['source end']) {
                $result = $number - $mapping['source'] + $mapping['destination'];
                return $result;
            }
        }

        $result = $number;
        return $result;
    }

    private function findRangeMapping(string $typeA, array $ranges): array
    {
        $map = $this->bigMaps[$typeA];

        $keyer = fn ($range) => "{$range['start']}-{$range['end']}";

        $rangesToProcess = $ranges;

        $processedRanges = [];

        while (!empty($rangesToProcess)) {
            $range = array_shift($rangesToProcess);

//            echo "Range: " . $range['start'] . "-" . $range['end'] . "\n";

            foreach ($map as $mapping) {
//                echo "  Map: " . $mapping['source'] . "-" . $mapping['source end'] . "\n";
                $split = $this->splitRanges($mapping, $range);

                if (count($split) === 1) {
                    if ($split->first()['processed']) {
                        // Fully within range
                        $processedRanges[$keyer($split->first())] = $split->first();
                        continue 2;
                    } else {
                        // No overlap; continue to next mapping
                        continue;
                    }
                }

                if (count($split) === 2) {
                    $processedHalf = $split->where('processed', true)->first();
                    $unprocessedHalf = $split->where('processed', false)->first();

                    // Part that was within range
                    $processedRanges[$keyer($processedHalf)] = $processedHalf;

                    // Part that was not within range; send back to the big list for reprocessing
                    $rangesToProcess[] = $unprocessedHalf;
                    continue 2;
                }
            }

            // Anything remaining does not overlap with any of the mappings, return it as is
            $processedRanges[$keyer($range)] = $range;
        }

        return $processedRanges;
    }

    /**
     * @param BigInteger[] $mapping
     * @param BigInteger[] $range
     *
     * @return Collection<BigInteger[]>
     */
    private function splitRanges(array $mapping, array $range): Collection
    {
        $startWithinRange = $range['start']->isGreaterThanOrEqualTo($mapping['source'])
                            && $range['start']->isLessThanOrEqualTo($mapping['source end']);

        $endWithinRange = $range['end']->isGreaterThanOrEqualTo($mapping['source'])
                          && $range['end']->isLessThanOrEqualTo($mapping['source end']);

        // No overlap at all: return as-is
        if (!$startWithinRange && !$endWithinRange) {
//            echo "    No overlap\n";
            $range['processed'] = false;
            return collect([$range]);
        }

        // Fully within range: return a single range shifted up or down
        if ($startWithinRange && $endWithinRange) {
//            echo "    Full overlap\n";
            $range['start'] = $range['start']->plus($mapping['delta']);
            $range['end'] = $range['end']->plus($mapping['delta']);
            $range['processed'] = true;
            return collect([$range]);
        }

        // Range overlaps on the upper end: return two ranges, the first shifted, the second unchanged
        if ($startWithinRange && !$endWithinRange) {
//            echo "    Partial overlap upper end\n";
            $range1 = [
                'start' => $range['start']->plus($mapping['delta']),
                'end' => $mapping['source end']->plus($mapping['delta']),
                'range' => $mapping['source end']->minus($range['start'])->plus(1),
                'processed' => true,
            ];

            $range2 = [
                'start' => $mapping['source end']->plus(1),
                'end' => $range['end'],
                'range' => $range['end']->minus($mapping['source end']->plus(1))->plus(1),
                'processed' => false,
            ];

            return collect([$range1, $range2]);
        }

        // Range overlaps on the lower end: return two ranges, the first unchanged, the second shifted
        if (!$startWithinRange && $endWithinRange) {
//            echo "    Partial overlap lower end\n";
            $range1 = [
                'start' => $range['start'],
                'end' => $mapping['source']->minus(1),
                'range' => $mapping['source']->minus(1)->minus($range['start'])->plus(1),
                'processed' => false,
            ];

            $range2 = [
                'start' => $mapping['source']->plus($mapping['delta']),
                'end' => $range['end']->plus($mapping['delta']),
                'range' => $range['end']->minus($mapping['source end']->plus(1))->plus(1),
                'processed' => true,
            ];

            return collect([$range1, $range2]);
        }

        throw new \Exception('This should never happen');
    }
}
