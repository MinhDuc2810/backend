<?php
header('Content-Type: application/json');

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kiểm tra phương thức GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Truy vấn lấy id, membershipStart, và membershipEnd
        $sql = "SELECT usermemberships.id, usermemberships.membershipStart, usermemberships.membershipEnd, users.userName 
        FROM usermemberships 
        JOIN users ON usermemberships.userId = users.id";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            // Trả về danh sách membership dưới dạng JSON
            echo json_encode($result);
        } else {
            // Không có dữ liệu trong bảng membership
            echo json_encode([]);
        }
    } else {
        // Sai phương thức
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method. Only GET is allowed."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
