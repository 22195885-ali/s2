<?php
// Gerekli dosyaları dahil et
require_once __DIR__ . '/../db_test.php';             // Veritabanı bağlantısı ($pdo)
require_once __DIR__ . '/../config/core.php';         // Temel ayarlar (JWT_SECRET_KEY vb.)
require_once __DIR__ . '/config/auth-guard.php';      // ✅ doğru klasör: api/config/
require_once __DIR__ . '/../libs/src/JWT.php';        // JWT kütüphanesi
require_once __DIR__ . '/../libs/src/Key.php';        // JWT için Key sınıfı

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// JSON yanıtı olacak
header('Content-Type: application/json');

// 1. JWT token'ını doğrula ve admin yetkisini kontrol et
$userData = validate_token();  // Token geçersizse bu fonksiyon 401 döndürür
if (!isset($userData->role) || $userData->role !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Bu işlemi yapmaya yetkiniz yok."]);
    exit();
}

// 2. Gelen veriyi al
$request = json_decode(file_get_contents("php://input"), true);
$name = $request['name'] ?? null;
$email = $request['email'] ?? null;
$password = $request['password'] ?? null;

// 3. Tüm alanlar dolu mu?
if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(["message" => "Eksik bilgi gönderildi."]);
    exit();
}

// 4. E-posta daha önce kullanılmış mı? (Hem adminlerde hem akademisyenlerde kontrol et)
$checkAdmin = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
$checkAdmin->execute([$email]);

$checkLecturer = $pdo->prepare("SELECT id FROM lecturers WHERE email = ?");
$checkLecturer->execute([$email]);

if ($checkAdmin->rowCount() > 0 || $checkLecturer->rowCount() > 0) {
    http_response_code(409);
    echo json_encode(["message" => "Bu e-posta adresi zaten kullanımda."]);
    exit();
}

// 5. Şifreyi hashle
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// 6. Veritabanına ekle
$stmt = $pdo->prepare("INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)");
if ($stmt->execute([$name, $email, $password_hash])) {
    http_response_code(201);
    echo json_encode(["message" => "Admin user was successfully created."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Kayıt sırasında bir hata oluştu."]);
}
?>
