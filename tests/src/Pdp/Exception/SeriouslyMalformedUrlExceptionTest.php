<?php

namespace Pdp\Exception;

/**
 * Class SeriouslyMalformedUrlExceptionTest
 *
 * @package Pdp\Exception
 */
class SeriouslyMalformedUrlExceptionTest extends \PHPUnit_Framework_TestCase
{
  public function testInstanceOfPdpException()
  {
    self::assertInstanceOf(
        'Pdp\Exception\PdpException',
        new SeriouslyMalformedUrlException()
    );
  }

  public function testInstanceOfInvalidArgumentException()
  {
    self::assertInstanceOf(
        'InvalidArgumentException',
        new SeriouslyMalformedUrlException()
    );
  }

  public function testMessage()
  {
    $url = 'http:///example.com';
    $this->setExpectedException(
        'Pdp\Exception\SeriouslyMalformedUrlException',
        sprintf('"%s" is one seriously malformed url.', $url)
    );

    throw new SeriouslyMalformedUrlException($url);
  }
}
