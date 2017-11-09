<?php
/**
library-callnumber-lc is copyright Michael J. Giarlo and licensed under the MIT license.
https://github.com/libraryhackers/library-callnumber-lc/tree/master/python

The MIT License

Copyright (c) 2009 Michael J. Giarlo

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Extensions to library-callnumber-lc are copyright University of Oregon Libraries and provided without license.
https://choosealicense.com/no-license/

*/

require_once(dirname(__FILE__) . '/../../Interfaces/Call_Number_Parser_Interface.php');

class Regex_Call_Number_Parser implements Call_Number_Parser_Interface{

  /**
   * Transforms an unformatted LCCN to an array of parts
   *
   * @param String $callNumber
   *
   * @return array Array of LCCN parts
   */
  public static function parseCallNumber($callNumber) {
    $callNumber = strtoupper($callNumber);
    // Regex blatantly stolen from https://github.com/libraryhackers/library-callnumber-lc
    // This is mostly sorcery
    $callPattern = '
      \s*
        (?:VIDEO-D)?  # for video stuff
        (?:DVD-ROM)?  # DVDs, obviously
        (?:CD-ROM)?   # CDs
        (?:TAPE-C)?   # Tapes
      \s*
        ([A-Z]{1,3})  # alpha
      \s*
        (?:           # optional numbers with optional decimal point
          (\d+)
          (?:\s*?\.\s*?(\d+))?
        )?
      \s*
        (?:           # optional cutter
          \.? \s*
          ([A-Z])     # cutter letter
          \s*
          (\d+ | \Z)? # cutter numbers
        )?
      \s*
        (?:           # optional cutter
          \.? \s*
          ([A-Z])     # cutter letter
          \s*
          (\d+ | \Z)? # cutter numbers
        )?
      \s*
        (?:           # optional cutter
          \.? \s*
          ([A-Z])     # cutter letter
          \s*
          (\d+ | \Z)? # cutter numbers
        )?
        (\s+.+?)?     # everthing else
      \s*
    ';
    // Apply above regex with start/end of line anchors and ignore whitespace in regex
    preg_match('/^' . $callPattern . '$/x', $callNumber, $parts);

    return $parts;
  }

  /**
   * Normalize a LCCN. "AB123.4.C5.D67 2010" takes the form "AB 012340C500D670 000 2010"
   * Normalized call numbers of sortable and comparable
   *
   * @param string $callNumber Library Congress format call number (http://www.usg.edu/galileo/skills/unit03/libraries03_04.phtml)
   *
   * @return string Normalized LCCN
   */
  public static function normalizeCallNumber($callNumber) {
    $parts = Regex_Call_Number_Parser::parseCallNumber($callNumber);

    $norm = str_pad($parts[1], 3, ' ', STR_PAD_RIGHT);  // Alpha
    $norm .= str_pad($parts[2], 4, '0', STR_PAD_LEFT);  // Num
    $norm .= str_pad($parts[3], 2, '0', STR_PAD_RIGHT); // Dec
    $norm .= str_pad($parts[4], 1, ' ', STR_PAD_RIGHT); // C1 Alpha
    $norm .= str_pad($parts[5], 3, '0', STR_PAD_RIGHT); // C1 Num
    $norm .= str_pad($parts[6], 1, ' ', STR_PAD_RIGHT); // C2 Alpha
    $norm .= str_pad($parts[7], 3, '0', STR_PAD_RIGHT); // C2 Num
    $norm .= str_pad($parts[8], 1, ' ', STR_PAD_RIGHT); // C3 Alpha
    $norm .= str_pad($parts[9], 3, '0', STR_PAD_RIGHT); // C3 Num
    $norm .= ' ' . trim($parts[10]); // Extra

    return $norm;
  }

  /**
   * Generate a human readable LCCN from a normalized one. The inverse of NormalizeCallNumber()
   *
   * @param string $norm Normalized LCCN
   *
   * @return string Human readable LCCN
   */
  public static function denormalizeCallNumber($norm) {
    $parts[] = rtrim(substr($norm, 0, 3), ' '); // Alpha
    $parts[] = ltrim(substr($norm, 3, 4), '0'); // Num
    $parts[] = rtrim(substr($norm, 7, 2), '0'); // Dec
    $parts[] = rtrim(substr($norm, 9, 1), ' '); // C1 Alpha
    $parts[] = rtrim(substr($norm, 10, 3), '0'); // C1 Num
    $parts[] = rtrim(substr($norm, 13, 1), ' '); // C2 Alpha
    $parts[] = rtrim(substr($norm, 14, 3), '0'); // C2 Num
    $parts[] = rtrim(substr($norm, 17, 1), ' '); // C3 Alpha
    $parts[] = rtrim(substr($norm, 18, 3), '0'); // C3 Num
    $parts[] = trim(substr($norm, 21)); // Extra

    $call = $parts[0] . $parts[1]; // AlphaNum
    if ($parts[2]) $call .= '.' . $parts[2]; // AlphaNum.Dec
    if ($parts[3]) $call .= '.' . $parts[3] . $parts[4]; // AlphaNum.Dec.C1
    if ($parts[5]) $call .= '.' . $parts[5] . $parts[6]; // AlphaNum.Dec.C1.C2
    if ($parts[7]) $call .= '.' . $parts[7] . $parts[8]; // AlphaNum.Dec.C1.C2.C3
    if (!empty($parts[9])) $call .= ' ' . $parts[9]; // AlphaNum.Dec.C1.C2.C3 Extra

    return $call;
  }
}