<?php

/**
 * PHP Domain Parser: Public Suffix List based URL parsing.
 *
 * @link      http://github.com/jeremykendall/php-domain-parser for the canonical source repository
 *
 * @copyright Copyright (c) 2014 Jeremy Kendall (http://about.me/jeremykendall)
 * @license   http://github.com/jeremykendall/php-domain-parser/blob/master/LICENSE MIT License
 */
namespace Pdp\HttpAdapter;

use Pdp\Exception\HttpAdapterException;

/**
 * Interface for http adapters.
 *
 * Lifted pretty much completely from William Durand's excellent Geocoder
 * project
 *
 * @link   https://github.com/willdurand/Geocoder Geocoder on GitHub
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Jeremy Kendall <jeremy@jeremykendall.net>
 */
interface HttpAdapterInterface
{
  /**
   * Returns the content fetched from a given URL.
   *
   * @param string $url
   * @param int    $timeout
   *
   * @return string Retrieved content
   *
   * @throws HttpAdapterException
   */
  public function getContent($url, $timeout = 5);
}
