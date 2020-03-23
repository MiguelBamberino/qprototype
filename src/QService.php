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



/*


workerStarted(Worker $w)
  workLog($w,'started')
  createProcessingQueue($w)

workerKilled(Worker $w)
  workLog($w,'killed')
  reQueueStaleJob($w)
  deleteProcessingQueue($w)
  
checkStaleJobs()
  $js = getJobsInProccess()
  foreach($js as $j)
    if($j->stale())
      workLog($j->worker,'died')
      reQueueStaleJob($j->worker)
      deleteProcessingQueue($j->worker)
      
heartBeat(Worker $w)
  $q->heartBeats($w->id)
--------------------------------------------------
addJob(Job $j)
  jobLog($j,'added')
  $q->push('main',$j)

takeJob(Worker $w)
  jobLog($j,'started')
  // check own worker Q for left overs?
  // check worker Q empty?
  $j = $q->reserve('main',$w->proccess_q)
  return $j

completeJob(Job $j)
    jobLog($j,'completed')
    $q->pop($j->worker_id)

failedJob(Job $j)
  if( $j->tooMAnyFails() )
    jobLog($j,'broken')
    $q->pop($j->worker_id)
  else
    jobLog($j,'failed')
    $j->fails++
    reQueueStaleJob($j->worker)
  
--------------------------------------------------
private workerLog(Worker $w, string $event)
  log->insert([ 
    'worker_id'=>$w->id, 
    'queue_name'=>$w->queue_name,
    'type'=>'worker',
    'sub_type'=>$event,
    'object'=>json_encode($w),
  ]);
private jobLog(Worker $w, Job $j, string $event)
  log->insert([ 
    'worker_id'=>$w->id, 
    'job_id'=>$j->id, 
    'queue_name'=>$j->queue_name,
    'type'=>'job',
    'sub_type'=>$event,
    'object'=>json_encode($j),
  ]);
--------------------------------------------------
log:
  - id
  - worker_id
  - job_id
  - queue_name ?
  - type (worker,job)
  - sub_type (started,died,stat,killed,added,readded,completed,failed,broken,deleted)
  - object
  - date
  
  
*/