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

    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL query to fetch all packages
    $sql = "SELECT id, name, type, price, duration, status, createdAt, updatedAt FROM packages";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all packages
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if packages exist
    if (empty($packages)) {
        echo json_encode([
            "status" => "error",
            "message" => "No packages found."
        ]);
        exit();
    }

    // Return JSON response
    echo json_encode([
        "status" => "success",
        "message" => "Packages retrieved successfully.",
        "data" => $packages
    ]);
} catch (PDOException $e) {
    // Handle errors
    echo json_encode([
        "status" => "error",
        "message" => "Error retrieving packages: " . $e->getMessage()
    ]);
}
