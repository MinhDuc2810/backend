<?php
// Hàm gửi email
function sendEmail($to, $subject, $message)
{
    // Kiểm tra xem PHP có hỗ trợ hàm mail hay không
    if (function_exists('mail')) {
        $headers = "From: no-reply@gym.com\r\n" .
            "Reply-To: no-reply@gym.com\r\n" .
            "X-Mailer: PHP/" . phpversion();
        return mail($to, $subject, $message, $headers);
    }
    return false;
}
