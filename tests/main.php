<?php
include __DIR__."/../vendor/autoload.php";

$qr = new QMan\Resources\InMemoryQueueResource();
$d=[
  'headings'=>[
    'queue_log'=>['id','worker_id','job_id','queue_name','type','action','job','date']
  ],
  'data'=>['queue_log'=>[]],
];
$conf = new PPCore\Collections\Config($d);
$ds = new PPCore\Adapters\DataSources\InMemoryDataSource($conf);

$qs = new QMan\QService($qr,$ds);
$w = new QMan\Actors\Worker("dave");
$response = $qs->workerStarted($w);
var_dump($response->success());
var_dump($response->errors());
cliTable($qr->getAllOnQ('main'),"Main Queue");
cliTable($ds->getMany(),"Queue logs");

exit;
$job1 = new QMan\Job();
$job2 = new QMan\Job();
$instructions = json_encode( ['service'=>'PPInvoices\Payments','method'=>'doDatTing',
                              'request'=>['name'=>'PPInvoices\Requests\UIDInput','params'=>['id'=>4]]
                             ] );
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


function cliTable(array $data,string $title=''){
  
  if($title){
    echo $title,"\n---------------------------\n";
  }
  
  if(empty($data)){
    echo "No results\n";
    echo "---------------------------\n\n";
    return;
  }
  
  $headings = array_keys(PPCore\Helpers\ArrayHelper::first($data));
  foreach($headings as $h){
    renderCell($h);
  }
  echo "\n";
  echo str_pad('',count($headings)*18 ,'-');
  echo "\n";
  
  foreach($data as $row){
    foreach($row as $cell){
      renderCell($cell);
    }
    echo "\n";
    
  }
  echo str_pad('',count($headings)*18 ,'-')."\n";
  
  
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
