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

    // Kiểm tra phương thức PUT
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Nhận dữ liệu từ PUT request
        $data = json_decode(file_get_contents("php://input"), true);

        // Kiểm tra dữ liệu nhận được
        if (!isset($data['request_id']) || !isset($data['action']) || !isset($data['pt_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields (request_id, action, or pt_id)."
            ]);
            exit();
        }

        $requestId = $data['request_id'];
        $action = $data['action']; // approved hoặc rejected
        $ptId = $data['pt_id'];

        // Kiểm tra hành động hợp lệ
        if (!in_array($action, ['approved', 'rejected'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid action. Allowed values are 'approved' or 'rejected'."
            ]);
            exit();
        }

        // Kiểm tra yêu cầu tồn tại và thuộc về PT
        $sqlCheckRequest = "SELECT * FROM pt_requests WHERE id = :request_id AND ptId = :pt_id";
        $stmtCheckRequest = $conn->prepare($sqlCheckRequest);
        $stmtCheckRequest->bindParam(':request_id', $requestId, PDO::PARAM_INT);
        $stmtCheckRequest->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtCheckRequest->execute();

        $request = $stmtCheckRequest->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request_id or pt_id. Request not found."
            ]);
            exit();
        }

        // Cập nhật trạng thái yêu cầu
        $sqlUpdateRequest = "UPDATE pt_requests SET status = :action, updatedAt = NOW() WHERE id = :request_id";
        $stmtUpdateRequest = $conn->prepare($sqlUpdateRequest);
        $stmtUpdateRequest->bindParam(':action', $action, PDO::PARAM_STR);
        $stmtUpdateRequest->bindParam(':request_id', $requestId, PDO::PARAM_INT);

        if ($stmtUpdateRequest->execute()) {
            // Nếu hành động là approved, trừ 1 buổi tập của người dùng
            if ($action === 'approved') {
                $userId = $request['userId'];

                // Kiểm tra và cập nhật số buổi tập trong user_pt_relations
                $sqlUpdateSessions = "UPDATE user_pt_relations 
                                      SET sessionsLeft = sessionsLeft - 1 
                                      WHERE userId = :user_id AND ptId = :pt_id AND sessionsLeft > 0";
                $stmtUpdateSessions = $conn->prepare($sqlUpdateSessions);
                $stmtUpdateSessions->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmtUpdateSessions->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
                $stmtUpdateSessions->execute();

                // Kiểm tra xem số buổi có được trừ không
                if ($stmtUpdateSessions->rowCount() === 0) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "No available sessions to deduct."
                    ]);
                    exit();
                }
            }

            echo json_encode([
                "status" => "success",
                "message" => "Request updated successfully",
                "action" => $action
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update request."
            ]);
        }
    } else {
        // Sai phương thức, chỉ cho phép PUT
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only PUT is allowed."
        ]);
    }
} catch (PDOException $e) {
    // Lỗi kết nối hoặc truy vấn
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
