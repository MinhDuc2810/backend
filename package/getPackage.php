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
        echo json_encode([]);
        exit();
    }

    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL query to fetch all packages
    $sql = "SELECT * FROM packages where status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all packages
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any packages exist
    if (count($packages) === 0) {
        echo json_encode([]);
        exit();
    }

    // Prepare the response in the desired format
    $response = [];

    // Map each package to the frontend format
    foreach ($packages as $package) {
        $response[] = [
            "id" => $package['id'],
            "name" => $package['name'],
            "duration" => $package['duration'],
            "type" => $package['type'],
            "price" => $package['price']
            
        ];
    }

    // Return the JSON response (only the data)
    echo json_encode($response);

} catch (PDOException $e) {
    // Handle errors
    echo json_encode([]);
}
?>
