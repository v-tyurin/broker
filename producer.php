<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . "/vendor/autoload.php";

date_default_timezone_set('PRC');
sleep(1);//warmup
$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));


$conf = new RdKafka\Conf();
$ip = gethostbyname('kafka');
$conf->set('metadata.broker.list', "$ip:9092");
//$conf->set('enable.idempotence', 'true');

$producer = new RdKafka\Producer($conf);

$topic = $producer->newTopic("main");

while (true){
    $sendArray = [];
    for($account_id = 0; $account_id < 1000; $account_id++) {
        $countTasks = mt_rand(1,10);
        for ($taskOrderNumber = 0;$taskOrderNumber<$countTasks;$taskOrderNumber++){
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, 'account_id: '.$account_id.' payload '.$taskOrderNumber,$account_id);
            $producer->poll(0);
        }
    }

    for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
        $result = $producer->flush(10000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
            $logger->info("messages sended");
            break;
        }
    }

    if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
        $logger->info("error on send");
    }

      sleep(2);
}
