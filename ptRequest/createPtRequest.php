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
        if (
            !isset($data['user_id']) ||
            !isset($data['pt_id']) ||
            !isset($data['date']) ||
            !isset($data['slot_id'])
        ) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields (user_id, pt_id, date, or slot_id)."
            ]);
            exit();
        }

        $userId = $data['user_id'];
        $ptId = $data['pt_id'];
        $date = $data['date'];
        $slotId = $data['slot_id'];

        // Kiểm tra xem user_id có tồn tại không
        $sqlCheckUser = "SELECT id FROM users WHERE id = :user_id";
        $stmtCheckUser = $conn->prepare($sqlCheckUser);
        $stmtCheckUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCheckUser->execute();

        if ($stmtCheckUser->rowCount() === 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid user_id. User not found."
            ]);
            exit();
        }

        // Kiểm tra xem pt_id có tồn tại và là PT hay không
        $sqlCheckPT = "SELECT id FROM users WHERE id = :pt_id AND role = 'pt'";
        $stmtCheckPT = $conn->prepare($sqlCheckPT);
        $stmtCheckPT->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtCheckPT->execute();

        if ($stmtCheckPT->rowCount() === 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid pt_id. PT not found or not a valid PT."
            ]);
            exit();
        }

        // Kiểm tra user_id có thể book PT này không (còn sessionsLeft > 0)
        $sqlCheckUserPTRelation = "
            SELECT sessionsLeft 
            FROM user_pt_relations 
            WHERE userId = :user_id AND ptId = :pt_id AND sessionsLeft > 0
        ";
        $stmtUserPTRelation = $conn->prepare($sqlCheckUserPTRelation);
        $stmtUserPTRelation->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtUserPTRelation->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtUserPTRelation->execute();

        $userPTRelation = $stmtUserPTRelation->fetch(PDO::FETCH_ASSOC);

        if (!$userPTRelation) {
            echo json_encode([
                "status" => "error",
                "message" => "You do not have any remaining sessions with this PT."
            ]);
            exit();
        }

        // Kiểm tra xem slot_id có hợp lệ không
        $sqlCheckSlot = "SELECT id FROM pt_slots WHERE id = :slot_id";
        $stmtCheckSlot = $conn->prepare($sqlCheckSlot);
        $stmtCheckSlot->bindParam(':slot_id', $slotId, PDO::PARAM_INT);
        $stmtCheckSlot->execute();

        if ($stmtCheckSlot->rowCount() === 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid slot_id. Slot not found."
            ]);
            exit();
        }

        // Kiểm tra trùng lặp lịch
        $sqlCheckConflict = "
            SELECT id 
            FROM pt_requests 
            WHERE ptId = :pt_id 
              AND date = :date 
              AND slotId = :slot_id 
              AND status IN ('approved', 'pending')
        ";
        $stmtCheckConflict = $conn->prepare($sqlCheckConflict);
        $stmtCheckConflict->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtCheckConflict->bindParam(':date', $date, PDO::PARAM_STR);
        $stmtCheckConflict->bindParam(':slot_id', $slotId, PDO::PARAM_INT);
        $stmtCheckConflict->execute();

        if ($stmtCheckConflict->rowCount() > 0) {
            echo json_encode([
                "status" => "error",
                "message" => "This PT is already booked for the selected slot."
            ]);
            exit();
        }

        // Thêm yêu cầu tập vào bảng pt_requests
        $sqlRequest = "INSERT INTO pt_requests 
                       (userId, ptId, date, slotId, status, createdAt) 
                       VALUES 
                       (:user_id, :pt_id, :date, :slot_id, 'pending', NOW())";
        $stmtRequest = $conn->prepare($sqlRequest);
        $stmtRequest->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtRequest->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtRequest->bindParam(':date', $date, PDO::PARAM_STR);
        $stmtRequest->bindParam(':slot_id', $slotId, PDO::PARAM_INT);

        if ($stmtRequest->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Request created successfully",
                "request_id" => $conn->lastInsertId()
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to create request."
            ]);
        }
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
