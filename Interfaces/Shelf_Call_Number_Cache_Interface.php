<?php

/**
 * Caching interface. Implement these methods to guarantee your caching backend fulfills the contractual caching methods.
 *
 * Interface Shelf_Call_Number_Cache_Interface
 */
interface Shelf_Call_Number_Cache_Interface {
  /**
   * Cache shelf range/stack where the implementing class is designed to cache
   *
   * It's the Cacher's job to use a Reader to receive shelf data via Shelf_Call_Number_Reader_Interface::getShelves()
   * @see Shelf_Call_Number_Reader_Interface::getShelves()
   */
  function cacheShelves();

  /**
   * Retrieve shelf data by shelf number
   *
   * @param int $shelf Shelf number
   * @param int $floor Floor shelf is on
   *
   * @return array Array of shelves, generally two at most. N/S/E/W sides
   *  Example:
   *    array(
   *      'floor' => #,
   *      'shelf' => '',
   *      'start_norm' => '',
   *      'start_denorm' => '',
   *      'end_norm' => '',
   *      'end_denorm' => '',
   *    );
   */
  function retrieveShelvesByShelfNumber($shelf, $floor);

  /**
   * Retrieve possible shelves an LCCN can be on
   *
   * @param String $callNumber Unformatted/Denormalized LCCN
   *
   * @return array Array of shelf ranges/stacks where call number can be found
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
  function retrieveShelves($callNumber);
}