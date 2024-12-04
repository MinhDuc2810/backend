<?php
require_once('../connect.php');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Kiểm tra xem ID đã được truyền hay chưa
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(["message" => "User ID is required", "status" => "error"]);
        exit;
    }

    $userId = $_GET['id'];

    try {
        // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Truy vấn lấy thông tin người dùng
        $sql = "SELECT id, userName, phoneNumber, email, role, avatar, isActive, createdAt, updatedAt 
                FROM Users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Kiểm tra nếu người dùng tồn tại
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Trả về dữ liệu người dùng dưới dạng JSON
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "User retrieved successfully",
                "status" => "success",
                "data" => $user
            ]);
        } else {
            // Nếu không tìm thấy người dùng
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "User not found",
                "status" => "error"
            ]);
        }
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
        "message" => "Invalid request method",
        "status" => "error"
    ]);
}
