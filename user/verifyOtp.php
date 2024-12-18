<?php
require_once('../connect.php');

// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Kết nối đến cơ sở dữ liệu
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy dữ liệu JSON từ yêu cầu
        $data = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['otp']) || !isset($data['email'])) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Email and OTP are required", "status" => "error"]);
            exit;
        }

        $otp = $data['otp'];
        $email = $data['email'];

        // Truy vấn người dùng theo email
        $sql = "SELECT id, otp, otpExpiresAt, isActive FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra nếu người dùng không tồn tại
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "User not found", "status" => "error"]);
            exit;
        }

        // Kiểm tra mã OTP
        if ($user['otp'] !== $otp) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Invalid OTP", "status" => "error"]);
            exit;
        }

        // Kiểm tra thời gian hết hạn OTP
        if (strtotime($user['otpExpiresAt']) < time()) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "OTP has expired", "status" => "error"]);
            exit;
        }

        // Cập nhật trạng thái tài khoản
        $updateSql = "UPDATE users SET isActive = 1, otp = NULL, otpExpiresAt = NULL WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':id', $user['id']);
        $updateStmt->execute();

        // Trả về phản hồi thành công
        header('Content-Type: application/json');
        echo json_encode(["message" => "Account verified successfully", "status" => "success"]);
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode(["message" => "Error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    // Nếu không phải phương thức POST, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid request method", "status" => "error"]);
}
