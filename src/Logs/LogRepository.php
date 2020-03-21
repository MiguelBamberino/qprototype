<?php
namespace QMan\Logs;

use PPCore\Repositories\BaseRepository;

class LogRepository extends BaseRepository{
  
  protected $location = "queue_log";
  protected $defaultEntityClass = "LogEntry";
  
  public function getDefaultEntityClassName(){
    return "QMan\Logs\LogEntry";
  }
}