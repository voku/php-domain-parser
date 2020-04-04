<?php

namespace Pdp\HttpAdapter;

use PHPUnit\Framework\TestCase;

/**
 * Class CurlHttpAdapterTest
 *
 * @internal
 */
final class CurlHttpAdapterTest extends TestCase
{
    /**
     * @var HttpAdapterInterface
     */
    protected $adapter;

    protected function setUp()
    {
        if (!\function_exists('curl_init')) {
            static::markTestSkipped('cURL has to be enabled.');
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
        static::assertNotNull($content);
        static::assertContains('google', $content);
    }

    public function testExceptionBadUrl()
    {
        $this->expectException(\Pdp\Exception\HttpAdapterException::class);

        $this->adapter->getContent('https://aaaa.aaaa');
    }

    public function testExceptionMalformat()
    {
        $this->expectException(\Pdp\Exception\HttpAdapterException::class);

        $this->adapter->getContent('https://google.com:9996543/');
    }

    public function testExceptionCouldntConnect()
    {
        $this->expectException(\Pdp\Exception\HttpAdapterException::class);

        $this->adapter->getContent('https://google.com:999/');
    }
}
