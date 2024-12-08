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
        if (!isset($data['user_id']) || !isset($data['package_id']) || !isset($data['payment_method'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields (user_id, package_id, or payment_method)."
            ]);
            exit();
        }

        $userId = $data['user_id'];
        $packageId = $data['package_id'];
        $paymentMethod = $data['payment_method'];

        // Kiểm tra nếu payment_method là "cash"
        if ($paymentMethod === 'Cash') {
            // Lấy thông tin giá từ bảng packages
            $sqlPackage = "SELECT price FROM packages WHERE id = :package_id";
            $stmtPackage = $conn->prepare($sqlPackage);
            $stmtPackage->bindParam(':package_id', $packageId, PDO::PARAM_INT);
            $stmtPackage->execute();

            $package = $stmtPackage->fetch(PDO::FETCH_ASSOC);

            // Kiểm tra nếu package_id hợp lệ
            if (!$package) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid package_id. Package not found."
                ]);
                exit();
            }

            $price = $package['price'];

            // Tạo một giao dịch mới với trạng thái 'pending'
            $sqlTransaction = "INSERT INTO transactions (user_id, package_id, payment_method, price, status, createdAt) 
                               VALUES (:user_id, :package_id, :payment_method, :price, 'pending', NOW())";
            $stmtTransaction = $conn->prepare($sqlTransaction);
            $stmtTransaction->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtTransaction->bindParam(':package_id', $packageId, PDO::PARAM_INT);
            $stmtTransaction->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
            $stmtTransaction->bindParam(':price', $price, PDO::PARAM_STR);

            // Thực thi câu lệnh
            if ($stmtTransaction->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Gửi yêu cầu thành công",
                    "transaction_id" => $conn->lastInsertId()
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to create transaction."
                ]);
            }
        } else {
            // Nếu phương thức thanh toán không phải cash
            echo json_encode([
                "status" => "error",
                "message" => "Invalid payment method. Only 'cash' is accepted for this operation."
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
?>
