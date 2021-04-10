<?php

function getLoaderPath()
{
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $path = dirname($scriptName);
    $pathArr = array_filter(explode('/', $path), function ($elem) {
        $rv = ! empty($elem);
        return $rv;
    });
    $pathArrCount = count($pathArr);
    if (1 > $pathArrCount) {
        throw new \Exception('wrong path!', 404);
    }
    $upperLevels = implode('/', array_map(function () {
        return '..';
    }, range(0, $pathArrCount)));

    return __DIR__ . "/$upperLevels/lib/Henson/Henson.php";
}

$loaderPath = getLoaderPath();
require_once $loaderPath;
Henson\RouterController::run();
