<?php


namespace Broker;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Mux
{

    public function __construct()
    {
        $logger = new Logger('logger');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        $config = \Kafka\ConsumerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList('192.168.1.128:9092');
        $config->setGroupId('consume_group');
        $config->setBrokerVersion('1.0.0');
        $config->setTopics(['main']);
        $config->setLogger($logger);
        $consumer = new \Kafka\Consumer();
        $consumer->start(function($topic, $part, $message) {

            var_dump($message);
        });
    }
}