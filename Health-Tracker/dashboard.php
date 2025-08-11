<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Get user data
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get today's calories
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT SUM(f.calories * fl.servings) as total_calories
    FROM food_logs fl
    JOIN food_items f ON fl.food_id = f.food_id
    WHERE fl.user_id = ? AND DATE(fl.date_logged) = ?
");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$calories = $stmt->get_result()->fetch_assoc()['total_calories'] ?? 0;

// Get today's workout
$stmt = $conn->prepare("
    SELECT SUM(calories_burned) as total_burned
    FROM workout_logs
    WHERE user_id = ? AND DATE(date_logged) = ?
");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$calories_burned = $stmt->get_result()->fetch_assoc()['total_burned'] ?? 0;

// Get last sleep record
$stmt = $conn->prepare("
    SELECT * FROM sleep_logs
    WHERE user_id = ?
    ORDER BY sleep_id DESC LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sleep = $stmt->get_result()->fetch_assoc();

$conn->close();
?>

<style>
/* Dashboard specific styles */
.welcome-section {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
    padding: 2rem;
    border-radius: 15px;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    animation: fadeInUp 0.5s ease;
}

.welcome-section h2 {
    margin: 0;
    font-weight: 600;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    height: 100%;
    animation: fadeIn 0.5s ease;
    position: relative;
    overflow: hidden;
}

[data-theme="dark"] .stat-card {
    background: var(--bg-card);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.stat-card h6 {
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-card h6 i {
    font-size: 1.1rem;
    opacity: 0.8;
}

.stat-card h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--primary-dark);
    position: relative;
    z-index: 1;
}

[data-theme="dark"] .stat-card h3 {
    color: var(--text-light);
}

.stat-card small {
    color: var(--text-muted);
    font-size: 0.85rem;
    font-weight: 500;
}

.stat-card::after {
    content: '';
    position: absolute;
    right: -20px;
    top: -20px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: var(--primary-light);
    opacity: 0.1;
    z-index: 0;
}

[data-theme="dark"] .stat-card::after {
    background: var(--primary-dark);
    opacity: 0.15;
}

.stat-card.calories-consumed::after {
    background: #3498db;
}

.stat-card.calories-burned::after {
    background: #2ecc71;
}

.stat-card.net-calories::after {
    background: #f1c40f;
}

.stat-card.bmi::after {
    background: #9b59b6;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .welcome-section {
        padding: 1.5rem;
        text-align: center;
    }

    .stat-card {
        margin-bottom: 1rem;
    }

    .stat-card h3 {
        font-size: 1.8rem;
    }
}
</style>

<div class="container py-4">
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                <p class="mb-0">Here's your fitness summary for today</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="profile.php" class="btn btn-light">
                    <i class="bi bi-person-circle"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Enhanced Summary Cards -->
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card calories-consumed">
                <h6><i class="bi bi-pie-chart-fill"></i> Calories Consumed</h6>
                <h3><?php echo number_format($calories); ?></h3>
                <small class="text-muted">Today's Intake</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card calories-burned">
                <h6><i class="bi bi-activity"></i> Calories Burned</h6>
                <h3><?php echo number_format($calories_burned); ?></h3>
                <small class="text-muted">From Workouts</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card net-calories">
                <h6><i class="bi bi-lightning-charge-fill"></i> Net Calories</h6>
                <h3><?php echo number_format($calories - $calories_burned); ?></h3>
                <small class="text-muted">Consumed - Burned</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card bmi">
                <h6><i class="bi bi-heart-pulse-fill"></i> BMI</h6>
                <?php if ($user['height'] && $user['weight']): ?>
                    <?php $bmi = calculateBMI($user['weight'], $user['height']); ?>
                    <h3><?php echo number_format($bmi, 1); ?></h3>
                    <small class="text-muted"><?php echo getBMICategory($bmi)[0]; ?></small>
                <?php else: ?>
                    <p class="text-muted">Update your profile</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html> 