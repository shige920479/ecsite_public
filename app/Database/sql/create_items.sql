CREATE table items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  shop_id BIGINT NOT NULL,
  item_category_id BIGINT NOT NULL,
  name VARCHAR(50) NOT NULL,
  information TEXT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  sort_order INT DEFAULT NULL,
  is_selling TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP DEFAULT NULL,
  FOREIGN KEY(shop_id) REFERENCES shops(id)  ON DELETE CASCADE,
  FOREIGN KEY(item_category_id) REFERENCES item_categories(id),
  INDEX idx_deleted_at (deleted_at)
);