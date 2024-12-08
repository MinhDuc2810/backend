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
        // Kiểm tra xem userId có được gửi qua query string hay không
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $userId = $_GET['id'];

            // Truy vấn lấy membership theo userId
            $sql = "SELECT id, payment_method,  price, transaction_date
                    FROM transactions   
                    WHERE user_id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                // Trả về danh sách membership dưới dạng JSON
                echo json_encode($result);
            } else {
                // Không có dữ liệu cho userId này
                echo json_encode([]);
            }
        } else {
            // Không có userId trong yêu cầu
            http_response_code(400); // Bad Request
            echo json_encode([
                "status" => "error",
                "message" => "Missing or invalid userId parameter."
            ]);
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
