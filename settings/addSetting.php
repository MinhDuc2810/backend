<?php
require_once('../connect.php');

// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy dữ liệu từ body của yêu cầu
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Kiểm tra dữ liệu đầu vào
        if (empty($data['key']) || empty($data['value'])) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "The 'key' and 'value' fields are required.",
                "status" => "error"
            ]);
            exit();
        }

        $key = $data['key'];
        $value = $data['value'];
        $description = isset($data['description']) ? $data['description'] : null;

        // Kiểm tra xem key đã tồn tại chưa
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM settings WHERE `key` = :key");
        $checkStmt->bindParam(':key', $key);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() > 0) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "The key '$key' already exists.",
                "status" => "error"
            ]);
            exit();
        }

        // Thêm cài đặt mới
        $sql = "INSERT INTO settings (`key`, `value`, `description`) VALUES (:key, :value, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':description', $description);

        $stmt->execute();

        // Trả về phản hồi thành công
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Setting added successfully.",
            "status" => "success",
            "data" => [
                "id" => $conn->lastInsertId(),
                "key" => $key,
                "value" => $value,
                "description" => $description
            ]
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
    // Nếu không phải phương thức POST, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode([
        "message" => "Invalid request method. Only POST is allowed.",
        "status" => "error"
    ]);
}
