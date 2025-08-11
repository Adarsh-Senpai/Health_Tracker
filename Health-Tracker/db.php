<?php
function getDBConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'fitness_tracker';

    try {
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Function to check if a table exists
function tableExists($tableName) {
    $conn = getDBConnection();
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to create the users table if it doesn't exist
function createUsersTable() {
    $conn = getDBConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_premium BOOLEAN DEFAULT FALSE,
        is_admin BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }
}

// Initialize database and tables if they don't exist
try {
    // Create database if it doesn't exist
    $temp_conn = new mysqli('localhost', 'root', '');
    $temp_conn->query("CREATE DATABASE IF NOT EXISTS fitness_tracker");
    $temp_conn->close();

    // Create tables if they don't exist
    if (!tableExists('users')) {
        createUsersTable();
    }
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?> 