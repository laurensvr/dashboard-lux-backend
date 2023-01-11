<?php
include_once('settings.php');
include_once(__DIR__ . '/vendor/autoload.php');

$client = new Google_Client();
## Used Calendar IDs
# Create google project Elegant Dashboard
# Activate calendar google API
# Create service worker account with no extra permissions
# 1. Elegant Works ID=
# 2. Personal Schedule ID=



## 1. Create api instance
$client = new Google\Client();

function checkServiceAccountCredentialsFile(){
    return __DIR__ . '/'. CREDENTIALS_FILE;
}

if ($credentials_file = checkServiceAccountCredentialsFile()) {
    // set the location manually
    $client->setAuthConfig($credentials_file);
} elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
    // use the application default credentials
    $client->useApplicationDefaultCredentials();
} else {
    echo missingServiceAccountDetailsWarning();
    return;
}

$client->setScopes(['https://www.googleapis.com/auth/calendar.readonly']);
$service = new Google\Service\Calendar($client);

setlocale(LC_ALL, 'nl_NL');

$optParams = [
    'timeMin' => (new DateTime())->format(\DateTime::RFC3339), 
    'timeMax' => (new DateTime('+2 month'))->format(\DateTime::RFC3339), 
    'showDeleted' => false,
    'orderBy' => 'startTime',
    'singleEvents' => 'true',
  ];

$whitelist = array(
    'summary',
    'start',
    'end',
);

function filter($array, $whitelist) {
    foreach($array as $key => $value) {
        $value = (array) $value;
        if(is_array($value)) {
            $array[$key] = array_intersect_key($value, array_flip($whitelist));
        }
    }
    return $array;
}

$afspraken = $service->events->listEvents(CALENDARS_IDS[0], $optParams);  // Elegant Works


$optParams = [
    'timeMin' => (new DateTime())->format(\DateTime::RFC3339), 
    'timeMax' => (new DateTime('+1 week'))->format(\DateTime::RFC3339), 
    'showDeleted' => false,
    'orderBy' => 'startTime',
    'singleEvents' => 'true',
  ];
$schedule = $service->events->listEvents(CALENDARS_IDS[1], $optParams);  // Personal Schedule
$return = array();
$return['cal1'] = filter($afspraken->getItems(), $whitelist);
$return['cal2'] = filter($schedule->getItems(), $whitelist);

header("Content-type: application/json; charset=utf-8");
echo json_encode($return);
