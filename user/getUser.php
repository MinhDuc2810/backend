<?php
require_once('../connect.php');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Kiểm tra phương thức HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode([
            "message" => "Invalid request method. Only GET is allowed.",
            "status" => "error",
            "data" => []
        ]);
        exit();
    }

    // Kiểm tra xem ID có được truyền qua URL không
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            "message" => "User ID is required.",
            "status" => "error",
            "data" => []
        ]);
        exit();
    }

    // Lấy ID từ query string
    $id = $_GET['id'];

    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Truy vấn lấy dữ liệu người dùng theo ID
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Kiểm tra nếu không tìm thấy người dùng
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "message" => "User with ID $id not found.",
            "status" => "error",
            "data" => []
        ]);
        exit();
    }

    // Lấy dữ liệu người dùng
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $userinfo = array(
        "id" => (string)$row['id'], // Chuyển đổi ID thành chuỗi
        "userName" => $row['userName'],
        "phoneNumber" => $row['phoneNumber'],
        "email" => $row['email'],
        "role" => $row['role'],
        "createdAt" => $row['createdAt'],
        "updatedAt" => $row['updatedAt']
    );

    // Đóng kết nối
    $conn = null;

    // Trả về dữ liệu dưới dạng JSON
    echo json_encode([
        "message" => "User retrieved successfully.",
        "status" => "success",
        "data" => $userinfo
    ]);
} catch (PDOException $e) {
    // Xử lý lỗi kết nối hoặc truy vấn
    echo json_encode([
        "message" => "Failed to retrieve user.",
        "status" => "error",
        "data" => []
    ]);
}
