<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
define('DB_SERVER', 'brivdntiki1qnbunl6lc-mysql.services.clever-cloud.com');
define('DB_USERNAME', 'u4rytbbwvqjrogsf');
define('DB_PASSWORD', '3cIFU6mrtXC7mtaMILbB');
define('DB_DATABASE', 'brivdntiki1qnbunl6lc');

require_once '../vendor/autoload.php';

$databaseConnection = new MongoDB\Client;
$myDatabase = $databaseConnection->guvi;
$userCollection = $myDatabase->data;
$db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if (isset($_POST['save_reg'])) {
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 422;
        $response['message'] = 'A valid email is required';
        echo json_encode($response);
        exit;
    }
    if ($password != $cpassword) {
        $response['status'] = 422;
        $response['message'] = 'Password does not match';
        echo json_encode($response);
        exit;
    }
    if (empty($email) || empty($password)) {
        $response['status'] = 422;
        $response['message'] = 'All fields are mandatory';
        echo json_encode($response);
        exit;
    }

    // Check if email already exists using a prepared statement
    $checkQuery = "SELECT * FROM data WHERE email = ?";
    $checkStatement = $db->prepare($checkQuery);
    $checkStatement->bind_param("s", $email);
    $checkStatement->execute();
    $checkResult = $checkStatement->get_result();

    if ($checkResult->num_rows > 0) {
        $response['status'] = 422;
        $response['message'] = 'Email already exists';
        echo json_encode($response);
        exit;
    }

    // Insert data into the MySQL database using a prepared statement
    $insertQuery = "INSERT INTO data (email, pass) VALUES (?, ?)";
    $insertStatement = $db->prepare($insertQuery);
    $insertStatement->bind_param("ss", $email, $password);
    $insertResult = $insertStatement->execute();

    // Check if the data was inserted successfully into MySQL
    if ($insertResult) {
        // Now, store the same data in MongoDB
        $userData = [
            'email' => $email,
            'fname' => "first name",
            'lname' => "lastname",
            'age' => "00",
            'dob' => '00-00-0000',
            'contact' => 'phone number and address',
            'phone' => "1234567890"
        ];
        $userCollection->insertOne($userData);

        $response['status'] = 200;
        $response['message'] = 'Registered successfully';
    } else {
        $response['status'] = 500;
        $response['message'] = 'Registration failed';
    }

    echo json_encode($response);
}

// Close the database connections
$db->close();

?>