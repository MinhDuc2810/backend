<?php
require_once('../connect.php');
// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kết nối đến cơ sở dữ liệu MySQL sử dụng PDO
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Kiểm tra xem có dữ liệu được gửi từ client
        if (isset($_POST['userName'], $_POST['phoneNumber'], $_POST['email'], $_POST['password'], $_POST['role'])) {
            $userName = $_POST['userName'];
            $phoneNumber = $_POST['phoneNumber'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'];

            // Kiểm tra xem email hoặc số điện thoại đã tồn tại chưa
            $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email OR phoneNumber = :phoneNumber");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Nếu đã tồn tại email hoặc số điện thoại
                header('Content-Type: application/json');
                echo json_encode(array("message" => "Email or phone number already exists", "status" => "error"));
                exit;
            }

            // Kiểm tra mật khẩu mạnh
            if (!isPasswordStrong($password)) {
                header('Content-Type: application/json');
                echo json_encode(array("message" => "Password is too weak. It must contain at least 6 characters, including uppercase, lowercase, digits, and special characters.", "status" => "error"));
                exit;
            }

            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Kiểm tra xem có file ảnh hay không
            $avatar = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Lấy thông tin về file ảnh
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileSize = $_FILES['image']['size'];
                $fileType = $_FILES['image']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Kiểm tra kiểu file ảnh hợp lệ
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    // Tạo tên file duy nhất
                    $newFileName = uniqid() . '.' . $fileExtension;

                    // Đường dẫn lưu file ảnh
                    $uploadDir = "../userimage/";
                    $dest_path = $uploadDir . $newFileName;

                    // Di chuyển file ảnh từ thư mục tạm thời đến thư mục đích
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $avatar = $newFileName; // Lưu tên file ảnh vào biến avatar
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(array("message" => "Error uploading the image", "status" => "error"));
                        exit;
                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(array("message" => "Invalid image type. Only jpg, jpeg, png, gif are allowed.", "status" => "error"));
                    exit;
                }
            }

            // Câu lệnh SQL để thêm người dùng
            $sql = "INSERT INTO Users (userName, phoneNumber, email, password, role, avatar)
                    VALUES (:userName, :phoneNumber, :email, :password, :role, :avatar)";
            $stmt = $conn->prepare($sql);

            // Gán giá trị tham số
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':avatar', $avatar); // Gửi tên file ảnh vào cột avatar

            // Thực thi câu lệnh
            $stmt->execute();

            // Trả về phản hồi thành công
            header('Content-Type: application/json');
            echo json_encode(array("message" => "User added successfully", "status" => "success"));
        } else {
            // Trả về lỗi nếu thiếu dữ liệu
            header('Content-Type: application/json');
            echo json_encode(array("message" => "Invalid input data", "status" => "error"));
        }
    } catch (PDOException $e) {
        // Trả về lỗi nếu kết nối hoặc câu lệnh SQL gặp vấn đề
        header('Content-Type: application/json');
        echo json_encode(array("message" => "Error: " . $e->getMessage(), "status" => "error"));
    }
} else {
    // Nếu không phải phương thức POST, trả về lỗi
    header('Content-Type: application/json');
    echo json_encode(array("message" => "Invalid request method", "status" => "error"));
}
// Hàm kiểm tra độ mạnh của mật khẩu
function isPasswordStrong($password) {
    // Kiểm tra mật khẩu có ít nhất 6 ký tự
    if (strlen($password) < 6) {
        return false;
    }

    // Kiểm tra có ít nhất một chữ cái hoa
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Kiểm tra có ít nhất một chữ cái thường
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }

    // Kiểm tra có ít nhất một chữ số
    if (!preg_match('/\d/', $password)) {
        return false;
    }

    // Kiểm tra có ít nhất một ký tự đặc biệt (chẳng hạn như @, #, $, %, ^, &)
    if (!preg_match('/[\W_]/', $password)) {
        return false;
    }

    return true;
}
