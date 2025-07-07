<?php
require_once __DIR__ . '/../../libs/src/JWTExceptionWithPayloadInterface.php'; // Eğer gerekiyorsa
require_once __DIR__ . '/../../libs/src/JWT.php';
require_once __DIR__ . '/../../libs/src/Key.php';
require_once __DIR__ . '/../../libs/src/ExpiredException.php';
require_once __DIR__ . '/../../libs/src/SignatureInvalidException.php';
require_once __DIR__ . '/../../config/core.php'; // 🔧 Bu satır düzeltildi

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validate_token() {
    $authHeader = getallheaders()['Authorization'] ?? null;
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(["message" => "Access denied. No token provided."]);
        exit();
    }
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1];
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
        return $decoded->data; // 🔥 Sadece data kısmı dönüyor
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Access denied. Invalid token.", "error" => $e->getMessage()]);
        exit();
    }
}
