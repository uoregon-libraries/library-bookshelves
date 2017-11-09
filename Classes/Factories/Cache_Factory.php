<?php
require_once(dirname(__FILE__) . '/../Caches/Null_DB_Cache.php');

class Cache_Factory {
  public static function create($context) {
    $db = $context['db'];
    $cacheContext = array(
      'building' => $context['building'],
    );
    return new Null_DB_Cache($db, $cacheContext);
  }
}