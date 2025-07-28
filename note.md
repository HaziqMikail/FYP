##DATABASE

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
);


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
);
