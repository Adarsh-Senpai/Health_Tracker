<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$message = '';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['food_id'], $_POST['servings'], $_POST['meal_type'])) {
        $food_id = intval($_POST['food_id']);
        $servings = floatval($_POST['servings']);
        $meal_type = $_POST['meal_type'];
        
        $stmt = $conn->prepare("INSERT INTO food_logs (user_id, food_id, servings, meal_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idds", $user_id, $food_id, $servings, $meal_type);
        
        if ($stmt->execute()) {
            $message = displayAlert('Food logged successfully!', 'success');
        } else {
            $message = displayAlert('Error logging food.', 'danger');
        }
    } elseif (isset($_POST['delete_log'])) {
        // Handle deletion of food log entry
        $log_id = intval($_POST['log_id']);
        
        // Verify the log belongs to the user before deleting
        $stmt = $conn->prepare("DELETE FROM food_logs WHERE log_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $log_id, $user_id);
        
        if ($stmt->execute()) {
            $message = displayAlert('Food log entry deleted successfully!', 'success');
        } else {
            $message = displayAlert('Error deleting food log entry.', 'danger');
        }
    } elseif (isset($_POST['delete_all_logs'])) {
        // Delete all food logs for today for the current user
        $stmt = $conn->prepare("DELETE FROM food_logs WHERE user_id = ? AND DATE(date_logged) = CURRENT_DATE");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = displayAlert('All food logs for today have been deleted successfully!', 'success');
            // Reset totals
            $total_calories = 0;
            $total_protein = 0;
            $total_carbs = 0;
            $total_fats = 0;
            $logs = array(); // Clear the logs array
        } else {
            $message = displayAlert('Error deleting food logs.', 'danger');
        }
    }
}

// Get all food items
$result = $conn->query("SELECT * FROM food_items ORDER BY name");
$food_items = $result->fetch_all(MYSQLI_ASSOC);

// Get today's food logs
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT fl.*, f.name, f.calories, f.protein, f.carbs, f.fats, f.serving_size
    FROM food_logs fl
    JOIN food_items f ON fl.food_id = f.food_id
    WHERE fl.user_id = ? AND DATE(fl.date_logged) = ?
    ORDER BY fl.date_logged DESC
");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$total_calories = 0;
$total_protein = 0;
$total_carbs = 0;
$total_fats = 0;

foreach ($logs as $log) {
    $total_calories += $log['calories'] * $log['servings'];
    $total_protein += $log['protein'] * $log['servings'];
    $total_carbs += $log['carbs'] * $log['servings'];
    $total_fats += $log['fats'] * $log['servings'];
}

$conn->close();
?>

<style>
/* Enhanced Calories Page Styles */
.nutrition-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: none;
}

[data-theme="dark"] .nutrition-card {
    background: var(--bg-card);
}

.nutrition-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.nutrition-summary {
    padding: 1rem;
}

.macro-item {
    position: relative;
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    background: rgba(var(--bs-primary-rgb), 0.05);
    transition: all 0.3s ease;
}

.macro-item:hover {
    background: rgba(var(--bs-primary-rgb), 0.1);
}

.macro-item i {
    font-size: 1.5rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.macro-item.calories {
    background: rgba(52, 152, 219, 0.05);
}
.macro-item.calories:hover { background: rgba(52, 152, 219, 0.1); }
.macro-item.calories i { color: #3498db; }

.macro-item.protein {
    background: rgba(46, 204, 113, 0.05);
}
.macro-item.protein:hover { background: rgba(46, 204, 113, 0.1); }
.macro-item.protein i { color: #2ecc71; }

.macro-item.carbs {
    background: rgba(241, 196, 15, 0.05);
}
.macro-item.carbs:hover { background: rgba(241, 196, 15, 0.1); }
.macro-item.carbs i { color: #f1c40f; }

.macro-item.fats {
    background: rgba(231, 76, 60, 0.05);
}
.macro-item.fats:hover { background: rgba(231, 76, 60, 0.1); }
.macro-item.fats i { color: #e74c3c; }

.progress {
    height: 8px;
    border-radius: 4px;
    background-color: rgba(0, 0, 0, 0.05);
    margin-top: 0.5rem;
}

[data-theme="dark"] .progress {
    background-color: rgba(255, 255, 255, 0.05);
}

.progress-bar {
    border-radius: 4px;
    transition: width 0.6s ease;
}

.food-log-form {
    background: rgba(52, 152, 219, 0.03);
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.food-log-table {
    border-radius: 12px;
    overflow: hidden;
}

.food-log-table thead th {
    background: rgba(52, 152, 219, 0.05);
    border: none;
    padding: 1rem;
    font-weight: 600;
}

.food-log-table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-color: rgba(0, 0, 0, 0.05);
}

.meal-type-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.meal-type-badge.breakfast { background: rgba(52, 152, 219, 0.1); color: #3498db; }
.meal-type-badge.lunch { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
.meal-type-badge.dinner { background: rgba(155, 89, 182, 0.1); color: #9b59b6; }
.meal-type-badge.snack { background: rgba(241, 196, 15, 0.1); color: #f1c40f; }

.delete-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
    border: none;
}

.delete-btn:hover {
    background: #e74c3c;
    color: white;
}

/* Select2 Custom Styling */
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease;
}

/* Dark Mode Enhancements */
[data-theme="dark"] .form-select,
[data-theme="dark"] .form-control {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
    font-weight: 500;
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
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-selection:hover {
    background-color: rgba(44, 62, 80, 0.4);
    border-color: rgba(255, 255, 255, 0.2);
}

[data-theme="dark"] .select2-container--bootstrap-5.select2-container--focus .select2-selection {
    background-color: rgba(44, 62, 80, 0.5);
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
    background-color: rgba(44, 62, 80, 0.95);
    border-color: rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-search__field {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
    color: #ecf0f1;
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: rgba(52, 152, 219, 0.2);
    color: #ffffff;
}

[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
    background-color: rgba(52, 152, 219, 0.3);
    color: #ffffff;
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
</style>

<div class="container py-4">
    <div class="row">
        <!-- Nutrition Summary -->
        <div class="col-md-4">
            <div class="nutrition-card fade-in">
                <div class="card-body">
                    <h4 class="mb-4">
                        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>
                        Today's Summary
                    </h4>
                    <div class="nutrition-summary">
                        <div class="macro-item calories">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Calories</span>
                                        <strong><?php echo number_format($total_calories); ?></strong>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: <?php echo min(100, ($total_calories/2000)*100); ?>%"
                                             aria-valuenow="<?php echo $total_calories; ?>" aria-valuemin="0" aria-valuemax="2000">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="macro-item protein">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-egg-fill"></i>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Protein</span>
                                        <strong><?php echo number_format($total_protein, 1); ?>g</strong>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo min(100, ($total_protein/50)*100); ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="macro-item carbs">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-circle-square"></i>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Carbs</span>
                                        <strong><?php echo number_format($total_carbs, 1); ?>g</strong>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?php echo min(100, ($total_carbs/250)*100); ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="macro-item fats">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-droplet-fill"></i>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Fats</span>
                                        <strong><?php echo number_format($total_fats, 1); ?>g</strong>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                             style="width: <?php echo min(100, ($total_fats/65)*100); ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Food Logger -->
        <div class="col-md-8">
            <div class="nutrition-card fade-in">
                <div class="card-body">
                    <h4 class="mb-4">
                        <i class="bi bi-journal-plus me-2 text-primary"></i>
                        Log Food
                    </h4>
                    <?php echo $message; ?>
                    
                    <form method="POST" action="" class="food-log-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="food_id" class="form-label">
                                    <i class="bi bi-search me-1"></i>
                                    Food Item
                                </label>
                                <select class="form-select" id="food_id" name="food_id" required>
                                    <option value="">Select food item...</option>
                                    <?php foreach ($food_items as $item): ?>
                                        <option value="<?php echo $item['food_id']; ?>">
                                            <?php echo htmlspecialchars($item['name']); ?> 
                                            (<?php echo $item['calories']; ?> cal/<?php echo $item['serving_size']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="servings" class="form-label">
                                    <i class="bi bi-123 me-1"></i>
                                    Servings
                                </label>
                                <input type="number" class="form-control" id="servings" name="servings" 
                                       step="0.1" min="0.1" value="1" required>
                            </div>
                            <div class="col-md-3">
                                <label for="meal_type" class="form-label">
                                    <i class="bi bi-clock me-1"></i>
                                    Meal Type
                                </label>
                                <select class="form-select" id="meal_type" name="meal_type" required>
                                    <option value="breakfast">Breakfast</option>
                                    <option value="lunch">Lunch</option>
                                    <option value="dinner">Dinner</option>
                                    <option value="snack">Snack</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>
                                Log Food
                            </button>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-utensils me-2"></i>Today's Food Log
                        </h4>
                        <?php if (!empty($logs)): ?>
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete all food logs for today? This action cannot be undone.');">
                                <button type="submit" name="delete_all_logs" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-2"></i>Delete All
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table food-log-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Food</th>
                                    <th>Servings</th>
                                    <th>Calories</th>
                                    <th>Meal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="bi bi-calendar-x me-2"></i>
                                            No food logged today
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-clock me-1 text-muted"></i>
                                                <?php echo date('H:i', strtotime($log['date_logged'])); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['name']); ?></td>
                                            <td><?php echo $log['servings']; ?></td>
                                            <td>
                                                <strong><?php echo number_format($log['calories'] * $log['servings']); ?></strong>
                                                <small class="text-muted">cal</small>
                                            </td>
                                            <td>
                                                <span class="meal-type-badge <?php echo $log['meal_type']; ?>">
                                                    <?php echo ucfirst($log['meal_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this food log entry?');">
                                                    <input type="hidden" name="log_id" value="<?php echo $log['log_id']; ?>">
                                                    <button type="submit" name="delete_log" class="delete-btn">
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
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('#food_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search for a food item...',
        allowClear: true
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<!-- Add Select2 Bootstrap 5 Theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" /> 