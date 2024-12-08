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
        if (isset($_GET['userId']) && !empty($_GET['userId'])) {
            $userId = $_GET['userId'];

            // Truy vấn lấy membership theo userId
            $sql = "SELECT usermemberships.id, usermemberships.membershipStart, usermemberships.membershipEnd, users.userName 
                    FROM usermemberships 
                    JOIN users ON usermemberships.userId = users.id 
                    WHERE usermemberships.userId = :userId";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
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
