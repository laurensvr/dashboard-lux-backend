<?php

include_once('settings.php');

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.track.toggl.com/api/v9/workspaces/5457561/projects',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic YTQ5NWE1YmZhNmNmNzhjZjZmOGQ0MmVmODJmNzgzNDQ6YXBpX3Rva2Vu'
  ),
));

$projects = curl_exec($curl);



curl_close($curl);

$projects =  json_decode($projects, false);
$return = array();
// echo "<pre>";
// print_r($projects);
// echo "</pre>";
$projectNames = array();
foreach($projects as $project){
    $projectNames[$project->id] = $project->name;
    $return[$project->id] = array();
    $return[$project->id]['name'] = $project->name;
    $return[$project->id]['id'] = $project->id;
    $return[$project->id]['summarySeconds'] = 0;
}

// print_r($projectNames);


$curl = curl_init();
$start = substr((new DateTime('-1 week today'))->format(\DateTime::RFC3339), 0, -15);
$end = substr((new DateTime('tomorrow'))->format(\DateTime::RFC3339), 0, -15);
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.track.toggl.com/api/v9/me/time_entries?start_date='.$start.'&end_date='.$end,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Basic ' . TOGGL_TOKEN
  ),
));

$events = curl_exec($curl);
$events =  json_decode($events, false);
$currentEvent = null;

curl_close($curl);

foreach($events as $event){
    if(!isset($event->pid)) continue;
    $event->project = $return[$event->pid]['name'];
    if($event->duration < 0) {
        $currentProject = $return[$event->pid]['name'];
        $currentDescription = $event->description;
        $currentStartTime = $event->start;
    } else {
        $return[$event->pid]['summarySeconds'] += $event->duration;
    }
    
}

function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}
foreach($projects as $project){
    if($return[$project->id]['summarySeconds'] == 0) 
    ## Remove project->id from array
    unset($return[$project->id]);
    else{ 
        $return[$project->id]['summaryReadable'] = secondsToTime($return[$project->id]['summarySeconds']);
    }
}



$return['currentProject'] = $currentProject;
$return['currentDescription'] = $currentDescription;
$return['currentStartTime'] = $currentStartTime;
// echo "<pre>";
// print_r($return);
// echo "</pre>";

echo json_encode($return);
