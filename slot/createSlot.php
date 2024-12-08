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
        if (!isset($data['startTime']) || !isset($data['endTime']) || !isset($data['duration'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields (startTime, endTime, or duration)."
            ]);
            exit();
        }

        $startTime = strtotime($data['startTime']); // Giờ bắt đầu
        $endTime = strtotime($data['endTime']);     // Giờ kết thúc
        $duration = intval($data['duration']);      // Duration in minutes

        if ($startTime >= $endTime || $duration <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid startTime, endTime, or duration."
            ]);
            exit();
        }

        // Chuẩn bị dữ liệu slots
        $slots = [];
        while ($startTime + $duration * 60 <= $endTime) {
            $slotStart = date("H:i:s", $startTime);
            $slotEnd = date("H:i:s", $startTime + $duration * 60);
            $slots[] = [
                'startTime' => $slotStart,
                'endTime' => $slotEnd
            ];
            $startTime += $duration * 60;
        }

        // Chèn slots vào cơ sở dữ liệu
        $sql = "INSERT INTO pt_slots (startTime, endTime, createdAt, updatedAt) VALUES (:startTime, :endTime, NOW(), NOW())";
        $stmt = $conn->prepare($sql);

        foreach ($slots as $slot) {
            $stmt->bindParam(':startTime', $slot['startTime'], PDO::PARAM_STR);
            $stmt->bindParam(':endTime', $slot['endTime'], PDO::PARAM_STR);
            $stmt->execute();
        }

        echo json_encode([
            "status" => "success",
            "message" => "Slots created successfully.",
            "total_slots" => count($slots),
            "slots" => $slots
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
