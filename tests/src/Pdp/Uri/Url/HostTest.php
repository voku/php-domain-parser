<?php

namespace Pdp\Uri\Url;

use PHPUnit\Framework\TestCase;

/**
 * Class HostTest
 *
 * @internal
 */
final class HostTest extends TestCase
{
    /**
     * @dataProvider hostDataProvider
     *
     * @param $publicSuffix
     * @param $registrableDomain
     * @param $subdomain
     * @param $hostPart
     */
    public function testToString($publicSuffix, $registrableDomain, $subdomain, $hostPart)
    {
        $host = new Host(
            $subdomain,
            $registrableDomain,
            $publicSuffix,
            $hostPart
        );

        static::assertSame($hostPart, (string) $host);
    }

    public function testGetPublicSuffix()
    {
        $host = new Host(
            'www',
            'example.com',
            'com'
        );

        static::assertSame('com', $host->getPublicSuffix());
    }

    public function testToStringWhenHostPartIsNull()
    {
        $host = new Host(
            'www',
            'example.com',
            'com'
        );

        static::assertSame('www.example.com', (string) $host);
    }

    /**
     * @dataProvider hostDataProvider
     *
     * @param $publicSuffix
     * @param $registrableDomain
     * @param $subdomain
     * @param $hostPart
     */
    public function testToArray($publicSuffix, $registrableDomain, $subdomain, $hostPart)
    {
        $parts = [
            'subdomain'         => $subdomain,
            'registrableDomain' => $registrableDomain,
            'publicSuffix'      => $publicSuffix,
            'host'              => $hostPart,
        ];

        $host = new Host(
            $parts['subdomain'],
            $parts['registrableDomain'],
            $parts['publicSuffix'],
            $parts['host']
        );

        static::assertSame($parts, $host->toArray());
    }

    /**
     * @return array
     */
    public function hostDataProvider()
    {
        // $publicSuffix, $registrableDomain, $subdomain, $hostPart
        return [
            ['com.au', 'waxaudio.com.au', 'www', 'www.waxaudio.com.au'],
            ['com', 'example.com', null, 'example.com'],
            ['com', 'cnn.com', 'edition', 'edition.cnn.com'],
            ['org', 'wikipedia.org', 'en', 'en.wikipedia.org'],
            ['uk.com', 'example.uk.com', 'a.b', 'a.b.example.uk.com'],
            [null, null, null, 'localhost'],
        ];
    }
}
