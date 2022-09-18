<?php

namespace Tests;

use BeastBytes\N6lPhoneNumber\PHP\N6lPhoneNumberData;
use BeastBytes\PhoneNumber\Helper\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
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
        $this->assertSame(
            $formattedNumber,
            PhoneNumber::formatN6l($originalNumber, $countries, new N6lPhoneNumberData())
        );
    }

    /**
     * @dataProvider badNationalPhoneNumberProvider
     */
    function test_it_throws_an_exception_if_phone_number_not_in_national_format($phoneNumber, $country)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Phone number $phoneNumber does not match country $country pattern");
        PhoneNumber::formatN6l($phoneNumber, $country, new N6lPhoneNumberData());
    }

    /**
     * @dataProvider badCountryProvider
     */
    function test_it_throws_an_exception_if_bad_country(string $country)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Country $country not found in list of national phone number formats");
        PhoneNumber::formatN6l('202-456-2122', $country, new N6lPhoneNumberData());
    }

    public function itu2eppProvider()
    {
        return [ // epp, itu
            ['+44.2079250918', '+44 20 7925 0918'],
            ['+44.2079250918', '+44 20-7925-0918'],
            ['+44.2079250918', '+44 20.7925.0918'],
            ['+1.2024562121', '+1 202-456-2121'],
            ['+1.2024562121x123', '+1 202-456-2121x123'],
            ['+1.2024562121x123', '+1 202-456-2121#123'],
            ['+44.2079250918', '+44.2079250918'], // ITU also matches EPP
            ['+1.2024562121', '+1.2024562121'],
        ];
    }

    public function badItuProvider()
    {
        return [
            'UK local number 1' => ['020-7925-0918'],
            'UK local number 2' => ['0300 126 7000'],
            'US local number' => ['202-456-2121'],
            'no leading `+`' => ['44 20 7925 0918'],
        ];
    }

    public function nationalPhoneNumberProvider()
    {
        return [ // expected result, number, countries
            ['020 79250918', '020-7925-0918', 'GB'],
            ['0300 1267000', '0300 126 7000', 'GB'],
            ['(202) 456-2121', '202-456-2121', 'US'],
            ['12345678', '12345678', 'SZ'],
        ];
    }

    public function badNationalPhoneNumberProvider()
    {
        return [
            'International number' => ['+44 20 7925 0918',  'GB'],
            'US number, Spanish format' => ['202-456-2121',  'ES'],
        ];
    }

    public function badCountryProvider(): array
    {
        return [
            'non-existent code' => ['XX'],
            'alpha-3 code' => ['GBR'],
            'too short' => ['G'],
            'too long' => ['GBRT'],
            'number string' => ['12']
        ];
    }
}
