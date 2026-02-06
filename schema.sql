-- Database Schema for Wedding Drink Platform

CREATE TABLE IF NOT EXISTS `tables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `drinks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `image_url` VARCHAR(255),
  `category` VARCHAR(50) DEFAULT 'General',
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `table_id` INT NOT NULL,
  `guest_name` VARCHAR(100),
  `guest_note` TEXT,
  `status` ENUM('pending', 'ready', 'done', 'cancelled') DEFAULT 'pending',
  `device_id` VARCHAR(255), -- For basic throttling/identification
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`table_id`) REFERENCES `tables`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `drink_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`drink_id`) REFERENCES `drinks`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some dummy data for testing
INSERT IGNORE INTO `tables` (`name`) VALUES 
('Table 1'), ('Table 2'), ('Table 3'), ('Head Table'), ('Table 10');

INSERT IGNORE INTO `drinks` (`name`, `description`, `category`, `image_url`, `is_active`) VALUES 
('Mojito', 'Refreshing mint and lime cocktail', 'Cocktail', 'uploads/mojito.jpg', 1),
('Whiskey Sour', 'Classic whiskey drink', 'Cocktail', 'uploads/whiskey.jpg', 1),
('Coca Cola', 'Chilled soda', 'Soft Drink', 'uploads/coke.jpg', 1),
('Water', 'Still water with ice', 'Soft Drink', 'uploads/water.jpg', 1);

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `guest_name` VARCHAR(100),
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
