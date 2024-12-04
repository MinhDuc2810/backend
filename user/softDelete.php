<?php
require_once('../connect.php');
// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy dữ liệu từ body của yêu cầu
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Kiểm tra dữ liệu đầu vào
        if (isset($data['id']) && !empty($data['id'])) {
            $id = $data['id'];

            // Kiểm tra xem người dùng có tồn tại không và chưa bị xóa
            $checkSql = "SELECT * FROM Users WHERE id = :id AND isActive = 0";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                // Cập nhật trạng thái isActive thành 1
                $sql = "UPDATE Users SET isActive = 1 WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                // Trả về phản hồi thành công
                header('Content-Type: application/json');
                echo json_encode(array("message" => "User soft deleted successfully", "status" => "success"));
            } else {
                // Người dùng không tồn tại hoặc đã bị xóa
                header('Content-Type: application/json');
                echo json_encode(array("message" => "User not found or already deleted", "status" => "error"));
            }
        } else {
            // Trả về lỗi nếu thiếu ID
            header('Content-Type: application/json');
            echo json_encode(array("message" => "User ID is required", "status" => "error"));
        }
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode(array("message" => "Error: " . $e->getMessage(), "status" => "error"));
    }
} else {
    // Nếu không phải phương thức POST, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode(array("message" => "Invalid request method", "status" => "error"));
}
