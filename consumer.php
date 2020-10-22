<?php

require __DIR__ . "/vendor/autoload.php";

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Workerman\Worker;

if (!ini_get('date.timezone')) {
    ini_set('date.timezone', date_default_timezone_get()); //fix for workerman trouble with timezone
}


$workers = [];
$countWorkers = $_ENV["CONSUME_WORKERS"] ?? 1;
$useStdout = (bool)($_ENV["USE_STDOUT"] ?? true);
$mux = new \Broker\Mux();

$logger = new \Monolog\Logger('logger');
for ($i = 0; $i < $countWorkers; $i++) {

    if ($useStdout) {
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
    } else {
        $logger->pushHandler(new StreamHandler(__DIR__ . '/consumer.log', Logger::INFO));
    }

    $workers[] = new \Broker\Consumer($i, $logger);
}

Worker::runAll();

