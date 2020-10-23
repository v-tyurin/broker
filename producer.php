<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . "/vendor/autoload.php";

date_default_timezone_set('PRC');
sleep(1);//warmup
$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));


$config = \Kafka\ProducerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(10000);
//workaround for hostname
$ip = gethostbyname('kafka');
var_dump($ip);
$config->setMetadataBrokerList('127.0.0.1:9092');

$config->setBrokerVersion('2.6.0.0');
$config->setRequiredAck(0);
$config->setIsAsyn(false);
$config->setProduceInterval(500);
$producer = new \Kafka\Producer();
$producer->setLogger($logger);
while (true){
    $sendArray = [];
    for($account_id = 0; $account_id < 1000; $account_id++) {

        $countTasks = mt_rand(1,10);
        for ($taskOrderNumber = 0;$taskOrderNumber<$countTasks;$taskOrderNumber++){
            $sendArray[] =[
                'topic' => 'main',
                'value' => 'account_id: '.$account_id.' payload '.$taskOrderNumber,
                'key' => $account_id, // key for partitioning
            ];
        }


    }
    $result = $producer->send($sendArray);
    var_dump('sended '.count($sendArray).' tasks');
    sleep(15);
}
