<?php

namespace Pdp\HttpAdapter;

use PHPUnit\Framework\TestCase;

/**
 * Class PhpHttpAdapterTest
 *
 * @internal
 */
final class PhpHttpAdapterTest extends TestCase
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
        static::assertNotNull($content);
        static::assertContains('google', $content);
    }
}
