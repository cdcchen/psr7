<?php

namespace spec\cdcchen\psr7;

use cdcchen\psr7\Cookie;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CookieSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Cookie::class);
    }
}
