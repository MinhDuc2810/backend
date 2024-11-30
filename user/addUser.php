<?php
require_once('../connect.php');
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
        if (isset($data['userName'], $data['phoneNumber'], $data['email'], $data['password'], $data['role'])) {
            $userName = $data['userName'];
            $phoneNumber = $data['phoneNumber'];
            $email = $data['email'];
            $password = $data['password'];
            $role = $data['role'];

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

            // Thêm người dùng vào cơ sở dữ liệu
            $sql = "INSERT INTO Users (userName, phoneNumber, email, password, role) VALUES (:userName, :phoneNumber, :email, :password, :role)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->execute();

            header('Content-Type: application/json');
            echo json_encode(["message" => "User added successfully", "status" => "success"]);
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

function isPasswordStrong($password)
{
    return strlen($password) >= 6
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password)
        && preg_match('/[\W_]/', $password);
}
