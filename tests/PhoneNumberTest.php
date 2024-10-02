<?php

namespace BeastBytes\PhoneNumber\Helper\Tests;

use BeastBytes\PhoneNumber\N6l\PHP\N6lPhoneNumberData;
use BeastBytes\PhoneNumber\Helper\PhoneNumber;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    private static N6lPhoneNumberData $h6lPhoneNumberData;

    #[BeforeClass]
    public static function init(): void

    {
        self::$h6lPhoneNumberData = new N6lPhoneNumberData();
    }

    #[DataProvider('itu2EppPass')]
    function test_itu_to_epp_pass(string $epp, string $itu): void
    {
        $this->assertSame($epp, PhoneNumber::itu2Epp($itu));
    }

    #[DataProvider('itu2EppFail')]
    function test_itu_to_epp_fail(string $Itu): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(PhoneNumber::INVALID_ITU_FORMAT_MESSAGE);
        PhoneNumber::itu2Epp($Itu);
    }
    #[DataProvider('n6l2EppPass')]
    function test_n6l_to_epp_pass(string $epp, string $n6l, string $country): void
    {
        $this->assertSame($epp, PhoneNumber::n6l2Epp($n6l, $country, self::$h6lPhoneNumberData));
    }

    #[DataProvider('n6lFail')]
    function test_n6l_to_epp_fail(string $n6l, string $country): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(strtr(
            PhoneNumber::INVALID_N6L_PHONE_NUMBER_MESSAGE,
            [
                '{value}' => $n6l,
                '{country}' => $country,
            ]
        ));
        PhoneNumber::n6l2Epp($n6l, $country, self::$h6lPhoneNumberData);
    }

    #[DataProvider('formatN6lPass')]
    function test_format_n6l(string $formattedNumber, string $originalNumber, string $countries): void
    {
        $this->assertSame(
            $formattedNumber,
            PhoneNumber::formatN6l($originalNumber, $countries, self::$h6lPhoneNumberData)
        );
    }

    #[DataProvider('n6lFail')]
    function test_format_n6l_fail(string $phoneNumber, string $country): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(strtr(
            PhoneNumber::INVALID_N6L_PHONE_NUMBER_MESSAGE,
            [
                '{value}' => $phoneNumber,
                '{country}' => $country,
            ]
        ));
        PhoneNumber::formatN6l($phoneNumber, $country, self::$h6lPhoneNumberData);
    }

    #[DataProvider('countryFail')]
    function test_country_fail(string $country): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(strtr(
            N6lPhoneNumberData::COUNTRY_NOT_FOUND_EXCEPTION_MESSAGE,
            [
                '{country}' => $country,
            ]
        ));
        PhoneNumber::formatN6l('202-456-2122', $country, self::$h6lPhoneNumberData);
    }

    // End Tests //

    // Data Providers //

    public static function itu2eppPass(): \Generator
    {
        foreach ([ // epp, itu
            ['+44.2079250918', '+44 20 7925 0918'],
            ['+44.2079250918', '+44 20-7925-0918'],
            ['+44.2079250918', '+44 20.7925.0918'],
            ['+1.2024562121', '+1 202-456-2121'],
            ['+1.2024562121x123', '+1 202-456-2121x123'],
            ['+1.2024562121x123', '+1 202-456-2121#123'],
            ['+44.2079250918', '+44.2079250918'], // ITU also matches EPP
            ['+1.2024562121', '+1.2024562121'],
        ] as $name => $data) {
            yield $name => $data;
        }
    }

    public static function itu2EppFail(): \Generator
    {
        foreach ([
            'UK local number 1' => ['020-7925-0918'],
            'UK local number 2' => ['0300 126 7000'],
            'US local number' => ['202-456-2121'],
            'no leading `+`' => ['44 20 7925 0918'],
        ] as $name => $data) {
            yield $name => $data;
        }
    }

    public static function formatN6lPass(): \Generator
    {
        foreach ([ // expected result, number, countries
            ['020 79250918', '020-7925-0918', 'GB'],
            ['0300 1267000', '0300 126 7000', 'GB'],
            ['202 456 2121', '202-456-2121', 'US'],
            ['12345678', '12345678', 'SZ'],
        ] as $name => $data) {
            yield $name => $data;
        }
    }

    public static function n6lFail(): \Generator
    {
        foreach ([
            'International number' => ['+44 20 7925 0918',  'GB'],
            'US number, Spanish format' => ['202-456-2121',  'ES'],
            'Badly formatted number' => ['(650) 253-000x404', 'US']
        ] as $name => $data) {
            yield $name => $data;
        }
    }

    public static function N6l2EppPass(): \Generator
    {
        foreach ([
            ['+44.2079250918', '020-7925-0918', 'GB'],
            ['+44.3001267000', '0300 126 7000', 'GB'],
            ['+1.2024562121', '202-456-2121', 'US'],
            ['+268.12345678', '12345678', 'SZ'],
            ['+1.6502530000x404', '(650) 253-0000x404', 'US'],
        ] as $name => $data) {
            yield $name => $data;
        }
    }

    public static function countryFail(): \Generator
    {
        foreach ([
            'non-existent code' => ['XX'],
            'alpha-3 code' => ['GBR'],
            'too short' => ['G'],
            'too long' => ['GBRT'],
            'number string' => ['12']
        ] as $name => $data) {
            yield $name => $data;
        }
    }
}
