<?php
require_once('./connect.php');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Thông tin kết nối cơ sở dữ liệu
try {
    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Thiết lập chế độ báo lỗi của PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Truy vấn lấy dữ liệu sản phẩm
    $sql = "SELECT userid, name, email, ngaysinh, phonenumber, image FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Mảng chứa dữ liệu trả về
    $userinfos = array();

    // Lấy tất cả dữ liệu
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Tạo cấu trúc dữ liệu cho mỗi sản phẩm
        $userinfo = array(
            "userid" => $row['userid'],
            "name" => $row['name'],
            "email" => $row['email'],
            "ngaysinh" => $row['ngaysinh'],
            "phonenumber" => $row['phonenumber'],
            // Tạo URL hoàn chỉnh cho ảnh
            "image" => "http://172.19.201.39:80/api/gym/userimage/" . $row['image']
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
?>



