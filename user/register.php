<?php
require_once('../connect.php');
require_once('../helper/email.php'); // Hàm sendMail để gửi email OTP
session_start(); // Bắt đầu session
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy dữ liệu JSON từ yêu cầu
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Kiểm tra dữ liệu đầu vào
        if (isset($data['userName'], $data['phoneNumber'], $data['email'], $data['password'])) {
            $userName = $data['userName'];
            $phoneNumber = $data['phoneNumber'];
            $email = $data['email'];
            $password = $data['password'];

            // Kiểm tra email hoặc số điện thoại đã tồn tại chưa
            $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email OR phoneNumber = :phoneNumber");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                header('Content-Type: application/json');
                echo json_encode(["message" => "Email or phone number already exists", "status" => "error"]);
                exit;
            }

            // Kiểm tra mật khẩu mạnh
            if (!isPasswordStrong($password)) {
                header('Content-Type: application/json');
                echo json_encode([
                    "message" => "Password is too weak. It must contain at least 6 characters, including uppercase, lowercase, digits, and special characters.",
                    "status" => "error"
                ]);
                exit;
            }

            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Tạo OTP và thời gian hết hạn
            $otp = generateOTP(6);
            $otpExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Thêm người dùng vào cơ sở dữ liệu với role mặc định là 'User' và isActive = 0
            $sql = "INSERT INTO Users (userName, phoneNumber, email, password, role, otp, otpExpiresAt, isActive) 
                    VALUES (:userName, :phoneNumber, :email, :password, 'User', :otp, :otpExpiresAt, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':otp', $otp);
            $stmt->bindParam(':otpExpiresAt', $otpExpiresAt);
            $stmt->execute();

            // Lưu email vào session
            $_SESSION['email'] = $email;

            // Gửi email OTP
            $subject = "Verify Your Account";
            $body = "Hello $userName,\n\nYour OTP for account verification is: $otp\n\nThis OTP will expire in 15 minutes.\n\nThank you!";
            if (sendMail($email, $subject, $body)) {
                header('Content-Type: application/json');
                echo json_encode(["message" => "User registered successfully. OTP sent to email.", "status" => "success"]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(["message" => "User registered but failed to send OTP email.", "status" => "error"]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Invalid input data", "status" => "error"]);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(["message" => "Error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid request method", "status" => "error"]);
}

// Hàm kiểm tra độ mạnh của mật khẩu
function isPasswordStrong($password)
{
    return strlen($password) >= 6
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password)
        && preg_match('/[\W_]/', $password);
}

// Hàm tạo OTP
function generateOTP($length = 6)
{
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}
