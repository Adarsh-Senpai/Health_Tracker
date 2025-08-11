<?php
require_once 'includes/header.php';

$conn = getDBConnection();

$sql = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(10,2),
    plan_type VARCHAR(20),
    transaction_id VARCHAR(255),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if ($conn->query($sql)) {
    echo "Payments table created successfully";
} else {
    echo "Error creating payments table: " . $conn->error;
}

$conn->close(); 