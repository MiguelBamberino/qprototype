<?php

namespace QMan;
use PPCore\Adapters\DataSources\DataSourceInterface;
use PPCore\Responses\Response;
use QMan\Actors\Worker;
use QMan\Logs\LogEntry;
use QMan\Logs\LogRepository;
use QMan\Resources\QueueResourceInterface;



class QService{
  
  protected $repository;
  protected $log;
  
  ####################################################################
  # Worker Calls
  ####################################################################
  
  public function __construct(QueueResourceInterface $queueResource,DataSourceInterface $logger){
    $this->repository = new QueueRepository($queueResource);
    $this->repository->createQueue("main");
    $this->log = new LogRepository($logger);
  }
  public function workerStarted(Worker $worker):Response{
    
    if($worker->validate()){
      $wq_name = $this->workerQName($worker);
      // create queue if doesn't already exist
      if($this->repository->haveQueue($wq_name) == false ){  
         $this->repository->createQueue($wq_name);
         $this->log->save( (new LogEntry())->worker('started',$worker) );        
      }else{
        $worker->addError("Work {$worker->id()} already at work.");
      }   
    }
    
    return new Response($worker,$worker->getValidationErrors(),$worker->valid(),null);
  }
  
  public function workerKilled(Worker $worker):Response{
    
  }
  
  public function takeJob(Worker $worker):Response{
    
    $job = null;
    if($worker->validate()){
      $wq_name = $this->workerQName($worker);
      if($this->repository->haveQueue($wq_name) ){ 
        
        $existing = $this->repository->getFirstJob($wq_name);
        if( ($existing instanceof Job) == false ){
          
          $job = $this->repository->popAndPushJob("main",$wq_name);
          if($job instanceof Job){
            $job->start($worker->id());
            $this->log->save( (new LogEntry())->job('started',$job,"main") ); 
          }
          
        }else{
          $worker->addError("Worker {$worker->id()} does not exists.");
        }
        
      } else{
        $worker->addError("Worker {$worker->id()} already has a job. Job ID: {$existing->id()}");
      }
      
                                    
    }
    return new Response($worker,$worker->getValidationErrors(),$worker->valid(),$job);
  }
  
  private function workerQName(Worker $w):string{
    return "wq-".$w->id();
  }
  ####################################################################
  # Jobs Calls
  ####################################################################
  public function addJob(Job $job):Response{
    
    if($job->valid()){
      $this->repository->pushJob("main",$job);
      $this->log->save( (new LogEntry())->job('added',$job,"main") );     
    }
    return new Response($job,$job->getValidationErrors(),$job->valid(),null);
  }
  ####################################################################
  # Queue Calls
  ####################################################################
  public function queryQueue(){
    return $this->repository->getAllJobs('main')->toArray();
  }
}