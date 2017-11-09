<?php
require_once('dbcx.php');
require_once('Classes/Factories/Cache_Factory.php');

$building = $_GET['building'] ? $_GET['building'] : 'dummy';

$context = array(
  'db' => $db,
  'building' => $building,
);

$cache = Cache_Factory::create($context);
$cache->cacheShelves();
