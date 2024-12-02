<?php
require_once('../connect.php');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Check if the request method is PUT
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only PUT requests are allowed."
        ]);
        exit();
    }

    // Check if ID is provided in the query string
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Package ID is required in the URL."
        ]);
        exit();
    }

    // Retrieve package ID from URL
    $id = $_GET['id'];

    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read PUT request data
    $data = json_decode(file_get_contents('php://input'), true);

    // Prepare SQL for fetching the package
    $checkSql = "SELECT * FROM packages WHERE id = :id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    // Check if package exists
    if ($checkStmt->rowCount() === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Package with ID $id not found."
        ]);
        exit();
    }

    // Build SQL for updating the package
    $updateSql = "UPDATE packages SET 
                    name = :name, 
                    type = :type, 
                    price = :price, 
                    duration = :duration,
                    updatedAt = CURRENT_TIMESTAMP 
                  WHERE id = :id";

    $updateStmt = $conn->prepare($updateSql);

    // Bind values for update (use existing data if fields are missing)
    $package = $checkStmt->fetch(PDO::FETCH_ASSOC);
    $name = !empty($data['name']) ? $data['name'] : $package['name'];
    $type = !empty($data['type']) ? $data['type'] : $package['type'];
    $price = isset($data['price']) ? $data['price'] : $package['price'];
    $duration = !empty($data['duration']) ? $data['duration'] : $package['duration'];

    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':type', $type);
    $updateStmt->bindParam(':price', $price);
    $updateStmt->bindParam(':duration', $duration);
    

    // Execute the update
    $updateStmt->execute();

    // Return JSON response
    echo json_encode([
        "status" => "success",
        "message" => "Package updated successfully.",
        "data" => [
            "id" => $id,
            "name" => $name,
            "type" => $type,
            "price" => $price,
            "duration" => $duration,
        ]
    ]);
} catch (PDOException $e) {
    // Handle errors
    echo json_encode([
        "status" => "error",
        "message" => "Error updating package: " . $e->getMessage()
    ]);
}
