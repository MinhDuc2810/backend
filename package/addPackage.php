<?php
require_once('../connect.php');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only POST requests are allowed."
        ]);
        exit();
    }

    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read POST request data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (
        empty($data['name']) ||
        empty($data['type']) ||
        !isset($data['price']) ||
        empty($data['duration'])
    ) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields (name, type, price, duration)."
        ]);
        exit();
    }

    // Prepare SQL query
    $sql = "INSERT INTO packages (name, type, price, duration, status) 
            VALUES (:name, :type, :price, :duration, :status)";
    $stmt = $conn->prepare($sql);

    // Bind values from POST data
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':type', $data['type']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':duration', $data['duration']);
    $status = isset($data['status']) ? $data['status'] : 'active'; // Default status is 'active'
    $stmt->bindParam(':status', $status);

    // Execute SQL query
    $stmt->execute();

    // Return JSON response
    echo json_encode([
        "status" => "success",
        "message" => "Package has been added successfully.",
        "data" => [
            "id" => $conn->lastInsertId(),
            "name" => $data['name'],
            "type" => $data['type'],
            "price" => $data['price'],
            "duration" => $data['duration'],
            "status" => $status
        ]
    ]);
} catch (PDOException $e) {
    // Handle errors
    echo json_encode([
        "status" => "error",
        "message" => "Error adding package: " . $e->getMessage()
    ]);
}
