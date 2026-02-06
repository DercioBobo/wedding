<?php
// api/orders.php
header('Content-Type: application/json');
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

switch ($method) {
    case 'GET':
        // Barman View: Get all active orders (not done) or all if specified
        $status = $_GET['status'] ?? null;
        
        $sql = "SELECT o.*, t.name as table_name, 
                (SELECT GROUP_CONCAT(CONCAT(d.name, ' (x', oi.quantity, ')') SEPARATOR ', ') 
                 FROM order_items oi 
                 JOIN drinks d ON oi.drink_id = d.id 
                 WHERE oi.order_id = o.id) as summary
                FROM orders o
                JOIN tables t ON o.table_id = t.id";
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $sql .= " ORDER BY o.created_at ASC"; 
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status]);
        } elseif (isset($_GET['scope']) && $_GET['scope'] === 'me') {
             // Fetch orders for this device
             if (!isset($_COOKIE['wedding_device_id'])) {
                 echo json_encode([]);
                 exit;
             }
             $deviceId = $_COOKIE['wedding_device_id'];
             $sql .= " WHERE o.device_id = ? AND o.status IN ('pending', 'ready')";
             $sql .= " ORDER BY o.created_at DESC LIMIT 1";
             $stmt = $pdo->prepare($sql);
             $stmt->execute([$deviceId]);
        } else {
             // Default dashboard view: Pending and Ready
            $sql .= " WHERE o.status IN ('pending', 'ready')";
            $sql .= " ORDER BY o.created_at ASC";
            $stmt = $pdo->query($sql);
        }
        
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        // Guest: Place Order
        $input = getJsonInput();
        
        // Validation
        if (empty($input['table_id']) || empty($input['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing table or items']);
            exit;
        }

        // 1. Throttling Check
        // Check if this device has an active order
        if (isset($_COOKIE['wedding_device_id'])) {
            $deviceId = $_COOKIE['wedding_device_id'];
            $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE device_id = ? AND status IN ('pending', 'ready') LIMIT 1");
            $stmt->execute([$deviceId]);
            $activeOrder = $stmt->fetch();
            
            if ($activeOrder) {
                http_response_code(429); // Too Many Requests
                echo json_encode(['error' => 'You already have an active order. Please wait until it is served.']);
                exit;
            }
        } else {
            // Generate new device ID if missing
            $deviceId = uniqid('dev_', true);
            // Set cookie for 24 hours
             setcookie('wedding_device_id', $deviceId, time() + 86400, "/");
        }

        // 2. Validate Quantity (Max 2 drinks types, max 2 bottles each)
        if (count($input['items']) > 2) {
             http_response_code(400);
             echo json_encode(['error' => 'Max 2 different drinks per order allowed.']);
             exit;
        }

        foreach ($input['items'] as $item) {
             if ($item['quantity'] > 2) {
                 http_response_code(400);
                 echo json_encode(['error' => 'Max 2 bottles per drink allowed.']);
                 exit;
             }
        }

        // 3. Create Order
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO orders (table_id, guest_name, guest_note, device_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $input['table_id'], 
                $input['guest_name'] ?? 'Guest', 
                $input['guest_note'] ?? '',
                $deviceId
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // 4. Create Order Items
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, drink_id, quantity) VALUES (?, ?, ?)");
            foreach ($input['items'] as $item) {
                $stmtItem->execute([$orderId, $item['id'], $item['quantity']]);
            }
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'order_id' => $orderId]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Order failed: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Barman: Update Status
        $input = getJsonInput();
        
        if (!isset($input['id']) || !isset($input['status'])) {
             http_response_code(400);
             exit;
        }
        
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['status'], $input['id']]);
        
        echo json_encode(['status' => 'success']);
        break;
}
?>
