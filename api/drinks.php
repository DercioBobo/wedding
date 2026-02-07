<?php
// api/drinks.php
header('Content-Type: application/json');
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM drinks ORDER BY is_active DESC, category, name";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        // Handle Add OR Edit (if ID is present)
        // We use POST for edit too because of file upload complexity with PUT
        $input = $_POST;
        
        $name = $input['name'] ?? '';
        $description = $input['description'] ?? '';
        $category = $input['category'] ?? 'General';
        $editId = $input['id'] ?? null;
        
        $image_url = null;
        
        // Handle Image Upload or Link
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image_url = 'uploads/' . $filename;
            }
        } elseif (!empty($input['image_link'])) {
             $image_url = $input['image_link'];
        }
        
        if ($editId) {
            // Update
            $sql = "UPDATE drinks SET name=?, description=?, category=?";
            $params = [$name, $description, $category];
            
            if ($image_url) {
                $sql .= ", image_url=?";
                $params[] = $image_url;
            }
            
            $sql .= " WHERE id=?";
            $params[] = $editId;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['status' => 'success']);
            
        } else {
            // Insert
            $sql = "INSERT INTO drinks (name, description, category, image_url) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $category, $image_url ?? '']);
            echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
        }
        break;

    case 'PUT':
        // JSON based updates (Toggle Active)
        $input = getJsonInput();
        if (isset($input['id']) && isset($input['is_active'])) {
             $sql = "UPDATE drinks SET is_active = ? WHERE id = ?";
             $stmt = $pdo->prepare($sql);
             $stmt->execute([$input['is_active'], $input['id']]);
             echo json_encode(['status' => 'success']);
        }
        break;
        
    case 'DELETE':
         $input = getJsonInput();
         if (isset($input['id'])) {
             // Optional: Unlink image file
             $sql = "DELETE FROM drinks WHERE id = ?";
             $stmt = $pdo->prepare($sql);
             $stmt->execute([$input['id']]);
             echo json_encode(['status' => 'success']);
         }
         break;
}
?>
