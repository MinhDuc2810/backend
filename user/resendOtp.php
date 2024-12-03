<?php
require_once('../connect.php');
require_once('../helper/email.php'); // Hàm tái sử dụng để gửi email
session_start();
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Kết nối đến cơ sở dữ liệu
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy dữ liệu JSON từ yêu cầu
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['email'])) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Email is required", "status" => "error"]);
            exit;
        }

        $email = $data['email'];

        // Tìm người dùng dựa trên email
        $sql = "SELECT id, userName, isActive FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "User not found", "status" => "error"]);
            exit;
        }

        // Tạo OTP mới
        $otp = strval(rand(100000, 999999));
        $otpExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes')); // OTP hết hạn sau 15 phút

        // Cập nhật OTP và thời gian hết hạn vào cơ sở dữ liệu
        $updateSql = "UPDATE users SET otp = :otp, otpExpiresAt = :otpExpiresAt WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':otp', $otp);
        $updateStmt->bindParam(':otpExpiresAt', $otpExpiresAt);
        $updateStmt->bindParam(':id', $user['id']);
        $updateStmt->execute();

        // Lưu email vào session
        $_SESSION['email'] = $email;

        // Gửi email OTP
        $subject = "Your OTP Code";
        $message = "Dear " . $user['userName'] . ",\n\nYour new OTP code is: " . $otp . "\n\nThis code will expire in 15 minutes.\n\nThank you.";
        if (sendMail($email, $subject, $message)) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "OTP resent successfully", "status" => "success"]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Failed to send OTP email", "status" => "error"]);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(["message" => "Error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    // Nếu không phải phương thức POST, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid request method", "status" => "error"]);
}
