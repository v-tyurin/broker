<?php

require __DIR__ . "/vendor/autoload.php";

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Workerman\Worker;

date_default_timezone_set('PRC');



sleep(1);//warmup

$workers = [];
$countWorkers = $_ENV["CONSUME_WORKERS"] ?? 1;
$useStdout = (bool)($_ENV["USE_STDOUT"] ?? true);

$logger = new \Monolog\Logger('logger');

for ($i = 0; $i < $countWorkers; $i++) {

    if ($useStdout) {
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
    } else {
        $logger->pushHandler(new StreamHandler(__DIR__ . '/consumer.log', Logger::DEBUG));
    }

    $workers[] = new \Broker\Consumer($i, $logger,15);
}
$logger->info('test info');
$mux = new \Broker\Mux($workers,$logger);
Worker::runAll();
