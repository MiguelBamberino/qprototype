<?php

namespace QMan;

use PPCore\Helpers\DateHelper;
use PPCore\Requests\AbstractRequestInput;
use PPCore\Collections\SimpleCollection;
use PPCore\ValidationRules\StringRule;
use PPCore\ValidationRules\IntegerRule;
use PPCore\ValidationRules\MySQLDateTimeRule;

class Job extends AbstractRequestInput{
  
  protected $id;
  protected $type;
  protected $instructions;
  protected $response;
  protected $errors;
  protected $worker_id;
  
  protected $created_at;
  protected $started_at;
  protected $completed_at;
  protected $failed_at;
  protected $broke_at;
  protected $deleted_at;
  
  protected $max_fails;
  protected $fail_count;
  
  public function create(string $type, string $instructions,int $max_fails=1):Job{
    $this->created_at = DateHelper::now();
    $this->type = $type;
    $this->instructions = $instructions;
    $this->id = uniqid(true).rand(0,9);
    $this->fail_count = 0;
    $this->max_fails = $max_fails;
    return $this;
  }
  
  public function start(string $worker_id):string{
    $this->worker_id = $worker_id;
    $this->started_at = DateHelper::now();
    return $this->started_at;
  }
  
  public function completed(string $response):string{
    $this->response = $response;
    $this->completed_at = DateHelper::now();
    return $this->completed_at;
  }
  
  public function fail(array $errors=[]):string{
    $this->errors = json_encode($errors);
    $this->failed_at = DateHelper::now();
    $this->failed_count++;
    if($this->reachedMaxFails() ){
      $this->broke_at = $this->failed_at;
    }
    return $this->failed_at;
  }
  public function reachedMaxFails(){
    return ($this->failed_count>= $this->max_fails);
  }
  
  public function WorkerId(){
    return $this->worker_id;
  }
  public function id(){
    return $this->id;
  }

  public static function buildValidationRules(SimpleCollection $rules) {
      $rules->set('id',new StringRule(true,0));
      $rules->set('type',new StringRule(true,0));
      $rules->set('instructions',new StringRule(true,0));
      $rules->set('response',new StringRule(false));
      $rules->set('errors',new StringRule(false));
      $rules->set('worker_id',new StringRule(false,0));
      $rules->set('created_at',new MySQLDateTimeRule(true));
      $rules->set('started_at',new MySQLDateTimeRule(false));
      $rules->set('completed_at',new MySQLDateTimeRule(false));
      $rules->set('failed_at',new MySQLDateTimeRule(false));
      $rules->set('broke_at',new MySQLDateTimeRule(false));
      $rules->set('deleted_at',new MySQLDateTimeRule(false));
      $rules->set('max_fails',new IntegerRule(true,1));
      $rules->set('fail_count',new IntegerRule(true,0));
  }
  
}