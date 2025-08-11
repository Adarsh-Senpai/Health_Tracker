<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function displayAlert($message, $type = 'success') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

function calculateBMI($weight, $height) {
    // Weight in kg, height in meters
    return $weight / ($height * $height);
}

function getBMICategory($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal weight';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}

function calculateCaloriesBurned($activity, $duration, $weight) {
    $mets = [
        'walking' => 3.5,
        'running' => 8.0,
        'cycling' => 7.0,
        'swimming' => 6.0,
        'weight_training' => 4.0
    ];
    
    $met = $mets[$activity] ?? 4.0;
    // Formula: Calories = MET × weight (kg) × duration (hours)
    return round($met * $weight * ($duration / 60));
}
?> 