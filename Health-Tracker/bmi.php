<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$bmi = null;
$category = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']) / 100; // Convert cm to meters
    
    if ($weight > 0 && $height > 0) {
        $bmi = calculateBMI($weight, $height);
        $category = getBMICategory($bmi);
        
        // Update user profile if requested
        if (isset($_POST['update_profile']) && $_POST['update_profile'] === 'yes') {
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE users SET weight = ?, height = ? WHERE user_id = ?");
            $stmt->bind_param("ddi", $weight, $height, $_SESSION['user_id']);
            $stmt->execute();
            $conn->close();
            
            $message = displayAlert('Profile updated successfully!', 'success');
        }
    }
}

// Get user's current measurements
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT height, weight FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$conn->close();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">BMI Calculator</h2>
                    
                    <?php echo $message; ?>
                    
                    <form method="POST" action="" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" class="form-control" id="weight" name="weight" step="0.1" min="20" max="300" 
                                       value="<?php echo $user['weight'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="height" class="form-label">Height (cm)</label>
                                <input type="number" class="form-control" id="height" name="height" step="0.1" min="100" max="250" 
                                       value="<?php echo $user['height'] ? ($user['height'] * 100) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="update_profile" name="update_profile" value="yes">
                            <label class="form-check-label" for="update_profile">
                                Update my profile with these measurements
                            </label>
                        </div>
                        
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">Calculate BMI</button>
                        </div>
                    </form>
                    
                    <?php if ($bmi): ?>
                    <div class="result-section text-center p-4 bg-light rounded">
                        <h3 class="mb-3">Your BMI Result</h3>
                        <div class="display-4 mb-3"><?php echo number_format($bmi, 1); ?></div>
                        <h4 class="mb-3"><?php echo $category; ?></h4>
                        
                        <div class="bmi-scale mt-4">
                            <div class="row text-center">
                                <div class="col">
                                    <small class="d-block text-muted">Underweight</small>
                                    <small>&lt; 18.5</small>
                                </div>
                                <div class="col">
                                    <small class="d-block text-muted">Normal</small>
                                    <small>18.5 - 24.9</small>
                                </div>
                                <div class="col">
                                    <small class="d-block text-muted">Overweight</small>
                                    <small>25 - 29.9</small>
                                </div>
                                <div class="col">
                                    <small class="d-block text-muted">Obese</small>
                                    <small>&gt; 30</small>
                                </div>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 25%"></div>
                                <div class="progress-bar bg-success" role="progressbar" style="width: 25%"></div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 25%"></div>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 25%"></div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>What does this mean?</h5>
                            <p class="text-muted">
                                <?php
                                switch ($category) {
                                    case 'Underweight':
                                        echo 'Being underweight might indicate nutritional deficiencies. Consider consulting with a healthcare provider about a healthy weight gain plan.';
                                        break;
                                    case 'Normal weight':
                                        echo 'You are at a healthy weight for your height. Maintain your weight by continuing balanced diet and regular exercise.';
                                        break;
                                    case 'Overweight':
                                        echo 'Being overweight may increase your risk of health issues. Consider making lifestyle changes and consult with healthcare providers.';
                                        break;
                                    case 'Obese':
                                        echo 'Obesity increases your risk of several health conditions. It\'s recommended to consult with healthcare providers about weight management strategies.';
                                        break;
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <h5>About BMI</h5>
                        <p class="text-muted">
                            Body Mass Index (BMI) is a simple measure using your height and weight to work out if your weight is healthy.
                            The BMI calculation divides an adult's weight in kilograms by their height in metres squared.
                            While BMI is useful as a general guide, it doesn't account for factors like muscle mass, age, and ethnicity.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 