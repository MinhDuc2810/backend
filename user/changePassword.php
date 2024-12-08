<?php
require_once('../connect.php');
// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy email từ query string
        if (isset($_GET['email'])) {
            $email = $_GET['email'];

            // Kiểm tra định dạng email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Content-Type: application/json');
                echo json_encode(array(
                    "message" => "Invalid email format.",
                    "status" => "error"
                ));
                exit;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(array(
                "message" => "Email is required in query string.",
                "status" => "error"
            ));
            exit;
        }

        // Lấy dữ liệu từ body của yêu cầu
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Kiểm tra dữ liệu đầu vào
        if (isset($data['currentPassword'], $data['newPassword'])) {
            $currentPassword = $data['currentPassword'];
            $newPassword = $data['newPassword'];

            // Kiểm tra độ mạnh của mật khẩu mới
            if (!isPasswordStrong($newPassword)) {
                header('Content-Type: application/json');
                echo json_encode(array(
                    "message" => "New password is too weak. It must contain at least 6 characters, including uppercase, lowercase, digits, and special characters.",
                    "status" => "error"
                ));
                exit;
            }

            // Truy vấn tìm người dùng với email
            $sql = "SELECT id, password FROM Users WHERE email = :email AND isActive = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Xác thực mật khẩu hiện tại
                if (password_verify($currentPassword, $user['password'])) {
                    // Mã hóa mật khẩu mới
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Cập nhật mật khẩu mới vào cơ sở dữ liệu
                    $updateSql = "UPDATE Users SET password = :newPassword WHERE id = :id";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bindParam(':newPassword', $hashedNewPassword);
                    $updateStmt->bindParam(':id', $user['id']);
                    $updateStmt->execute();

                    // Trả về phản hồi thành công
                    header('Content-Type: application/json');
                    echo json_encode(array("message" => "Password updated successfully", "status" => "success"));
                } else {
                    // Mật khẩu hiện tại không khớp
                    header('Content-Type: application/json');
                    echo json_encode(array("message" => "Current password is incorrect", "status" => "error"));
                }
            } else {
                // Người dùng không tồn tại
                header('Content-Type: application/json');
                echo json_encode(array("message" => "User not found", "status" => "error"));
            }
        } else {
            // Trả về lỗi nếu thiếu dữ liệu
            header('Content-Type: application/json');
            echo json_encode(array("message" => "currentPassword and newPassword are required in body.", "status" => "error"));
        }
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode(array("message" => "Error: " . $e->getMessage(), "status" => "error"));
    }
} else {
    // Nếu không phải phương thức PUT, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode(array("message" => "Invalid request method", "status" => "error"));
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
