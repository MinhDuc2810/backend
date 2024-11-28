<?php
require_once('../connect.php');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Kiểm tra xem phương thức HTTP có phải là GET hay không
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only GET is allowed."
        ]);
        exit();
    }

    // Kiểm tra xem `id` có được truyền qua URL không
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "User ID is required."
        ]);
        exit();
    }

    // Lấy `id` từ URL
    $id = $_GET['id'];

    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Thiết lập chế độ báo lỗi của PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Truy vấn lấy dữ liệu người dùng theo ID
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Kiểm tra nếu không có kết quả
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "User with ID $id not found."
        ]);
        exit();
    }

    // Lấy dữ liệu người dùng
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Tạo cấu trúc dữ liệu cho người dùng
    $userinfo = [
        "id" => $row['id'],
        "userName" => $row['userName'],
        "email" => $row['email'],
        "phoneNumber" => $row['phoneNumber'],
        "avatar" => "http://192.168.1.19:80/api/gym/userimage/" . $row['avatar'],
        "role" => $row['role']
    ];

    // Đóng kết nối
    $conn = null;

    // Định dạng phản hồi JSON
    $response = [
        "status" => "success",
        "message" => "User retrieved successfully.",
        "data" => $userinfo
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    // Xử lý lỗi nếu kết nối hoặc truy vấn gặp vấn đề
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $e->getMessage()
    ]);
}
