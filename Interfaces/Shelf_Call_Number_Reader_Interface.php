<?php

/**
 * Reading interface. Implement these methods to guarantee your data collection class fulfills the contractual reading methods.
 *
 * Interface Shelf_Call_Number_Reader_Interface
 */
interface Shelf_Call_Number_Reader_Interface {
  /**
   * Get shelf data from whatever source the implementing class is designed to receive from
   *
   * @return array Array of shelf data
   *  Example:
   *    array(
   *      array(
   *        'floor' => #,
   *        'shelf' => '',
   *        'start_norm' => '',
   *        'start_denorm' => '',
   *        'end_norm' => '',
   *        'end_denorm' => '',
   *      ),
   *      array(
   *        ...
   *      ),
   *      ...
   *    );
   */
  public function getShelves();
}