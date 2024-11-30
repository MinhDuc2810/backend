<?php
require_once('../connect.php');

// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy id từ tham số URL
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Setting ID is required.",
                "status" => "error"
            ]);
            exit();
        }
        $id = $_GET['id'];

        // Truy vấn để lấy thông tin setting
        $sql = "SELECT id, `key`, `value`, description, createdAt, updatedAt FROM settings WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Kiểm tra nếu không tìm thấy setting
        if ($stmt->rowCount() === 0) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Setting with ID $id not found.",
                "status" => "error"
            ]);
            exit();
        }

        // Lấy dữ liệu từ truy vấn
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);

        // Trả về dữ liệu dưới dạng JSON
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Setting retrieved successfully.",
            "status" => "success",
            "data" => $setting
        ]);
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Error: " . $e->getMessage(),
            "status" => "error"
        ]);
    }
} else {
    // Nếu không phải phương thức GET, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode([
        "message" => "Invalid request method. Only GET is allowed.",
        "status" => "error"
    ]);
}
