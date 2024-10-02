<?php
/**
 * @copyright Copyright (c) 2024 BeastBytes - All Rights Reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\PhoneNumber\Helper;

use BeastBytes\PhoneNumber\N6l\N6lPhoneNumberDataInterface;
use InvalidArgumentException;

final class PhoneNumber
{
    public const INVALID_ITU_FORMAT_MESSAGE = 'Phone number not in ITU format';
    public const INVALID_N6L_PHONE_NUMBER_MESSAGE = '{value} is not a valid national phone number for {country}';
    /**
     * @var string Regex pattern to extract parts of international phone numbers
     */
    private const PATTERN = '/^(\+\d{1,3})\D+((\d+[\s.-]*)+)(\D+(\d+))?/';

    /**
     * Formats national phone numbers.
     *
     * Formatting includes number grouping, group separators, etc.
     * If there is not a replacement format for the country the phone is returned unchanged.
     *
     * @param string $value Phone number to be formatted
     * @param string $country The country whose format to use
     * @param N6lPhoneNumberDataInterface $n6lPhoneNumberData
     * @return string The formatted phone number
     * @throws InvalidArgumentException If the phone number is not an acceptable format for the country
     */
    public static function formatN6l(
        string $value,
        string $country,
        N6lPhoneNumberDataInterface $n6lPhoneNumberData
    ): string
    {
        /** @psalm-var array{pattern: non-empty-string, replacement?: non-empty-string} $n6l */
        $n6l = $n6lPhoneNumberData->getN6l($country);

        $result = preg_match($n6l['pattern'], $value);

        if ((bool) $result === false) {
            throw new InvalidArgumentException(strtr(
                self::INVALID_N6L_PHONE_NUMBER_MESSAGE,
                [
                    '{value}' => $value,
                    '{country}' => $country,
                ]
            ));
        }

        return (array_key_exists('replacement', $n6l)
            ? preg_replace($n6l['pattern'], $n6l['replacement'], $value)
            : $value
        );
    }

    /**
     * Convert an international phone number in ITU-T Recommendation E.123 format to EPP format
     *
     * @link https://www.itu.int/rec/T-REC-E.123
     * @link https://www.rfc-editor.org/rfc/rfc4933.html#section-2.5 Extensible Provisioning Protocol (EPP)
     *
     * @param string $value Phone number in ITU-T E.123 format
     * @return string Phone number in EPP format
     * @throws InvalidArgumentException Phone number not in ITU format
     */
    public static function itu2Epp(string $value): string
    {
        if (!(bool) preg_match(self::PATTERN, $value, $matches)) {
            throw new InvalidArgumentException(self::INVALID_ITU_FORMAT_MESSAGE);
        }

        // $matches[1] => country code
        // $matches[2] => national significant number, potentially containing non-digits (e.g. white space)
        // $matches[4] => extension including identifier - if given
        // $matches[5] => extension number - if given
        return $matches[1] . '.'
            . preg_replace('/(\D)/', '', $matches[2])
            . (isset($matches[4]) ? 'x' . $matches[5] : '');
    }

    /**
     * Converts a national number to EPP format
     *
     * @param string $value Phone number to be formatted
     * @param string $country The country whose format to use
     * @param N6lPhoneNumberDataInterface $n6lPhoneNumberData
     * @return string The formatted phone number
     * @throws InvalidArgumentException If the phone number is not in an acceptable format for the country
     */
    public static function n6l2Epp(
        string $value,
        string $country,
        N6lPhoneNumberDataInterface $n6lPhoneNumberData
    ): string
    {
        /** @psalm-var array{pattern: non-empty-string, replacement?: non-empty-string} $epp */
        $n6l = $n6lPhoneNumberData->getN6l($country);

        if (!(bool) preg_match($n6l['pattern'], $value)) {
            throw new InvalidArgumentException(strtr(
                self::INVALID_N6L_PHONE_NUMBER_MESSAGE,
                [
                    '{value}' => $value,
                    '{country}' => $country,
                ]
            ));
        }

        /** @psalm-var array{pattern?: non-empty-string, idc: non-empty-string} $epp */
        $epp = $n6lPhoneNumberData->getEpp($country);
        $ext = '';

        // Extract extension
        $pos = strrpos($value, '#');
        if ($pos === false) {
            $pos = strrpos($value, 'x');
        }

        if (is_int($pos)) {
            $ext = 'x' . substr($value, ++$pos);
            $value = substr($value, 0, $pos);
        }
        //

        if (array_key_exists('pattern', $epp)) {
            $value = preg_replace($epp['pattern'], '', $value);
        }

        return '+' . $epp['idc'] . '.' . $value . $ext;
    }
}
