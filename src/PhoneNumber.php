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
}