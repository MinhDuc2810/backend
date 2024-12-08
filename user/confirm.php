<?php
require_once('../connect.php');
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
        // Lấy dữ liệu từ query string
        if (!isset($_GET['id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing transaction_id."
            ]);
            exit();
        }

        $transactionId = $_GET['id'];

        // Kiểm tra giao dịch có tồn tại không
        $sqlCheck = "SELECT t.id, t.user_id, t.package_id FROM transactions t WHERE t.id = :id AND t.status = 'pending'";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id', $transactionId, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() === 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Transaction not found or not in pending status."
            ]);
            exit();
        }

        // Lấy thông tin giao dịch
        $transaction = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $userId = $transaction['user_id'];
        $packageId = $transaction['package_id'];

        // Cập nhật trạng thái giao dịch thành 'completed'
        $sqlUpdate = "UPDATE transactions SET status = 'completed', updatedAt = NOW() WHERE id = :id";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id', $transactionId, PDO::PARAM_INT);
        $stmtUpdate->execute();

        // Kiểm tra cập nhật trạng thái giao dịch
        if ($stmtUpdate->rowCount() > 0) {
            // Xử lý thẻ tập cho người dùng sau khi cập nhật giao dịch

            // Lấy thông tin gói tập
            $stmtPackage = $conn->prepare("SELECT type, duration FROM packages WHERE id = :package_id");
            $stmtPackage->bindParam(':package_id', $packageId, PDO::PARAM_INT);
            $stmtPackage->execute();
            $package = $stmtPackage->fetch(PDO::FETCH_ASSOC);

            if (!$package) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Package not found."
                ]);
                exit();
            }

            // Lấy thẻ tập của người dùng
            $stmtCard = $conn->prepare("SELECT * FROM usermemberships WHERE userId = :user_id");
            $stmtCard->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtCard->execute();
            $card = $stmtCard->fetch(PDO::FETCH_ASSOC);

            $type = $package['type']; // Monthly hoặc Yearly
            $duration = $package['duration']; // Số tháng hoặc năm
            $now = new DateTime();

            if (!$card) {
                // Người dùng chưa có thẻ tập -> Tạo mới
                $membershipStart = $now;
                $membershipEnd = clone $now;

                if ($type === 'Monthly') {
                    $membershipEnd->modify("+$duration month");
                } elseif ($type === 'Yearly') {
                    $membershipEnd->modify("+$duration year");
                }

                // Thêm thẻ tập mới
                $stmtInsertCard = $conn->prepare("INSERT INTO usermemberships (userId, membershipStart, membershipEnd) VALUES (?, ?, ?)");
                $stmtInsertCard->execute([
                    $userId,
                    $membershipStart->format('Y-m-d H:i:s'),
                    $membershipEnd->format('Y-m-d H:i:s'),
                ]);
            } else {
                // Người dùng đã có thẻ tập -> Cập nhật thẻ tập
                $membershipEnd = new DateTime($card['membershipEnd']);

                if ($membershipEnd > $now) {
                    // Nếu thẻ tập đã hết hạn, cộng dồn thời gian
                    if ($type === 'Monthly') {
                        $membershipEnd->modify("+$duration month");
                    } elseif ($type === 'Yearly') {
                        $membershipEnd->modify("+$duration year");
                    }

                    $stmtUpdateCard = $conn->prepare("UPDATE usermemberships SET membershipEnd = ? WHERE userId = ?");
                    $stmtUpdateCard->execute([
                        $membershipEnd->format('Y-m-d H:i:s'),
                        $userId,
                    ]);
                } else {
                    // Nếu thẻ tập vẫn còn hiệu lực, tạo lại ngày bắt đầu và kết thúc
                    $membershipStart = $now;
                    $membershipEnd = clone $now;

                    if ($type === 'Monthly') {
                        $membershipEnd->modify("+$duration month");
                    } elseif ($type === 'Yearly') {
                        $membershipEnd->modify("+$duration year");
                    }

                    $stmtUpdateCard = $conn->prepare("UPDATE usermemberships SET membershipStart = ?, membershipEnd = ? WHERE userId = ?");
                    $stmtUpdateCard->execute([
                        $membershipStart->format('Y-m-d H:i:s'),
                        $membershipEnd->format('Y-m-d H:i:s'),
                        $userId,
                    ]);
                }
            }

            // Trả về thông báo thành công
            echo json_encode([
                "status" => "success",
                "message" => "Transaction status updated to completed and membership processed successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update transaction status."
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
?>
