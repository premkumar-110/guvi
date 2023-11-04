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
$redis = new Client([
    'scheme' => 'tcp', 
    'host' => 'redis-14093.c253.us-central1-1.gce.cloud.redislabs.com', 
    'port' => 14093, 
    'password' => 'VpUKiqTS3UXx3nf42NVFeeqOwW09Lngq',
]);
$response = array();
$databaseConnection = new MongoDB\Client;
$myDatabase = $databaseConnection->guvi;
$userCollection = $myDatabase->data;

$sessionId = $_POST['sessionId'];
$query = ['_id' => new MongoDB\BSON\ObjectID($sessionId)];
$document = $userCollection->findOne($query);

if ($document) {
    $email = $document['email'];
    $response['status'] = 200;
    $response['user'] = $document;
} else {
    $response['status'] = 422;
    $response['message'] = 'Data not found';
}


if (isset($_POST['logout'])) {
    $response = array();
    $session = $_POST['sessionId'];

    if (!empty($session)) {
        $redisKey = 'user.' . $session;
        $deletedKeys = $redis->del($redisKey);

        if ($deletedKeys > 0) {
            $response = [
                'status' => 200,
                'message' => 'Data removed from Redis successfully.',
                'data'=>$deletedKeys
            ];
        } else {
            $response = [
                'status' => 422,
                'message' => 'Data not found in Redis for the provided session ID.',
            ];
        }
    } else {
        $response = [
            'status' => 400,
            'message' => 'Session ID not provided.',
        ];
    }
}
if (isset($_POST['updateData'])) {
    $response = array();
    $newEmail = $_POST['email'];
    $newFname = $_POST['fname'];
    $newLname = $_POST['lname'];
    $newContact = $_POST['contact'];
    $newAge = $_POST['age'];
    $newDob = $_POST['dob'];
    $newPhone = $_POST['phone'];
    $sessionId = $_POST['sessionId'];
  
    if (!empty($sessionId)) {
      $query = ['_id' => new MongoDB\BSON\ObjectID($sessionId)];
      $updateData = [
        '$set' => [
          'email' => $newEmail,
          'fname' => $newFname,
          'lname' => $newLname,
          'contact' => $newContact,
          'age' => $newAge,
          'dob' => $newDob,
          'phone' => $newPhone,
        ],
      ];
  
      $result = $userCollection->updateOne($query, $updateData);
  
      if ($result->getModifiedCount() > 0) {
        $response['status'] = 200;
        $response['message'] = 'Data updated successfully.';
    } else {
        $response['status'] = 422;
        $response['message'] = 'Data not updated in MongoDB.';
    }
    } else {
      $response = [
        'status' => 400,
        'message' => 'Session ID not provided.',
      ];
    }
  
    echo json_encode($response);
    exit;
  }
  
  if (isset($_POST['passUpdateData'])) {
    $response = [];

    $email = $_POST['email'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword1 = $_POST['newPassword1'];
    $newPassword2 = $_POST['newPassword2'];
    $sessionId = $_POST['sessionId'];

    if (!$sessionId) {
        $response['status'] = 400;
        $response['message'] = "Please login to continue";
    } else {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $stmt = $conn->prepare("SELECT pass FROM data WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($storedPassword);
        $stmt->fetch();
        $stmt->close();
        if ($oldPassword === $storedPassword) {
            // Check if the new passwords match
            if ($newPassword1 === $newPassword2) {
                $stmt = $conn->prepare("UPDATE data SET pass = ? WHERE email = ?");
                $stmt->bind_param("ss", $newPassword1, $email);

                if ($stmt->execute()) {
                    $response['status'] = 200;
                    $response['message'] = "Password updated successfully.";
                } else {
                    $response['status'] = 422;
                    $response['message'] = "Failed to update the password.";
                }

                $stmt->close();
            } else {
                $response['status'] = 422;
                $response['message'] = "New passwords do not match.";
            }
        } else {
            $response['status'] = 422;
            $response['message'] = "Old password does not match.";
        }

        $conn->close();
    }

    echo json_encode($response);
    exit;
}





echo json_encode($response);

?>