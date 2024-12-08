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

    // Kiểm tra phương thức POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Nhận dữ liệu từ POST request
        $data = json_decode(file_get_contents("php://input"), true);

        // Kiểm tra dữ liệu nhận được
        if (!isset($data['date']) || !isset($data['pt_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields (date or pt_id)."
            ]);
            exit();
        }

        $date = $data['date']; // Ngày được chọn
        $ptId = $data['pt_id']; // ID của PT

        // Lấy tất cả các slot khả dụng
        $sqlSlots = "SELECT id, startTime, endTime FROM pt_slots";
        $stmtSlots = $conn->prepare($sqlSlots);
        $stmtSlots->execute();
        $allSlots = $stmtSlots->fetchAll(PDO::FETCH_ASSOC);

        // Lấy các slot đã được đặt của PT trong ngày
        $sqlBookedSlots = "
            SELECT slotId 
            FROM pt_requests 
            WHERE ptId = :pt_id AND date = :date AND status = 'approved'
        ";
        $stmtBookedSlots = $conn->prepare($sqlBookedSlots);
        $stmtBookedSlots->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtBookedSlots->bindParam(':date', $date, PDO::PARAM_STR);
        $stmtBookedSlots->execute();
        $bookedSlots = $stmtBookedSlots->fetchAll(PDO::FETCH_COLUMN);

        // Lọc ra các slot chưa được đặt
        $availableSlots = array_filter($allSlots, function ($slot) use ($bookedSlots) {
            return !in_array($slot['id'], $bookedSlots);
        });

        // Định dạng kết quả
        $resultSlots = array_map(function ($slot) {
            return [
                'slot_id' => $slot['id'],
                'startTime' => $slot['startTime'],
                'endTime' => $slot['endTime']
            ];
        }, $availableSlots);

        // Trả về danh sách các slot khả dụng
        echo json_encode([
            "status" => "success",
            "date" => $date,
            "available_slots" => $resultSlots
        ]);
    } else {
        // Sai phương thức, chỉ cho phép POST
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only POST is allowed."
        ]);
    }
} catch (PDOException $e) {
    // Lỗi kết nối hoặc truy vấn
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
