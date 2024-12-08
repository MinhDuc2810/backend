<?php
require_once('../connect.php');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Kiểm tra dữ liệu đầu vào từ multipart/form-data
        if (isset($_POST['userName'], $_POST['email'], $_POST['phoneNumber'], $_POST['password'], $_POST['role']) && isset($_FILES['image'])) {
            $userName = $_POST['userName'];
            $email = $_POST['email'];
            $phoneNumber = $_POST['phoneNumber'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $image = $_FILES['image'];

            // Kiểm tra email hoặc số điện thoại đã tồn tại
            $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email OR phoneNumber = :phoneNumber");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                header('Content-Type: application/json');
                echo json_encode(["message" => "Email or phone number already exists", "status" => "error"]);
                exit;
            }

            // Kiểm tra mật khẩu mạnh
            if (!isPasswordStrong($password)) {
                header('Content-Type: application/json');
                echo json_encode([
                    "message" => "Password is too weak. It must contain at least 6 characters, including uppercase, lowercase, digits, and special characters.",
                    "status" => "error"
                ]);
                exit;
            }

            // Lưu ảnh vào thư mục
            $imagePath = saveImage($image, $userName);

            if (!$imagePath) {
                header('Content-Type: application/json');
                echo json_encode(["message" => "Failed to save image", "status" => "error"]);
                exit;
            }

            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Thêm người dùng vào cơ sở dữ liệu
            $sql = "INSERT INTO Users (userName, phoneNumber, email, password, role, avatar, isActive) VALUES (:userName, :phoneNumber, :email, :password, :role, :avatar, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':avatar', $imagePath);
            $stmt->execute();

            header('Content-Type: application/json');
            echo json_encode(["message" => "User added successfully", "status" => "success"]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Invalid input data", "status" => "error"]);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(["message" => "Error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid request method", "status" => "error"]);
}

// Kiểm tra độ mạnh mật khẩu
function isPasswordStrong($password)
{
    return strlen($password) >= 6
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password)
        && preg_match('/[\W_]/', $password);
}

// Lưu ảnh vào thư mục
function saveImage($file, $userName)
{
    if ($file['error'] === UPLOAD_ERR_OK) {
        $directory = '../userimage/';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $userName . "_" . time() . "." . $fileExtension; // Đặt tên file dựa trên userName và timestamp
        $filePath = $directory . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $filePath; // Trả về đường dẫn file đã lưu
        }
    }
    return false; // Lỗi trong quá trình upload
}
?>
