<?php

namespace WckD123\UsPostalCodes;

class PostalCodeLookup
{
    public const STATE = 'state';
    public const CITY  = 'city';

    protected const ZIP_PATTERN = '/^\d{5}(-?\d{4})?$/';

    protected const PREFIX_LENGTH = 3;

    protected string $dataDirectory;

    public function __construct(?string $dataDirectory = null)
    {
        $this->dataDirectory = $dataDirectory ?? \dirname(__DIR__) . '/data';
    }

    /**
     * Looks up a US ZIP or ZIP+4 (with or without the dash). Returns ['state' => USPS code, 'city' => name] or null.
     */
    public function find(string $zip): ?array
    {
        $code = $this->normaliseZip($zip);

        if ($code === null)
        {
            return null;
        }

        $path = $this->dataDirectory . '/' . substr($code, 0, self::PREFIX_LENGTH) . '.php';

        // A missing prefix file means "no data", not an error.
        if (is_file($path) === false)
        {
            return null;
        }

        $entries = require $path;

        return $entries[$code] ?? null;
    }

    /**
     * Checks the whole input before cutting or building a path, because this method is public
     * and cutting first would let 123456 look up 12345.
     */
    protected function normaliseZip(string $zip): ?string
    {
        $trimmed = trim($zip);

        return preg_match(self::ZIP_PATTERN, $trimmed) === 1 ? substr($trimmed, 0, 5) : null;
    }
}
