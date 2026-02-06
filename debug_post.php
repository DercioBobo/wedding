<?php
require 'config/db.php';

echo "--- Testing Direct DB Insert ---\n";
try {
    $stmt = $pdo->prepare("INSERT INTO messages (guest_name, message) VALUES (?, ?)");
    $stmt->execute(['TestBot', 'Direct INSERT test']);
    echo "Direct Insert Success. ID: " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "Direct Insert Failed: " . $e->getMessage() . "\n";
}

echo "\n--- Testing API POST ---\n";
$url = 'http://localhost/wedding/api/messages.php';
$data = ['guest_name' => 'APIBot', 'message' => 'API POST test'];
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "API Response: " . $result . "\n";

echo "\n--- Checking Count ---\n";
$stmt = $pdo->query("SELECT * FROM messages");
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($all);
?>
