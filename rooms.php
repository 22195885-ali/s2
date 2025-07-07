<?php
// Gerekli başlıklar
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Bağlantılar
require_once __DIR__ . '/../db_test.php';
require_once __DIR__ . '/config/auth-guard.php';

// JWT doğrulama
$user_data = validate_token();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
    if ($user_data->role !== 'admin') {
        http_response_code(403);
        echo json_encode(["message" => "Access denied. Only admins can create rooms."]);
        exit();
    }

    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->name) && !empty($data->capacity)) {
        $stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, features, building) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([
            $data->name,
            $data->capacity,
            $data->features ?? '',
            $data->building ?? ''
        ])) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Room created successfully."]);
        } else {
            http_response_code(503);
            echo json_encode(["status" => "error", "message" => "Unable to create room."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete data."]);
    }
    break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->name) && !empty($data->capacity)) {
            $stmt = $pdo->prepare("UPDATE rooms SET name = ?, capacity = ?, building = ? WHERE id = ?");
            if ($stmt->execute([$data->name, $data->capacity, $data->building ?? '', $data->id])) {
                echo json_encode(["status" => "success", "message" => "Room updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["status" => "error", "message" => "Unable to update room."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Incomplete data. ID is required."]);
        }
        break;

    case 'DELETE':
        if ($user_data->role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Only admins can delete rooms."]);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            if ($stmt->execute([$data->id])) {
                echo json_encode(["status" => "success", "message" => "Room deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["status" => "error", "message" => "Unable to delete room."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID is required."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}
?>
