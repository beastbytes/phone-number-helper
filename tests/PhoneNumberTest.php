<?php

namespace Tests;

use BeastBytes\PhoneNumber\Helper\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    const ITU_2_EPP = [ // epp, itu
        ['+44.2079250918', '+44 20 7925 0918'],
        ['+44.2079250918', '+44 20-7925-0918'],
        ['+44.2079250918', '+44 20.7925.0918'],
        ['+1.2024562121', '+1 202-456-2121'],
        ['+1.2024562121x123', '+1 202-456-2121x123'],
        ['+1.2024562121x123', '+1 202-456-2121#123'],
        ['+44.2079250918', '+44.2079250918'], // ITU also matches EPP
        ['+1.2024562121', '+1.2024562121'],
    ];

    const BAD_ITU_PHONE_NUMBERS = [
        ['020-7925-0918'],
        ['0300 126 7000'],
        ['202-456-2121'],
        ['44 20 7925 0918'], // no leading `+`
    ];

    const NATIONAL_PHONE_NUMBERS = [ // expected result, number, countries
        ['020 79250918', '020-7925-0918', ['GB']],
        ['0300 1267000', '0300 126 7000', 'GB'],
        ['(202) 456-2121', '202-456-2121', ['GB', 'US']],
        ['12345678', '12345678', 'SZ'],
    ];

    const BAD_NATIONAL_PHONE_NUMBERS = [
        'International number' => ['+44 20 7925 0918',  'GB'],
        'US number being checked against French and Spanish formats' => ['202-456-2121',  ['FR', 'ES']],
        'alpha-3 country code' => ['202-456-2122', ['USA']],
    ];

    /**
     * @dataProvider itu2eppProvider
     */
    function test_it_converts_from_itu_format_to_epp_format($epp, $itu)
    {
        $this->assertSame($epp, PhoneNumber::itu2Epp($itu));
    }

    /**
     * @dataProvider badItuProvider
     */
    function test_it_throws_an_exception_if_phone_number_not_in_itu_format($badItu)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Phone number not in ITU format');

        PhoneNumber::itu2Epp($badItu);
    }

    /**
     * @dataProvider nationalPhoneNumberProvider
     */
    function test_it_formats_national_phone_numbers($formattedNumber, $originalNumber, $countries)
    {
        $this->assertSame($formattedNumber, PhoneNumber::formatN6l($originalNumber, $countries));
    }

    /**
     * @dataProvider badNationalPhoneNumberProvider
     */
    function test_it_throws_an_exception_if_phone_number_not_in_national_list($phoneNumber, $countries)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No match found for phone number');
        PhoneNumber::formatN6l($phoneNumber, $countries);
    }

    public function itu2eppProvider()
    {
        return self::ITU_2_EPP;
    }

    public function badItuProvider()
    {
        return self::BAD_ITU_PHONE_NUMBERS;
    }

    public function nationalPhoneNumberProvider()
    {
        return self::NATIONAL_PHONE_NUMBERS;
    }

    public function badNationalPhoneNumberProvider()
    {
        return self::BAD_NATIONAL_PHONE_NUMBERS;
    }
}