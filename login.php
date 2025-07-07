<?php
// 1. Hata ve CORS ayarları
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. Gerekli dosyaları dahil et
require_once __DIR__ . '/../db_test.php'; // Veritabanı bağlantısı
require_once __DIR__ . '/../config/core.php'; // JWT_SECRET_KEY
require_once __DIR__ . '/../libs/src/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/../libs/src/Key.php';
require_once __DIR__ . '/../libs/src/JWT.php';
require_once __DIR__ . '/../libs/src/ExpiredException.php';
require_once __DIR__ . '/../libs/src/SignatureInvalidException.php';
require_once __DIR__ . '/../libs/src/BeforeValidException.php';

use Firebase\JWT\JWT;

// 3. JSON body'den email ve password al
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 4. Giriş kontrolü
$user = null;
$role = null;
$name = null;

// Önce admin olarak kontrol et
$stmt = $pdo->prepare("SELECT id, name, password_hash FROM admins WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (password_verify($password, $row['password_hash'])) {
        $user = $row;
        $role = 'admin';
        $name = $row['name'];
    }
}

// Eğer admin değilse, lecturers tablosunda ara
if (!$user) {
    $stmt = $pdo->prepare("SELECT id, name, password_hash FROM lecturers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row['password_hash'])) {
            $user = $row;
            $role = 'lecturer';
            $name = $row['name'];
        }
    }
}

// 5. JWT ve başarılı yanıt gönder
if ($user && $role) {
    $payload = [
        'iss' => 'https://baskent.edu.tr',
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24),
        'data' => [
            'id' => $user['id'],
            'role' => $role
        ]
    ];

    $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Giriş başarılı.',
        'name' => $name,
        'email' => $email,
        'userType' => $role === 'admin' ? 'admin' : 'user',
        'token' => $jwt,
        'redirect' => $role === 'admin' ? '../Pages/Admin/Home.php' : '../Pages/User/Home.php'
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Login failed.'
    ]);
}
