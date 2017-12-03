<?php

namespace Pdp\HttpAdapter;

use PHPUnit\Framework\TestCase;

/**
 * Class CurlHttpAdapterTest
 *
 * @package Pdp\HttpAdapter
 */
class CurlHttpAdapterTest extends TestCase
{
  /**
   * @var HttpAdapterInterface
   */
  protected $adapter;

  protected function setUp()
  {
    if (!function_exists('curl_init')) {
      self::markTestSkipped('cURL has to be enabled.');
    }

    $this->adapter = new CurlHttpAdapter();
  }

  protected function tearDown()
  {
    $this->adapter = null;
  }

  public function testGetContent()
  {
    $content = $this->adapter->getContent('http://www.google.com');
    self::assertNotNull($content);
    self::assertContains('google', $content);
  }

  /**
   * @expectedException Pdp\Exception\HttpAdapterException
   */
  public function testExceptionBadUrl()
  {
    $this->adapter->getContent('https://aaaa.aaaa');
  }

  /**
   * @expectedException Pdp\Exception\HttpAdapterException
   */
  public function testExceptionMalformat()
  {
    $this->adapter->getContent('https://google.com:9996543/');
  }

  /**
   * @expectedException Pdp\Exception\HttpAdapterException
   */
  public function testExceptionCouldntConnect()
  {
    $this->adapter->getContent('https://google.com:999/');
  }
}
