<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$message = '';
$messageType = '';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get last 7 days sleep logs
$stmt = $conn->prepare("
    SELECT 
        sleep_id,
        user_id,
        sleep_start,
        sleep_end,
        quality,
        TIMESTAMPDIFF(HOUR, sleep_start, sleep_end) as sleep_hours,
        DATE(sleep_start) as sleep_date
    FROM sleep_logs 
    WHERE user_id = ? 
        AND sleep_start >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ORDER BY sleep_start DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$weekly_logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate weekly statistics
$total_sleep = 0;
$total_quality = 0;
$log_count = count($weekly_logs);
$sleep_data_by_date = [];

foreach ($weekly_logs as $log) {
    $total_sleep += $log['sleep_hours'];
    $total_quality += $log['quality'];
    $sleep_data_by_date[$log['sleep_date']] = $log;
}

$avg_sleep = $log_count > 0 ? round($total_sleep / $log_count, 1) : 0;
$avg_quality = $log_count > 0 ? round($total_quality / $log_count, 1) : 0;

// Handle sleep log deletion
if (isset($_POST['delete_sleep'])) {
    $sleep_id = intval($_POST['sleep_id']);
    $stmt = $conn->prepare("DELETE FROM sleep_logs WHERE sleep_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $sleep_id, $user_id);
    
    if ($stmt->execute()) {
        $message = "Sleep log deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting sleep log.";
        $messageType = "danger";
    }
}

// Handle form submission for new sleep log
if (isset($_POST['log_sleep'])) {
    $sleep_date = $_POST['sleep_date'];
    $sleep_time = $_POST['sleep_time'];
    $wake_time = $_POST['wake_time'];
    $quality = intval($_POST['quality']);
    
    // Combine date and time for sleep_start and sleep_end
    $sleep_start = date('Y-m-d H:i:s', strtotime("$sleep_date $sleep_time"));
    $wake_date = $sleep_time > $wake_time ? date('Y-m-d', strtotime("$sleep_date +1 day")) : $sleep_date;
    $sleep_end = date('Y-m-d H:i:s', strtotime("$wake_date $wake_time"));
    
    $stmt = $conn->prepare("INSERT INTO sleep_logs (user_id, sleep_start, sleep_end, quality) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $sleep_start, $sleep_end, $quality);
    
    if ($stmt->execute()) {
        $message = "Sleep logged successfully!";
        $messageType = "success";
    } else {
        $message = "Error logging sleep.";
        $messageType = "danger";
    }
}

// Get recent sleep logs
$stmt = $conn->prepare("
    SELECT 
        sleep_id,
        sleep_start,
        sleep_end,
        quality,
        TIMESTAMPDIFF(HOUR, sleep_start, sleep_end) as sleep_hours
    FROM sleep_logs 
    WHERE user_id = ? 
    ORDER BY sleep_start DESC 
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sleep_logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<style>
    /* Dark mode styles */
    [data-theme="dark"] .card {
        background: linear-gradient(145deg, rgba(44, 62, 80, 0.98), rgba(52, 73, 94, 0.98));
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        border-radius: 1.5rem;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    [data-theme="dark"] .card-body {
        color: #ecf0f1;
        padding: 2rem;
    }

    [data-theme="dark"] h3 {
        color: #ffffff;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
    }

    [data-theme="dark"] .form-label {
        color: #ecf0f1;
        font-weight: 500;
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background-color: rgba(236, 240, 241, 0.1);
        border: 2px solid rgba(52, 152, 219, 0.3);
        color: #ffffff;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-select:focus {
        background-color: rgba(236, 240, 241, 0.15);
        border-color: #3498db;
        box-shadow: 0 0 15px rgba(52, 152, 219, 0.3);
    }

    [data-theme="dark"] .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    }

    [data-theme="dark"] .btn-primary {
        background: linear-gradient(135deg, #3498db, #2980b9);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    [data-theme="dark"] .btn-danger {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        border: none;
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
    }

    [data-theme="dark"] .table {
        color: #ecf0f1;
    }

    [data-theme="dark"] .table thead th {
        border-bottom: 2px solid rgba(52, 152, 219, 0.3);
        color: #3498db;
        font-weight: 600;
        padding: 1rem;
    }

    [data-theme="dark"] .table td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1rem;
        vertical-align: middle;
    }

    [data-theme="dark"] .text-warning {
        color: #f1c40f !important;
    }

    .alert {
        border-radius: 1rem;
        border: none;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    [data-theme="dark"] .alert-success {
        background-color: rgba(46, 204, 113, 0.2);
        border: 1px solid rgba(46, 204, 113, 0.3);
        color: #2ecc71;
    }

    [data-theme="dark"] .alert-danger {
        background-color: rgba(231, 76, 60, 0.2);
        border: 1px solid rgba(231, 76, 60, 0.3);
        color: #e74c3c;
    }

    .star-rating {
        font-size: 1.2rem;
        letter-spacing: 2px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card {
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .btn-delete:hover i {
        animation: pulse 0.5s ease infinite;
    }

    .sleep-summary-card {
        background: linear-gradient(145deg, rgba(44, 62, 80, 0.98), rgba(52, 73, 94, 0.98));
        border: none;
        margin-bottom: 2rem;
    }

    .stat-box {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #3498db;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #ecf0f1;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .sleep-chart {
        padding: 1rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 1rem;
        margin-top: 1.5rem;
    }

    .day-bar {
        height: 20px;
        background: rgba(52, 152, 219, 0.5);
        border-radius: 10px;
        margin-bottom: 0.5rem;
        position: relative;
        transition: all 0.3s ease;
    }

    .day-bar:hover {
        background: rgba(52, 152, 219, 0.8);
    }

    .day-label {
        color: #ecf0f1;
        font-size: 0.85rem;
        margin-right: 1rem;
        width: 100px;
        display: inline-block;
    }

    .quality-stars {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #f1c40f;
        font-size: 0.8rem;
    }

    .sleep-time-tooltip {
        display: none;
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        top: -25px;
        left: 50%;
        transform: translateX(-50%);
        white-space: nowrap;
    }

    .day-bar {
        position: relative;
    }

    .day-bar:hover .sleep-time-tooltip {
        display: block;
    }

    .sleep-time-tooltip:after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 50%;
        transform: translateX(-50%);
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 4px solid rgba(0, 0, 0, 0.8);
    }
</style>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Weekly Sleep Summary -->
    <div class="card sleep-summary-card mb-4" data-theme="dark">
        <div class="card-body">
            <h3 class="mb-4"><i class="fas fa-chart-line me-2"></i>7-Day Sleep Summary</h3>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $avg_sleep; ?> hrs</div>
                        <div class="stat-label">Average Sleep Duration</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-box">
                        <div class="stat-value">
                            <?php
                            $stars = str_repeat('★', round($avg_quality));
                            $empty_stars = str_repeat('☆', 5 - round($avg_quality));
                            echo '<span class="text-warning">' . $stars . '</span>' . $empty_stars;
                            ?>
                        </div>
                        <div class="stat-label">Average Sleep Quality</div>
                    </div>
                </div>
            </div>

            <div class="sleep-chart">
                <?php
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $log = $sleep_data_by_date[$date] ?? null;
                    $width = $log ? ($log['sleep_hours'] / 12 * 100) : 0; // Assuming max 12 hours
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="day-label"><?php echo date('D, M j', strtotime($date)); ?></span>
                        <?php if ($log): ?>
                            <div class="day-bar flex-grow-1" style="width: <?php echo $width; ?>%">
                                <span class="quality-stars">
                                    <?php echo str_repeat('★', $log['quality']); ?>
                                </span>
                                <span class="sleep-time-tooltip">
                                    <?php echo formatTime($log['sleep_start']); ?> - <?php echo formatTime($log['sleep_end']); ?>
                                </span>
                            </div>
                            <span class="ms-2 text-light"><?php echo round($log['sleep_hours'], 1); ?>hrs</span>
                        <?php else: ?>
                            <div class="text-muted">No data</div>
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Log Sleep Form -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
                <div class="card-body">
                    <h3><i class="fas fa-moon me-2"></i>Log Sleep</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="log_sleep" value="1">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-calendar me-2"></i>Date</label>
                            <input type="date" class="form-control" name="sleep_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-bed me-2"></i>Sleep Time</label>
                            <input type="time" class="form-control" name="sleep_time" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-sun me-2"></i>Wake Time</label>
                            <input type="time" class="form-control" name="wake_time" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-star me-2"></i>Sleep Quality</label>
                            <select class="form-select" name="quality" required>
                                <option value="1">1 - Poor</option>
                                <option value="2">2 - Fair</option>
                                <option value="3">3 - Good</option>
                                <option value="4">4 - Very Good</option>
                                <option value="5">5 - Excellent</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Log Sleep
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Sleep Logs -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
                <div class="card-body">
                    <h3><i class="fas fa-history me-2"></i>Recent Sleep Logs</h3>
                    <?php if (empty($sleep_logs)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-bed fa-3x mb-3" style="color: rgba(52, 152, 219, 0.5);"></i>
                            <p class="text-muted">No sleep logs found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Sleep Time</th>
                                        <th>Wake Time</th>
                                        <th>Quality</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sleep_logs as $log): ?>
                                        <tr>
                                            <td><i class="fas fa-calendar-alt me-2"></i><?php echo date('M j, Y', strtotime($log['sleep_start'])); ?></td>
                                            <td><i class="fas fa-moon me-2"></i><?php echo formatTime($log['sleep_start']); ?></td>
                                            <td><i class="fas fa-sun me-2"></i><?php echo formatTime($log['sleep_end']); ?></td>
                                            <td>
                                                <div class="star-rating">
                                                    <?php
                                                    $stars = str_repeat('★', $log['quality']);
                                                    $empty_stars = str_repeat('☆', 5 - $log['quality']);
                                                    echo '<span class="text-warning">' . $stars . '</span>' . $empty_stars;
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sleep log?');">
                                                    <input type="hidden" name="delete_sleep" value="1">
                                                    <input type="hidden" name="sleep_id" value="<?php echo $log['sleep_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm btn-delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 