<?php


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