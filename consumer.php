<?php

require __DIR__ . "/vendor/autoload.php";

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Workerman\Worker;

date_default_timezone_set('PRC');
sleep(40);//warmup

$useStdout = (array_key_exists("USE_STDOUT",$_SERVER) && $_SERVER["USE_STDOUT"]==='true');
$logger = new \Monolog\Logger('logger');
if ($useStdout){
    $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
}else{
    $logger->pushHandler(new StreamHandler(__DIR__.'/consumerlogger.log', Logger::DEBUG));
}

$worker = new \Broker\Consumer($logger);

$mux = new \Broker\Mux($worker,$logger);
$mux->run();
