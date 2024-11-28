<?php
require_once('../connect.php');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only GET requests are allowed."
        ]);
        exit();
    }

    // Check if ID is provided in the query parameters
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Package ID is required."
        ]);
        exit();
    }

    // Retrieve package ID
    $id = $_GET['id'];

    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL query to fetch package
    $sql = "SELECT id, name, type, price, duration, status, createdAt, updatedAt FROM packages WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    // Check if package exists
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Package with ID $id not found."
        ]);
        exit();
    }

    // Fetch package details
    $package = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return JSON response
    echo json_encode([
        "status" => "success",
        "message" => "Package retrieved successfully.",
        "data" => $package
    ]);
} catch (PDOException $e) {
    // Handle errors
    echo json_encode([
        "status" => "error",
        "message" => "Error retrieving package: " . $e->getMessage()
    ]);
}
