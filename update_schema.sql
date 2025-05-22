-- Select the database to use
USE redroosterfarm;

-- 1. Modify `users` table
ALTER TABLE `users`
MODIFY COLUMN `user_type` VARCHAR(10) DEFAULT 'user';

-- 2. Rename `category` table to `property_types`, rename its PK, and clear its data
ALTER TABLE `category` RENAME TO `property_types`;

ALTER TABLE `property_types`
CHANGE COLUMN `category_id` `property_type_id` INT(11) NOT NULL AUTO_INCREMENT;

-- Delete existing data from the renamed table
DELETE FROM `property_types`;

-- For consistency and FK support, convert users and property_types to InnoDB.
-- This should be done before creating tables that reference them if they are MyISAM.
ALTER TABLE `users` ENGINE=InnoDB;
ALTER TABLE `property_types` ENGINE=InnoDB;

-- 3. Create `properties` table
CREATE TABLE `properties` (
  `property_id` INT PRIMARY KEY AUTO_INCREMENT,
  `seller_id` INT,
  `title` VARCHAR(255),
  `description` TEXT,
  `location_text` VARCHAR(255),
  `latitude` DECIMAL(10, 8) NULL,
  `longitude` DECIMAL(11, 8) NULL,
  `size_value` DECIMAL(10, 2),
  `size_unit` VARCHAR(20),
  `price` DECIMAL(12, 2),
  `property_type_id` INT,
  `status` VARCHAR(20) DEFAULT 'available',
  `date_listed` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `main_image_url` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`seller_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL, -- Allow property to exist if seller deleted, or use ON DELETE CASCADE
  FOREIGN KEY (`property_type_id`) REFERENCES `property_types`(`property_type_id`) ON DELETE SET NULL -- Allow property to exist if type deleted
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create `property_images` table
CREATE TABLE `property_images` (
  `image_id` INT PRIMARY KEY AUTO_INCREMENT,
  `property_id` INT,
  `image_url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL,
  `is_primary_image` BOOLEAN DEFAULT FALSE,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`property_id`) REFERENCES `properties`(`property_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create `saved_properties` table
CREATE TABLE `saved_properties` (
  `saved_id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT,
  `property_id` INT,
  `date_saved` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`property_id`) REFERENCES `properties`(`property_id`) ON DELETE CASCADE,
  UNIQUE KEY `user_property_unique` (`user_id`, `property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create `inquiries` table
CREATE TABLE `inquiries` (
  `inquiry_id` INT PRIMARY KEY AUTO_INCREMENT,
  `property_id` INT,
  `buyer_id` INT,
  `seller_id` INT,
  `agent_id` INT NULL,
  `message` TEXT NOT NULL,
  `inquiry_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(20) DEFAULT 'new',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`property_id`) REFERENCES `properties`(`property_id`) ON DELETE SET NULL,
  FOREIGN KEY (`buyer_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`seller_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`agent_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Remove unnecessary tables
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `brands`;

-- The product table is intentionally left untouched as per requirements.

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
