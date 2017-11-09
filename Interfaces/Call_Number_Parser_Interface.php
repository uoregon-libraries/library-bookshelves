<?php

/**
 * Parsing interface. Implement these methods to guarantee your parsing engine fulfills the contractual parsing methods.
 *
 * Interface Call_Number_Parser_Interface
 */
interface Call_Number_Parser_Interface {

  /**
   * Transforms an unformatted LCCN to an array of parts
   *
   * @param String $callNumber
   *
   * @return array Array of LCCN parts
   */
  public static function parseCallNumber($callNumber);

  /**
   * Normalize a LCCN. "AB123.4.C5.D67 2010" takes the form "AB 012340C500D670 000 2010"
   * Normalized call numbers of sortable and comparable
   *
   * @param string $callNumber Library Congress format call number (http://www.usg.edu/galileo/skills/unit03/libraries03_04.phtml)
   *
   * @return string Normalized LCCN
   */
  public static function normalizeCallNumber($callNumber);

  /**
   * Generate a human readable LCCN from a normalized one. The inverse of NromalizeCallNumber()
   *
   * @param string $norm Normalized LCCN
   *
   * @return string Human readable LCCN
   */
  public static function denormalizeCallNumber($norm);

}