<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

define('DB_SERVER', 'brivdntiki1qnbunl6lc-mysql.services.clever-cloud.com');
define('DB_USERNAME', 'u4rytbbwvqjrogsf');
define('DB_PASSWORD', '3cIFU6mrtXC7mtaMILbB');
define('DB_DATABASE', 'brivdntiki1qnbunl6lc');

require_once '../vendor/autoload.php'; 
use Predis\Client;

$databaseConnection = new MongoDB\Client;
$myDatabase = $databaseConnection->guvi;
$userCollection = $myDatabase->data;
$response = array();
$db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

$redis = new Client([
    'scheme' => 'tcp',
    'host' => 'redis-14093.c253.us-central1-1.gce.cloud.redislabs.com',
    'port' => 14093,
    'password' => 'VpUKiqTS3UXx3nf42NVFeeqOwW09Lngq',
]);

try {
    $redis->ping();
    $response['message'] = "Connected to Redis server successfully";
} catch (Exception $e) {
    error_log("Failed to connect to Redis: " . $e->getMessage());
}


if (isset($_POST['session_login'])) {
    $sessionId = $_POST['sessionId'];
    $sessionExists = $redis->get('user.' . $sessionId);
    $response = array();
    if ($sessionExists) {

        $response['status'] = 200;
        $response['message'] = "Session exists";
    } else {
        $response['status'] = 404;
        $response['message'] = "Session not found";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['save_login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM data WHERE email = ? AND pass = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $userCollection->findOne(['email' => $email]);
        if ($user) {
            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['user_data'] = $user; 

            $redisKey = 'user.' . $user['_id'];
            $redis->set($redisKey, json_encode($user));
            echo json_encode($response);
        } else {
            $response['status'] = 404;
            $response['message'] = 'User data not found in MongoDB';
            echo json_encode($response);
        }
    } else {
        $response['status'] = 401;
        $response['message'] = 'Invalid Email or Password...';
        echo json_encode($response);
    }
} else {
    $response['status'] = 400;
    $response['message'] = 'Invalid request';
    echo json_encode($response);
}

$db->close();
?>