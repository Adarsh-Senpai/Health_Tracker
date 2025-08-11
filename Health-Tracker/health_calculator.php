<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$message = '';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user's current measurements
$stmt = $conn->prepare("SELECT height, weight, age, gender FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$conn->close();

// Initialize variables
$bmi = null;
$body_fat = null;
$daily_calories = null;
$category = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['calculator_type'])) {
        switch ($_POST['calculator_type']) {
            case 'bmi':
                $weight = isset($_POST['weight_kg']) ? floatval($_POST['weight_kg']) : 
                         (isset($_POST['weight_lb']) ? floatval($_POST['weight_lb']) * 0.453592 : 0);
                $height = isset($_POST['height_cm']) ? floatval($_POST['height_cm']) / 100 : 
                         (isset($_POST['height_ft']) ? (floatval($_POST['height_ft']) * 30.48 + floatval($_POST['height_in']) * 2.54) / 100 : 0);
                
                if ($weight > 0 && $height > 0) {
                    $bmi = calculateBMI($weight, $height);
                    $category = getBMICategory($bmi)[0];
                }
                break;

            case 'body_fat':
                $weight = isset($_POST['bf_weight_kg']) ? floatval($_POST['bf_weight_kg']) : 
                         (isset($_POST['bf_weight_lb']) ? floatval($_POST['bf_weight_lb']) * 0.453592 : 0);
                $waist = isset($_POST['waist_cm']) ? floatval($_POST['waist_cm']) : 
                        (isset($_POST['waist_in']) ? floatval($_POST['waist_in']) * 2.54 : 0);
                $neck = isset($_POST['neck_cm']) ? floatval($_POST['neck_cm']) : 
                       (isset($_POST['neck_in']) ? floatval($_POST['neck_in']) * 2.54 : 0);
                $height = isset($_POST['bf_height_cm']) ? floatval($_POST['bf_height_cm']) : 
                         (isset($_POST['bf_height_ft']) ? (floatval($_POST['bf_height_ft']) * 30.48 + floatval($_POST['bf_height_in']) * 2.54) : 0);
                $hip = isset($_POST['hip_cm']) ? floatval($_POST['hip_cm']) : 
                      (isset($_POST['hip_in']) ? floatval($_POST['hip_in']) * 2.54 : 0);
                $gender = $_POST['gender'];

                if ($weight > 0 && $height > 0 && $waist > 0 && $neck > 0 && ($gender === 'male' || ($gender === 'female' && $hip > 0))) {
                    // U.S. Navy Method
                    if ($gender === 'male') {
                        $body_fat = 495 / (1.0324 - 0.19077 * log10($waist - $neck) + 0.15456 * log10($height)) - 450;
                    } else {
                        $body_fat = 495 / (1.29579 - 0.35004 * log10($waist + $hip - $neck) + 0.22100 * log10($height)) - 450;
                    }
                }
                break;

            case 'calories':
                $weight = isset($_POST['cal_weight_kg']) ? floatval($_POST['cal_weight_kg']) : 
                         (isset($_POST['cal_weight_lb']) ? floatval($_POST['cal_weight_lb']) * 0.453592 : 0);
                $height = isset($_POST['cal_height_cm']) ? floatval($_POST['cal_height_cm']) : 
                         (isset($_POST['cal_height_ft']) ? (floatval($_POST['cal_height_ft']) * 30.48 + floatval($_POST['cal_height_in']) * 2.54) : 0);
                $age = intval($_POST['age']);
                $gender = $_POST['cal_gender'];
                $activity = $_POST['activity_level'];

                if ($weight > 0 && $height > 0 && $age > 0) {
                    $daily_calories = calculateDailyCalories($weight, $height/100, $age, $gender, $activity);
                }
                break;
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="calculator-card">
                <div class="card-body p-4">
                    <h2 class="card-title text-center mb-4">
                        <i class="bi bi-calculator-fill me-2"></i>
                        Health Calculator
                    </h2>
                    
                    <?php echo $message; ?>

                    <!-- Calculator Type Selector -->
                    <ul class="nav nav-pills mb-4 justify-content-center calculator-tabs" id="calculatorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#bmi" type="button">
                                <i class="bi bi-rulers me-2"></i>BMI Calculator
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#bodyFat" type="button">
                                <i class="bi bi-percent me-2"></i>Body Fat Calculator
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#calories" type="button">
                                <i class="bi bi-fire me-2"></i>Calorie Calculator
                            </button>
                        </li>
                    </ul>

                    <!-- Calculator Forms -->
                    <div class="tab-content calculator-content p-4" id="calculatorTabsContent">
                        <!-- BMI Calculator -->
                        <div class="tab-pane fade show active" id="bmi" role="tabpanel">
                            <form method="POST" action="" class="calculator-form">
                                <input type="hidden" name="calculator_type" value="bmi">
                                
                                <div class="mb-4">
                                    <div class="btn-group w-100 unit-toggle" role="group">
                                        <input type="radio" class="btn-check" name="bmi_unit" id="bmi_metric" value="metric" checked>
                                        <label class="btn btn-outline-primary" for="bmi_metric">
                                            <i class="bi bi-rulers me-2"></i>Metric
                                        </label>
                                        <input type="radio" class="btn-check" name="bmi_unit" id="bmi_imperial" value="imperial">
                                        <label class="btn btn-outline-primary" for="bmi_imperial">
                                            <i class="bi bi-rulers me-2"></i>Imperial
                                        </label>
                                    </div>
                                </div>

                                <div class="metric-inputs">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-speedometer2 me-2"></i>Weight (kg)
                                            </label>
                                            <input type="number" class="form-control" name="weight_kg" step="0.1" min="20" max="300" 
                                                   value="<?php echo $user['weight'] ?? ''; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-arrows-vertical me-2"></i>Height (cm)
                                            </label>
                                            <input type="number" class="form-control" name="height_cm" step="0.1" min="100" max="250" 
                                                   value="<?php echo $user['height'] ? ($user['height'] * 100) : ''; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="imperial-inputs d-none">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-speedometer2 me-2"></i>Weight (lb)
                                            </label>
                                            <input type="number" class="form-control" name="weight_lb" step="0.1" min="44" max="660">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">
                                                <i class="bi bi-arrows-vertical me-2"></i>Height (ft)
                                            </label>
                                            <input type="number" class="form-control" name="height_ft" min="3" max="8">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Height (in)</label>
                                            <input type="number" class="form-control" name="height_in" min="0" max="11">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg calculate-btn">
                                        <i class="bi bi-calculator me-2"></i>Calculate BMI
                                    </button>
                                </div>
                            </form>

                            <?php if ($bmi): ?>
                            <div class="result-section text-center p-4 mt-4">
                                <h3 class="mb-3">
                                    <i class="bi bi-clipboard2-data me-2"></i>
                                    Your BMI Result
                                </h3>
                                <div class="result-value mb-3"><?php echo number_format($bmi, 1); ?></div>
                                <h4 class="result-category mb-3"><?php echo $category; ?></h4>
                                
                                <div class="bmi-scale mt-4">
                                    <div class="row text-center g-3">
                                        <div class="col">
                                            <div class="category-item underweight">
                                                <i class="bi bi-arrow-down-circle"></i>
                                                <small class="d-block">Underweight</small>
                                                <small>&lt; 18.5</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="category-item normal">
                                                <i class="bi bi-check-circle"></i>
                                                <small class="d-block">Normal</small>
                                                <small>18.5 - 24.9</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="category-item overweight">
                                                <i class="bi bi-exclamation-circle"></i>
                                                <small class="d-block">Overweight</small>
                                                <small>25 - 29.9</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="category-item obese">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <small class="d-block">Obese</small>
                                                <small>&gt; 30</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 25%"></div>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 25%"></div>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 25%"></div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 25%"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Body Fat Calculator -->
                        <div class="tab-pane fade" id="bodyFat" role="tabpanel">
                            <form method="POST" action="" class="calculator-form">
                                <input type="hidden" name="calculator_type" value="body_fat">
                                
                                <div class="mb-4">
                                    <div class="btn-group w-100 unit-toggle" role="group">
                                        <input type="radio" class="btn-check" name="bf_unit" id="bf_metric" value="metric" checked>
                                        <label class="btn btn-outline-primary" for="bf_metric">
                                            <i class="bi bi-rulers me-2"></i>Metric
                                        </label>
                                        <input type="radio" class="btn-check" name="bf_unit" id="bf_imperial" value="imperial">
                                        <label class="btn btn-outline-primary" for="bf_imperial">
                                            <i class="bi bi-rulers me-2"></i>Imperial
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="gender" id="male" value="male" checked>
                                        <label class="btn btn-outline-primary" for="male">Male</label>
                                        <input type="radio" class="btn-check" name="gender" id="female" value="female">
                                        <label class="btn btn-outline-primary" for="female">Female</label>
                                    </div>
                                </div>

                                <div class="metric-inputs">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-speedometer2 me-2"></i>Weight (kg)
                                            </label>
                                            <input type="number" class="form-control" name="bf_weight_kg" step="0.1">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-arrows-vertical me-2"></i>Height (cm)
                                            </label>
                                            <input type="number" class="form-control" name="bf_height_cm" step="0.1" min="100" max="250">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Waist (cm)</label>
                                            <input type="number" class="form-control" name="waist_cm" step="0.1">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Neck (cm)</label>
                                            <input type="number" class="form-control" name="neck_cm" step="0.1">
                                        </div>
                                        <div class="col-md-4 hip-measurement">
                                            <label class="form-label">Hip (cm)</label>
                                            <input type="number" class="form-control" name="hip_cm" step="0.1">
                                        </div>
                                    </div>
                                </div>

                                <div class="imperial-inputs d-none">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-speedometer2 me-2"></i>Weight (lb)
                                            </label>
                                            <input type="number" class="form-control" name="bf_weight_lb" step="0.1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">
                                                <i class="bi bi-arrows-vertical me-2"></i>Height (ft)
                                            </label>
                                            <input type="number" class="form-control" name="bf_height_ft" min="3" max="8">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Height (in)</label>
                                            <input type="number" class="form-control" name="bf_height_in" min="0" max="11">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Waist (in)</label>
                                            <input type="number" class="form-control" name="waist_in" step="0.1">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Neck (in)</label>
                                            <input type="number" class="form-control" name="neck_in" step="0.1">
                                        </div>
                                        <div class="col-md-4 hip-measurement">
                                            <label class="form-label">Hip (in)</label>
                                            <input type="number" class="form-control" name="hip_in" step="0.1">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg calculate-btn">
                                        <i class="bi bi-calculator me-2"></i>Calculate Body Fat
                                    </button>
                                </div>
                            </form>

                            <?php if ($body_fat !== null): ?>
                            <div class="result-section text-center p-4 mt-4">
                                <h3 class="mb-3">
                                    <i class="bi bi-clipboard2-data me-2"></i>
                                    Your Body Fat Percentage
                                </h3>
                                <div class="result-value mb-3"><?php echo number_format($body_fat, 1); ?>%</div>
                                <div class="body-fat-category mt-4">
                                    <h5>Category Ranges:</h5>
                                    <div class="row text-center g-3">
                                        <div class="col-md-6">
                                            <h6>Men</h6>
                                            <small class="d-block">Essential Fat: 2-5%</small>
                                            <small class="d-block">Athletes: 6-13%</small>
                                            <small class="d-block">Fitness: 14-17%</small>
                                            <small class="d-block">Average: 18-24%</small>
                                            <small class="d-block">Obese: 25%+</small>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Women</h6>
                                            <small class="d-block">Essential Fat: 10-13%</small>
                                            <small class="d-block">Athletes: 14-20%</small>
                                            <small class="d-block">Fitness: 21-24%</small>
                                            <small class="d-block">Average: 25-31%</small>
                                            <small class="d-block">Obese: 32%+</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Calorie Calculator -->
                        <div class="tab-pane fade" id="calories" role="tabpanel">
                            <form method="POST" action="" class="calculator-form">
                                <input type="hidden" name="calculator_type" value="calories">
                                
                                <div class="mb-4">
                                    <div class="btn-group w-100 unit-toggle" role="group">
                                        <input type="radio" class="btn-check" name="cal_unit" id="cal_metric" value="metric" checked>
                                        <label class="btn btn-outline-primary" for="cal_metric">
                                            <i class="bi bi-rulers me-2"></i>Metric
                                        </label>
                                        <input type="radio" class="btn-check" name="cal_unit" id="cal_imperial" value="imperial">
                                        <label class="btn btn-outline-primary" for="cal_imperial">
                                            <i class="bi bi-rulers me-2"></i>Imperial
                                        </label>
                                    </div>
                                </div>

                                <div class="metric-inputs">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-speedometer2 me-2"></i>Weight (kg)
                                            </label>
                                            <input type="number" class="form-control" name="cal_weight_kg" step="0.1" min="20" max="300">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-arrows-vertical me-2"></i>Height (cm)
                                            </label>
                                            <input type="number" class="form-control" name="cal_height_cm" step="0.1" min="100" max="250">
                                        </div>
                                    </div>
                                </div>

                                <div class="imperial-inputs d-none">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="bi bi-speedometer2 me-2"></i>Weight (lb)
                                            </label>
                                            <input type="number" class="form-control" name="cal_weight_lb" step="0.1" min="44" max="660">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">
                                                <i class="bi bi-arrows-vertical me-2"></i>Height (ft)
                                            </label>
                                            <input type="number" class="form-control" name="cal_height_ft" min="3" max="8">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Height (in)</label>
                                            <input type="number" class="form-control" name="cal_height_in" min="0" max="11">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Age</label>
                                        <input type="number" class="form-control" name="age" min="15" max="100" 
                                               value="<?php echo $user['age'] ?? ''; ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select" name="cal_gender" required>
                                            <option value="male" <?php echo ($user['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo ($user['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Activity Level</label>
                                        <select class="form-select" name="activity_level" required>
                                            <option value="sedentary">Sedentary</option>
                                            <option value="light">Lightly Active</option>
                                            <option value="moderate">Moderately Active</option>
                                            <option value="active">Very Active</option>
                                            <option value="very_active">Extra Active</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg calculate-btn">
                                        <i class="bi bi-calculator me-2"></i>Calculate Daily Calories
                                    </button>
                                </div>
                            </form>

                            <?php if ($daily_calories !== null): ?>
                            <div class="result-section text-center p-4 mt-4">
                                <h3 class="mb-3">
                                    <i class="bi bi-clipboard2-data me-2"></i>
                                    Your Daily Calorie Needs
                                </h3>
                                <div class="result-value mb-3"><?php echo number_format($daily_calories); ?></div>
                                <div class="calorie-goals mt-4">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="p-3 bg-white rounded shadow-sm">
                                                <h6 class="text-muted">Weight Loss</h6>
                                                <div class="h5 mb-0"><?php echo number_format($daily_calories - 500); ?></div>
                                                <small class="text-muted">calories/day</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-white rounded shadow-sm">
                                                <h6 class="text-muted">Maintenance</h6>
                                                <div class="h5 mb-0"><?php echo number_format($daily_calories); ?></div>
                                                <small class="text-muted">calories/day</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-white rounded shadow-sm">
                                                <h6 class="text-muted">Weight Gain</h6>
                                                <div class="h5 mb-0"><?php echo number_format($daily_calories + 500); ?></div>
                                                <small class="text-muted">calories/day</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Unit toggle handlers
    const unitToggles = {
        bmi: {
            metric: document.querySelector('#bmi_metric'),
            imperial: document.querySelector('#bmi_imperial'),
            metricInputs: document.querySelector('#bmi .metric-inputs'),
            imperialInputs: document.querySelector('#bmi .imperial-inputs')
        },
        bodyFat: {
            metric: document.querySelector('#bf_metric'),
            imperial: document.querySelector('#bf_imperial'),
            metricInputs: document.querySelector('#bodyFat .metric-inputs'),
            imperialInputs: document.querySelector('#bodyFat .imperial-inputs')
        },
        calories: {
            metric: document.querySelector('#cal_metric'),
            imperial: document.querySelector('#cal_imperial'),
            metricInputs: document.querySelector('#calories .metric-inputs'),
            imperialInputs: document.querySelector('#calories .imperial-inputs')
        }
    };

    // Check if there's a stored active tab
    const storedTab = sessionStorage.getItem('activeCalculatorTab');
    if (storedTab) {
        // Find and click the stored tab
        const tabElement = document.querySelector(`[data-bs-target="#${storedTab}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }

    // Store the active tab when changed
    const calculatorTabs = document.querySelectorAll('[data-bs-toggle="pill"]');
    calculatorTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
            sessionStorage.setItem('activeCalculatorTab', targetId);
        });
    });

    // Check if we need to show a specific calculator based on form submission
    <?php if (isset($_POST['calculator_type'])): ?>
    const submittedCalculator = '<?php echo $_POST['calculator_type']; ?>';
    let targetTab;
    switch(submittedCalculator) {
        case 'bmi':
            targetTab = '#bmi';
            break;
        case 'body_fat':
            targetTab = '#bodyFat';
            break;
        case 'calories':
            targetTab = '#calories';
            break;
    }
    if (targetTab) {
        const tabElement = document.querySelector(`[data-bs-target="${targetTab}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
            sessionStorage.setItem('activeCalculatorTab', targetTab.replace('#', ''));
        }
    }
    <?php endif; ?>

    Object.keys(unitToggles).forEach(calculator => {
        const toggle = unitToggles[calculator];
        toggle.metric.addEventListener('change', () => toggleUnits(toggle, true));
        toggle.imperial.addEventListener('change', () => toggleUnits(toggle, false));
    });

    // Gender toggle for body fat calculator
    const genderInputs = document.querySelectorAll('input[name="gender"]');
    const hipMeasurement = document.querySelectorAll('.hip-measurement');
    
    genderInputs.forEach(input => {
        input.addEventListener('change', () => {
            hipMeasurement.forEach(elem => {
                elem.style.display = input.value === 'female' ? 'block' : 'none';
            });
        });
    });

    // Initialize hip measurement visibility
    hipMeasurement.forEach(elem => {
        elem.style.display = document.querySelector('input[name="gender"]:checked').value === 'female' ? 'block' : 'none';
    });
});

function toggleUnits(toggle, isMetric) {
    toggle.metricInputs.classList.toggle('d-none', !isMetric);
    toggle.imperialInputs.classList.toggle('d-none', isMetric);
}
</script>

<style>
/* Modern Calculator Styles */
.calculator-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: none;
    overflow: hidden;
}

.calculator-tabs {
    gap: 0.5rem;
}

.calculator-tabs .nav-link {
    border-radius: 12px;
    padding: 0.8rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background: white;
}

.calculator-tabs .nav-link:hover {
    transform: translateY(-2px);
}

.calculator-tabs .nav-link.active {
    background: linear-gradient(45deg, #3498db, #2ecc71);
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.calculator-content {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 15px;
}

.calculator-form {
    max-width: 800px;
    margin: 0 auto;
}

.unit-toggle .btn {
    padding: 0.8rem;
    border-radius: 12px;
    font-weight: 500;
}

.unit-toggle .btn-check:checked + .btn {
    background: linear-gradient(45deg, #3498db, #2ecc71);
    border-color: transparent;
    color: white;
}

.form-label {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 12px;
    padding: 0.8rem 1rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

.calculate-btn {
    border-radius: 12px;
    padding: 1rem;
    font-weight: 600;
    background: linear-gradient(45deg, #3498db, #2ecc71);
    border: none;
    transition: all 0.3s ease;
}

.calculate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.result-section {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(46, 204, 113, 0.1));
    border-radius: 15px;
    border: 1px solid rgba(52, 152, 219, 0.2);
}

.result-value {
    font-size: 3.5rem;
    font-weight: 700;
    color: #2c3e50;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.result-category {
    font-weight: 600;
    color: #3498db;
}

.category-item {
    padding: 1rem;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.category-item i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.category-item.underweight { color: #3498db; }
.category-item.normal { color: #2ecc71; }
.category-item.overweight { color: #f1c40f; }
.category-item.obese { color: #e74c3c; }

.progress {
    height: 0.8rem;
    border-radius: 1rem;
    overflow: hidden;
}

/* Dark Mode Enhancements */
[data-theme="dark"] .calculator-card {
    background: rgba(44, 62, 80, 0.2);
}

[data-theme="dark"] .calculator-tabs .nav-link {
    background: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] .calculator-content {
    background: rgba(44, 62, 80, 0.2);
}

[data-theme="dark"] .form-label {
    color: #ecf0f1;
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

[data-theme="dark"] .form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ecf0f1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
}

[data-theme="dark"] .form-select option {
    background-color: rgba(44, 62, 80, 0.95);
    color: #ecf0f1;
}

[data-theme="dark"] .result-value {
    color: #ecf0f1;
}

[data-theme="dark"] .result-section {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.2), rgba(46, 204, 113, 0.2));
}

[data-theme="dark"] .category-item {
    background-color: rgba(44, 62, 80, 0.3);
}

[data-theme="dark"] .category-item i {
    color: #ecf0f1;
}

[data-theme="dark"] .category-item.underweight {
    background-color: rgba(52, 152, 219, 0.2);
}

[data-theme="dark"] .category-item.normal {
    background-color: rgba(46, 204, 113, 0.2);
}

[data-theme="dark"] .category-item.overweight {
    background-color: rgba(241, 196, 15, 0.2);
}

[data-theme="dark"] .category-item.obese {
    background-color: rgba(231, 76, 60, 0.2);
}

[data-theme="dark"] .progress {
    background-color: rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .progress-bar {
    background-color: rgba(52, 152, 219, 0.2);
}

[data-theme="dark"] .progress-bar.bg-info {
    background-color: rgba(52, 152, 219, 0.3);
}

[data-theme="dark"] .progress-bar.bg-success {
    background-color: rgba(46, 204, 113, 0.3);
}

[data-theme="dark"] .progress-bar.bg-warning {
    background-color: rgba(241, 196, 15, 0.3);
}

[data-theme="dark"] .progress-bar.bg-danger {
    background-color: rgba(231, 76, 60, 0.3);
}
</style> 