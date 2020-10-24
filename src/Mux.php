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

    protected Worker $worker;

    /** @var Consumer[] $workers */
    protected array $workers=[];

    protected array $workersMetadata = [];

    protected LoggerInterface $logger;

    protected KafkaConsumer $muxConsumer;

    public function __construct(array $workers, LoggerInterface $logger=null)
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
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo "Revoke: ";
                    var_dump($partitions);
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });

        $conf->set('group.id', 'cosumer');

        $conf->set('metadata.broker.list', $ip);
        $conf->set('auto.offset.reset', 'earliest');

        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe(['main']);
        $this->muxConsumer = $consumer;


        $this->worker = new Worker();
        $this->worker->name = 'mux';
        $this->worker->onWorkerStart = [$this, 'run'];
        $this->workers = $workers;

    }

    public function run(){
        echo "Waiting for partition assignment... (make take some time when\n";
        echo "quickly re-joining the group after leaving it.)\n";

        while (true) {
            $message = $this->muxConsumer->consume(120*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    var_dump($message);
                    $this->processRequest($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "No more messages; will wait for more\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    public function processRequest($message){
        var_dump($message['message']['key']);
//            $logger->info($message['message']['key'].' '.$message['message']['value']);
        usleep(1000000);
    }
}