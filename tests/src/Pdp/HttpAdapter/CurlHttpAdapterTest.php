<?php

namespace Pdp\HttpAdapter;

/**
 * Class CurlHttpAdapterTest
 *
 * @package Pdp\HttpAdapter
 */
class CurlHttpAdapterTest extends \PHPUnit_Framework_TestCase
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

  public function testExceptionBadUrl()
  {
    $this->setExpectedException('Pdp\Exception\HttpAdapterException', '', CURLE_COULDNT_RESOLVE_HOST);
    $this->adapter->getContent('https://aaaa.aaaa');
  }

  public function testExceptionMalformat()
  {
    $this->setExpectedException('Pdp\Exception\HttpAdapterException', '', CURLE_URL_MALFORMAT);
    $this->adapter->getContent('https://google.com:9996543/');
  }

  public function testExceptionCouldntConnect()
  {
    $this->setExpectedException('Pdp\Exception\HttpAdapterException', '', CURLE_COULDNT_CONNECT);
    $this->adapter->getContent('https://google.com:999/');
  }
}
