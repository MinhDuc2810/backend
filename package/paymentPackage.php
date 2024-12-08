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
        if (!isset($data['user_id']) || !isset($data['payment_method']) || !isset($data['type'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields (user_id, payment_method, or type)."
            ]);
            exit();
        }

        $userId = $data['user_id'];
        $paymentMethod = $data['payment_method'];
        $type = $data['type']; // Loại giao dịch ('pt' hoặc 'package')
        $sessions = isset($data['sessions']) ? intval($data['sessions']) : null; // Số buổi thuê (chỉ cho PT)

        $packageId = null;
        $ptId = null;
        $price = null;

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

        if ($type === 'pt') {
            // Kiểm tra và lấy thông tin PT
            if (!isset($data['pt_id'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing required field (pt_id) for PT transaction."
                ]);
                exit();
            }

            $ptId = $data['pt_id'];

            $sqlPT = "SELECT id FROM users WHERE id = :pt_id AND role = 'pt'";
            $stmtPT = $conn->prepare($sqlPT);
            $stmtPT->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
            $stmtPT->execute();

            if ($stmtPT->rowCount() === 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid pt_id. PT not found."
                ]);
                exit();
            }

            // Kiểm tra số buổi thuê hợp lệ
            if (!$sessions || $sessions <= 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid session count for PT transaction."
                ]);
                exit();
            }

            $price = $sessions * 100000; // Giả định mỗi buổi thuê PT giá 100000

            // Kiểm tra xem user_pt_relations đã tồn tại chưa
            $sqlRelation = "SELECT * FROM user_pt_relations WHERE userId = :user_id AND ptId = :pt_id";
            $stmtRelation = $conn->prepare($sqlRelation);
            $stmtRelation->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtRelation->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
            $stmtRelation->execute();

            if ($stmtRelation->rowCount() > 0) {
                // Nếu đã tồn tại, cập nhật số buổi tập
                $sqlUpdateRelation = "UPDATE user_pt_relations 
                                      SET sessionsLeft = sessionsLeft + :sessions, updatedAt = NOW() 
                                      WHERE userId = :user_id AND ptId = :pt_id";
                $stmtUpdateRelation = $conn->prepare($sqlUpdateRelation);
                $stmtUpdateRelation->bindParam(':sessions', $sessions, PDO::PARAM_INT);
                $stmtUpdateRelation->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmtUpdateRelation->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
                $stmtUpdateRelation->execute();
            } else {
                // Nếu chưa tồn tại, thêm bản ghi mới
                $sqlInsertRelation = "INSERT INTO user_pt_relations (userId, ptId, sessionsLeft, createdAt, updatedAt) 
                                      VALUES (:user_id, :pt_id, :sessions, NOW(), NOW())";
                $stmtInsertRelation = $conn->prepare($sqlInsertRelation);
                $stmtInsertRelation->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmtInsertRelation->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
                $stmtInsertRelation->bindParam(':sessions', $sessions, PDO::PARAM_INT);
                $stmtInsertRelation->execute();
            }
        } elseif ($type === 'package') {
            // Kiểm tra và lấy thông tin gói tập
            if (!isset($data['package_id'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing required field (package_id) for Package transaction."
                ]);
                exit();
            }

            $packageId = $data['package_id'];

            $sqlPackage = "SELECT id, price FROM packages WHERE id = :package_id";
            $stmtPackage = $conn->prepare($sqlPackage);
            $stmtPackage->bindParam(':package_id', $packageId, PDO::PARAM_INT);
            $stmtPackage->execute();

            $package = $stmtPackage->fetch(PDO::FETCH_ASSOC);

            if (!$package) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid package_id. Package not found."
                ]);
                exit();
            }

            $price = $package['price'];
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid transaction type."
            ]);
            exit();
        }

        // Tạo một giao dịch mới
        $sqlTransaction = "INSERT INTO transactions (user_id, package_id, pt_id, payment_method, price, status, type, sessions, createdAt) 
                           VALUES (:user_id, :package_id, :pt_id, :payment_method, :price, 'pending', :type, :sessions, NOW())";
        $stmtTransaction = $conn->prepare($sqlTransaction);
        $stmtTransaction->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtTransaction->bindParam(':package_id', $packageId, PDO::PARAM_INT);
        $stmtTransaction->bindParam(':pt_id', $ptId, PDO::PARAM_INT);
        $stmtTransaction->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
        $stmtTransaction->bindParam(':price', $price, PDO::PARAM_STR);
        $stmtTransaction->bindParam(':type', $type, PDO::PARAM_STR);
        $stmtTransaction->bindValue(':sessions', $type === 'pt' ? $sessions : null, PDO::PARAM_INT);

        if ($stmtTransaction->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Transaction created successfully",
                "transaction_id" => $conn->lastInsertId()
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to create transaction."
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
