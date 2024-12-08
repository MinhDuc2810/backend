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
        $ptId = isset($_GET['pt_id']) ? intval($_GET['pt_id']) : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        // Kiểm tra thông tin bắt buộc
        if (!$ptId || !$status) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields (pt_id or status)."
            ]);
            exit();
        }

        // Kiểm tra xem pt_id có phải là PT hay không
        $sqlCheckPT = "SELECT id FROM users WHERE id = :pt_id AND role = 'pt'";
        $stmtCheckPT = $conn->prepare($sqlCheckPT);
        $stmtCheckPT->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtCheckPT->execute();

        if ($stmtCheckPT->rowCount() === 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid pt_id. This user is not a PT."
            ]);
            exit();
        }

        // Truy vấn các ca dạy với điều kiện status
        $sql = "SELECT 
                    pr.id AS request_id,
                    pr.date,
                    pr.status,
                    u.id AS user_id,
                    u.userName,
                    u.email,
                    u.phoneNumber,
                    ps.startTime,
                    ps.endTime
                FROM pt_requests pr
                INNER JOIN users u ON pr.userId = u.id
                INNER JOIN pt_slots ps ON pr.slotId = ps.id
                WHERE pr.ptId = :pt_id
                  AND pr.status = :status
                ORDER BY pr.date ASC, ps.startTime ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();

        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn bị kết quả trả về
        $sessionDetails = [];
        foreach ($sessions as $session) {
            $sessionDetails[] = [
                "request_id" => $session['request_id'],
                "date" => $session['date'],
                "time_range" => $session['startTime'] . " - " . $session['endTime'],
                "status" => $session['status'],
                "user" => [
                    "user_id" => $session['user_id'],
                    "userName" => $session['userName'],
                    "email" => $session['email'],
                    "phoneNumber" => $session['phoneNumber']
                ]
            ];
        }

        echo json_encode([
            "status" => "success",
            "pt_id" => $ptId,
            "status_filter" => $status,
            "total_sessions" => count($sessionDetails),
            "sessions" => $sessionDetails
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
