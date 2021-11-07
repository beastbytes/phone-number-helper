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

    const INVALID_PHONE_NUMERS = [
        '020-7925-0918',
        '0300 126 7000',
        '202-456-2121',
        '44 20 7925 0918', // no leading `+`
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
        foreach (self::INVALID_PHONE_NUMERS as $phoneNumber) {
            $this
                ->shouldThrow(new \InvalidArgumentException('Phone number not in ITU format'))
                ->during('itu2Epp', [$phoneNumber]);
        }
    }
}
