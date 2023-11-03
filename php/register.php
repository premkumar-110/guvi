<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'guvi');

// Include the MongoDB PHP driver
require_once '../vendor/autoload.php';

// Connect to the MongoDB server
$databaseConnection = new MongoDB\Client;

// Connect to a specific MongoDB database
$myDatabase = $databaseConnection->guvi;

// Connect to a specific MongoDB collection
$userCollection = $myDatabase->data;

// Attempt to connect to the MySQL database
$db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if (isset($_POST['save_reg'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    // $fname = $_POST['fname'];
    // $lname = $_POST['lname'];

    // Validate all fields
    if ($password != $cpassword) {
        $response['status'] = 422;
        $response['message'] = 'Password does not match';
        echo json_encode($response);
        exit;
    }
    if (empty($email)  || empty($password)) {
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
            'password' => $password,
            'fname' => "first name",
            'lname' => "lastname",
            'age' => "00",
            'dob'=> '00-00-0000',
            'contact' => 'phone number and address'
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
