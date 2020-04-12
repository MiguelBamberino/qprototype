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
      $wq_name = $this->workerQName($worker->id());
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
    
    if($worker->validate()){
        $wq_name = $this->workerQName($worker->id());
        if($this->repository->haveQueue($wq_name)){
          // requeue any job then delete queue
          $job = $this->repository->popAndPushJob($wq_name,"main");
          if($job instanceof Job){
            $this->log->save( (new LogEntry())->job('readded',$job,"main") );             
          }
          $this->repository->deleteQueue($wq_name);
        }
        
        $this->log->save( (new LogEntry())->worker('killed',$worker) );        
      
    }
    return new Response($worker,$worker->getValidationErrors(),$worker->valid(),null);
  }
  
  public function takeJob(Worker $worker):Response{
    
    $job = null;
    if($worker->validate()){
      $wq_name = $this->workerQName($worker->id());
      if($this->repository->haveQueue($wq_name) ){ 
        
        $existing = $this->repository->getFirstJob($wq_name);
        if( ($existing instanceof Job) == false ){
          
          $job = $this->repository->popAndPushJob("main",$wq_name);
          if($job instanceof Job){
            $job->start($worker->id());
            $this->log->save( (new LogEntry())->job('started',$job,"main") ); 
          }
          
        }else{
          $worker->addError("Worker {$worker->id()} already has a job. Job ID: {$existing->id()}");
        }
        
      } else{
        $worker->addError("Worker {$worker->id()} does not exists.");
      }
      
                                    
    }
    return new Response($worker,$worker->getValidationErrors(),$worker->valid(),$job);
  }
  
  private function workerQName(string $worker_id):string{
    return "wq-".$worker_id;
  }
  ####################################################################
  # Jobs Calls
  ####################################################################
  public function addJob(Job $job):Response{
    
    if($job->validate()){
      $this->repository->pushJob("main",$job);
      $this->log->save( (new LogEntry())->job('added',$job,"main") );     
    }
    return new Response($job,$job->getValidationErrors(),$job->valid(),null);
  }
  
  public function completeJob(Job $job):Response{
    
    if($job->validate()){
      
      if($job->completedAt()){
        $wq_name = $this->workerQName($job->workerId());
        $this->repository->popJob($wq_name);
        $this->log->save( (new LogEntry())->job('completed',$job,"main") );    
      }else{
        $job->addError("Job ID: {$job->id()} must be marked complete with Job::complete() before passing into service::completeJob()");
      }
      
    } 
    return new Response($job,$job->getValidationErrors(),$job->valid(),null);
  }
  
  public function failedJob(Job $job):Response{
      
    if($job->validate()){
      
      if($job->errors()){
        
        $wq_name = $this->workerQName($job->workerId());
        
        if($job->reachedMaxFails()){
          $this->repository->popJob($wq_name);
          $this->log->save( (new LogEntry())->job('broken',$job,"main") );            
        }else{
          $this->repository->popAndPushJob($wq_name,"main");
          $this->log->save( (new LogEntry())->job('failed',$job,"main") );
        }
        
      }else{
        $job->addError("Job ID: {$job->id()} has no errors. Call Job::fail() before calling service::failedJob()");
      }
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