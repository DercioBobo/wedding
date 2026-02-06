<?php
require 'config/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM messages");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($messages) . "\n";
    print_r($messages);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
