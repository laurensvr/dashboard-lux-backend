<?php
include_once('settings.php');
include 'httpsocket.php';

$sock = new HTTPSocket;

$sock->connect(DIRECTADMIN_URL,2222);
$sock->set_login(DIRECTADMIN_USER , DIRECTADMIN_KEY);

$sock->query('/CMD_API_RESELLER_STATS?type=usage');
$result = $sock->fetch_parsed_body();

// echo "<pre>";
// var_dump($result);
// echo "</pre>";
header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=utf-8");
echo json_encode([
    'bandwidth' => round($result['bandwidth']/1024),
    'email_deliveries_incoming' => $result['email_deliveries_incoming'],
    'email_deliveries_outgoing' => $result['email_deliveries_outgoing'],
    'nusers' => $result['nusers'],
]);

// echo "Data\n".round($result['bandwidth']/1024)." GB\n";
// echo "".$result['email_deliveries_incoming'];
// echo "/".$result['email_deliveries_outgoing'];

?>