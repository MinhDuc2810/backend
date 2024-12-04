<?php
require_once('../connect.php');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Kiểm tra phương thức HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        echo json_encode([
            "message" => "Invalid request method. Only PUT is allowed.",
            "status" => "error"
        ]);
        exit();
    }

    // Kiểm tra xem ID có được truyền qua URL không
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            "message" => "User ID is required in the URL.",
            "status" => "error"
        ]);
        exit();
    }

    // Lấy ID từ query string
    $id = $_GET['id'];

    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Đọc dữ liệu từ yêu cầu PUT
    $data = json_decode(file_get_contents('php://input'), true);

    // Kiểm tra nếu dữ liệu trống
    if (!$data) {
        echo json_encode([
            "message" => "No data provided for update.",
            "status" => "error"
        ]);
        exit();
    }

    // Truy vấn kiểm tra nếu người dùng tồn tại
    $checkSql = "SELECT * FROM users WHERE id = :id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        echo json_encode([
            "message" => "User with ID $id not found.",
            "status" => "error"
        ]);
        exit();
    }

    // Cập nhật thông tin người dùng
    $updateSql = "UPDATE users SET 
                    userName = COALESCE(:userName, userName),
                    phoneNumber = COALESCE(:phoneNumber, phoneNumber),
                    email = COALESCE(:email, email),
                    updatedAt = CURRENT_TIMESTAMP 
                  WHERE id = :id";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $updateStmt->bindParam(':userName', $data['userName']);
    $updateStmt->bindParam(':phoneNumber', $data['phoneNumber']);
    $updateStmt->bindParam(':email', $data['email']);

    $updateStmt->execute();

    // Trả về thông tin đã cập nhật
    $userUpdated = $checkStmt->fetch(PDO::FETCH_ASSOC);
    $response = [
        "id" => $userUpdated['id'],
        "userName" => $data['userName'] ?? $userUpdated['userName'],
        "phoneNumber" => $data['phoneNumber'] ?? $userUpdated['phoneNumber'],
        "email" => $data['email'] ?? $userUpdated['email'],
        "avatar" => "http://192.168.6.193:80/api/backend/userimage/" . $userUpdated['avatar'],
        "role" => $userUpdated['role']
    ];

    echo json_encode([
        "message" => "User updated successfully.",
        "status" => "success",
        "user" => $response
    ]);
} catch (PDOException $e) {
    // Xử lý lỗi kết nối hoặc truy vấn
    echo json_encode([
        "message" => "Error updating user: " . $e->getMessage(),
        "status" => "error"
    ]);
}
