<?php

namespace App\Support;

class UsZipStateResolver
{
    /**
     * Resolve US state abbreviation from a ZIP code (5-digit or ZIP+4).
     */
    public static function resolve(?string $zip): ?string
    {
        if ($zip === null || $zip === '') {
            return null;
        }

        if (! preg_match('/(\d{5})/', $zip, $matches)) {
            return null;
        }

        $zip5 = (int) $matches[1];
        $zip3 = (int) substr($matches[1], 0, 3);

        foreach (self::ranges() as [$from, $to, $state]) {
            if ($zip5 >= $from && $zip5 <= $to) {
                return $state;
            }
        }

        $prefixMap = self::prefixMap();

        return $prefixMap[sprintf('%03d', $zip3)] ?? null;
    }

    /**
     * @return list<array{0: int, 1: int, 2: string}>
     */
    private static function ranges(): array
    {
        return [
            [35004, 36925, 'AL'],
            [99501, 99950, 'AK'],
            [85001, 86556, 'AZ'],
            [71601, 72959, 'AR'],
            [90001, 96162, 'CA'],
            [80001, 81658, 'CO'],
            [6001, 6928, 'CT'],
            [19701, 19980, 'DE'],
            [32003, 34997, 'FL'],
            [30002, 39901, 'GA'],
            [96701, 96898, 'HI'],
            [83201, 83877, 'ID'],
            [60001, 62999, 'IL'],
            [46001, 47997, 'IN'],
            [50001, 52809, 'IA'],
            [66002, 67954, 'KS'],
            [40003, 42788, 'KY'],
            [70001, 71497, 'LA'],
            [3901, 4992, 'ME'],
            [20601, 21930, 'MD'],
            [1001, 5544, 'MA'],
            [48001, 49971, 'MI'],
            [55001, 56763, 'MN'],
            [38601, 39776, 'MS'],
            [63001, 65899, 'MO'],
            [59001, 59937, 'MT'],
            [68001, 69367, 'NE'],
            [88901, 89883, 'NV'],
            [3031, 3897, 'NH'],
            [7001, 8989, 'NJ'],
            [87001, 88439, 'NM'],
            [10001, 14925, 'NY'],
            [27006, 28909, 'NC'],
            [58001, 58856, 'ND'],
            [43001, 45999, 'OH'],
            [73001, 74966, 'OK'],
            [97001, 97920, 'OR'],
            [15001, 19640, 'PA'],
            [2801, 2940, 'RI'],
            [29001, 29945, 'SC'],
            [57001, 57799, 'SD'],
            [37010, 38589, 'TN'],
            [73301, 88595, 'TX'],
            [84001, 84791, 'UT'],
            [5001, 5907, 'VT'],
            [20101, 24658, 'VA'],
            [98001, 99403, 'WA'],
            [24701, 26886, 'WV'],
            [53001, 54990, 'WI'],
            [82001, 83128, 'WY'],
            [20001, 20599, 'DC'],
            [600, 999, 'PR'],
            [801, 988, 'VI'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function prefixMap(): array
    {
        return [
            '006' => 'PR', '007' => 'PR', '008' => 'VI', '009' => 'PR',
        ];
    }
}
