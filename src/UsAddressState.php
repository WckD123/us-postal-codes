<?php

namespace WckD123\UsPostalCodes;

/**
 * The places a US address can be in - the 50 states, DC and the territories - keyed by USPS code.
 * Use UsState when only the 50 states and DC are allowed.
 */
class UsAddressState
{
    public const TERRITORY_NAMES_BY_CODE = [
        'AS' => 'American Samoa',
        'GU' => 'Guam',
        'MP' => 'Northern Mariana Islands',
        'PR' => 'Puerto Rico',
        'VI' => 'U.S. Virgin Islands',
    ];

    // Territories come last in the list, after the 50 states and DC.
    public const NAMES_BY_CODE = [
        ...UsState::NAMES_BY_CODE,
        ...self::TERRITORY_NAMES_BY_CODE,
    ];
}
