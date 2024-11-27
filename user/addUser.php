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
        if (isset($data['userName'], $data['phoneNumber'], $data['email'], $data['password'], $data['role'])) {
            $userName = $data['userName'];
            $phoneNumber = $data['phoneNumber'];
            $email = $data['email'];
            $password = $data['password'];
            $role = $data['role'];

            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Câu lệnh SQL để thêm người dùng
            $sql = "INSERT INTO Users (userName, phoneNumber, email, password, role)
                    VALUES (:userName, :phoneNumber, :email, :password, :role)";
            $stmt = $conn->prepare($sql);

            // Gán giá trị tham số
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);

            // Thực thi câu lệnh
            $stmt->execute();

            // Trả về phản hồi thành công
            header('Content-Type: application/json');
            echo json_encode(array("message" => "User added successfully", "status" => "success"));
        } else {
            // Trả về lỗi nếu thiếu dữ liệu
            header('Content-Type: application/json');
            echo json_encode(array("message" => "Invalid input data", "status" => "error"));
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
