<?php
namespace QMan\Resources;
interface QueueResourceInterface{
  
  public function createQ(string $name):bool;
  public function pushOnQ(string $queue, string $value):bool;
  public function popOffQ(string $queue):string;
  public function popOffPushOnQ(string $popOffQueue, string $pushOnQueue):string;
  public function getAllOnQ(string $queue):array;
  public function getOneOnQ(string $queue):string;
}