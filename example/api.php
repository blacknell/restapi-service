<?php
/**
 * Copyright (c) 2019. Paul Blacknell https://github.com/blacknell
 */

require __DIR__ . '/../vendor/autoload.php';
require "MyAPI.class.php";

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

$log = new Logger('restapi-test');
$logHandler = new ErrorLogHandler();
$log->pushHandler($logHandler);

try {
    $API = new MyAPI($_REQUEST['request'], $log);
    echo $API->processAPI();
} catch (RuntimeException $e) {
    http_response_code($e->getCode());
}

//warning - do not let any output after this closing brace
?>