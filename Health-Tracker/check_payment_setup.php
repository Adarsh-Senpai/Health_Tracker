<?php
require_once 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $conn = getDBConnection();
    
    // Check if payments table exists
    $result = $conn->query("SHOW TABLES LIKE 'payments'");
    if ($result->num_rows == 0) {
        // Create payments table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            plan_type VARCHAR(20) NOT NULL,
            transaction_id VARCHAR(255) NOT NULL,
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )";
        
        if ($conn->query($sql)) {
            echo "Payments table created successfully<br>";
        } else {
            throw new Exception("Error creating payments table: " . $conn->error);
        }
    } else {
        echo "Payments table already exists<br>";
    }
    
    // Check table structure
    $result = $conn->query("DESCRIBE payments");
    echo "<br>Table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Key']}<br>";
    }
    
    // Test database connection
    echo "<br>Database connection: OK<br>";
    
    // Check session
    echo "<br>Session status:<br>";
    echo "Session active: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "<br>";
    echo "User logged in: " . (isset($_SESSION['user_id']) ? "Yes (ID: {$_SESSION['user_id']})" : "No") . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 