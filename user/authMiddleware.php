<?php
require_once('./jwt.php'); // File chứa hàm generateJWT và verifyJWT

function authenticate()
{
    // Lấy JWT từ Authorization header
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Authorization header is missing."]);
        exit();
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader); // Bỏ prefix "Bearer"

    // Xác minh JWT
    if (!verifyJWT($token)) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid or expired token."]);
        exit();
    }

    // Giải mã token để lấy thông tin người dùng
    $decodedPayload = json_decode(base64_decode(explode('.', $token)[1]), true);

    // Thêm thông tin người dùng vào superglobal $_SERVER để sử dụng trong các file khác
    $_SERVER['user'] = $decodedPayload;
}
