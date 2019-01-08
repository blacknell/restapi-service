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
}
catch (RuntimeException $e) {
	header("HTTP/1.1 " . $e->getMessage() . " " . $e->getCode());
	echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode()]);
}

//warning - do not let any output after this closing brace
?>