<?php
require_once(dirname(__FILE__) . '/../../Interfaces/Shelf_Call_Number_Cache_Interface.php');
require_once(dirname(__FILE__) . '/../Factories/Reader_Factory.php');
require_once(dirname(__FILE__) . '/../Loggers/SQL_Logger.php');
require_once(dirname(__FILE__) . '/../Parsers/Regex_Call_Number_Parser.php');

/**
 * Database caching backend. Fills with null data
 *
 * Class Null_DB_Cache
 */
class Null_DB_Cache implements Shelf_Call_Number_Cache_Interface {

  /** @var PDO $db Cache database connection */
  private $db;
  /** @var Shelf_Call_Number_Reader_Interface $reader */
  private $reader;
  /** @var SQL_Logger $logger */
  private $logger;
  private $shelfTable = 'shelves_os';
  private $buildingTable = 'buildings';
  private $bid;

  /**
   * Null_DB_Cache constructor.
   *
   * @param PDO $db Cache database instance
   * @param array $context Array of data needed to construct our cache. In this case we need a file name and a building name
   */
  public function __construct($db, $context=NULL) {
    $building = $context['building'];
    $readOnly = $context['readonly'];
    $readerContext = array(
      'count' => 20,
    );

    $this->db = $db;
    $this->reader = $readOnly ? NULL : Reader_Factory::create($readerContext);

    // Grab the building ID from the building name and table
    $sql = "SELECT bid FROM $this->buildingTable WHERE building_name = :building";

    $query = $this->db->prepare($sql);
    $query->execute(array('building' => $building));
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $this->bid = $results[0]['bid'];

    $this->logger = new SQL_Logger($this->db, $this->bid);
  }

  /**
   * Cache shelf range/stack
   */
  function cacheShelves() {
    if (!isset($this->reader)) Throw new Exception('Cache open in read-only mode: Cannot write to cache');

    $this->logger->info('Cache Begin');
    $this->db->beginTransaction();

    $shelves = $this->reader->getShelves();
    foreach ($shelves as $shelf) {
      $shelf['bid'] = $this->bid;

      try {
        // First try to insert new rows
        // If primary keys are properly set in your DB this will fail and fall back to update
        try {
          $sql = "INSERT INTO {$this->shelfTable} (
              bid,
              floor,
              shelf,
              start_norm,
              start_denorm,
              end_norm,
              end_denorm
            ) VALUES (
              :bid,
              :floor,
              :shelf,
              :sn,
              :sd,
              :en,
              :ed
            )
          ";

          $query = $this->db->prepare($sql);
          $query->execute($shelf);

          $this->logger->info("New shelf-floor combo. Adding floor {$shelf['floor']} - shelf {$shelf['shelf']}");
        } catch (PDOException $e) {
          // Assume the first attempt failed because primary key already exists
          // Try to update instead
          $sql = "UPDATE {$this->shelfTable} SET
            bid = :bid,
            start_norm = :sn,
            start_denorm = :sd,
            end_norm = :en,
            end_denorm = :ed
            WHERE floor = :floor AND shelf = :shelf
          ";

          $query = $this->db->prepare($sql);
          $query->execute($shelf);
        }
      } catch (Exception $e) {
        // If the update failed, something really weird happened
        // Log it so we can examine it later
        $this->logger->error("Failed to cache: $e");
        echo print_r($shelf, TRUE) . "<br/><b>Cached failed: $e</b><hr>";
        continue;
      }

      echo print_r($shelf, TRUE) . '<br/><b>Cached successfully</b><hr>';
    }

    $this->db->commit();

    $this->logger->success('Cache completed successfully');
    echo '<br/><b>All shelves cached successfully</b><hr>';
  }

  /**
   * Retrieve shelf data by shelf number
   *
   * @param int $shelf Shelf number as it appears in DB minus the compass direction
   * @param int $floor Floor shelf is on as it appears in DB
   *
   * @return array Array of shelves, each shelf contains all columns from DB
   */
  function retrieveShelvesByShelfNumber($shelf, $floor) {
    $params = array(
      'shelf' => $shelf . '_',
      'floor' => $floor,
    );
    $sql = "SELECT * FROM {$this->shelfTable} WHERE shelf LIKE :shelf AND floor = :floor";

    $query = $this->db->prepare($sql);
    $query->execute($params);
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    return $results;
  }

  /**
   * Retrieve possible shelves an LCCN can be on
   *
   * @param String $callNumber Unformatted/Non-Normalized LCCN
   *
   * @return array Array of shelf ranges/stacks where call number can be found
   */
  function retrieveShelves($callNumber) {
    $norm = Regex_Call_Number_Parser::normalizeCallNumber($callNumber);
    $params = array(
      'norm' => $norm,
    );

    $sql = "SELECT * FROM {$this->shelfTable} s JOIN {$this->buildingTable} b ON s.bid = b.bid WHERE " . PHP_EOL;
    $sql .= "s.start_norm <= :norm AND s.end_norm >= :norm";

    $query = $this->db->prepare($sql);
    $query->execute($params);
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    // Log failed and successful lookups
    if (empty($results)) {
      $this->logger->warning("Failed to find shelf for LCCN: $callNumber");
    }
    foreach($results as $result) {
      $this->logger->lookup($callNumber, array(
        'floor' => $result['floor'],
        'shelf' => $result['shelf'],
      ));
    }

    return $results;
  }
}