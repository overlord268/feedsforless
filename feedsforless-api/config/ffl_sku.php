<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FFL SKU category map (parent_category label → 3-letter CAT code)
    |--------------------------------------------------------------------------
    |
    | Keys must match category labels used in the catalog (case-sensitive).
    |
    */
    'category_map' => [
        'Phosphates' => 'PHO',
        'Magnesium Oxide' => 'MAG',
        'Magnesium' => 'MAG',
        'Na Buffers' => 'NAB',
        'Prilled Urea' => 'URE',
        'Palm Oil' => 'PAL',
        'Amino Acids' => 'AMI',
        'Trace Minerals' => 'TRC',
        'Potassium' => 'POT',
    ],

    /*
    |--------------------------------------------------------------------------
    | FFL SKU product map (product_name → PROD code)
    |--------------------------------------------------------------------------
    |
    | Product abbreviations are fixed; new products require a manual map entry.
    |
    */
    'product_map' => [
        'Dicalcium Feed Phosphate' => 'DICAL',
        'Monocalcium Feed Phosphate' => 'MOCAL',
        'Monodicalcium Feed Phosphate' => 'MDCAL',
        'Magnesium Oxide' => 'MGOX',
        'Urea' => 'UREA',
        'Sodium Bicarbonate' => 'NAHCO3',
        'TronaCarb' => 'TRONA',
        'Buffer Pac' => 'BPAC',
    ],

    'prefix' => 'FFL',

    'pending_grade' => 'PENDING',

];
