<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../db_test.php';
require_once __DIR__ . '/config/auth-guard.php';

$user_data = validate_token();
$method = $_SERVER['REQUEST_METHOD'];

// Çakışma kontrol fonksiyonları
function isRoomAvailable($pdo, $room_id, $date, $start_time, $end_time) {
    $stmt = $pdo->prepare("
        SELECT id FROM room_reservations
        WHERE room_id = ? AND date = ? AND status != 'rejected'
        AND start_time < ? AND end_time > ?
    ");
    $stmt->execute([$room_id, $date, $end_time, $start_time]);
    return $stmt->rowCount() === 0;
}

function isLecturerAvailable($pdo, $lecturer_id, $date, $start_time, $end_time) {
    $stmt = $pdo->prepare("
        SELECT id FROM room_reservations
        WHERE lecturer_id = ? AND date = ? AND status != 'rejected'
        AND start_time < ? AND end_time > ?
    ");
    $stmt->execute([$lecturer_id, $date, $end_time, $start_time]);
    return $stmt->rowCount() === 0;
}

switch ($method) {
    case 'GET':
        $lecturer_id = $_GET['lecturer_id'] ?? null;
        $query = "
            SELECT res.id, res.date, res.start_time, res.end_time, res.status,
                   r.name as room_name, l.name as lecturer_name
            FROM room_reservations res
            LEFT JOIN rooms r ON res.room_id = r.id
            LEFT JOIN lecturers l ON res.lecturer_id = l.id
        ";
        if ($lecturer_id) {
            $query .= " WHERE res.lecturer_id = ?";
            $stmt = $pdo->prepare($query . " ORDER BY res.date DESC, res.start_time");
            $stmt->execute([$lecturer_id]);
        } else {
            $stmt = $pdo->query($query . " ORDER BY res.date DESC, res.start_time");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->room_id) && !empty($data->date) && !empty($data->start_time) && !empty($data->end_time)) {
            if (!isRoomAvailable($pdo, $data->room_id, $data->date, $data->start_time, $data->end_time)) {
                http_response_code(409);
                echo json_encode(["message" => "Bu zaman aralığında seçilen oda dolu."]);
                exit();
            }
            if (!isLecturerAvailable($pdo, $user_data->id, $data->date, $data->start_time, $data->end_time)) {
                http_response_code(409);
                echo json_encode(["message" => "Akademisyenin bu saatte zaten başka bir rezervasyonu bulunuyor."]);
                exit();
            }
            $stmt = $pdo->prepare("INSERT INTO room_reservations (room_id, lecturer_id, date, start_time, end_time, status)
                                   VALUES (?, ?, ?, ?, ?, 'pending')");
            if ($stmt->execute([$data->room_id, $user_data->id, $data->date, $data->start_time, $data->end_time])) {
                http_response_code(201);
                echo json_encode(["message" => "Reservation request submitted."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to submit reservation."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if ($user_data->role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Only admins can update reservation status."]);
            exit();
        }
        if (!empty($data->id) && !empty($data->status)) {
            $allowed = ['pending', 'approved', 'rejected'];
            if (!in_array($data->status, $allowed)) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid status value."]);
                exit();
            }
            $stmt = $pdo->prepare("UPDATE room_reservations SET status = ? WHERE id = ?");
            if ($stmt->execute([$data->status, $data->id])) {
                echo json_encode(["status" => "success", "message" => "Reservation status updated."]);
            } else {
                http_response_code(503);
                echo json_encode(["status" => "error", "message" => "Unable to update reservation."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID and status are required."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $stmt = $pdo->prepare("SELECT * FROM room_reservations WHERE id = ? AND lecturer_id = ? AND status = 'pending'");
            $stmt->execute([$data->id, $user_data->id]);
            if ($stmt->rowCount() > 0) {
                $del = $pdo->prepare("DELETE FROM room_reservations WHERE id = ?");
                $del->execute([$data->id]);
                echo json_encode(["status" => "success", "message" => "Reservation deleted."]);
            } else {
                http_response_code(403);
                echo json_encode(["message" => "Bu işlemi yapmaya yetkiniz yok veya rezervasyon zaten onaylanmış."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Reservation ID is required."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed for reservations."]);
        break;
}
?>
