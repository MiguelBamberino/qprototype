<?php



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