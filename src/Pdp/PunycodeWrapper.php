<?php

namespace Pdp;

use TrueBV\Punycode;

class PunycodeWrapper
{
  /**
   * @var Punycode
   */
  private static $punycode;

  /**
   * @var bool
   */
  private $idnSupport = false;

  public function __construct()
  {
    if (function_exists('idn_to_ascii') && function_exists('idn_to_utf8')) {
      $this->idnSupport = true;
      return;
    }

    if (self::$punycode === null) {
      self::$punycode = new Punycode();
    }
  }

  /**
   * Encode a domain to its Punycode version
   *
   * @param string $input Domain name in Unicode to be encoded
   *
   * @return string Punycode representation in ASCII
   */
  public function encode($input)
  {
    if ($this->idnSupport === true) {
      return idn_to_ascii($input);
    }

    return self::$punycode->encode($input);
  }

  /**
   * Decode a Punycode domain name to its Unicode counterpart
   *
   * @param string $input Domain name in Punycode
   *
   * @return string Unicode domain name
   */
  public function decode($input)
  {
    if ($this->idnSupport === true) {
      return idn_to_utf8($input);
    }

    return self::$punycode->decode($input);
  }
}
