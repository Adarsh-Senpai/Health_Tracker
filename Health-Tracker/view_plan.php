<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$message = '';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;

// Verify plan belongs to user
$stmt = $conn->prepare("SELECT * FROM meal_plans WHERE plan_id = ? AND user_id = ?");
$stmt->bind_param("ii", $plan_id, $user_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    header('Location: diet_plan.php');
    exit;
}

// Handle deletion of meal plan items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $item_id = intval($_POST['item_id']);
    $stmt = $conn->prepare("DELETE FROM meal_plan_items WHERE item_id = ? AND plan_id = ?");
    $stmt->bind_param("ii", $item_id, $plan_id);
    if ($stmt->execute()) {
        $message = displayAlert('Meal plan item removed successfully!', 'success');
    } else {
        $message = displayAlert('Error removing meal plan item.', 'danger');
    }
}

// Get meal plan items grouped by day
$stmt = $conn->prepare("
    SELECT mpi.*, r.name as recipe_name, r.calories_per_serving, r.protein, r.carbs, r.fats, r.is_vegetarian, r.cuisine_type
    FROM meal_plan_items mpi
    JOIN recipes r ON mpi.recipe_id = r.recipe_id
    WHERE mpi.plan_id = ?
    ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
             FIELD(meal_type, 'breakfast', 'lunch', 'dinner', 'snack')
");
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();

$meal_plan_items = [];
$daily_totals = [];
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

while ($item = $result->fetch_assoc()) {
    $meal_plan_items[$item['day_of_week']][$item['meal_type']][] = $item;
    
    // Calculate daily totals
    if (!isset($daily_totals[$item['day_of_week']])) {
        $daily_totals[$item['day_of_week']] = [
            'calories' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fats' => 0
        ];
    }
    
    $daily_totals[$item['day_of_week']]['calories'] += $item['calories_per_serving'] * $item['servings'];
    $daily_totals[$item['day_of_week']]['protein'] += $item['protein'] * $item['servings'];
    $daily_totals[$item['day_of_week']]['carbs'] += $item['carbs'] * $item['servings'];
    $daily_totals[$item['day_of_week']]['fats'] += $item['fats'] * $item['servings'];
}

$conn->close();
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?php echo htmlspecialchars($plan['name']); ?></h2>
                <a href="diet_plan.php" class="btn btn-outline-primary">Back to Diet Plans</a>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-2"><?php echo htmlspecialchars($plan['description']); ?></p>
                            <p class="mb-0"><strong>Target Daily Calories:</strong> <?php echo $plan['target_calories']; ?> kcal</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button class="btn btn-primary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print Plan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo $message; ?>

            <div class="row">
                <?php foreach ($days as $day): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0 text-capitalize"><?php echo $day; ?></h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($daily_totals[$day])): ?>
                                    <div class="daily-nutrition mb-3">
                                        <h6>Daily Totals</h6>
                                        <div class="row g-2">
                                            <div class="col-3">
                                                <div class="p-2 bg-light rounded text-center">
                                                    <div class="h6 mb-0"><?php echo round($daily_totals[$day]['calories']); ?></div>
                                                    <small>Calories</small>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="p-2 bg-light rounded text-center">
                                                    <div class="h6 mb-0"><?php echo round($daily_totals[$day]['protein']); ?>g</div>
                                                    <small>Protein</small>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="p-2 bg-light rounded text-center">
                                                    <div class="h6 mb-0"><?php echo round($daily_totals[$day]['carbs']); ?>g</div>
                                                    <small>Carbs</small>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="p-2 bg-light rounded text-center">
                                                    <div class="h6 mb-0"><?php echo round($daily_totals[$day]['fats']); ?>g</div>
                                                    <small>Fats</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $meal_types = ['breakfast', 'lunch', 'dinner', 'snack'];
                                foreach ($meal_types as $meal_type):
                                    if (isset($meal_plan_items[$day][$meal_type])):
                                ?>
                                    <div class="meal-section mb-3">
                                        <h6 class="text-capitalize"><?php echo $meal_type; ?></h6>
                                        <?php foreach ($meal_plan_items[$day][$meal_type] as $item): ?>
                                            <div class="meal-item p-2 bg-light rounded mb-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($item['recipe_name']); ?></div>
                                                        <div class="small text-muted">
                                                            <?php echo $item['servings']; ?> serving(s) •
                                                            <?php echo $item['calories_per_serving'] * $item['servings']; ?> kcal •
                                                            <?php echo $item['cuisine_type']; ?>
                                                        </div>
                                                    </div>
                                                    <form method="POST" action="" class="ms-2" onsubmit="return confirm('Are you sure you want to remove this item?');">
                                                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                                        <button type="submit" name="delete_item" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php
                                    endif;
                                endforeach;
                                
                                if (!isset($meal_plan_items[$day])):
                                ?>
                                    <p class="text-muted">No meals planned for this day.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, form, .navbar {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}
</style> 