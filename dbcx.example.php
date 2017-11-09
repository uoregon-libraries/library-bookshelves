<?php

define( 'DB_HOST', 'host' );
define( 'DB_USER', 'user' );
define( 'DB_PASS', 'password' );
define( 'DB_NAME', 'database' );	

$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
