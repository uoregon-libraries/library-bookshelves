<?php
require_once(dirname(__FILE__) . '/../../Interfaces/Shelf_Call_Number_Reader_Interface.php');
require_once(dirname(__FILE__) . '/../Parsers/Regex_Call_Number_Parser.php');

class Null_Reader implements Shelf_Call_Number_Reader_Interface {

  private $shelves;

  private $rangeCol = 0;
  private $callCol = 1;
  private $sheetCount = 11;
  private $rowOffset = 3;

  /**
   * Null_Reader constructor.
   *
   * @param array $context Variables necessary to build this version of a reader
   */
  public function __construct($context=NULL) {
    $count = min($context['count'], 499);
    
    $this->shelves = array();

    for ($i=1; $i<($count*2)+1; ++$i) {
      $shelfNumber = ceil($i/2);
      $shelfSide = $i%2 ? 'W' : 'E'; // Even $i is West side, odd is East
      $callNumbers = array(          // Generate some dummy call numbers
        'AB123.4.C5.D' . ($i*2-1) . ' 2010',
        'AB123.4.C5.D' . ($i*2) . ' 2010',
      );

      $this->createShelf($callNumbers, "{$shelfNumber}{$shelfSide}");
    }
  }

  /**
   * Get shelf data
   *
   * @return array Array of shelf data
   */
  public function getShelves() {
    return $this->shelves;
  }

  private function createShelf($callNumbers, $shelf) {
    $start_norm = Regex_Call_Number_Parser::normalizeCallNumber($callNumbers[0]);
    $start_denorm = Regex_Call_Number_Parser::denormalizeCallNumber($start_norm);
    $end_norm = Regex_Call_Number_Parser::normalizeCallNumber($callNumbers[1]);
    $end_denorm = Regex_Call_Number_Parser::denormalizeCallNumber($end_norm);

    $this->shelves[] = array(
      'floor' => 1,
      'shelf' => $shelf,
      'sn' => $start_norm,
      'sd' => $start_denorm,
      'en' => $end_norm,
      'ed' => $end_denorm,
    );
  }
}