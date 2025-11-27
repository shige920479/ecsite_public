CREATE TABLE cart_clear_errors (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  order_id BIGINT,
  error_type VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);