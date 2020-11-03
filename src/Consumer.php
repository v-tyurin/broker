<?php


namespace Broker;


use Psr\Log\LoggerInterface;
use Workerman\Worker;

class Consumer
{
    protected Worker $worker;
    protected LoggerInterface $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function pushTask($payload){
        $this->logger->info("worker: $payload");
    }




}