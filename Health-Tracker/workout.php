<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Display session message if exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
} else {
    $message = '';
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_all_workouts'])) {
        // Delete all workout logs for today for the current user
        $stmt = $conn->prepare("DELETE FROM workout_logs WHERE user_id = ? AND DATE(date_logged) = CURRENT_DATE");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = displayAlert('All workout logs for today have been deleted successfully!', 'success');
            // Reset totals
            $total_duration = 0;
            $total_calories = 0;
            $workouts = array(); // Clear the workouts array
        } else {
            $message = displayAlert('Error deleting workout logs.', 'danger');
        }
    } elseif (isset($_POST['workout_type'], $_POST['duration'], $_POST['calories_burned'])) {
        $workout_type = sanitizeInput($_POST['workout_type']);
        $duration = intval($_POST['duration']);
        $calories_burned = intval($_POST['calories_burned']);
        
        // Validate inputs
        if (empty($workout_type) || $duration <= 0 || $calories_burned <= 0) {
            $message = displayAlert('Please fill in all fields with valid values.', 'danger');
        } else {
            $stmt = $conn->prepare("INSERT INTO workout_logs (user_id, workout_type, duration, calories_burned) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isii", $user_id, $workout_type, $duration, $calories_burned);
            
            if ($stmt->execute()) {
                $message = displayAlert('Workout logged successfully!', 'success');
            } else {
                $message = displayAlert('Error logging workout.', 'danger');
            }
        }
    }
}

// Get user's weight for calorie calculation
$stmt = $conn->prepare("SELECT weight FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_weight = $stmt->get_result()->fetch_assoc()['weight'];

// Get today's workouts
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT *
    FROM workout_logs
    WHERE user_id = ? AND DATE(date_logged) = ?
    ORDER BY date_logged DESC
");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$workouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$total_duration = 0;
$total_calories = 0;
foreach ($workouts as $workout) {
    $total_duration += $workout['duration'];
    $total_calories += $workout['calories_burned'];
}

// Handle successful payment
if (isset($_GET['payment']) && $_GET['payment'] === 'success' && isset($_GET['plan'])) {
    $plan = $_GET['plan'];
    
    // Set duration based on plan
    switch($plan) {
        case 'monthly':
            $duration_months = 1;
            break;
        case 'yearly':
            $duration_months = 12;
            break;
        case 'lifetime':
            $duration_months = 999; // Effectively lifetime
            break;
        default:
            $duration_months = 0;
    }

    if ($duration_months > 0) {
        // Update user's premium status
        $expiry_date = date('Y-m-d H:i:s', strtotime("+$duration_months months"));
        $stmt = $conn->prepare("UPDATE users SET is_premium = 1, premium_expiry = ? WHERE user_id = ?");
        $stmt->bind_param("si", $expiry_date, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = displayAlert('Welcome to Premium! Your account has been upgraded.', 'success');
        }
    }
}

$conn->close();
?>

<style>
/* Enhanced Workout Page Styles with Better Colors */
.workout-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: none;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

[data-theme="dark"] .workout-card {
    background: var(--bg-card);
}

.workout-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Gradient backgrounds for different cards */
.summary-card {
    background: linear-gradient(135deg, #3498db10 0%, #2ecc7110 100%);
}

.timer-card {
    background: linear-gradient(135deg, #9b59b610 0%, #3498db10 100%);
}

.logger-card {
    background: linear-gradient(135deg, #2ecc7110 0%, #3498db10 100%);
}

.info-card {
    background: linear-gradient(135deg, #34495e10 0%, #9b59b610 100%);
}

/* Enhanced Stat Items */
.stat-item {
    position: relative;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    background: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
    border: 1px solid rgba(52, 152, 219, 0.1);
}

.stat-item:hover {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(52, 152, 219, 0.2);
    transform: translateY(-2px);
}

.stat-item.duration-stat i {
    color: #9b59b6;
}

.stat-item.calories-stat i {
    color: #e74c3c;
}

.stat-item h3 {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(45deg, #3498db, #2ecc71);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0;
}

/* Enhanced Timer Styles */
.timer-container {
    text-align: center;
    padding: 1rem;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(155, 89, 182, 0.1);
    margin: 0 auto;
    max-width: 320px;
}

.timer-container:hover {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(155, 89, 182, 0.2);
}

.timer-display {
    font-size: 2.8rem;
    font-weight: 700;
    font-family: 'Roboto Mono', monospace;
    background: linear-gradient(45deg, #3498db, #9b59b6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    letter-spacing: 2px;
    line-height: 1.2;
}

.timer-controls {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.timer-controls .btn {
    padding: 0.4rem 1rem;
    border-radius: 50px;
    transition: all 0.3s ease;
    border: none;
    font-size: 0.85rem;
    min-width: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timer-controls .btn i {
    margin-right: 0.3rem;
    font-size: 0.9rem;
}

.timer-controls .btn-start {
    background: linear-gradient(45deg, #2ecc71, #27ae60);
    color: white;
}

.timer-controls .btn-pause {
    background: linear-gradient(45deg, #f1c40f, #f39c12);
    color: white;
}

.timer-controls .btn-reset {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    color: white;
}

.timer-controls .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

/* Enhanced Form Styles */
.workout-form {
    background: rgba(255, 255, 255, 0.8);
    padding: 1.8rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(52, 152, 219, 0.1);
}

.workout-form:hover {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(52, 152, 219, 0.2);
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
    font-size: 1rem;
}

.form-label i {
    background: linear-gradient(45deg, #3498db, #2ecc71);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.form-select, .form-control {
    background-color: #fff;
    border: 1px solid rgba(52, 152, 219, 0.2);
    color: #2c3e50;
    font-weight: 500;
    transition: all 0.3s ease;
}

.form-select:hover, .form-control:hover {
    border-color: rgba(52, 152, 219, 0.4);
    background-color: #f8f9fa;
}

.form-select:focus, .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    background-color: #fff;
}

.form-text {
    color: #34495e;
    font-weight: 500;
    font-size: 0.85rem;
    margin-top: 0.3rem;
}

/* Make readonly input match other inputs */
input[readonly] {
    background-color: #fff !important;
    opacity: 1;
    cursor: default;
}

/* Enhanced Table Styles */
.workout-table {
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.9);
}

.workout-table thead th {
    background: linear-gradient(45deg, #3498db30, #2ecc7130);
    border: none;
    padding: 1.2rem 1rem;
    font-weight: 700;
    color: #2c3e50;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
}

.workout-table thead th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(45deg, #3498db50, #2ecc7150);
}

.workout-table tbody td {
    color: #2c3e50;
    font-weight: 500;
    padding: 1rem;
    font-size: 0.95rem;
}

.activity-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    background: linear-gradient(45deg, #3498db20, #2ecc7120);
    color: #2c3e50;
    transition: all 0.3s ease;
}

.activity-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.2);
}

/* Category-specific badges */
.activity-badge.cardio {
    background: linear-gradient(45deg, #3498db20, #2980b920);
    color: #2980b9;
}

.activity-badge.strength {
    background: linear-gradient(45deg, #2ecc7120, #27ae6020);
    color: #27ae60;
}

.activity-badge.sports {
    background: linear-gradient(45deg, #e67e2220, #d3541620);
    color: #d35400;
}

.activity-badge.mind-body {
    background: linear-gradient(45deg, #9b59b620, #8e44ad20);
    color: #8e44ad;
}

/* Enhanced Delete Button */
.delete-btn {
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
    border: none;
}

.delete-btn:hover {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    color: white;
    transform: rotate(90deg);
}

/* Enhanced Info Section */
.info-section {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(52, 152, 219, 0.1);
}

.info-section:hover {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(52, 152, 219, 0.2);
}

.info-section ul li {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-bottom: 0.8rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.info-section ul li:hover {
    background: rgba(52, 152, 219, 0.05);
    transform: translateX(5px);
}

.info-section ul li i {
    background: linear-gradient(45deg, #3498db, #2ecc71);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 1.2rem;
}

/* Animation Keyframes */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease;
}

/* Dark Mode Enhancements */
[data-theme="dark"] .workout-card {
    background: linear-gradient(135deg, #2c3e5010 0%, #34495e10 100%);
}

[data-theme="dark"] .stat-item,
[data-theme="dark"] .timer-container,
[data-theme="dark"] .workout-form,
[data-theme="dark"] .workout-table,
[data-theme="dark"] .info-section {
    background: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .stat-item:hover,
[data-theme="dark"] .timer-container:hover,
[data-theme="dark"] .workout-form:hover,
[data-theme="dark"] .info-section:hover {
    background: rgba(44, 62, 80, 0.4);
    border-color: rgba(255, 255, 255, 0.2);
}

[data-theme="dark"] .form-select,
[data-theme="dark"] .form-control {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .form-select:hover,
[data-theme="dark"] .form-control:hover {
    background-color: rgba(44, 62, 80, 0.4);
    border-color: rgba(255, 255, 255, 0.2);
}

[data-theme="dark"] .form-select:focus,
[data-theme="dark"] .form-control:focus {
    background-color: rgba(44, 62, 80, 0.5);
    border-color: #3498db;
}

[data-theme="dark"] .form-label {
    color: #ecf0f1;
}

[data-theme="dark"] .form-text {
    color: #bdc3c7;
}

[data-theme="dark"] .workout-table tbody td {
    color: #ecf0f1;
}

[data-theme="dark"] input[readonly] {
    background-color: rgba(44, 62, 80, 0.3) !important;
}

/* Enhanced Table Header */
.workout-list-header {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
    padding: 0.5rem 0;
    border-bottom: 2px solid rgba(52, 152, 219, 0.2);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.workout-list-header i {
    color: #3498db;
    font-size: 1.3rem;
}

[data-theme="dark"] .workout-list-header {
    color: #ecf0f1;
    border-bottom-color: rgba(52, 152, 219, 0.3);
}

[data-theme="dark"] .workout-list-header i {
    color: #3498db;
}

[data-theme="dark"] .workout-table thead th {
    color: #ffffff;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    background: linear-gradient(45deg, #3498db40, #2ecc7140);
}

[data-theme="dark"] .workout-table thead th::after {
    background: linear-gradient(45deg, #3498db70, #2ecc7170);
    height: 3px;
}

[data-theme="dark"] .workout-table tbody td {
    color: #ecf0f1;
    font-weight: 500;
}

.btn-danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border: none;
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
}

/* Dark mode styles for delete button */
[data-theme="dark"] .btn-danger {
    background: linear-gradient(135deg, #c0392b, #e74c3c);
    color: #ffffff;
}

[data-theme="dark"] .btn-danger:hover {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
}
</style>

<div class="container py-4">
    <div class="row">
        <!-- Workout Summary -->
        <div class="col-md-4">
            <!-- Workout Timer (Now First) -->
            <div class="workout-card timer-card fade-in">
                <div class="card-body">
                    <h4 class="mb-4">
                        <i class="bi bi-stopwatch me-2 text-primary"></i>
                        Workout Timer
                    </h4>
                    <div class="timer-container">
                        <div class="timer-display" id="timer">00:00:00</div>
                        <div class="timer-controls">
                            <button class="btn btn-start" id="startTimer">
                                <i class="bi bi-play-fill"></i>Start
                            </button>
                            <button class="btn btn-pause" id="pauseTimer">
                                <i class="bi bi-pause-fill"></i>Pause
                            </button>
                            <button class="btn btn-reset" id="resetTimer">
                                <i class="bi bi-arrow-counterclockwise"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Summary (Now Second) -->
            <div class="workout-card summary-card fade-in">
                <div class="card-body">
                    <h4 class="mb-4">
                        <i class="bi bi-activity me-2 text-primary"></i>
                        Today's Summary
                    </h4>
                    <div class="stat-item duration-stat text-center">
                        <i class="bi bi-stopwatch-fill"></i>
                        <h6>Total Duration</h6>
                        <h3><?php echo $total_duration; ?> <small>min</small></h3>
                    </div>
                    <div class="stat-item calories-stat text-center">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <h6>Calories Burned</h6>
                        <h3><?php echo $total_calories; ?> <small>cal</small></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workout Logger -->
        <div class="col-md-8">
            <div class="workout-card logger-card fade-in">
                <div class="card-body">
                    <h4 class="mb-4">
                        <i class="bi bi-journal-plus me-2 text-primary"></i>
                        Log Workout
                    </h4>
                    <?php echo $message; ?>
                    
                    <form method="POST" action="" class="workout-form">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="workout_type" class="form-label">Activity Type</label>
                                <select class="form-select" id="workout_type" name="workout_type" required>
                                    <option value="">Select activity...</option>
                                    
                                    <optgroup label="Cardio Exercises">
                                        <option value="walking_light" data-met="2.5">Walking (light pace)</option>
                                        <option value="walking" data-met="3.5">Walking (moderate pace)</option>
                                        <option value="walking_brisk" data-met="4.5">Walking (brisk pace)</option>
                                        <option value="jogging" data-met="7.0">Jogging</option>
                                        <option value="running" data-met="8.0">Running (moderate)</option>
                                        <option value="running_fast" data-met="11.0">Running (fast pace)</option>
                                        <option value="cycling_light" data-met="4.0">Cycling (light effort)</option>
                                        <option value="cycling" data-met="7.0">Cycling (moderate)</option>
                                        <option value="cycling_vigorous" data-met="10.0">Cycling (vigorous)</option>
                                        <option value="swimming_leisure" data-met="4.0">Swimming (leisure)</option>
                                        <option value="swimming" data-met="6.0">Swimming (moderate)</option>
                                        <option value="swimming_fast" data-met="8.0">Swimming (fast)</option>
                                        <option value="elliptical" data-met="5.0">Elliptical Trainer</option>
                                        <option value="stair_climbing" data-met="7.0">Stair Climbing</option>
                                        <option value="rowing" data-met="6.0">Rowing Machine</option>
                                        <option value="jumping_rope" data-met="10.0">Jumping Rope</option>
                                    </optgroup>

                                    <optgroup label="Strength Training">
                                        <option value="weight_training_light" data-met="3.0">Weight Training (light)</option>
                                        <option value="weight_training" data-met="4.0">Weight Training (moderate)</option>
                                        <option value="weight_training_vigorous" data-met="6.0">Weight Training (vigorous)</option>
                                        <option value="bodyweight_exercise" data-met="3.5">Bodyweight Exercises</option>
                                        <option value="circuit_training" data-met="7.0">Circuit Training</option>
                                        <option value="crossfit" data-met="8.0">CrossFit</option>
                                        <option value="hiit" data-met="8.5">HIIT</option>
                                    </optgroup>

                                    <optgroup label="Sports">
                                        <option value="basketball_casual" data-met="4.5">Basketball (casual)</option>
                                        <option value="basketball" data-met="6.5">Basketball (competitive)</option>
                                        <option value="football" data-met="7.0">Football</option>
                                        <option value="tennis" data-met="7.0">Tennis</option>
                                        <option value="volleyball" data-met="4.0">Volleyball</option>
                                        <option value="badminton" data-met="5.5">Badminton</option>
                                        <option value="table_tennis" data-met="4.0">Table Tennis</option>
                                        <option value="soccer" data-met="7.0">Soccer</option>
                                        <option value="cricket" data-met="5.0">Cricket</option>
                                    </optgroup>

                                    <optgroup label="Mind & Body">
                                        <option value="yoga_light" data-met="2.0">Yoga (light)</option>
                                        <option value="yoga" data-met="2.5">Yoga (moderate)</option>
                                        <option value="yoga_power" data-met="4.0">Power Yoga</option>
                                        <option value="pilates" data-met="3.0">Pilates</option>
                                        <option value="stretching" data-met="2.3">Stretching</option>
                                        <option value="tai_chi" data-met="3.0">Tai Chi</option>
                                    </optgroup>

                                    <optgroup label="Dance & Aerobics">
                                        <option value="dancing_casual" data-met="3.5">Dancing (casual)</option>
                                        <option value="dancing" data-met="4.5">Dancing (moderate)</option>
                                        <option value="dancing_vigorous" data-met="6.0">Dancing (vigorous)</option>
                                        <option value="zumba" data-met="6.0">Zumba</option>
                                        <option value="aerobics_low" data-met="4.0">Aerobics (low impact)</option>
                                        <option value="aerobics_high" data-met="7.0">Aerobics (high impact)</option>
                                    </optgroup>

                                    <optgroup label="Outdoor Activities">
                                        <option value="hiking" data-met="5.3">Hiking</option>
                                        <option value="rock_climbing" data-met="7.5">Rock Climbing</option>
                                        <option value="kayaking" data-met="5.0">Kayaking</option>
                                        <option value="surfing" data-met="3.0">Surfing</option>
                                        <option value="gardening" data-met="3.8">Gardening</option>
                                    </optgroup>

                                    <optgroup label="Martial Arts">
                                        <option value="karate" data-met="6.0">Karate</option>
                                        <option value="kickboxing" data-met="7.5">Kickboxing</option>
                                        <option value="judo" data-met="6.5">Judo</option>
                                        <option value="boxing" data-met="7.8">Boxing</option>
                                    </optgroup>
                                </select>
                                <div class="form-text">Select your activity type - MET values vary based on intensity</div>
                            </div>
                            <div class="col-md-4">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" min="1" required>
                                <div class="form-text">Enter workout duration in minutes</div>
                            </div>
                            <div class="col-md-4">
                                <label for="calories_burned" class="form-label">Calories Burned</label>
                                <input type="number" class="form-control" id="calories_burned" name="calories_burned" readonly>
                                <div class="form-text">Automatically calculated based on activity and duration</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Log Workout</button>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-dumbbell me-2"></i>Today's Workouts
                        </h4>
                        <?php if (!empty($workouts)): ?>
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete all workout logs for today? This action cannot be undone.');">
                                <button type="submit" name="delete_all_workouts" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-2"></i>Delete All
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table workout-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Activity</th>
                                    <th>Duration</th>
                                    <th>Calories</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($workouts)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="bi bi-calendar-x me-2"></i>
                                            No workouts logged today
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($workouts as $workout): ?>
                                        <?php
                                        $category = '';
                                        if (strpos($workout['workout_type'], 'walking') !== false || 
                                            strpos($workout['workout_type'], 'running') !== false || 
                                            strpos($workout['workout_type'], 'cycling') !== false) {
                                            $category = 'cardio';
                                        } elseif (strpos($workout['workout_type'], 'weight') !== false || 
                                                strpos($workout['workout_type'], 'training') !== false) {
                                            $category = 'strength';
                                        } elseif (strpos($workout['workout_type'], 'yoga') !== false || 
                                                strpos($workout['workout_type'], 'pilates') !== false) {
                                            $category = 'mind-body';
                                        } else {
                                            $category = 'sports';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-clock me-1 text-muted"></i>
                                                <?php echo date('H:i', strtotime($workout['date_logged'])); ?>
                                            </td>
                                            <td>
                                                <span class="activity-badge <?php echo $category; ?>">
                                                    <?php echo ucwords(str_replace('_', ' ', $workout['workout_type'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="bi bi-stopwatch me-1"></i>
                                                <?php echo $workout['duration']; ?> min
                                            </td>
                                            <td>
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                <?php echo $workout['calories_burned']; ?> cal
                                            </td>
                                            <td>
                                                <form method="POST" action="includes/delete_workout.php" class="d-inline delete-workout-form">
                                                    <input type="hidden" name="workout_id" value="<?php echo $workout['workout_id']; ?>">
                                                    <button type="submit" class="delete-btn" title="Delete workout">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="workout-card info-card fade-in">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        About Calorie Calculations
                    </h5>
                    <div class="info-section">
                        <p class="text-muted small mb-2">
                            Calories burned are calculated using MET (Metabolic Equivalent of Task) values. 
                            The calculation takes into account:
                        </p>
                        <ul class="text-muted small mb-2">
                            <li>
                                <i class="bi bi-person-circle"></i>
                                Your current weight: <?php echo $user_weight ? $user_weight . ' kg' : 'Not set'; ?>
                            </li>
                            <li>
                                <i class="bi bi-activity"></i>
                                Activity intensity (MET value)
                            </li>
                            <li>
                                <i class="bi bi-clock"></i>
                                Duration of the activity
                            </li>
                        </ul>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-calculator me-1"></i>
                            Formula: Calories = MET × weight (kg) × duration (hours)
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!$user['is_premium']): ?>
                <div class="workout-card premium-promo-card fade-in mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-2">
                                    <i class="fas fa-crown text-warning me-2"></i>
                                    Upgrade to Premium
                                </h4>
                                <p class="mb-0">Get access to advanced analytics, custom workout plans, and more!</p>
                            </div>
                            <a href="premium.php" class="btn premium-btn">Upgrade Now</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($user['is_premium']): ?>
                <!-- Premium Analytics Section -->
                <div class="workout-card analytics-card fade-in mb-4">
                    <div class="card-body">
                        <h4 class="mb-4">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Premium Analytics
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stat-item">
                                    <h6>Weekly Progress</h6>
                                    <canvas id="weeklyProgress"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-item">
                                    <h6>Workout Distribution</h6>
                                    <canvas id="workoutDistribution"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Data Button -->
                <div class="text-end mb-4">
                    <a href="export_workouts.php" class="btn btn-outline-primary">
                        <i class="fas fa-file-export me-2"></i>
                        Export Workout Data
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Timer functionality
let timerInterval;
let seconds = 0;
let isRunning = false;

function formatTime(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

document.getElementById('startTimer').addEventListener('click', function() {
    if (!isRunning) {
        isRunning = true;
        timerInterval = setInterval(function() {
            seconds++;
            document.getElementById('timer').textContent = formatTime(seconds);
            // Auto-update duration field if timer is running
            document.getElementById('duration').value = Math.floor(seconds / 60);
            calculateCalories(); // Recalculate calories when duration updates
        }, 1000);
    }
});

document.getElementById('pauseTimer').addEventListener('click', function() {
    isRunning = false;
    clearInterval(timerInterval);
});

document.getElementById('resetTimer').addEventListener('click', function() {
    isRunning = false;
    clearInterval(timerInterval);
    seconds = 0;
    document.getElementById('timer').textContent = formatTime(seconds);
    document.getElementById('duration').value = ''; // Clear duration field
    calculateCalories(); // Recalculate calories
});

// Enhanced calorie calculation
const userWeight = <?php echo $user_weight ?? 70; ?>; // Use user's weight or default to 70kg

function calculateCalories() {
    const workoutSelect = document.getElementById('workout_type');
    const duration = parseInt(document.getElementById('duration').value) || 0;
    const caloriesField = document.getElementById('calories_burned');
    
    if (workoutSelect.value && duration > 0) {
        const selectedOption = workoutSelect.options[workoutSelect.selectedIndex];
        const met = parseFloat(selectedOption.dataset.met);
        
        // Formula: Calories = MET × weight (kg) × duration (hours)
        // Convert duration from minutes to hours
        const durationHours = duration / 60;
        const calories = Math.round(met * userWeight * durationHours);
        
        caloriesField.value = calories;
    } else {
        caloriesField.value = '';
    }
}

// Add event listeners for real-time calculation
document.getElementById('workout_type').addEventListener('change', calculateCalories);
document.getElementById('duration').addEventListener('input', calculateCalories);

// Initialize tooltips
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Add validation for duration
document.getElementById('duration').addEventListener('input', function(e) {
    const value = parseInt(e.target.value);
    if (value > 1440) { // More than 24 hours
        e.target.value = 1440;
        alert('Duration cannot exceed 24 hours (1440 minutes)');
    }
    calculateCalories();
});

// Sync timer with duration input
document.getElementById('duration').addEventListener('input', function(e) {
    const minutes = parseInt(e.target.value) || 0;
    if (!isRunning) { // Only update if timer is not running
        seconds = minutes * 60;
        document.getElementById('timer').textContent = formatTime(seconds);
    }
});

// Add confirmation dialog for workout deletion
document.querySelectorAll('.delete-workout-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to delete this workout?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php if ($user['is_premium']): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Weekly Progress Chart
const weeklyProgressCtx = document.getElementById('weeklyProgress').getContext('2d');
const weeklyProgressChart = new Chart(weeklyProgressCtx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Calories Burned',
            data: [
                <?php
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $stmt = $conn->prepare("
                        SELECT COALESCE(SUM(calories_burned), 0) as total_calories
                        FROM workout_logs
                        WHERE user_id = ? AND DATE(date_logged) = ?
                    ");
                    $stmt->bind_param("is", $user_id, $date);
                    $stmt->execute();
                    $result = $stmt->get_result()->fetch_assoc();
                    echo $result['total_calories'] . ",";
                }
                ?>
            ],
            borderColor: '#3498db',
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(52, 152, 219, 0.1)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Workout Distribution Chart
const workoutDistributionCtx = document.getElementById('workoutDistribution').getContext('2d');
const workoutDistributionChart = new Chart(workoutDistributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Cardio', 'Strength', 'Sports', 'Mind & Body'],
        datasets: [{
            data: [
                <?php
                // Cardio workouts
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM workout_logs
                    WHERE user_id = ? AND (
                        workout_type LIKE '%walking%' OR
                        workout_type LIKE '%running%' OR
                        workout_type LIKE '%cycling%'
                    )
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                echo $stmt->get_result()->fetch_assoc()['count'] . ",";

                // Strength workouts
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM workout_logs
                    WHERE user_id = ? AND (
                        workout_type LIKE '%weight%' OR
                        workout_type LIKE '%training%'
                    )
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                echo $stmt->get_result()->fetch_assoc()['count'] . ",";

                // Sports workouts
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM workout_logs
                    WHERE user_id = ? AND (
                        workout_type LIKE '%basketball%' OR
                        workout_type LIKE '%football%' OR
                        workout_type LIKE '%tennis%'
                    )
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                echo $stmt->get_result()->fetch_assoc()['count'] . ",";

                // Mind & Body workouts
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM workout_logs
                    WHERE user_id = ? AND (
                        workout_type LIKE '%yoga%' OR
                        workout_type LIKE '%pilates%' OR
                        workout_type LIKE '%stretching%'
                    )
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                echo $stmt->get_result()->fetch_assoc()['count'];
                ?>
            ],
            backgroundColor: [
                '#3498db',
                '#2ecc71',
                '#e67e22',
                '#9b59b6'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
<?php endif; ?> 