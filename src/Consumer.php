<?php


namespace Broker;


use Workerman\Worker;

class Consumer
{
    protected $worker;
    protected $logger;

    public function __construct($logger)
    {
        $this->worker = new Worker();
        $this->worker->onWorkerStart = [$this,'consume'];
    }

    public function consume(){

        file_put_contents("log.log",rand(0,10),FILE_APPEND);
    }
}