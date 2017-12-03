<?php

namespace Pdp\Exception;

use PHPUnit\Framework\TestCase;

/**
 * Class SeriouslyMalformedUrlExceptionTest
 *
 * @package Pdp\Exception
 */
class SeriouslyMalformedUrlExceptionTest extends TestCase
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

  /**
   * @expectedException Pdp\Exception\SeriouslyMalformedUrlException
   */
  public function testMessage()
  {
    $url = 'http:///example.com';

    throw new SeriouslyMalformedUrlException($url);
  }
}
