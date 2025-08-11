<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$message = '';
$messageType = '';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
        $gender = sanitizeInput($_POST['gender']);
        $height = !empty($_POST['height']) ? floatval($_POST['height']) : null;
        $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
        $activity_level = sanitizeInput($_POST['activity_level']);
        $fitness_goal = sanitizeInput($_POST['fitness_goal']);

        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, age = ?, gender = ?, height = ?, weight = ?, activity_level = ?, fitness_goal = ? WHERE user_id = ?");
        $stmt->bind_param("ssisssssi", $username, $email, $age, $gender, $height, $weight, $activity_level, $fitness_goal, $user_id);
        
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating profile: " . $conn->error;
            $messageType = "danger";
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $message = "Password updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating password.";
                    $messageType = "danger";
                }
            } else {
                $message = "New passwords do not match.";
                $messageType = "danger";
            }
        } else {
            $message = "Current password is incorrect.";
            $messageType = "danger";
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT DATE(date_logged)) as total_workout_days,
        SUM(calories_burned) as total_calories_burned
    FROM workout_logs 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workout_stats = $stmt->get_result()->fetch_assoc();

$conn->close();
?>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Information -->
        <div class="col-md-8">
            <div class="profile-card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i>Profile Information
                    </h3>
                    <form method="POST" action="">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-person me-2"></i>Username
                                </label>
                                <input type="text" class="form-control modern-input" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control modern-input" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-calendar me-2"></i>Age
                                </label>
                                <input type="number" class="form-control modern-input" name="age" value="<?php echo $user['age']; ?>" min="1" max="120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-gender-ambiguous me-2"></i>Gender
                                </label>
                                <select class="form-select modern-select" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-rulers me-2"></i>Height (m)
                                </label>
                                <input type="number" class="form-control modern-input" name="height" value="<?php echo $user['height']; ?>" step="0.01" min="0.5" max="3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-speedometer2 me-2"></i>Weight (kg)
                                </label>
                                <input type="number" class="form-control modern-input" name="weight" value="<?php echo $user['weight']; ?>" step="0.1" min="20" max="300">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-lightning-charge me-2"></i>Activity Level
                                </label>
                                <select class="form-select modern-select" name="activity_level">
                                    <option value="sedentary" <?php echo $user['activity_level'] === 'sedentary' ? 'selected' : ''; ?>>Sedentary</option>
                                    <option value="light" <?php echo $user['activity_level'] === 'light' ? 'selected' : ''; ?>>Lightly Active</option>
                                    <option value="moderate" <?php echo $user['activity_level'] === 'moderate' ? 'selected' : ''; ?>>Moderately Active</option>
                                    <option value="active" <?php echo $user['activity_level'] === 'active' ? 'selected' : ''; ?>>Very Active</option>
                                    <option value="very_active" <?php echo $user['activity_level'] === 'very_active' ? 'selected' : ''; ?>>Extra Active</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-trophy me-2"></i>Fitness Goal
                                </label>
                                <select class="form-select modern-select" name="fitness_goal">
                                    <option value="weight_loss" <?php echo $user['fitness_goal'] === 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                                    <option value="maintenance" <?php echo $user['fitness_goal'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="muscle_gain" <?php echo $user['fitness_goal'] === 'muscle_gain' ? 'selected' : ''; ?>>Muscle Gain</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4">
                            <i class="bi bi-check-circle me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="profile-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">
                        <i class="bi bi-shield-lock-fill text-primary me-2"></i>Change Password
                    </h3>
                    <form method="POST" action="">
                        <input type="hidden" name="update_password" value="1">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="bi bi-key me-2"></i>Current Password
                                </label>
                                <input type="password" class="form-control modern-input" name="current_password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-lock me-2"></i>New Password
                                </label>
                                <input type="password" class="form-control modern-input" name="new_password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>Confirm New Password
                                </label>
                                <input type="password" class="form-control modern-input" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4">
                            <i class="bi bi-shield-check me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats and Quick Info -->
        <div class="col-md-4">
            <!-- Profile Summary -->
            <div class="profile-card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <div class="profile-avatar mb-3">
                        <i class="bi bi-person-circle display-1 text-primary"></i>
                    </div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h4>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
                    <hr class="styled-hr">
                    <div class="row text-start g-3">
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar-event me-2"></i>Age
                                </small>
                                <span class="info-value"><?php echo $user['age'] ?? 'Not set'; ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted d-block">
                                    <i class="bi bi-gender-ambiguous me-2"></i>Gender
                                </small>
                                <span class="info-value"><?php echo ucfirst($user['gender']) ?? 'Not set'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fitness Stats -->
            <div class="profile-card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-graph-up text-primary me-2"></i>Fitness Stats
                    </h5>
                    <div class="stat-item mb-4">
                        <small class="text-muted d-block">
                            <i class="bi bi-clipboard-data me-2"></i>BMI
                        </small>
                        <?php if ($user['height'] && $user['weight']): ?>
                            <?php 
                            $bmi = calculateBMI($user['weight'], $user['height']);
                            $bmiCategory = getBMICategory($bmi);
                            ?>
                            <h4 class="stat-value mb-1"><?php echo number_format($bmi, 1); ?></h4>
                            <small class="text-muted"><?php echo $bmiCategory[0]; ?></small>
                        <?php else: ?>
                            <span class="text-muted">Update height and weight</span>
                        <?php endif; ?>
                    </div>
                    <div class="stat-item mb-4">
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar-check me-2"></i>Total Workout Days
                        </small>
                        <h4 class="stat-value"><?php echo number_format($workout_stats['total_workout_days']); ?></h4>
                    </div>
                    <div class="stat-item mb-4">
                        <small class="text-muted d-block">
                            <i class="bi bi-fire me-2"></i>Total Calories Burned
                        </small>
                        <h4 class="stat-value"><?php echo number_format($workout_stats['total_calories_burned']); ?></h4>
                    </div>
                    <div class="stat-item">
                        <small class="text-muted d-block">
                            <i class="bi bi-lightning me-2"></i>Daily Calorie Goal
                        </small>
                        <?php if ($user['weight'] && $user['height'] && $user['age'] && $user['gender'] && $user['activity_level']): ?>
                            <?php 
                            $daily_calories = calculateDailyCalories(
                                $user['weight'],
                                $user['height'],
                                $user['age'],
                                $user['gender'],
                                $user['activity_level']
                            );
                            ?>
                            <h4 class="stat-value"><?php echo number_format($daily_calories); ?></h4>
                        <?php else: ?>
                            <span class="text-muted">Complete your profile</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Card Styles */
.profile-card {
    background: white;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
}

/* Form Controls */
.modern-input,
.modern-select {
    border-radius: 12px;
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.modern-input:focus,
.modern-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
}

/* Profile Avatar */
.profile-avatar {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 50%;
    transition: all 0.3s ease;
}

.profile-avatar:hover {
    transform: scale(1.05);
    background: rgba(52, 152, 219, 0.2);
}

/* Styled HR */
.styled-hr {
    border: none;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.1), transparent);
    margin: 1.5rem 0;
}

/* Info Items */
.info-item {
    padding: 0.75rem;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.info-item:hover {
    background: rgba(52, 152, 219, 0.15);
    transform: translateY(-2px);
}

.info-value {
    font-weight: 500;
    color: #2c3e50;
}

/* Stat Items */
.stat-item {
    padding: 1rem;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 12px;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: rgba(52, 152, 219, 0.15);
    transform: translateY(-2px);
}

.stat-value {
    color: #2c3e50;
    margin: 0.5rem 0 0;
    font-size: 1.5rem;
}

/* Button Styles */
.btn-primary {
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}

/* Dark Mode Styles */
[data-theme="dark"] .profile-card {
    background: rgba(44, 62, 80, 0.2);
}

[data-theme="dark"] .modern-input,
[data-theme="dark"] .modern-select {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .modern-input:focus,
[data-theme="dark"] .modern-select:focus {
    border-color: #3498db;
    background-color: rgba(44, 62, 80, 0.4);
}

[data-theme="dark"] .modern-select option {
    background-color: #2c3e50;
    color: #ecf0f1;
}

[data-theme="dark"] .info-item,
[data-theme="dark"] .stat-item {
    background: rgba(52, 152, 219, 0.1);
}

[data-theme="dark"] .info-item:hover,
[data-theme="dark"] .stat-item:hover {
    background: rgba(52, 152, 219, 0.2);
}

[data-theme="dark"] .info-value,
[data-theme="dark"] .stat-value {
    color: #ecf0f1;
}

[data-theme="dark"] .styled-hr {
    background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.1), transparent);
}

[data-theme="dark"] .text-muted {
    color: #95a5a6 !important;
}

[data-theme="dark"] h3,
[data-theme="dark"] h4,
[data-theme="dark"] h5,
[data-theme="dark"] .card-title {
    color: #ecf0f1;
}
</style> 