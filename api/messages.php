<?php
// api/messages.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

switch ($method) {
    case 'GET':
        // Fetch all messages, newest first
        $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        // Guest sending a message
        $input = getJsonInput();
        
        if (empty($input['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Message cannot be empty']);
            exit;
        }

        if (empty($input['guest_name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name cannot be empty']);
            exit;
        }

        $guestName = $input['guest_name'];
        $message = $input['message'];

        try {
            $stmt = $pdo->prepare("INSERT INTO messages (guest_name, message) VALUES (?, ?)");
            $stmt->execute([$guestName, $message]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save message']);
        }
        break;
        
    case 'DELETE':
        // (Optional) Allow deleting messages if needed later
        $input = getJsonInput();
        if (!empty($input['id'])) {
             $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
             $stmt->execute([$input['id']]);
             echo json_encode(['status' => 'deleted']);
        }
        break;
}
?>
