<?php

/**
 * Class SQL_Logger Logs messages and data to MySQL database
 * TODO: Replace with PSR-3 compliant logging
 */
class SQL_Logger {
  /** @var PDO $db */
  private $db;
  private $bid;
  private $table;

  private $levels = array(
    'error' => 'ERROR',
    'info' => 'INFO',
    'warning' => 'WARNING',
    'success' => 'SUCCESS',
    'lookup' => 'LOOKUP',
    'general' => 'GENERAL',
  );

  public function __construct($db, $bid) {
    $this->db = $db;
    $this->bid = $bid;
    $this->table = 'logs';
  }

  public function error($message, $fields=array()) {
    $fields = array('level' => $this->levels['error']) + $fields;
    $this->log($message, $fields);
  }

  public function info($message, $fields=array()) {
    $fields = array('level' => $this->levels['info']) + $fields;
    $this->log($message, $fields);
  }

  public function warning($message, $fields=array()) {
    $fields = array('level' => $this->levels['warning']) + $fields;
    $this->log($message, $fields);
  }

  public function success($message, $fields=array()) {
    $fields = array('level' => $this->levels['success']) + $fields;
    $this->log($message, $fields);
  }

  public function lookup($callNumber, $fields=array()) {
    $norm = Regex_Call_Number_Parser::normalizeCallNumber($callNumber);
    $denorm = Regex_Call_Number_Parser::denormalizeCallNumber($norm);
    $message = "{$denorm}";

    $fields = array('level' => $this->levels['lookup']) + $fields;
    $this->log($message, $fields);
  }

  /**
   * @param String $message
   * @param array $fields
   */
  public function log($message, $fields=array()) {
    $query = $this->db->prepare("DESCRIBE {$this->table}");
    $query->execute();
    $columns = $query->fetchAll(PDO::FETCH_COLUMN);

    $fields += array(
      'level' => $this->levels['general'],
      'bid' => $this->bid,
    );
    $fields = array_intersect_key($fields, array_flip($columns));

    $sql = "INSERT INTO {$this->table} (message";
    foreach ($fields as $col=>$value) {
      $sql .= ", $col";
    }
    $sql .= ') VALUES (:message';
    foreach ($fields as $col=>$value) {
      $sql .= ", :$col";
    }
    $sql .= ')';

    $fields += array(
      'message' => $message,
    );

    $query = $this->db->prepare($sql);
    $query->execute($fields);
  }
}