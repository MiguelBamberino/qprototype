<?php
namespace QMan;

use QMan\Resources\QueueResourceInterface;

class QueueRepository{
  
  private $resource;
  
  public function __construct(QueueResourceInterface $resource){
    $this->resource = $resource;
    $this->resource->createQ("main");
  }
  
  public function GetJob(Worker $worker){
     // check if one  
  }
  
  public function pushJob(string $queue, Job $job):bool{
    return $this->resource->pushOnQ($queue,$this->jobToString($job));
  }
    
  private function stringToJob(string $jobString):Job{
    $data = json_decode($jobString);
    return new Job($data);
  }
  private function jobToString(Job $job):string{
    return $job->toJson();
  }
}