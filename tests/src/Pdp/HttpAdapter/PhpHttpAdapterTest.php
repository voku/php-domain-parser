<?php

namespace Pdp\HttpAdapter;

use PHPUnit\Framework\TestCase;

/**
 * Class PhpHttpAdapterTest
 *
 * @package Pdp\HttpAdapter
 */
class PhpHttpAdapterTest extends TestCase
{
  /**
   * @var HttpAdapterInterface
   */
  protected $adapter;

  protected function setUp()
  {
    $this->adapter = new PhpHttpAdapter();
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
}
