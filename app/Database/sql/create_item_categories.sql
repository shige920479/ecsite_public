CREATE table item_categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  sub_category_id BIGINT NOT NULL,
  name VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(sub_category_id) REFERENCES sub_categories(id)  ON DELETE CASCADE
);