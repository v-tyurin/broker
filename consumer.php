<?php

require __DIR__ . "/vendor/autoload.php";

use Workerman\Worker;

if (!ini_get('date.timezone')) {
    ini_set('date.timezone', date_default_timezone_get()); //fix for workerman trouble with timezone
}


$workers = [];
$countWorkers = $_ENV["CONSUME_WORKERS"] ?? 1;

for ($i = 0; $i < $countWorkers; $i++) {
   $workers[] = new \Broker\Consumer();
}

Worker::runAll();