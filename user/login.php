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
        if (isset($data['email'], $data['password'])) {
            $email = $data['email'];
            $password = $data['password'];

            // Truy vấn tìm người dùng với email
            $sql = "SELECT id, userName, email, password, role FROM Users WHERE email = :email AND isDeleted = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Xác thực mật khẩu
                if (password_verify($password, $user['password'])) {
                    // Mật khẩu đúng, trả về thông tin người dùng
                    header('Content-Type: application/json');
                    echo json_encode(array(
                        "message" => "Login successful",
                        "status" => "success",
                        "user" => array(
                            "id" => $user['id'],
                            "userName" => $user['userName'],
                            "email" => $user['email'],
                            "role" => $user['role']
                        )
                    ));
                } else {
                    // Mật khẩu không khớp
                    header('Content-Type: application/json');
                    echo json_encode(array("message" => "Invalid email or password", "status" => "error"));
                }
            } else {
                // Người dùng không tồn tại
                header('Content-Type: application/json');
                echo json_encode(array("message" => "Invalid email or password", "status" => "error"));
            }
        } else {
            // Trả về lỗi nếu thiếu dữ liệu
            header('Content-Type: application/json');
            echo json_encode(array("message" => "Email and password are required", "status" => "error"));
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
