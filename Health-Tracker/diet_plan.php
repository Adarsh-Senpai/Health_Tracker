<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$message = '';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_plan'])) {
        $plan_name = sanitizeInput($_POST['plan_name']);
        $plan_description = sanitizeInput($_POST['plan_description']);
        $target_calories = intval($_POST['target_calories']);
        
        $stmt = $conn->prepare("INSERT INTO meal_plans (user_id, name, description, target_calories) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $plan_name, $plan_description, $target_calories);
        
        if ($stmt->execute()) {
            $message = displayAlert('Meal plan created successfully!', 'success');
        } else {
            $message = displayAlert('Error creating meal plan.', 'danger');
        }
    } elseif (isset($_POST['add_to_plan'])) {
        $plan_id = intval($_POST['plan_id']);
        $recipe_id = intval($_POST['recipe_id']);
        $meal_type = sanitizeInput($_POST['meal_type']);
        $day_of_week = sanitizeInput($_POST['day_of_week']);
        $servings = intval($_POST['servings']);
        
        $stmt = $conn->prepare("INSERT INTO meal_plan_items (plan_id, recipe_id, meal_type, day_of_week, servings) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $plan_id, $recipe_id, $meal_type, $day_of_week, $servings);
        
        if ($stmt->execute()) {
            $message = displayAlert('Recipe added to meal plan!', 'success');
        } else {
            $message = displayAlert('Error adding recipe to meal plan.', 'danger');
        }
    }
}

// Get user's meal plans
$stmt = $conn->prepare("SELECT * FROM meal_plans WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$meal_plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all recipes
$recipes_query = "SELECT * FROM recipes ORDER BY name";
$recipes = $conn->query($recipes_query)->fetch_all(MYSQLI_ASSOC);

// Get recipe ingredients if a recipe is selected
$selected_recipe = null;
$recipe_ingredients = null;
if (isset($_GET['recipe_id'])) {
    $recipe_id = intval($_GET['recipe_id']);
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE recipe_id = ?");
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $selected_recipe = $stmt->get_result()->fetch_assoc();
    
    if ($selected_recipe) {
        $stmt = $conn->prepare("SELECT * FROM recipe_ingredients WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $recipe_ingredients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$conn->close();
?>

<div class="container py-4">
    <div class="row">
        <!-- Recipe Browser -->
        <div class="col-md-8">
            <div class="recipe-browser-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-journal-bookmark-fill me-2"></i>Recipe Collection
                        </h4>
                        <div class="view-toggle">
                            <button class="btn btn-outline-primary btn-sm active" id="gridView">
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm" id="listView">
                                <i class="bi bi-list-ul"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="form-control search-input" id="recipeSearch" placeholder="Search recipes...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="select-wrapper">
                                <i class="bi bi-globe2 select-icon"></i>
                                <select class="form-select custom-select" id="cuisineFilter">
                                    <option value="">All Cuisines</option>
                                    <option value="Indian">Indian</option>
                                    <option value="International">International</option>
                                    <option value="Mediterranean">Mediterranean</option>
                                    <option value="Asian">Asian</option>
                                    <option value="Italian">Italian</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="select-wrapper">
                                <i class="bi bi-filter select-icon"></i>
                                <select class="form-select custom-select" id="dietFilter">
                                    <option value="">All Types</option>
                                    <option value="1">Vegetarian</option>
                                    <option value="0">Non-Vegetarian</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Recipe Grid -->
                    <div class="row g-4" id="recipeGrid">
                        <?php 
                        $recipes_per_page = 8;
                        $total_recipes = count($recipes);
                        $total_pages = ceil($total_recipes / $recipes_per_page);
                        $current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
                        $offset = ($current_page - 1) * $recipes_per_page;
                        $displayed_recipes = array_slice($recipes, $offset, $recipes_per_page);

                        foreach ($displayed_recipes as $recipe): 
                        ?>
                            <div class="col-md-6 recipe-card" 
                                 data-cuisine="<?php echo $recipe['cuisine_type']; ?>"
                                 data-vegetarian="<?php echo $recipe['is_vegetarian']; ?>">
                                <div class="modern-card h-100">
                                    <?php if ($recipe['image_url']): ?>
                                        <div class="recipe-image-wrapper">
                                            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                                 class="card-img-top recipe-image" 
                                                 alt="<?php echo htmlspecialchars($recipe['name']); ?>">
                                            <div class="recipe-overlay">
                                                <span class="badge recipe-badge <?php echo $recipe['is_vegetarian'] ? 'badge-veg' : 'badge-non-veg'; ?>">
                                                    <i class="bi <?php echo $recipe['is_vegetarian'] ? 'bi-leaf-fill' : 'bi-egg-fried'; ?>"></i>
                                                    <?php echo $recipe['is_vegetarian'] ? 'Veg' : 'Non-Veg'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="recipe-title"><?php echo htmlspecialchars($recipe['name']); ?></h5>
                                        <p class="recipe-description"><?php echo substr(htmlspecialchars($recipe['description']), 0, 100); ?>...</p>
                                        <div class="recipe-meta">
                                            <div class="meta-item">
                                                <i class="bi bi-clock"></i>
                                                <span><?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> min</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="bi bi-fire"></i>
                                                <span><?php echo $recipe['calories_per_serving']; ?> cal</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="bi bi-globe2"></i>
                                                <span><?php echo $recipe['cuisine_type']; ?></span>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary w-100 view-recipe-btn" 
                                                onclick="viewRecipeDetails(<?php echo $recipe['recipe_id']; ?>)">
                                            <i class="bi bi-eye me-2"></i>View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Recipe pagination" class="mt-4">
                        <ul class="pagination justify-content-center modern-pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Recipe Details and Meal Planning -->
        <div class="col-md-4">
            <?php if ($selected_recipe): ?>
                <div class="recipe-details-card mb-4">
                    <div class="card-body p-4">
                        <h4 class="recipe-detail-title">
                            <i class="bi bi-info-circle me-2"></i>
                            <?php echo htmlspecialchars($selected_recipe['name']); ?>
                        </h4>
                        <div class="nutrition-section mb-4">
                            <h6 class="section-title">
                                <i class="bi bi-pie-chart me-2"></i>Nutrition per Serving
                            </h6>
                            <div class="row g-3">
                                <div class="col-4">
                                    <div class="nutrition-card">
                                        <i class="bi bi-fire nutrition-icon"></i>
                                        <div class="h5 mb-0"><?php echo $selected_recipe['calories_per_serving']; ?></div>
                                        <small>Calories</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="nutrition-card">
                                        <i class="bi bi-egg nutrition-icon"></i>
                                        <div class="h5 mb-0"><?php echo $selected_recipe['protein']; ?>g</div>
                                        <small>Protein</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="nutrition-card">
                                        <i class="bi bi-circle-half nutrition-icon"></i>
                                        <div class="h5 mb-0"><?php echo $selected_recipe['carbs']; ?>g</div>
                                        <small>Carbs</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ingredients-section mb-4">
                            <h6 class="section-title">
                                <i class="bi bi-basket me-2"></i>Ingredients
                            </h6>
                            <ul class="ingredients-list">
                                <?php foreach ($recipe_ingredients as $ingredient): ?>
                                    <li>
                                        <i class="bi bi-check2-circle"></i>
                                        <?php echo $ingredient['amount'] . ' ' . $ingredient['unit'] . ' ' . $ingredient['ingredient_name']; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="instructions-section mb-4">
                            <h6 class="section-title">
                                <i class="bi bi-list-check me-2"></i>Instructions
                            </h6>
                            <ol class="instructions-list">
                                <?php foreach (explode("\n", $selected_recipe['instructions']) as $step): ?>
                                    <li><?php echo htmlspecialchars($step); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>

                        <!-- Add to Meal Plan Form -->
                        <form method="POST" action="" class="meal-plan-form">
                            <input type="hidden" name="recipe_id" value="<?php echo $selected_recipe['recipe_id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-calendar-plus me-2"></i>Add to Meal Plan
                                </label>
                                <select class="form-select custom-select" id="plan_id" name="plan_id" required>
                                    <option value="">Select a meal plan...</option>
                                    <?php foreach ($meal_plans as $plan): ?>
                                        <option value="<?php echo $plan['plan_id']; ?>"><?php echo htmlspecialchars($plan['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <select class="form-select custom-select" name="meal_type" required>
                                        <option value="">Meal type...</option>
                                        <option value="breakfast">
                                            <i class="bi bi-sunrise"></i>Breakfast
                                        </option>
                                        <option value="lunch">
                                            <i class="bi bi-sun"></i>Lunch
                                        </option>
                                        <option value="dinner">
                                            <i class="bi bi-moon"></i>Dinner
                                        </option>
                                        <option value="snack">
                                            <i class="bi bi-apple"></i>Snack
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select custom-select" name="day_of_week" required>
                                        <option value="">Day of week...</option>
                                        <option value="monday">Monday</option>
                                        <option value="tuesday">Tuesday</option>
                                        <option value="wednesday">Wednesday</option>
                                        <option value="thursday">Thursday</option>
                                        <option value="friday">Friday</option>
                                        <option value="saturday">Saturday</option>
                                        <option value="sunday">Sunday</option>
                                    </select>
                                </div>
                            </div>
                            <div class="servings-input mt-3">
                                <label class="form-label">
                                    <i class="bi bi-people me-2"></i>Servings
                                </label>
                                <input type="number" class="form-control" name="servings" value="1" min="1" max="10" required>
                            </div>
                            <button type="submit" name="add_to_plan" class="btn btn-primary w-100 mt-3">
                                <i class="bi bi-plus-circle me-2"></i>Add to Plan
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Create New Meal Plan -->
            <div class="create-plan-card mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title">
                        <i class="bi bi-plus-circle me-2"></i>Create Meal Plan
                    </h4>
                    <form method="POST" action="" class="create-plan-form">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-pencil me-2"></i>Plan Name
                            </label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-card-text me-2"></i>Description
                            </label>
                            <textarea class="form-control" id="plan_description" name="plan_description" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-fire me-2"></i>Target Daily Calories
                            </label>
                            <input type="number" class="form-control" id="target_calories" name="target_calories" required>
                        </div>
                        <button type="submit" name="create_plan" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>Create Plan
                        </button>
                    </form>
                </div>
            </div>

            <!-- View Meal Plans -->
            <div class="meal-plans-card">
                <div class="card-body p-4">
                    <h4 class="card-title">
                        <i class="bi bi-calendar-week me-2"></i>Your Meal Plans
                    </h4>
                    <?php if (empty($meal_plans)): ?>
                        <p class="text-muted">
                            <i class="bi bi-info-circle me-2"></i>No meal plans created yet.
                        </p>
                    <?php else: ?>
                        <?php foreach ($meal_plans as $plan): ?>
                            <div class="meal-plan-item">
                                <h6>
                                    <i class="bi bi-calendar-check me-2"></i>
                                    <?php echo htmlspecialchars($plan['name']); ?>
                                </h6>
                                <p class="plan-description">
                                    <?php echo htmlspecialchars($plan['description']); ?>
                                </p>
                                <div class="plan-calories">
                                    <i class="bi bi-fire me-2"></i>
                                    Target: <?php echo $plan['target_calories']; ?> calories/day
                                </div>
                                <a href="view_plan.php?plan_id=<?php echo $plan['plan_id']; ?>" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="bi bi-eye me-2"></i>View Plan
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recipe Details Modal -->
<div class="modal fade" id="recipeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Recipe details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Search functionality
    const searchInput = document.getElementById('recipeSearch');
    searchInput.addEventListener('input', filterRecipes);

    // Filter functionality
    const cuisineFilter = document.getElementById('cuisineFilter');
    const dietFilter = document.getElementById('dietFilter');
    cuisineFilter.addEventListener('change', filterRecipes);
    dietFilter.addEventListener('change', filterRecipes);

    // View toggle
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const recipeGrid = document.getElementById('recipeGrid');

    gridView.addEventListener('click', () => {
        recipeGrid.classList.remove('list-view');
        gridView.classList.add('active');
        listView.classList.remove('active');
    });

    listView.addEventListener('click', () => {
        recipeGrid.classList.add('list-view');
        listView.classList.add('active');
        gridView.classList.remove('active');
    });
});

function filterRecipes() {
    const searchTerm = document.getElementById('recipeSearch').value.toLowerCase();
    const cuisine = document.getElementById('cuisineFilter').value;
    const diet = document.getElementById('dietFilter').value;

    document.querySelectorAll('.recipe-card').forEach(card => {
        const title = card.querySelector('.recipe-title').textContent.toLowerCase();
        const cardCuisine = card.dataset.cuisine;
        const isVegetarian = card.dataset.vegetarian;

        const matchesSearch = title.includes(searchTerm);
        const matchesCuisine = !cuisine || cardCuisine === cuisine;
        const matchesDiet = !diet || isVegetarian === diet;

        card.style.display = matchesSearch && matchesCuisine && matchesDiet ? '' : 'none';
    });
}

function viewRecipeDetails(recipeId) {
    // Fetch recipe details using AJAX
    fetch(`get_recipe_details.php?recipe_id=${recipeId}`)
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById('recipeModal'));
            document.querySelector('#recipeModal .modal-title').textContent = data.name;
            
            let modalContent = `
                <div class="recipe-details">
                    <div class="nutrition-info mb-4">
                        <h6>Nutrition Information</h6>
                        <div class="row g-2">
                            <div class="col-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="h5 mb-0">${data.calories_per_serving}</div>
                                    <small>Calories</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="h5 mb-0">${data.protein}g</div>
                                    <small>Protein</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="h5 mb-0">${data.carbs}g</div>
                                    <small>Carbs</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="h5 mb-0">${data.fats}g</div>
                                    <small>Fats</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ingredients mb-4">
                        <h6>Ingredients</h6>
                        <ul class="list-unstyled">
                            ${data.ingredients.map(ing => `
                                <li><i class="bi bi-dot"></i> ${ing.amount} ${ing.unit} ${ing.ingredient_name}</li>
                            `).join('')}
                        </ul>
                    </div>
                    <div class="instructions">
                        <h6>Instructions</h6>
                        <ol class="ps-3">
                            ${data.instructions.split('\n').map(step => `
                                <li class="mb-2">${step}</li>
                            `).join('')}
                        </ol>
                    </div>
                </div>
            `;
            
            document.querySelector('#recipeModal .modal-body').innerHTML = modalContent;
            modal.show();
        });
}
</script>

<style>
/* Modern Card Styles */
.recipe-browser-card,
.recipe-details-card,
.create-plan-card,
.meal-plans-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    border: none;
    overflow: hidden;
    transition: all 0.3s ease;
}

/* Search and Filter Styles */
.search-box {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-input {
    padding-left: 2.5rem;
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.select-wrapper {
    position: relative;
}

.select-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 1;
}

.custom-select {
    padding-left: 2.5rem;
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    cursor: pointer;
}

/* Recipe Card Styles */
.modern-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}

.modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.recipe-image-wrapper {
    position: relative;
    overflow: hidden;
}

.recipe-image {
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.modern-card:hover .recipe-image {
    transform: scale(1.05);
}

.recipe-overlay {
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.recipe-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
}

.badge-veg {
    background: rgba(46, 204, 113, 0.9);
    color: white;
}

.badge-non-veg {
    background: rgba(231, 76, 60, 0.9);
    color: white;
}

.recipe-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.recipe-description {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.recipe-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.view-recipe-btn {
    border-radius: 10px;
    font-weight: 500;
    background: linear-gradient(45deg, #3498db, #2ecc71);
    border: none;
    transition: all 0.3s ease;
}

.view-recipe-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

/* Nutrition Cards */
.nutrition-card {
    background: rgba(52, 152, 219, 0.1);
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.nutrition-icon {
    font-size: 1.5rem;
    color: #3498db;
    margin-bottom: 0.5rem;
}

/* Ingredients and Instructions */
.section-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.ingredients-list,
.instructions-list {
    padding-left: 0;
    list-style: none;
}

.ingredients-list li,
.instructions-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.ingredients-list li i {
    color: #2ecc71;
    margin-right: 0.5rem;
}

/* Meal Plan Items */
.meal-plan-item {
    padding: 1rem;
    border-radius: 12px;
    background: rgba(52, 152, 219, 0.1);
    margin-bottom: 1rem;
}

.plan-description {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.plan-calories {
    font-size: 0.9rem;
    color: #e74c3c;
    font-weight: 500;
}

/* Dark Mode Styles */
[data-theme="dark"] .recipe-browser-card,
[data-theme="dark"] .recipe-details-card,
[data-theme="dark"] .create-plan-card,
[data-theme="dark"] .meal-plans-card {
    background: rgba(44, 62, 80, 0.2);
}

[data-theme="dark"] .modern-card {
    background: rgba(44, 62, 80, 0.3);
}

[data-theme="dark"] .recipe-title {
    color: #ecf0f1;
}

[data-theme="dark"] .recipe-description {
    color: #bdc3c7;
}

[data-theme="dark"] .form-control,
[data-theme="dark"] .form-select {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .form-control:hover,
[data-theme="dark"] .form-select:hover {
    background-color: rgba(44, 62, 80, 0.4);
    border-color: rgba(255, 255, 255, 0.2);
}

[data-theme="dark"] .form-control:focus,
[data-theme="dark"] .form-select:focus {
    background-color: rgba(44, 62, 80, 0.5);
    border-color: #3498db;
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
}

[data-theme="dark"] .nutrition-card {
    background: rgba(52, 152, 219, 0.2);
    color: #ecf0f1;
}

[data-theme="dark"] .section-title {
    color: #ecf0f1;
}

[data-theme="dark"] .ingredients-list li,
[data-theme="dark"] .instructions-list li {
    border-bottom-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .meal-plan-item {
    background: rgba(52, 152, 219, 0.2);
}

[data-theme="dark"] .plan-description {
    color: #bdc3c7;
}

[data-theme="dark"] .search-icon,
[data-theme="dark"] .select-icon {
    color: #bdc3c7;
}

[data-theme="dark"] .form-select option {
    background-color: rgba(44, 62, 80, 0.95);
    color: #ecf0f1;
}

/* Pagination Styles */
.modern-pagination .page-link {
    border-radius: 8px;
    margin: 0 4px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    color: #2c3e50;
    background-color: #ffffff;
    transition: all 0.3s ease;
}

.modern-pagination .page-link:hover {
    background-color: #f8f9fa;
    color: #3498db;
    border-color: #3498db;
    transform: translateY(-2px);
}

.modern-pagination .page-item.active .page-link {
    background: linear-gradient(45deg, #3498db, #2ecc71);
    border-color: transparent;
    color: #ffffff;
    font-weight: 500;
}

/* Dark Mode Pagination Styles */
[data-theme="dark"] .modern-pagination .page-link {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .modern-pagination .page-link:hover {
    background-color: rgba(44, 62, 80, 0.5);
    border-color: rgba(52, 152, 219, 0.5);
    color: #3498db;
}

[data-theme="dark"] .modern-pagination .page-item.active .page-link {
    background: linear-gradient(45deg, #3498db, #2ecc71);
    border-color: transparent;
    color: #ffffff;
}
</style> 