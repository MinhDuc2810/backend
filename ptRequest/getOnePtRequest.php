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
        $requestId = isset($_GET['request_id']) ? intval($_GET['request_id']) : null;

        // Kiểm tra thông tin bắt buộc
        if (!$requestId) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required field (request_id)."
            ]);
            exit();
        }

        // Truy vấn chi tiết yêu cầu
        $sql = "SELECT 
                    pr.id AS request_id,
                    pr.date,
                    pr.status,
                    u.id AS user_id,
                    u.userName AS user_name,
                    u.email AS user_email,
                    u.phoneNumber AS user_phone,
                    pt.id AS pt_id,
                    pt.userName AS pt_name,
                    pt.email AS pt_email,
                    pt.phoneNumber AS pt_phone,
                    ps.startTime AS start_time,
                    ps.endTime AS end_time
                FROM pt_requests pr
                INNER JOIN users u ON pr.userId = u.id
                INNER JOIN users pt ON pr.ptId = pt.id
                INNER JOIN pt_slots ps ON pr.slotId = ps.id
                WHERE pr.id = :request_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':request_id', $requestId, PDO::PARAM_INT);
        $stmt->execute();

        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra nếu yêu cầu không tồn tại
        if (!$request) {
            echo json_encode([
                "status" => "error",
                "message" => "Request not found."
            ]);
            exit();
        }

        // Chuẩn bị kết quả trả về
        $result = [
            "request_id" => $request['request_id'],
            "date" => $request['date'],
            "start_time" => $request['start_time'],
            "end_time" => $request['end_time'],
            "status" => $request['status'],
            "user_info" => [
                "user_id" => $request['user_id'],
                "user_name" => $request['user_name'],
                "user_email" => $request['user_email'],
                "user_phone" => $request['user_phone']
            ],
            "pt_info" => [
                "pt_id" => $request['pt_id'],
                "pt_name" => $request['pt_name'],
                "pt_email" => $request['pt_email'],
                "pt_phone" => $request['pt_phone']
            ]
        ];

        echo json_encode([
            "status" => "success",
            "request_details" => $result
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
