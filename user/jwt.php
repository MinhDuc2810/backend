<?php
// Secret key cho JWT
define('JWT_SECRET', 'ductaoancut');

// Hàm tạo JWT
function generateJWT($payload, $expiryInSeconds = 3600)
{
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

    // Thêm thời gian phát hành và hết hạn vào payload
    $payload['iat'] = time(); // Thời gian phát hành
    $payload['exp'] = time() + $expiryInSeconds; // Thời gian hết hạn

    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

    // Tạo chữ ký
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

// Hàm xác minh JWT
function verifyJWT($jwt)
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false;
    }

    [$base64UrlHeader, $base64UrlPayload, $signatureProvided] = $parts;

    // Giải mã payload
    $decodedPayload = json_decode(base64_decode($base64UrlPayload), true);

    // Kiểm tra thời gian hết hạn
    if (isset($decodedPayload['exp']) && time() > $decodedPayload['exp']) {
        return false; // Token đã hết hạn
    }

    // Tạo lại chữ ký để so sánh
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    // So sánh chữ ký
    return hash_equals($base64UrlSignature, $signatureProvided);
}

// Hàm kiểm tra Authorization (chỉ cho phép Admin)
function authorize()
{
    // Lấy tiêu đề Authorization từ request headers
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        // Trả về lỗi nếu không có token
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Unauthorized: Token is missing or invalid."
        ]);
        http_response_code(401);
        exit();
    }

    $jwt = $matches[1];

    // Xác minh JWT
    if (!verifyJWT($jwt)) {
        // Trả về lỗi nếu JWT không hợp lệ
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Unauthorized: Invalid token."
        ]);
        http_response_code(401);
        exit();
    }

    // Giải mã payload từ token
    $parts = explode('.', $jwt);
    $payload = json_decode(base64_decode($parts[1]), true);

    // Kiểm tra vai trò cố định là Admin
    if (!isset($payload['role']) || $payload['role'] !== 'Admin') {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Forbidden: You must be an Admin to access this resource."
        ]);
        http_response_code(403);
        exit();
    }

    // Trả về payload để sử dụng trong mã của bạn
    return $payload;
}
