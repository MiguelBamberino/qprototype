<?php
namespace QMan;

use QMan\Resources\QueueResourceInterface;

class QueueRepository{
  
  private $resource;
  
  public function __construct(QueueResourceInterface $resource){
    $this->resource = $resource;
    
  }
  
  public function createQueue(string $queue){
     return $this->resource->createQ($queue);  
  }
  public function haveQueue(string $queue){
     return $this->resource->haveQ($queue);  
  }
  
  public function pushJob(string $queue, Job $job):bool{
    return $this->resource->pushOnQ($queue,$this->jobToString($job));
  }
  public function popJob(string $queue){
    $j = $this->resource->popOffQ($queue);
    if($j){
      return $this->stringToJob($j);
    }else{
      return null;
    }
  }
  public function popAndPushJob(string $queue_source, string $queue_target){
    $j= $this->resource->popOffPushOnQ($queue_source,$queue_target);
    if($j){
      return $this->stringToJob($j);
    }else{
      return null;
    }
  }
  public function getFirstJob(string $queue){
    $j= $this->resource->getOneOnQ($queue);
    if($j){
      return $this->stringToJob($j);
    }else{
      return null;
    }
  }
  public function getAllJobs(string $queue){
    $js= $this->resource->getAllOnQ($queue);
    $collection = new \PPCore\Collections\EntityCollection("QMan\Job");
    foreach($js as $j){
      $collection->push( $this->stringToJob($j) );
    }
    return $collection;
  }
    
  private function stringToJob(string $jobString):Job{
    $data = json_decode($jobString,true);
    return new Job($data);
  }
  private function jobToString(Job $job):string{
    return $job->toJson();
  }
}