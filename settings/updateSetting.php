<?php
require_once('../connect.php');

// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy id từ URL
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "The 'id' parameter is required in the URL.",
                "status" => "error"
            ]);
            exit();
        }

        $id = (int)$_GET['id'];

        // Lấy dữ liệu từ body của yêu cầu
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Kiểm tra dữ liệu đầu vào
        if (empty($data['key']) && empty($data['value']) && empty($data['description'])) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "At least one of 'key', 'value', or 'description' fields is required.",
                "status" => "error"
            ]);
            exit();
        }

        // Kiểm tra xem setting có tồn tại hay không
        $checkStmt = $conn->prepare("SELECT * FROM settings WHERE id = :id");
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();

        $existingSetting = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingSetting) {
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Setting with ID $id not found.",
                "status" => "error"
            ]);
            exit();
        }

        // Xây dựng câu lệnh SQL để cập nhật
        $sql = "UPDATE settings SET ";
        $updateFields = [];
        $params = [];

        if (!empty($data['key'])) {
            $updateFields[] = "`key` = :key";
            $params[':key'] = $data['key'];
        }
        if (!empty($data['value'])) {
            $updateFields[] = "`value` = :value";
            $params[':value'] = $data['value'];
        }
        if (isset($data['description'])) { // Cho phép description có giá trị null
            $updateFields[] = "`description` = :description";
            $params[':description'] = $data['description'];
        }

        $sql .= implode(", ", $updateFields);
        $sql .= " WHERE id = :id";
        $params[':id'] = $id;

        // Chuẩn bị và thực thi câu lệnh SQL
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Trả về phản hồi thành công
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Setting updated successfully.",
            "status" => "success",
            "data" => [
                "id" => $id,
                "key" => $data['key'] ?? $existingSetting['key'],
                "value" => $data['value'] ?? $existingSetting['value'],
                "description" => $data['description'] ?? $existingSetting['description']
            ]
        ]);
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Error: " . $e->getMessage(),
            "status" => "error"
        ]);
    }
} else {
    // Nếu không phải phương thức PUT, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode([
        "message" => "Invalid request method. Only PUT is allowed.",
        "status" => "error"
    ]);
}
