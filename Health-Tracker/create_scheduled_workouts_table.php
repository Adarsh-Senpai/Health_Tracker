<?php
require_once 'includes/db.php';

$conn = getDBConnection();

$sql = "CREATE TABLE IF NOT EXISTS scheduled_workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workout_type VARCHAR(50) NOT NULL,
    scheduled_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if ($conn->query($sql)) {
    echo "Scheduled workouts table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close(); 