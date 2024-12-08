<?php
header('Content-Type: application/json');

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    // Tạo kết nối PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kiểm tra phương thức GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Nhận tham số từ URL
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

        // Kiểm tra thông tin bắt buộc
        if (!$userId) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required field (user_id)."
            ]);
            exit();
        }

        // Truy vấn lịch sử đặt lịch của người dùng
        $sql = "
            SELECT 
                pr.id AS request_id,
                pr.date,
                pr.status,
                pt.id AS pt_id,
                pt.userName AS pt_name,
                pt.email AS pt_email,
                pt.phoneNumber AS pt_phone,
                ps.startTime,
                ps.endTime
            FROM pt_requests pr
            INNER JOIN users pt ON pr.ptId = pt.id
            INNER JOIN pt_slots ps ON pr.slotId = ps.id
            WHERE pr.userId = :user_id
            ORDER BY pr.date DESC, ps.startTime DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn bị kết quả trả về
        $result = [];
        foreach ($requests as $request) {
            $result[] = [
                "request_id" => $request['request_id'],
                "date" => $request['date'],
                "start_time" => $request['startTime'],
                "end_time" => $request['endTime'],
                "status" => $request['status'],
                "pt_info" => [
                    "pt_id" => $request['pt_id'],
                    "pt_name" => $request['pt_name'],
                    "pt_email" => $request['pt_email'],
                    "pt_phone" => $request['pt_phone']
                ]
            ];
        }

        echo json_encode([
            "status" => "success",
            "user_id" => $userId,
            "total_requests" => count($result),
            "history" => $result
        ]);
    } else {
        // Sai phương thức, chỉ cho phép GET
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only GET is allowed."
        ]);
    }
} catch (PDOException $e) {
    // Lỗi kết nối hoặc truy vấn
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
