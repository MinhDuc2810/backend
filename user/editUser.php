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

        // Lấy dữ liệu từ body của yêu cầu
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Kiểm tra dữ liệu đầu vào
        if (isset($data['id']) && !empty($data['id'])) {
            $id = $data['id'];
            $fieldsToUpdate = [];

            // Kiểm tra và chuẩn bị các trường để cập nhật
            if (isset($data['userName'])) {
                $fieldsToUpdate['userName'] = $data['userName'];
            }
            if (isset($data['phoneNumber'])) {
                $fieldsToUpdate['phoneNumber'] = $data['phoneNumber'];
            }
            if (isset($data['email'])) {
                $fieldsToUpdate['email'] = $data['email'];
            }
            if (isset($data['password'])) {
                $fieldsToUpdate['password'] = password_hash($data['password'], PASSWORD_DEFAULT); // Mã hóa mật khẩu
            }
            if (isset($data['role'])) {
                $fieldsToUpdate['role'] = $data['role'];
            }

            // Nếu không có trường nào để cập nhật
            if (empty($fieldsToUpdate)) {
                header('Content-Type: application/json');
                echo json_encode(array("message" => "No fields to update", "status" => "error"));
                exit;
            }

            // Xây dựng câu lệnh SQL động
            $updateFields = [];
            foreach ($fieldsToUpdate as $key => $value) {
                $updateFields[] = "$key = :$key";
            }
            $sql = "UPDATE Users SET " . implode(", ", $updateFields) . " WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // Gán giá trị tham số
            foreach ($fieldsToUpdate as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            // Thực thi câu lệnh
            $stmt->execute();

            // Trả về phản hồi thành công
            header('Content-Type: application/json');
            echo json_encode(array("message" => "User updated successfully", "status" => "success"));
        } else {
            // Trả về lỗi nếu thiếu ID
            header('Content-Type: application/json');
            echo json_encode(array("message" => "User ID is required", "status" => "error"));
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
