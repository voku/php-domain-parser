<?php

namespace Pdp\Uri;

use Pdp\Parser;
use Pdp\PublicSuffixList;
use Pdp\PublicSuffixListManager;
use Pdp\Uri\Url\Host;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlTest
 *
 * @internal
 */
final class UrlTest extends TestCase
{
    /**
     * @var Url
     */
    protected $url;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var string Url spec
     */
    protected $spec = 'http://anonymous:guest@example.com:8080/path/to/index.php/foo/bar.xml?baz=dib#anchor';

    /**
     * @var PublicSuffixList Public Suffix List
     */
    protected $psl;

    protected function setUp()
    {
        parent::setUp();
        /** @noinspection RealpathOnRelativePathsInspection */
        $file = \realpath(\dirname(__DIR__) . '/../../../data/' . PublicSuffixListManager::PDP_PSL_PHP_FILE);
        $psl = new PublicSuffixList($file);
        $this->parser = new Parser($psl);
        $this->url = $this->parser->parseUrl($this->spec);
    }

    public function testConstruct()
    {
        $url = new Url(
            'http',
            'anonymous',
            'guest',
            new Host(
                null,
                'example.com',
                'com'
            ),
            null,
            '/path/to/index.php/foo/bar.xml',
            'baz=dib',
            'anchor'
        );

        static::assertInstanceOf(Url::class, $url);
    }

    public function testToString()
    {
        static::assertSame($this->spec, (string) $this->url);
    }

    public function testGetSchemeless()
    {
        $schemeless = \substr_replace($this->spec, '', 0, 5);
        static::assertSame($schemeless, $this->url->getSchemeless());
    }

    public function testToArray()
    {
        $expected = [
            'scheme'            => 'http',
            'user'              => 'anonymous',
            'pass'              => 'guest',
            'host'              => 'example.com',
            'subdomain'         => null,
            'registrableDomain' => 'example.com',
            'publicSuffix'      => 'com',
            'port'              => '8080',
            'path'              => '/path/to/index.php/foo/bar.xml',
            'query'             => 'baz=dib',
            'fragment'          => 'anchor',
        ];

        static::assertEquals($expected, $this->url->toArray());
    }

    /**
     * @group issue18
     *
     * @see   https://github.com/jeremykendall/php-domain-parser/issues/18
     */
    public function testFtpUrlToString()
    {
        $ftpUrl = 'ftp://ftp.somewhere.com';
        $url = $this->parser->parseUrl($ftpUrl);
        static::assertSame($ftpUrl, (string) $url);
    }

    /**
     * This test fixes #29. It has been updated due to a change suggested in #46.
     * The original $expected value was 'http://яндекс.рф', as parsing would add
     * 'http://' to URLs that did not have a scheme. That behavior has been removed.
     * The new $expected result is 'яндекс.рф'.
     *
     * @group issue29
     * @group issue46
     *
     * @see   https://github.com/jeremykendall/php-domain-parser/issues/29
     * @see   https://github.com/jeremykendall/php-domain-parser/issues/46
     */
    public function testIdnToAscii()
    {
        $idn = 'Яндекс.РФ';
        $expected = 'яндекс.рф';
        $url = $this->parser->parseUrl($idn);
        $actual = (string) $url;

        static::assertSame($expected, $actual);
    }

    /**
     * Scheme should not be URL encoded.
     *
     * @group issue46
     * @group issue51
     *
     * @see   https://tools.ietf.org/html/rfc3986#section-3.1
     */
    public function testToStringDoesNotUrlEncodeScheme()
    {
        // The '+' should not be URL encoded when output to string
        $spec = 'fake-scheme+RFC-3986.compliant://www.graphstory.com';
        $expected = 'fake-scheme+rfc-3986.compliant://www.graphstory.com';
        $url = $this->parser->parseUrl($spec);
        static::assertSame($expected, (string) $url);
    }

    /**
     * Scheme should be output in lowercase regardless of case of original arg.
     *
     * @group issue51
     *
     * @see   https://tools.ietf.org/html/rfc3986#section-3.1
     */
    public function testSchemeAlwaysConvertedToLowerCasePerRFC3986()
    {
        $spec = 'HttPS://www.google.com';
        $expected = 'https://www.google.com';
        $url = $this->parser->parseUrl($spec);
        static::assertSame($expected, (string) $url);
    }

    /**
     * Scheme should return null when scheme is not provided.
     *
     * @group issue53
     *
     * @see   https://github.com/jeremykendall/php-domain-parser/issues/53
     */
    public function testSchemeReturnsNullIfNotProvidedToParser()
    {
        $spec = 'google.com';
        $url = $this->parser->parseUrl($spec);
        static::assertNull($url->getScheme());
    }
}
