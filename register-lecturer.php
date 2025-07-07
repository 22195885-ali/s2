<?php
// Gerekli dosyaları dahil et
require_once __DIR__ . '/../db_test.php';
require_once __DIR__ . '/../config/core.php';
require_once __DIR__ . '/../libs/src/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/../libs/src/Key.php';
require_once __DIR__ . '/../libs/src/JWT.php';
require_once __DIR__ . '/../libs/src/ExpiredException.php';
require_once __DIR__ . '/../libs/src/SignatureInvalidException.php';
require_once __DIR__ . '/../libs/src/BeforeValidException.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// İstek verisini al
$data = json_decode(file_get_contents("php://input"));

if (
    empty($data->name) ||
    empty($data->email) ||
    empty($data->password)
) {
    http_response_code(400);
    echo json_encode(["message" => "Tüm alanlar gereklidir."]);
    exit();
}

$name = trim($data->name);
$email = trim($data->email);
$password = $data->password;

// E-posta kullanımda mı kontrol et (hem admin hem lecturer içinde)
$stmt = $pdo->prepare("SELECT id FROM lecturers WHERE email = ? 
                       UNION 
                       SELECT id FROM admins WHERE email = ?");
$stmt->execute([$email, $email]);

if ($stmt->rowCount() > 0) {
    http_response_code(409);
    echo json_encode(["message" => "Bu e-posta adresi zaten kullanımda."]);
    exit();
}

// Şifreyi hashle
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Veritabanına ekle
$insert = $pdo->prepare("INSERT INTO lecturers (name, email, password_hash) VALUES (?, ?, ?)");
if ($insert->execute([$name, $email, $password_hash])) {
    $user_id = $pdo->lastInsertId();

    // JWT oluştur
    $payload = [
        "iss" => "https://baskent.edu.tr",
        "iat" => time(),
        "exp" => time() + (60 * 60), // 1 saat geçerli
        "data" => [
            "id" => $user_id,
            "role" => "lecturer"
        ]
    ];

    $token = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

    http_response_code(201);
    echo json_encode([
        "message" => "User was successfully registered.",
        "token" => $token
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Kayıt sırasında bir hata oluştu."]);
}
