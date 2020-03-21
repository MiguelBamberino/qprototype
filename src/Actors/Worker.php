<?php

namespace QMan\Actors;
use PPCore\Entities\BaseEntity;
use PPCore\Collections\SimpleCollection;
use PPCore\ValidationRules\StringRule;
use PPCore\ValidationRules\IntegerRule;

class Worker extends BaseEntity{
  protected $id;
  protected $processing_queue;
  
  public function __construct(string $id){
    $this->id = $id;
  }
  
  public function id():string{
    return $this->id;
  }
  public function processingQueueName(){
    return $this->processing_queue;
  }
  public function setProcessingQueueName(string $name){
     $this->processing_queue=$name;
  }
  
    public static function buildValidationRules(SimpleCollection $rules) {
      $rules->set('id',new IntegerRule(false,0));
      $rules->set('processing_queue',new StringRule(false,0));
  }
}


/*

$response = $Qservice->takeJob($worker);

workWorkWork()
  while(true)
    attemptJob()
    

attemptJob()
  $response = $qservice->takeJob()
  if($response IS Job)
    runJob($response)
    heartBeat()
  else
    sleep(1);
    heartBeat()

runJob(Job $j)
  $service = ServiceContainer()->get( $j->service )
  $request = new $j->reqest($j->params)
  $job->response = $service->($j->func)($request)
  
  if($job->response->success())
    $qservice->completeJob($j)
  else
    $qservice->failJob($j)

*/