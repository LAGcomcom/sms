CREATE TABLE IF NOT EXISTS devices (
  device_id VARCHAR(64) PRIMARY KEY,
  phone_number VARCHAR(32) UNIQUE,
  last_heartbeat DATETIME,
  status ENUM('online','offline') DEFAULT 'offline'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  message_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  device_id VARCHAR(64) NOT NULL,
  sender VARCHAR(64) NOT NULL,
  content_cipher VARBINARY(4096) NOT NULL,
  content_iv VARBINARY(32) NOT NULL,
  content_tag VARBINARY(32) NOT NULL,
  receive_time DATETIME NOT NULL,
  INDEX idx_messages_device_time (device_id, receive_time),
  INDEX idx_messages_time (receive_time),
  CONSTRAINT fk_messages_device FOREIGN KEY (device_id) REFERENCES devices(device_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
