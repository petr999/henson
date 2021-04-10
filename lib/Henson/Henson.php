<?php

use Henson\Config;

session_start();

spl_autoload_register(function ($class_name) {
    require_once implode('/', [
    __DIR__, '..', str_replace([ '\\', ], [ '/', ], $class_name) . '.php'
    ]);
});

Config::importConfig();
