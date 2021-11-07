<?php
/**
 * @copyright Copyright (c) 2021 BeastBytes - All Rights Reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\PhoneNumber\Helper;

/**
 *
 * @author Chris Yates
 * @package phoneNumberHelper
 */
final class PhoneNumber
{
    /**
     * @var string Regex pattern to extract parts of international phone numbers
     */
    private const PATTERN = '/^(\+\d{1,3})\D+((\d+[\s\.-]*)+)([\D]+(\d+))?/';

    /**
     * Convert an international phone number in ITU-T Recommendation E.123 format to EPP format
     *
     * @link https://www.itu.int/rec/T-REC-E.123
     * @link https://www.rfc-editor.org/rfc/rfc4933.html#section-2.5 Extensible Provisioning Protocol (EPP)
     *
     * @param string $value Phone number in ITU-T E.123 format
     * @return string Phone number in EPP format
     * @throws \InvalidArgumentException Phone number not in ITU format
     */
    public static function itu2Epp(string $value): string
    {
        if (preg_match(self::PATTERN, $value, $matches) === 0) {
            throw new \InvalidArgumentException('Phone number not in ITU format');
        }

        // $matches[1] => country code
        // $matches[2] => national significant number, potentially containing non-digits (e.g. white space)
        // $matches[5] => extension - if given
        return $matches[1] .
            '.' . preg_replace('/(\D)/', '', $matches[2])
            . (isset($matches[5]) ? 'x' . $matches[5] : '');
    }

    /**
     * Formats national phone numbers
     *
     * @param string $value Phone number to be formatted
     * @param ?string|array $countries List of countries to check. If null all the countries in $n6lPatterns are
     * checked.
     * @param array|null $n6lPatterns National patterns to use for formatting. Array keys are the countries; the values
     * are either:
     * * false - the country is not available
     * * string - regex pattern to match. In this case the phone number is returned unaltered if it matches
     * * array - the first element is the regex pattern to match and the second the replacement pattern
     * @return string The formatted phone number
     * @throws \InvalidArgumentException If no match is found
     */
    public function formatN6l(string $value, string|array $countries = null, ?array $n6lPatterns = null): string
    {
        if ($n6lPatterns === null) {
            $n6lPatterns = require(__DIR__ . DIRECTORY_SEPARATOR . 'n6lPatterns.php');
        }

        if ($countries === null) {
            $countries = array_keys($n6lPatterns);
        } elseif (is_string($countries)) {
            $countries = [$countries];
        }

        foreach ($countries as $country) {
            if (array_key_exists($country, $n6lPatterns)) {
                if (is_string($n6lPatterns[$country]) && preg_match($n6lPatterns[$country], $value)) {
                    return $value;
                }

                if (is_array($n6lPatterns[$country]) && preg_match($n6lPatterns[$country][0], $value)) {
                    return trim(preg_replace($n6lPatterns[$country][0], $n6lPatterns[$country][1], $value));
                }
            }
        }

        throw new \InvalidArgumentException('No match found for phone number');
    }
}
