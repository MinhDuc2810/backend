<?php
require_once('../connect.php');

// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy id từ URL (sử dụng $_GET)
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Setting ID is required.",
                "status" => "error"
            ]);
            exit();
        }
        $id = $_GET['id'];

        // Kiểm tra xem cài đặt có tồn tại không
        $checkSql = "SELECT id FROM settings WHERE id = :id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Setting with ID $id not found.",
                "status" => "error"
            ]);
            exit();
        }

        // Xóa cài đặt
        $deleteSql = "DELETE FROM settings WHERE id = :id";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Thực thi xóa
        $deleteStmt->execute();

        // Trả về phản hồi thành công
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Setting deleted successfully.",
            "status" => "success"
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
    // Nếu không phải phương thức DELETE, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode([
        "message" => "Invalid request method. Only DELETE is allowed.",
        "status" => "error"
    ]);
}
