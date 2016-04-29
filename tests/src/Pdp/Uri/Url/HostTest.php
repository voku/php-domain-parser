<?php

namespace Pdp\Uri\Url;

/**
 * Class HostTest
 *
 * @package Pdp\Uri\Url
 */
class HostTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider hostDataProvider
   *
   * @param $publicSuffix
   * @param $registrableDomain
   * @param $subdomain
   * @param $hostPart
   */
  public function test__toString($publicSuffix, $registrableDomain, $subdomain, $hostPart)
  {
    $host = new Host(
        $subdomain,
        $registrableDomain,
        $publicSuffix,
        $hostPart
    );

    self::assertEquals($hostPart, (string)$host);
  }

  public function test__toStringWhenHostPartIsNull()
  {
    $host = new Host(
        'www',
        'example.com',
        'com'
    );

    self::assertEquals('www.example.com', (string)$host);
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
    $parts = array(
        'subdomain'         => $subdomain,
        'registrableDomain' => $registrableDomain,
        'publicSuffix'      => $publicSuffix,
        'host'              => $hostPart,
    );

    $host = new Host(
        $parts['subdomain'],
        $parts['registrableDomain'],
        $parts['publicSuffix'],
        $parts['host']
    );

    self::assertEquals($parts, $host->toArray());
  }

  /**
   * @return array
   */
  public function hostDataProvider()
  {
    // $publicSuffix, $registrableDomain, $subdomain, $hostPart
    return array(
        array('com.au', 'waxaudio.com.au', 'www', 'www.waxaudio.com.au'),
        array('com', 'example.com', null, 'example.com'),
        array('com', 'cnn.com', 'edition', 'edition.cnn.com'),
        array('org', 'wikipedia.org', 'en', 'en.wikipedia.org'),
        array('uk.com', 'example.uk.com', 'a.b', 'a.b.example.uk.com'),
        array(null, null, null, 'localhost'),
    );
  }
}
