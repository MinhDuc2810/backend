<?php
require_once('../connect.php');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Thông tin kết nối cơ sở dữ liệu
try {
    
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Thiết lập chế độ báo lỗi của PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Truy vấn lấy dữ liệu sản phẩm
    $sql = "SELECT * FROM users where isActive = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Mảng chứa dữ liệu trả về
    $userinfos = array();

    // Lấy tất cả dữ liệu
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Tạo cấu trúc dữ liệu cho mỗi sản phẩm
        $userinfo = array(
            "id" => $row['id'],
            "userName" => $row['userName'],
            "email" => $row['email'],
            "phoneNumber" => $row['phoneNumber'],

            // Tạo URL hoàn chỉnh cho ảnh
            "avatar" => "http://192.168.1.19:80/api/backend/userimage/" . $row['avatar'],
            "role" => $row['role'],
            
        );
        $userinfos[] = $userinfo;
    }

    // Đóng kết nối
    $conn = null;

    // Trả về dữ liệu dưới dạng JSON
    header('Content-Type: application/json');
    echo json_encode($userinfos);
} catch (PDOException $e) {
    // Xử lý lỗi nếu kết nối gặp vấn đề
    echo "Connection failed: " . $e->getMessage();
}
