<?php
require_once(dirname(__FILE__) . '/../Readers/Null_Reader.php');

Class Reader_Factory {
  public static function create($context=NULL) {
    return new Null_Reader($context);
  }
}