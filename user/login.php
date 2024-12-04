<?php
require_once('../connect.php');
require_once('./jwt.php'); // Thêm file xử lý JWT
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
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
            $sql = "SELECT * FROM Users WHERE email = :email AND isDeleted = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Xác thực mật khẩu
                if (password_verify($password, $user['password'])) {
                    // Mật khẩu đúng, tạo JWT token
                    $payload = [
                        "id" => $user['id'],
                        "userName" => $user['userName'],
                        "email" => $user['email'],
                        "phoneNumber" => $user['phoneNumber'],
                        "avatar" => $user['avatar'],
                        "role" => $user['role'],
                        "exp" => time() + (60 * 60 * 24) // Token hết hạn sau 24 giờ
                    ];
                    $jwt = generateJWT($payload);

                    // Trả về token và thông tin người dùng
                    header('Content-Type: application/json');
                    echo json_encode([
                        "message" => "Login successful",
                        "status" => "success",
                        "token" => $jwt,
                        "user" => [
                            "id" => $user['id'],
                            "userName" => $user['userName'],
                            "email" => $user['email'],
                            "phoneNumber" => $user['phoneNumber'],
                            "avatar" => "http://192.168.6.193:80/api/backend/userimage/" . $user['avatar'],
                            "role" => $user['role']
                        ]
                    ]);
                } else {
                    // Mật khẩu không khớp
                    header('Content-Type: application/json');
                    echo json_encode([
                        "message" => "Invalid email or password",
                        "status" => "error"
                    ]);
                }
            } else {
                // Người dùng không tồn tại
                header('Content-Type: application/json');
                echo json_encode([
                    "message" => "Invalid email or password",
                    "status" => "error"
                ]);
            }
        } else {
            // Trả về lỗi nếu thiếu dữ liệu
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Email and password are required",
                "status" => "error"
            ]);
        }
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Error: " . $e->getMessage(),
            "status" => "error"
        ]);
    }
} else {
    // Nếu không phải phương thức POST, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode([
        "message" => "Invalid request method",
        "status" => "error"
    ]);
}
