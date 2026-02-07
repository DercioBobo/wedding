<?php
// api/tables.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

if ($method === 'GET') {
    $sql = "SELECT * FROM tables ORDER BY id";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
} 
else if ($method === 'POST') {
    $input = getJsonInput();
    if(isset($input['name'])) {
        $stmt = $pdo->prepare("INSERT INTO tables (name) VALUES (?)");
        $stmt->execute([$input['name']]);
        echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
    }
} 
else if ($method === 'PUT') {
    $input = getJsonInput();
    if(isset($input['id']) && isset($input['name'])) {
        $stmt = $pdo->prepare("UPDATE tables SET name = ? WHERE id = ?");
        $stmt->execute([$input['name'], $input['id']]);
        echo json_encode(['status' => 'success']);
    }
} 
else if ($method === 'DELETE') {
    $input = getJsonInput();
    if(isset($input['id'])) {
        // Optional: Check if used in active orders? For now simple delete.
        try {
            $stmt = $pdo->prepare("DELETE FROM tables WHERE id = ?");
            $stmt->execute([$input['id']]);
            echo json_encode(['status' => 'success']);
        } catch(PDOException $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete table (may be in use).']);
        }
    }
}
?>
