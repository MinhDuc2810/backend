<?php
require_once('../connect.php');
// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Truy vấn lấy tất cả người dùng
        $sql = "SELECT id, userName, phoneNumber, email, role, createdAt, updatedAt FROM Users";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Lấy dữ liệu từ truy vấn
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về dữ liệu dưới dạng JSON
        header('Content-Type: application/json');
        echo json_encode(array(
            "message" => "Users retrieved successfully",
            "status" => "success",
            "data" => $users
        ));
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode(array("message" => "Error: " . $e->getMessage(), "status" => "error"));
    }
} else {
    // Nếu không phải phương thức GET, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode(array("message" => "Invalid request method", "status" => "error"));
}
