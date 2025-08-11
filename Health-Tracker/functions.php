<?php
session_start();
ob_start();

// Database connection
function getDBConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'fitness_tracker';

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Redirect if not logged in
function redirectIfNotLoggedIn($redirect_url = '') {
    if (!isLoggedIn()) {
        if ($redirect_url) {
            $_SESSION['redirect_url'] = $redirect_url;
        }
        header('Location: login.php');
        exit();
    }
}

// Redirect if user is already logged in
function redirectIfLoggedIn($redirect_url = 'dashboard.php') {
    if (isLoggedIn()) {
        header('Location: ' . $redirect_url);
        exit();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Display alert message
function displayAlert($message, $type = 'success') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

if (!function_exists('formatTime')) {
    function formatTime($time) {
        return date('g:i A', strtotime($time));
    }
}

// Calculate BMI
function calculateBMI($weight, $height) {
    if ($weight > 0 && $height > 0) {
        return round($weight / ($height * $height), 1);
    }
    return 0;
}

// Get BMI category
function getBMICategory($bmi) {
    if ($bmi < 18.5) {
        return ['Underweight', 'warning'];
    } elseif ($bmi < 25) {
        return ['Normal weight', 'success'];
    } elseif ($bmi < 30) {
        return ['Overweight', 'warning'];
    } else {
        return ['Obese', 'danger'];
    }
}

// Calculate daily calorie needs
function calculateDailyCalories($weight, $height, $age, $gender, $activity_level) {
    // Harris-Benedict Formula
    if ($gender === 'male') {
        $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
    } else {
        $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
    }

    // Activity multipliers
    $multipliers = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9
    ];

    return round($bmr * ($multipliers[$activity_level] ?? 1.2));
}

// Get progress status with bootstrap classes
function getProgressStatus($current, $target, $type = 'normal') {
    if ($type === 'inverse') {
        if ($current <= $target) return 'success';
        if ($current <= $target * 1.1) return 'warning';
        return 'danger';
    } else {
        if ($current >= $target) return 'success';
        if ($current >= $target * 0.9) return 'warning';
        return 'danger';
    }
}

// Format duration in hours and minutes
function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($hours > 0) {
        return $hours . 'h ' . $mins . 'm';
    }
    return $mins . 'm';
}

// Get user data
function getUserData($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $conn->close();
    return $user;
}

// Update user profile
function updateUserProfile($user_id, $data) {
    $conn = getDBConnection();
    $sql = "UPDATE users SET ";
    $types = "";
    $params = [];
    
    foreach ($data as $key => $value) {
        $sql .= "$key = ?, ";
        $types .= "s";
        $params[] = $value;
    }
    
    $sql = rtrim($sql, ", ");
    $sql .= " WHERE user_id = ?";
    $types .= "i";
    $params[] = $user_id;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $conn->close();
    
    return $result;
}

/**
 * Check if a user is logged in
 * @return bool Returns true if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Log in a user and set session variables
 * @param array $user User data array
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    
    // If there's a redirect URL stored in session, use it
    if (isset($_SESSION['redirect_url'])) {
        $redirect_url = $_SESSION['redirect_url'];
        unset($_SESSION['redirect_url']);
        header('Location: ' . $redirect_url);
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

/**
 * Log out a user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
?> 