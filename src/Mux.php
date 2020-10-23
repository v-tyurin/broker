<?php


namespace Broker;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Workerman\Worker;

class Mux
{

    protected Worker $worker;

    /** @var Consumer[] $workers */
    protected array $workers=[];

    protected array $workersMetadata = [];

    protected LoggerInterface $logger;

    protected \Kafka\Consumer $muxConsumer;

    public function __construct(array $workers, LoggerInterface $logger=null)
    {
        if ($logger===null){
            $logger = new Logger('logger');
        }

        $config = \Kafka\ConsumerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(1000);
        $ip = gethostbyname('kafka');
        $config->setMetadataBrokerList('127.0.0.1:9092');
        $config->setGroupId('consume_group');
        $config->setBrokerVersion('2.6.0.0');
        $config->setTopics(['main']);
        $config->setLogger($logger);

        $this->muxConsumer = new \Kafka\Consumer();
        $this->logger = $logger;

        $this->worker = new Worker();
        $this->worker->name = 'mux';
        $this->worker->onWorkerStart = [$this, 'run'];

    }

    public function run(){
        $logger = $this->logger;
        var_dump('test');
        $this->muxConsumer->start(function($topic, $part, $message) use ($logger){
            var_dump('incoming');
            $logger->info($message['message']['key'].' '.$message['message']['value']);
            usleep(1000);
        },false);
    }
}