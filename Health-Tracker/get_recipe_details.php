<?php
require_once 'includes/functions.php';

// Check if recipe_id is provided
if (!isset($_GET['recipe_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Recipe ID is required']);
    exit;
}

$recipe_id = intval($_GET['recipe_id']);
$conn = getDBConnection();

// Get recipe details
$stmt = $conn->prepare("
    SELECT *
    FROM recipes
    WHERE recipe_id = ?
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();

if (!$recipe) {
    http_response_code(404);
    echo json_encode(['error' => 'Recipe not found']);
    exit;
}

// Get recipe ingredients
$stmt = $conn->prepare("
    SELECT *
    FROM recipe_ingredients
    WHERE recipe_id = ?
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$ingredients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Combine recipe and ingredients
$recipe['ingredients'] = $ingredients;

// Send JSON response
header('Content-Type: application/json');
echo json_encode($recipe);

$conn->close();
?> 