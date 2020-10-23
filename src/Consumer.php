<?php


namespace Broker;


use Psr\Log\LoggerInterface;
use Workerman\Worker;

class Consumer
{
    protected Worker $worker;
    protected LoggerInterface $logger;
    protected int $id;

    protected array $buffer = [];
    protected int $bufferLength = 0;
    protected int $bufferSize = 5;



    public function __construct(int $id,LoggerInterface $logger,int $bufferSize)
    {
        $this->worker = new Worker();
        $this->worker->name = 'worker '.$id;
        $this->worker->onWorkerStart = [$this, 'consume'];
        $this->logger = $logger;
        $this->id = $id;
        $this->bufferSize=$bufferSize;
    }

    public function pushTask($payload):bool{
        if($this->bufferLength===$this->bufferSize){
            return false;
        }

        array_push($this->buffer,$payload);
        /* need lock here */
        $this->bufferLength+=1;
        return true;
    }



    public function consume()
    {
        while (true) {
            if ($this->bufferLength>0){
                $payload = array_shift($this->buffer);
                /* need lock here */
                $this->bufferLength -=1;
                $this->logger->info("worker: $this->id $payload");
                sleep(1);
            }else{
                usleep(100);
            }

        }
    }
}