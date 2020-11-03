<?php


namespace Broker;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use Workerman\Worker;

class Mux
{

    protected Consumer $worker;

    protected LoggerInterface $logger;

    protected KafkaConsumer $muxConsumer;

    public function __construct(Consumer $worker, LoggerInterface $logger=null)
    {
        if ($logger===null){
            $logger = new Logger('logger');
        }
        $this->logger = $logger;

        $ip = gethostbyname('kafka');


        $conf = new Conf();

        // Set a rebalance callback to log partition assignments (optional)
        $conf->setRebalanceCb(function (KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });

        $conf->set('group.id', 'consumer');

        $conf->set('metadata.broker.list', "$ip");
        $conf->set('auto.offset.reset', 'latest');

        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe(['main']);
        $this->muxConsumer = $consumer;

        $this->worker = $worker;

    }

    public function run(){

        while (true) {
            $message = $this->muxConsumer->consume(20*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->processRequest($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    public function processRequest($message){
        $this->worker->pushTask($message->payload);
        sleep(1);
//        var_dump($message->payload);
//            $logger->info($message['message']['key'].' '.$message['message']['value']);
//        usleep(1000000);
    }
}