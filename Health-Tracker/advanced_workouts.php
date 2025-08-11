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

// Workout programs
$workout_programs = [
    'strength' => [
        'title' => 'Power Building Program',
        'duration' => '12 weeks',
        'level' => 'Advanced',
        'description' => 'Build strength and muscle with this comprehensive powerbuilding program',
        'workouts_per_week' => 5,
        'icon' => 'dumbbell',
        'color' => '#e74c3c'
    ],
    'hiit' => [
        'title' => 'Elite HIIT Series',
        'duration' => '8 weeks',
        'level' => 'Advanced',
        'description' => 'High-intensity interval training for maximum fat burn and conditioning',
        'workouts_per_week' => 4,
        'icon' => 'bolt',
        'color' => '#f1c40f'
    ],
    'crossfit' => [
        'title' => 'CrossFit Elite',
        'duration' => '16 weeks',
        'level' => 'Advanced',
        'description' => 'Comprehensive CrossFit program for improved overall fitness',
        'workouts_per_week' => 6,
        'icon' => 'fire',
        'color' => '#3498db'
    ],
    'bodybuilding' => [
        'title' => 'Hypertrophy Master',
        'duration' => '12 weeks',
        'level' => 'Advanced',
        'description' => 'Scientific approach to muscle building and aesthetics',
        'workouts_per_week' => 5,
        'icon' => 'user-ninja',
        'color' => '#9b59b6'
    ]
];
?>

<style>
.advanced-page {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.1), rgba(52, 152, 219, 0.1));
    padding: 2rem 0;
    min-height: calc(100vh - 60px);
}

.program-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.program-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.program-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.program-header {
    padding: 2rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.program-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="0" cy="0" r="20" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: 50px;
    opacity: 0.3;
}

.program-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.program-body {
    padding: 2rem;
}

.program-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin: 1.5rem 0;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 10px;
}

.stat-value {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
}

.program-btn {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.program-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.level-badge {
    display: inline-block;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    background: rgba(255, 255, 255, 0.2);
    margin-bottom: 1rem;
}

[data-theme="dark"] .program-card {
    background: #2c3e50;
    color: white;
}

[data-theme="dark"] .stat-item {
    background: rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .stat-label {
    color: #bdc3c7;
}
</style>

<div class="advanced-page">
    <div class="container">
        <div class="text-center mb-5">
            <h2><i class="fas fa-dumbbell me-2"></i>Advanced Workout Programs</h2>
            <p class="text-muted">Expert-designed programs for serious fitness enthusiasts</p>
        </div>

        <div class="program-grid">
            <?php foreach ($workout_programs as $type => $program): ?>
            <div class="program-card">
                <div class="program-header" style="background: <?php echo $program['color']; ?>">
                    <div class="program-icon">
                        <i class="fas fa-<?php echo $program['icon']; ?>"></i>
                    </div>
                    <div class="level-badge">
                        <i class="fas fa-star me-1"></i><?php echo $program['level']; ?>
                    </div>
                    <h3><?php echo $program['title']; ?></h3>
                    <p class="mb-0"><?php echo $program['description']; ?></p>
                </div>
                <div class="program-body">
                    <div class="program-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $program['duration']; ?></div>
                            <div class="stat-label">Duration</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $program['workouts_per_week']; ?></div>
                            <div class="stat-label">Workouts/Week</div>
                        </div>
                    </div>
                    <button class="program-btn" style="background: <?php echo $program['color']; ?>">
                        Start Program
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> 