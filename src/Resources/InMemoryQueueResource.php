<?php
namespace QMan\Resources;

use \LogicException;

class InMemoryQueueResource implements QueueResourceInterface{
  private $queues;
  
  public function createQ(string $name):bool{
    if(isset($this->queues[$name])){
      throw new LogicException("Queue already exists");
    }
    $this->queues[$name]=[];
    return true;
  }
  public function haveQ(string $name):bool{
    return isset($this->queues[$name]);
  }
  public function pushOnQ(string $queue, string $value):bool{
    $this->queues[$queue][] = $value;
    return true;
  }
  public function popOffQ(string $queue):string{
    if(count($this->queues[$queue])>0){
        foreach($this->queues[$queue] as $k=> $item){
          break;
        }
        unset($this->queues[$queue][$k]);
        return $item;
    }else{
      return '';
    }
   
  }
  public function popOffPushOnQ(string $popOffQueue, string $pushOnQueue):string{
    $item = $this->popOffQ($popOffQueue);
    if($item){
      $this->pushOnQ($pushOnQueue,$item);
    }
    return $item;
  }
  public function getAllOnQ(string $queue):array{
    return $this->queues[$queue];
  }
  public function getOneOnQ(string $queue):string{
    foreach($this->queues[$queue] as $k=> $item){
        return $item;
        break;
    }
    return '';
  }
}