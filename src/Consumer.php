<?php


namespace Broker;


use Psr\Log\LoggerInterface;
use Workerman\Worker;

class Consumer
{
    protected Worker $worker;
    protected LoggerInterface $logger;
    protected int $id;

    public function __construct(int $id,LoggerInterface $logger)
    {
        $this->worker = new Worker();
        $this->worker->onWorkerStart = [$this, 'consume'];
        $this->logger = $logger;
        $this->id = $id;
    }

    public function consume()
    {
        while (true) {
            $this->logger->info($this->id .' test');
            sleep(1);
        }
    }
}