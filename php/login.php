<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

define('DB_SERVER', 'brivdntiki1qnbunl6lc-mysql.services.clever-cloud.com');
define('DB_USERNAME', 'u4rytbbwvqjrogsf');
define('DB_PASSWORD', '3cIFU6mrtXC7mtaMILbB');
define('DB_DATABASE', 'brivdntiki1qnbunl6lc');

require_once '../vendor/autoload.php'; // Include Composer's autoloader
use Predis\Client;

// Connect to the MongoDB server
$databaseConnection = new MongoDB\Client;

// Connect to a specific MongoDB database
$myDatabase = $databaseConnection->guvi;

// Connect to a specific MongoDB collection
$userCollection = $myDatabase->data;


$response = array();
// Attempt to connect to the database
$db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Create a Predis client instance
$redis = new Client([
    'scheme' => 'tcp', // Use TCP protocol
    'host' => 'redis-14093.c253.us-central1-1.gce.cloud.redislabs.com', // Redis server hostname
    'port' => 14093, // Redis server port
    'password' => 'VpUKiqTS3UXx3nf42NVFeeqOwW09Lngq', // Your Redis password
]);

try {
    $redis->ping();
    $response['message']="Connected to Redis server successfully";
} catch (Exception $e) {
    error_log("Failed to connect to Redis: " . $e->getMessage());
}

if (isset($_POST['session_login'])) {
    $sessionId = $_POST['sessionId'];
    
    // Check if the session ID exists in the Redis database
    $sessionExists = $redis->get('user.' . $sessionId);

    $response = array(); // Initialize the response array

    if ($sessionExists) {
        // Session ID exists in Redis
        $response['status'] = 200;
        $response['message'] = "Session exists";
    } else {
        // Session ID does not exist in Redis
        $response['status'] = 404;
        $response['message'] = "Session not found";
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Terminate the script
}





if (isset($_POST['save_login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Sanitize and validate input (you should add proper validation and sanitization here)

    // Query the MySQL database to check if the credentials are valid
    $sql = "SELECT * FROM data WHERE email = ? AND pass = ?"; // You should hash the password and compare it securely
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Credentials are valid, retrieve user data from MongoDB
        $user = $userCollection->findOne(['email' => $email]);

        if ($user) {
            // User data found in MongoDB, send it as a JSON response
            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['user_data'] = $user; // MongoDB user data

            //Store the user data in the redis
            $redisKey = 'user.' . $user['_id'];
            $redis->set($redisKey, json_encode($user));
            
            // Store user data in local storage
            echo json_encode($response);
        } else {
            // User data not found in MongoDB
            $response['status'] = 404;
            $response['message'] = 'User data not found in MongoDB';
            echo json_encode($response);
        }
    } else {
        // Invalid credentials
        $response['status'] = 401;
        $response['message'] = 'Invalid Email or Password...';
        echo json_encode($response);
    }
} else {
    // 'save_login' parameter not set
    $response['status'] = 400;
    $response['message'] = 'Invalid request';
    echo json_encode($response);
}

// Close MySQL connection
$db->close();


/*
 // Initialize the response array

// Function to check the session in Redis
function checkSessionInRedis($sessionId, $redis) {
    $sessionData = $redis->hgetall($sessionId);

    if (isset($sessionData['loggedin']) && $sessionData['loggedin'] === '1') {
        return $sessionData;
    }

    return null;
}

// Function to check the session in MySQL
function checkSessionInMySQL($email, $password, $db) {
    $sql = "SELECT * FROM data WHERE email = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        if (password_verify($password, $row['pass'])) {
            return $row;
        } else {
            return null; // Password does not match
        }
    }

    return null; // User not found
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_login'])) {
        $myusername = mysqli_real_escape_string($db, $_POST['email']);
        $mypassword = mysqli_real_escape_string($db, $_POST['password']);
        $sessionId = $_POST['session_id'];

        if (empty($myusername) || empty($mypassword)) {
            $response['status'] = 422;
            $response['message'] = 'Username and password are required';
        } else {
            // Check if a session with the given session ID exists in Redis
            $sessionData = checkSessionInRedis($sessionId, $redis);

            if ($sessionData) {
                // The session exists in Redis, use it for authentication
                $response['status'] = 200;
                $response['message'] = 'Login successful';
            } else {
                // Session not found in Redis, check MySQL
                $sessionData = checkSessionInMySQL($myusername, $mypassword, $db);

                if ($sessionData) {
                    // Store session data in Redis
                    $sessionId = uniqid(); // Generate a unique session ID
                    $sessionData['loggedin'] = '1';

                    // Store the session data in Redis
                    $redis->hmset($sessionId, $sessionData);

                    // Set an expiration time for the session in Redis (e.g., 1 hour)
                    $redis->expire($sessionId, 3600);

                    $response['status'] = 200;
                    $response['message'] = 'Login successful';
                    $response['session_id'] = $sessionId;
                } else {
                    $response['status'] = 422;
                    $response['message'] = 'Invalid email or password';
                }
            }
        }
    } elseif (isset($_POST['check_session'])) {
        // Check if the session is valid
        $sessionId = $_POST['session_id'];
        $sessionData = checkSessionInRedis($sessionId, $redis);

        if ($sessionData) {
            $response['status'] = 200;
            $response['message'] = 'Session is valid';
        } else {
            $response['status'] = 401;
            $response['message'] = 'Session is not valid';
        }
    }

    // Return the JSON response
    header("Content-Type: application/json");

    echo json_encode($response);
}*/


?>
