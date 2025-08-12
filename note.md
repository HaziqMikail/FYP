# 📦 Database Structure for `bruy_db`

This document contains the full SQL schema for the `bruy_db` database, including all **4 main tables**:  
- `users`
- `transactions`
- `disputes`
- `support_requests`

## 📄 SQL Script

```sql
-- Create `users` table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'seller') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email_role (email, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create `transactions` table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(20) NOT NULL UNIQUE,
    seller_id INT NOT NULL,
    buyer_id INT,
    item_name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'completed', 'released', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create `disputes` table
CREATE TABLE disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(20) NOT NULL,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'rejected') DEFAULT 'open',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_notes TEXT DEFAULT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create `support_requests` table
CREATE TABLE support_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    issue TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_notes TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
