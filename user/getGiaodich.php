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
        // Truy vấn lấy thông tin giao dịch, tên người dùng và thông tin gói tập
        $sql = "
            SELECT 
                u.userName, 
                t.user_id, 
                t.id,
                t.package_id, 
                p.id AS package_id, 
                p.name AS package_name,
                p.duration,
                p.type,
                p.price
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            JOIN packages p ON t.package_id = p.id
            WHERE t.status = 'pending'
            ORDER BY t.createdAt DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Lấy kết quả
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về dữ liệu dưới dạng JSON
        if ($result) {
            echo json_encode($result);
        } else {
            // Không có dữ liệu trong bảng transactions
            echo json_encode([]);
        }
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
?>
