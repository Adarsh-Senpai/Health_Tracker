<?php
require_once 'functions.php';
session_start();

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['workout_id'])) {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    $workout_id = intval($_POST['workout_id']);
    
    // Verify the workout belongs to the current user
    $stmt = $conn->prepare("DELETE FROM workout_logs WHERE workout_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $workout_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = displayAlert('Workout deleted successfully!', 'success');
    } else {
        $_SESSION['message'] = displayAlert('Error deleting workout.', 'danger');
    }
    
    $conn->close();
}

// Redirect back to workout page
header('Location: ../workout.php');
exit();
?> 