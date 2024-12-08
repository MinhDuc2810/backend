<?php
require_once('../connect.php');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Check if the request method is DELETE
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only DELETE requests are allowed."
        ]);
        exit();
    }

    // Check if ID is provided in the query string
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "PT ID is required in the URL."
        ]);
        exit();
    }

    // Retrieve package ID from URL
    $id = $_GET['id'];

    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL to check if the package exists
    $checkSql = "SELECT * FROM users WHERE id = :id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    // Check if the package exists
    if ($checkStmt->rowCount() === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "PT with ID $id not found."
        ]);
        exit();
    }

    // Update the status to "inactive" for soft delete
    $deleteSql = "UPDATE users SET isActive = 0, updatedAt = CURRENT_TIMESTAMP WHERE id = :id";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    // Return JSON response
    echo json_encode([
        "status" => "success",
        "message" => "PT with ID $id has been soft deleted successfully."
    ]);
} catch (PDOException $e) {
    // Handle errors
    echo json_encode([
        "status" => "error",
        "message" => "Error soft deleting package: " . $e->getMessage()
    ]);
}
