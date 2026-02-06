<?php
require_once 'config/db.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS `messages` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `guest_name` VARCHAR(100),
      `message` TEXT NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Table 'messages' created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
