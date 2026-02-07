<?php
require 'config/db.php';

try {
    $pdo->exec("ALTER TABLE drinks MODIFY image_url TEXT");
    echo "Successfully updated image_url to TEXT.\n";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}
?>
