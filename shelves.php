<?php
/**
 * Look up shelf end caps in the DB and return the start and end LCCN
 */

require_once('dbcx.php');
require_once('Classes/Factories/Cache_Factory.php');

header('Content-Type: application/json');
// CORS header
$acceptedDomains = array(
  'http://localhost',
  'https://localhost',
);
$http_origin = $_SERVER['HTTP_ORIGIN'];
if (in_array($http_origin, $acceptedDomains)) {
  header("Access-Control-Allow-Origin: $http_origin");
}

// Gather & require query parameters
$floor = $_GET['floor'] ? $_GET['floor'] : die('floor required');
$startShelf = $_GET['shelf_start'] ? $_GET['shelf_start'] : die('shelf_start required');
$endShelf = $_GET['shelf_end'] ? $_GET['shelf_end'] : die('shelf_end required');
$building = $_GET['building'] ? $_GET['building'] : 'knight';
$context = array(
  'db' => $db,
  'building' => $building,
);

$cache = Cache_Factory::create($context);

$start = $cache->retrieveShelvesByShelfNumber($startShelf, $floor);
$end = $cache->retrieveShelvesByShelfNumber($endShelf, $floor);

// Build json
$json = array(
  'start' => array(),
  'end' => array(),
);
// Find lowest LCCN of start shelves (N/S:E/W)
foreach ($start as $shelf) {
  if (compareShelves($shelf, $json['start'])) {
    $json['start'] = $shelf;
  }
}
// Find highest LCCN of end shelves (N/S:E/W)
foreach ($end as $shelf) {
  if (compareShelves($json['end'], $shelf)) {
    $json['end'] = $shelf;
  }
}
// Simplify start and end JSON down to call numbers
$json['start'] = $json['start']['start_denorm'];
$json['end'] = $json['end']['end_denorm'];

// Content
echo json_encode($json);





/*
 * Starting from highest order LCCN token, compare each part until one is less than the other
 *
 */
function compareShelves($a, $b) {
  if (empty($a)) return $b;
  if (empty($b)) return $a;
  return $a['start_norm'] < $b['start_norm'];
}
