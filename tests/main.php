<?php
include __DIR__."/../vendor/autoload.php";

$job1 = new QMan\Job();
$job2 = new QMan\Job();
$instructions = json_encode( ['service'=>'PPInvoices\Payments','method'=>'doDatTing',
                              'request'=>['name'=>'PPInvoices\Requests\UIDInput','params'=>['id'=>4]]
                             ] );
$job1->create("service",$instructions);
$job2->create("service",$instructions);

$qr = new QMan\Resources\InMemoryQueueResource();

$repo = new QMan\QueueRepository($qr);
$repo->pushJob("main",$job1);
$repo->pushJob("main",$job2);

var_dump($qr->getAllOnQ('main'));

