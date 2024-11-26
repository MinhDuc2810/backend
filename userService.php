<?php
include './connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Check request method
$method = $_SERVER['REQUEST_METHOD'];


// Handle POST request (for signIn and register actions)
if ($method === 'POST') {
    // Decode JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if 'action' is set in the decoded data
    if (!isset($data['action'])) {
        echo json_encode(["status" => "error", "message" => "Action not specified"], JSON_PRETTY_PRINT);
        exit;
    }

    $action = $data['action'];

    // Sign In Action (Đăng nhập)
if ($action === 'login') {
    $email = $data['email'] ?? null;
    //$password = $data['password'] ?? null;
    
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email or password is missing"], JSON_PRETTY_PRINT);
        exit;
    }

    // Truy vấn người dùng theo email
    $sql = "SELECT * FROM users WHERE email = :email";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':email', $email, PDO::PARAM_STR);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
        $storedPassword = $user['password']; // Mật khẩu đã băm từ CSDL

        // So sánh mật khẩu đã băm với mật khẩu người dùng nhập vào
        if (password_verify($password, $storedPassword)) {
            session_start();
            $_SESSION['account'] = json_encode($user);
            echo json_encode(["status" => "success", "data" => $user], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password"], JSON_PRETTY_PRINT);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"], JSON_PRETTY_PRINT);
    }
    exit;
}

// Register Action (Đăng ký)
if ($action === 'register') {
    $errors = [];
    $requiredFields = ['name', 'email', 'ngaysinh', 'phonenumber', 'password'];

    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[] = "Field '{$field}' is missing or empty.";
        }
    }

    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'messages' => $errors], JSON_PRETTY_PRINT);
        exit;
    }

    $name = $data['name'];
    $email = $data['email'];
    $ngaysinh = $data['ngaysinh'];
    $phonenumber = $data['phonenumber'];
    $password = $data['password'];

    try {
        // Kiểm tra email đã tồn tại chưa
        $query = 'SELECT * FROM users WHERE email = :email';
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists'], JSON_PRETTY_PRINT);
            exit;
        }

        // Kiểm tra số điện thoại đã tồn tại chưa
        $query = 'SELECT * FROM users WHERE phonenumber = :phonenumber';
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':phonenumber', $phonenumber);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Phone number already exists'], JSON_PRETTY_PRINT);
            exit;
        }

        // Băm mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Thêm người dùng mới vào cơ sở dữ liệu
        $query = 'INSERT INTO users (name, email, ngaysinh, phonenumber, password) 
                  VALUES (:name, :email, :ngaysinh, :phonenumber, :password)';
        $stmt = $connection->prepare($query);

        // Bind các giá trị vào các tham số
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':ngaysinh', $ngaysinh);
        $stmt->bindParam(':phonenumber', $phonenumber);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful'], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed'], JSON_PRETTY_PRINT);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
    }
    exit;
}

// Invalid action for POST (Thao tác không hợp lệ)
echo json_encode(["status" => "error", "message" => "Invalid action for POST method"], JSON_PRETTY_PRINT);
exit;
}

// Invalid request method
echo json_encode(["status" => "error", "message" => "Invalid request method"], JSON_PRETTY_PRINT);
exit;

?>
