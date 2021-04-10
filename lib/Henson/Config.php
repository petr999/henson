<?php

namespace Henson;

class Config
{

  public static function importConfig()
  {
    define('HENSON_ROUTE_DEFAULT', '/');
    define('HENSON_SQLITE', __DIR__ . '/../../var/henson.sqlite');
    define('HENSON_TASKLIST_ITEMS_LIMIT', 3);
  }
}
