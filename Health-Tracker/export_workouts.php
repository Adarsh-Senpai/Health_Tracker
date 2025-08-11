<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Check if user is premium
$stmt = $conn->prepare("SELECT is_premium FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user['is_premium']) {
    $_SESSION['message'] = displayAlert('This feature is only available for premium users.', 'warning');
    header("Location: workout.php");
    exit();
}

// Get all workout data for the user
$stmt = $conn->prepare("
    SELECT 
        DATE(date_logged) as workout_date,
        workout_type,
        duration,
        calories_burned
    FROM workout_logs 
    WHERE user_id = ?
    ORDER BY date_logged DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="workout_history.csv"');

// Create CSV file
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, array('Date', 'Workout Type', 'Duration (minutes)', 'Calories Burned'));

// Add workout data
while ($row = $result->fetch_assoc()) {
    $workout_type = ucwords(str_replace('_', ' ', $row['workout_type']));
    fputcsv($output, array(
        $row['workout_date'],
        $workout_type,
        $row['duration'],
        $row['calories_burned']
    ));
}

fclose($output);
$conn->close();
?> 