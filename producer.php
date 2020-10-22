<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require "vendor\autoload.php";
date_default_timezone_set('PRC');

$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

$config = \Kafka\ProducerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(10000);
$config->setMetadataBrokerList('192.168.1.128:9092');
$config->setBrokerVersion('1.0.0');
$config->setRequiredAck(1);
$config->setIsAsyn(false);
$config->setLogger($logger);
$config->setProduceInterval(500);
$producer = new \Kafka\Producer(
    function() {
        return [
            [
                'topic' => 'main',
                'value' => '',
                'key' => '1',
            ],
        ];
    }
);
$producer->success(function($result) {
    var_dump('success');
    var_dump($result);
});
$producer->error(function($errorCode) {
    var_dump('error');
    var_dump($errorCode);
});
$producer->send(true);