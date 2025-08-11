<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Check if user is premium
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT is_premium FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user['is_premium']) {
    header("Location: premium.php");
    exit();
}

// Get user's scheduled workouts
$stmt = $conn->prepare("SELECT * FROM scheduled_workouts WHERE user_id = ? ORDER BY scheduled_date ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$scheduled_workouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Available workout types
$workout_types = [
    'strength' => ['icon' => 'dumbbell', 'color' => '#e74c3c'],
    'cardio' => ['icon' => 'running', 'color' => '#3498db'],
    'flexibility' => ['icon' => 'child', 'color' => '#2ecc71'],
    'hiit' => ['icon' => 'bolt', 'color' => '#f1c40f'],
    'recovery' => ['icon' => 'heart', 'color' => '#9b59b6']
];
?>

<style>
.schedule-page {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.1), rgba(52, 152, 219, 0.1));
    padding: 2rem 0;
    min-height: calc(100vh - 60px);
}

.calendar-container {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.calendar-day {
    aspect-ratio: 1;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 0.5rem;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.calendar-day:hover {
    background: rgba(241, 196, 15, 0.1);
    border-color: #f1c40f;
}

.calendar-day.has-workout {
    background: rgba(46, 204, 113, 0.1);
    border-color: #2ecc71;
}

.day-number {
    font-size: 1.1rem;
    font-weight: 600;
}

.workout-indicator {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: white;
}

.workout-list {
    margin-top: 2rem;
}

.workout-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.workout-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    margin-right: 1rem;
}

.add-workout-btn {
    background: linear-gradient(135deg, #f1c40f, #e67e22);
    border: none;
    padding: 1rem 2rem;
    color: white;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-bottom: 2rem;
}

.add-workout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(241, 196, 15, 0.3);
    color: white;
}

.workout-modal .modal-content {
    border-radius: 20px;
    overflow: hidden;
}

.workout-type-select {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.type-option {
    border: 2px solid #eee;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.type-option:hover {
    border-color: #f1c40f;
    background: rgba(241, 196, 15, 0.1);
}

.type-option.selected {
    border-color: #f1c40f;
    background: #f1c40f;
    color: white;
}

[data-theme="dark"] .calendar-container,
[data-theme="dark"] .workout-card {
    background: #2c3e50;
    color: white;
}

[data-theme="dark"] .calendar-day {
    border-color: #34495e;
}

[data-theme="dark"] .type-option {
    border-color: #34495e;
}
</style>

<div class="schedule-page">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt me-2"></i>Workout Schedule</h2>
            <button class="add-workout-btn" data-bs-toggle="modal" data-bs-target="#addWorkoutModal">
                <i class="fas fa-plus me-2"></i>Schedule Workout
            </button>
        </div>

        <div class="calendar-container">
            <div class="calendar-header">
                <button class="btn btn-outline-primary"><i class="fas fa-chevron-left"></i></button>
                <h4>September 2023</h4>
                <button class="btn btn-outline-primary"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="calendar-grid">
                <?php
                $days = range(1, 30);
                foreach ($days as $day):
                    $hasWorkout = array_filter($scheduled_workouts, function($workout) use ($day) {
                        return date('j', strtotime($workout['scheduled_date'])) == $day;
                    });
                ?>
                <div class="calendar-day <?php echo !empty($hasWorkout) ? 'has-workout' : ''; ?>">
                    <div class="day-number"><?php echo $day; ?></div>
                    <?php if (!empty($hasWorkout)): ?>
                        <div class="workout-indicator" style="background: <?php echo $workout_types[array_values($hasWorkout)[0]['workout_type']]['color']; ?>">
                            <i class="fas fa-<?php echo $workout_types[array_values($hasWorkout)[0]['workout_type']]['icon']; ?>"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="workout-list">
            <h4 class="mb-3">Upcoming Workouts</h4>
            <?php foreach ($scheduled_workouts as $workout): ?>
            <div class="workout-card">
                <div class="d-flex align-items-center">
                    <div class="workout-icon" style="background: <?php echo $workout_types[$workout['workout_type']]['color']; ?>">
                        <i class="fas fa-<?php echo $workout_types[$workout['workout_type']]['icon']; ?>"></i>
                    </div>
                    <div>
                        <h5 class="mb-1"><?php echo ucfirst($workout['workout_type']); ?> Workout</h5>
                        <div class="text-muted"><?php echo date('F j, Y', strtotime($workout['scheduled_date'])); ?></div>
                    </div>
                </div>
                <button class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add Workout Modal -->
<div class="modal fade" id="addWorkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule New Workout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleWorkoutForm">
                    <div class="mb-3">
                        <label class="form-label">Workout Type</label>
                        <div class="workout-type-select">
                            <?php foreach ($workout_types as $type => $details): ?>
                            <div class="type-option" data-type="<?php echo $type; ?>">
                                <i class="fas fa-<?php echo $details['icon']; ?> mb-2"></i>
                                <div><?php echo ucfirst($type); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Schedule Workout</button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.type-option').forEach(option => {
    option.addEventListener('click', () => {
        document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
    });
});
</script> 