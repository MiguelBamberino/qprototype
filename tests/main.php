<?php
include __DIR__."/../vendor/autoload.php";

use QMan\Actors\Worker;
use QMan\Job;

$instructions = json_encode( ['service'=>'PPInvoices\Payments','method'=>'doDatTing',
                              'request'=>['name'=>'PPInvoices\Requests\UIDInput','params'=>['id'=>4]]
                             ] );

$qr = new QMan\Resources\InMemoryQueueResource();
$d=[
  'headings'=>[
    'queue_log'=>['id','worker_id','job_id','queue_name','type','action','job','date']
  ],
  'primary_key'=>'id',
  'data'=>['queue_log'=>[]],
];
$conf = new PPCore\Collections\Config($d);
$ds = new PPCore\Adapters\DataSources\InMemoryDataSource($conf);

$qs = new QMan\QService($qr,$ds);
$dave = new Worker("dave");
$bobby = new Worker("bobby");
printResponse( $qs->workerStarted($dave) );
printResponse( $qs->workerStarted($bobby)  );

printResponse( $qs->addJob( (new Job())->create("service",$instructions)  ) );

printResponse( $qs->takeJob($dave) );

cliTable($qs->queryQueue(),"Main Queue",8);
cliTable($ds->getMany(),"Queue logs");

exit;
$job1 = new QMan\Job();
$job2 = new QMan\Job();

$job1->create("service",$instructions);
$job2->create("service",$instructions);


$repo = new QMan\QueueRepository($qr);
$repo->createQueue("main");
$repo->createQueue("worker-q1");
$repo->pushJob("main",$job1);
$repo->pushJob("main",$job2);
var_dump($qr->getAllOnQ('main'));
$j = $repo->getAllJobs("main");
var_dump($qr->getAllOnQ('main'));
var_dump($j);


function cliTable(array $data,string $title='',$width=15){
  if($title){
    echo $title,"\n---------------------------\n";
  }
  
  if(empty($data)){
    echo "No results\n";
    echo "---------------------------\n\n";
    return;
  }
  $blm = $width+3;// bottom line multiplier
  
  $headings = array_keys(PPCore\Helpers\ArrayHelper::first($data));
  foreach($headings as $h){
    renderCell($h,$width);
  }
  echo "\n";
  echo str_pad('',count($headings)*$blm ,'-');
  echo "\n";
  
  foreach($data as $row){
    foreach($row as $cell){
      renderCell($cell,$width);
    }
    echo "\n";
    
  }
  echo str_pad('',count($headings)*$blm ,'-')."\n";
  
  
}
function renderCell($text,$width=15, $colChar=" | "){
  
  $length = strlen($text);
  if($length == $width){
    echo $text.$colChar;
  }elseif($length<$width){
    
    echo str_pad($text, $width,' ').$colChar; 
  }else{
    echo substr($text,0,$width).$colChar;
  }
  
}

function printResponse(PPCore\Responses\Response $r){
  var_dump($r->success());
  var_dump($r->errors());
}
