<?php
require_once('../connect.php');
require_once('../helper/email.php'); // Hàm sendMail để gửi email
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
        $sql = "SELECT id, userName FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Email does not exist in our system.", "status" => "error"]);
            exit;
        }

        // Tạo mật khẩu mới
        $newPassword = generateRandomPassword(8); // Mật khẩu mới có 8 ký tự
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Mã hóa mật khẩu

        // Cập nhật mật khẩu mới vào cơ sở dữ liệu
        $updateSql = "UPDATE users SET password = :password WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':password', $hashedPassword);
        $updateStmt->bindParam(':id', $user['id']);
        $updateStmt->execute();

        // Gửi mật khẩu mới qua email
        $subject = "Your New Password";
        $message = "Hello " . $user['userName'] . ",\n\nYour password has been reset. Your new password is: " . $newPassword . "\n\nPlease log in and change your password immediately.\n\nThank you!";
        if (sendMail($email, $subject, $message)) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "A new password has been sent to your email.", "status" => "success"]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Failed to send email.", "status" => "error"]);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(["message" => "Error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid request method", "status" => "error"]);
}

// Hàm tạo mật khẩu ngẫu nhiên
function generateRandomPassword($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $password;
}
