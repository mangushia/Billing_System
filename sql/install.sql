  
-- Ardthon Solutions WiFi Billing System Database
-- Version 1.0

-- Create database
CREATE DATABASE IF NOT EXISTS wifi_billing 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE wifi_billing;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    full_name VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Packages table
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    speed INT NOT NULL COMMENT 'Speed in Mbps',
    duration INT NOT NULL,
    duration_unit ENUM('minutes', 'hours', 'days', 'months') NOT NULL,
    data_limit DECIMAL(10,2) COMMENT 'Data limit in GB',
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_price (price),
    INDEX idx_status (status),
    INDEX idx_duration (duration, duration_unit)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    phone VARCHAR(15) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    mpesa_code VARCHAR(50),
    package_id INT,
    checkout_request_id VARCHAR(100),
    result_code VARCHAR(10),
    result_desc TEXT,
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(20) DEFAULT 'mpesa',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_phone (phone),
    INDEX idx_mpesa_code (mpesa_code),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vouchers table
CREATE TABLE vouchers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    package_id INT NOT NULL,
    user_id INT,
    transaction_id INT,
    status ENUM('active', 'used', 'expired', 'cancelled') DEFAULT 'active',
    used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sessions table (for MikroTik/Radius)
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),
    voucher_id INT,
    mac_address VARCHAR(17),
    ip_address VARCHAR(45),
    session_id VARCHAR(100) UNIQUE,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    data_used BIGINT DEFAULT 0 COMMENT 'Data used in bytes',
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_mac (mac_address),
    INDEX idx_status (status),
    INDEX idx_session_id (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Logs table
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_type ENUM('payment', 'auth', 'system', 'error') NOT NULL,
    message TEXT,
    data JSON,
    ip_address VARCHAR(45),
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (log_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, phone, full_name, role) VALUES
('admin', '$2y$10$YourHashedPasswordHere', 'admin@ardthon.com', '254700000000', 'System Administrator', 'admin');

-- Insert default packages
INSERT INTO packages (name, price, speed, duration, duration_unit, data_limit, description, sort_order) VALUES
('Basic Hourly', 50, 2, 1, 'hours', 1, 'Perfect for quick browsing and emails', 1),
('Daily Surf', 100, 4, 24, 'hours', 5, 'Full day of browsing and social media', 2),
('Weekly Pro', 500, 8, 7, 'days', 20, 'Ideal for remote workers and students', 3),
('Monthly Unlimited', 1500, 10, 30, 'days', 100, 'Best value for heavy users', 4),
('Night Owl', 80, 6, 8, 'hours', 10, 'Midnight to 8AM special', 5),
('Weekend Special', 200, 8, 48, 'hours', 15, 'Friday 6PM to Monday 6AM', 6);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Ardthon Solutions WiFi', 'text', 'Website name'),
('site_email', 'info@ardthon.com', 'text', 'Contact email'),
('site_phone', '+254700000000', 'text', 'Contact phone'),
('currency', 'KES', 'text', 'Default currency'),
('mpesa_env', 'sandbox', 'text', 'M-PESA environment (sandbox/production)'),
('mpesa_shortcode', '174379', 'text', 'M-PESA shortcode'),
('session_timeout', '3600', 'number', 'Session timeout in seconds'),
('voucher_length', '8', 'number', 'Voucher code length'),
('enable_sms', 'true', 'boolean', 'Enable SMS notifications'),
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status');

-- Create indexes for performance
CREATE INDEX idx_transactions_composite ON transactions(status, created_at);
CREATE INDEX idx_vouchers_composite ON vouchers(status, expires_at);
CREATE INDEX idx_sessions_composite ON sessions(status, start_time);

-- Create view for revenue reports
CREATE VIEW revenue_daily AS
SELECT 
    DATE(created_at) as date,
    COUNT(*) as transaction_count,
    SUM(amount) as total_amount,
    AVG(amount) as average_amount
FROM transactions
WHERE status = 'completed'
GROUP BY DATE(created_at);

-- Create view for active sessions
CREATE VIEW active_sessions AS
SELECT 
    s.*,
    v.code as voucher_code,
    p.name as package_name,
    p.speed
FROM sessions s
LEFT JOIN vouchers v ON s.voucher_id = v.id
LEFT JOIN packages p ON v.package_id = p.id
WHERE s.status = 'active';

-- Create triggers for automatic expiry
DELIMITER //

CREATE TRIGGER check_voucher_expiry
BEFORE UPDATE ON vouchers
FOR EACH ROW
BEGIN
    IF NEW.expires_at < NOW() AND NEW.status = 'active' THEN
        SET NEW.status = 'expired';
    END IF;
END//

CREATE TRIGGER update_session_on_logout
BEFORE UPDATE ON sessions
FOR EACH ROW
BEGIN
    IF NEW.status = 'closed' AND OLD.status = 'active' THEN
        SET NEW.end_time = NOW();
    END IF;
END//

DELIMITER ;

-- Insert sample data for testing
INSERT INTO transactions (transaction_id, phone, amount, mpesa_code, package_id, status, created_at) VALUES
('TXN20240101001', '254712345678', 50.00, 'MPESA12345', 1, 'completed', NOW() - INTERVAL 1 DAY),
('TXN20240101002', '254723456789', 100.00, 'MPESA12346', 2, 'completed', NOW() - INTERVAL 12 HOUR),
('TXN20240101003', '254734567890', 500.00, 'MPESA12347', 3, 'pending', NOW() - INTERVAL 2 HOUR);

INSERT INTO vouchers (code, package_id, status, expires_at, created_at) VALUES
('ARD123456', 1, 'active', NOW() + INTERVAL 1 DAY, NOW()),
('ARD789012', 2, 'active', NOW() + INTERVAL 7 DAY, NOW()),
('ARD345678', 3, 'used', NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 2 DAY);

-- Grant privileges (adjust username and host as needed)
GRANT ALL PRIVILEGES ON wifi_billing.* TO 'wifi_user'@'localhost' IDENTIFIED BY 'secure_password';
FLUSH PRIVILEGES;