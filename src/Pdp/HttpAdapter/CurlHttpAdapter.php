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

/**
 * cURL http adapter.
 *
 * Lifted pretty much completely from William Durand's excellent Geocoder
 * project
 *
 * @link   https://github.com/willdurand/Geocoder Geocoder on GitHub
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Jeremy Kendall <jeremy@jeremykendall.net>
 */
class CurlHttpAdapter implements HttpAdapterInterface
{
  /**
   * {@inheritdoc}
   */
  public function getContent($url, $timeout = 5)
  {
    // init
    $content = false;

    try {

      $ch = curl_init();

      if (false === $ch) {
        throw new \Exception('failed to initialize');
      }

      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Domain-Parser cURL Request');

      $content = curl_exec($ch);
      if (false === $content) {
        throw new \Exception(curl_error($ch), curl_errno($ch));
      }

      curl_close($ch);
    } catch (\Exception $e) {
      trigger_error(
          sprintf(
              'Curl failed with error #%d: %s',
              $e->getCode(), $e->getMessage()
          ),
          E_USER_ERROR
      );
    }


    return $content;
  }
}
