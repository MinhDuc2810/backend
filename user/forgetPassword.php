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
        $subject = "Mật khẩu mới của bạn";
        $message = "Xin chào " . $user['userName'] . ",\n\nYour new password " . $newPassword . "\n\nVui lòng đăng nhập và đổi lại mật khẩu.\n\nCảm ơn!";
        if (sendMail($email, $subject, $message)) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Mật khẩu mới đã được gửi đến email.", "status" => "success"]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Không gửi được email.", "status" => "error"]);
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
