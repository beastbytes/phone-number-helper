<?php

namespace spec\BeastBytes\PhoneNumber\Helper;

use BeastBytes\PhoneNumber\Helper\PhoneNumber;
use PhpSpec\ObjectBehavior;

class PhoneNumberSpec extends ObjectBehavior
{
    const ITU_PHONE_NUMBERS = [ // number => expected result
        '+44 20 7925 0918' => '+44.2079250918',
        '+44 20-7925-0918' => '+44.2079250918',
        '+44 20.7925.0918' => '+44.2079250918',
        '+1 202-456-2121' => '+1.2024562121',
        '+1 202-456-2121x123' => '+1.2024562121x123',
        '+1 202-456-2121#123' => '+1.2024562121x123',
        '+44.2079250918' => '+44.2079250918', // ITU also matches EPP
        '+1.2024562121' => '+1.2024562121'
    ];

    const INVALID_ITU_PHONE_NUMBERS = [
        '020-7925-0918',
        '0300 126 7000',
        '202-456-2121',
        '44 20 7925 0918', // no leading `+`
    ];

    const VALID_NATIONAL_PHONE_NUMBERS = [ // number => [countries, expected result]
        '020-7925-0918' => [['GB'], '020 79250918'],
        '0300 126 7000' => ['GB', '0300 1267000'],
        '202-456-2121' => [['GB', 'US'], '(202) 456-2121'],
        '12345678' => ['SZ', '12345678'],
    ];

    const INVALID_NATIONAL_PHONE_NUMBERS = [
        '+44 20 7925 0918' => 'GB', // International number
        '202-456-2121' => ['FR', 'ES'], // US number being checked against French and Spanish formats
        '202-456-2122' => ['USA'], // alpha-2 country code
    ];

    function it_is_initializable()
    {
        $this->shouldHaveType(PhoneNumber::class);
    }

    function it_converts_from_itu_format_to_epp_format()
    {
        foreach (self::ITU_PHONE_NUMBERS as $itu => $epp) {
            self::itu2Epp($itu)->shouldBe($epp);
        }
    }

    function it_throws_an_exception_if_phone_number_not_in_itu_format()
    {
        foreach (self::INVALID_ITU_PHONE_NUMBERS as $phoneNumber) {
            $this
                ->shouldThrow(new \InvalidArgumentException('Phone number not in ITU format'))
                ->during('itu2Epp', [$phoneNumber]);
        }
    }

    function it_formats_national_phone_numbers()
    {
        foreach (self::VALID_NATIONAL_PHONE_NUMBERS as $phoneNumber => $params) {
            $this->formatN6l($phoneNumber, $params[0])->shouldBe($params[1]);
        }
    }

    function it_throws_an_exception_if_phone_number_not_in_national_list()
    {
        foreach (self::INVALID_NATIONAL_PHONE_NUMBERS as $phoneNumber => $countries) {
            $this
                ->shouldThrow(new \InvalidArgumentException('No match found for phone number'))
                ->during('formatN6l', [$phoneNumber, $countries]);
        }
    }
}
