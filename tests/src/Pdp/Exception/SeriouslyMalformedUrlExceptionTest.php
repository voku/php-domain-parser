<?php

namespace Pdp\Exception;

use PHPUnit\Framework\TestCase;

/**
 * Class SeriouslyMalformedUrlExceptionTest
 *
 * @internal
 */
final class SeriouslyMalformedUrlExceptionTest extends TestCase
{
    public function testInstanceOfPdpException()
    {
        static::assertInstanceOf(
            'Pdp\Exception\PdpException',
            new SeriouslyMalformedUrlException()
        );
    }

    public function testInstanceOfInvalidArgumentException()
    {
        static::assertInstanceOf(
            'InvalidArgumentException',
            new SeriouslyMalformedUrlException()
        );
    }

    public function testMessage()
    {
        $this->expectException(\Pdp\Exception\SeriouslyMalformedUrlException::class);

        $url = 'http:///example.com';

        throw new SeriouslyMalformedUrlException($url);
    }
}
