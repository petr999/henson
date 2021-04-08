<?php

session_start();

spl_autoload_register(function ($class_name) {
  require_once implode( '/', [
    __DIR__, '..', str_replace( [ '\\', ], [ '/', ], $class_name ) . '.php'
  ] );
});

define( 'HENSON_ROUTE_DEFAULT', '/' );
define( 'HENSON_SQLITE', __DIR__ . '/../../var/henson.sqlite' );
define( 'HENSON_TASKLIST_ITEMS_LIMIT', 3 );
