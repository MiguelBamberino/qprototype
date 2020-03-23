<?php
namespace QMan\Logs;
use PPCore\Entities\BaseEntity;
use PPCore\Collections\SimpleCollection;
use PPCore\ValidationRules\StringRule;
use PPCore\ValidationRules\IntegerRule;
use PPCore\ValidationRules\MySQLDateTimeRule;
use PPCore\ValidationRules\InListRule;
use PPCore\Helpers\DateHelper;
use QMan\Actors\Worker;
use QMan\Job;

class LogEntry extends BaseEntity{
  protected $id;
  protected $worker_id;
  protected $job_id;
  protected $queue_name;
  protected $type;
  protected $action;
  protected $job;
  protected $date;
  
  private static $allowed_types =['started','died','stat','killed','added','readded','completed','failed','broken','deleted'];
  
  public function setId($id){
    $this->id = $id;
  }
  private function baseCreate(string $action){
    $this->action =strtolower($action);
    $this->date = DateHelper::now();
  }
  public function worker(string $action,Worker $w):LogEntry{
    $this->type = 'worker';
    $this->worker_id = $w->id();
    $this->baseCreate($action);
    return $this;
  }
  public function job(string $action,Job $j, string $queue_name):LogEntry{
    $this->type = 'worker';
    $this->worker_id = $j->workerId();
    $this->job_id = $j->id();
    $this->queue_name = $queue_name;
    $this->job = $j->toJson();
    $this->baseCreate($action);
    return $this;
  }
  
  public static function buildValidationRules(SimpleCollection $rules) {
      $rules->set('id',new IntegerRule(false,0));
      $rules->set('worker_id',new StringRule(false,0));
      $rules->set('job_id',new StringRule(false,0));
      $rules->set('queue_name',new StringRule(false));
      $rules->set('type',new InListRule(true,['worker','job']));
      $rules->set('action',new InListRule(true,self::$allowed_types));
      $rules->set('job',new StringRule(false));
      $rules->set('date',new StringRule(true));
  }
}