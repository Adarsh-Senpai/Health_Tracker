<?php
require_once 'includes/header.php';
?>

<div class="container py-4">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="hero-card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-heart-pulse display-1 mb-3 text-danger"></i>
                    <h1 class="display-4 mb-3">Your Health Matters</h1>
                    <p class="lead mb-0">Understanding health risks and the importance of fitness in preventing diseases</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Common Health Issues -->
        <div class="col-md-6">
            <div class="info-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        Common Health Issues
                    </h2>
                    <div class="accordion custom-accordion" id="healthIssues">
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#obesity">
                                    <i class="bi bi-person-exclamation me-2"></i>
                                    Obesity and Overweight
                                </button>
                            </h3>
                            <div id="obesity" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <div class="risk-section">
                                        <h5 class="text-danger mb-3">
                                            <i class="bi bi-exclamation-triangle me-2"></i>Risk Factors:
                                        </h5>
                                        <ul class="list-unstyled risk-list">
                                            <li><i class="bi bi-dot"></i> Poor diet and eating habits</li>
                                            <li><i class="bi bi-dot"></i> Sedentary lifestyle</li>
                                            <li><i class="bi bi-dot"></i> Genetic factors</li>
                                            <li><i class="bi bi-dot"></i> Hormonal imbalances</li>
                                        </ul>
                                    </div>
                                    <div class="prevention-section">
                                        <h5 class="text-primary mb-3">
                                            <i class="bi bi-shield-check me-2"></i>Prevention:
                                        </h5>
                                        <ul class="list-unstyled prevention-list">
                                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Regular exercise</li>
                                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Balanced diet</li>
                                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Portion control</li>
                                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Regular health check-ups</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#heartDisease">
                                    Heart Disease
                                </button>
                            </h3>
                            <div id="heartDisease" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <h5 class="text-danger mb-3">Risk Factors:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-dot"></i> High blood pressure</li>
                                        <li><i class="bi bi-dot"></i> High cholesterol</li>
                                        <li><i class="bi bi-dot"></i> Smoking</li>
                                        <li><i class="bi bi-dot"></i> Physical inactivity</li>
                                    </ul>
                                    <h5 class="text-primary mb-3">Prevention:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success"></i> Regular cardiovascular exercise</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Heart-healthy diet</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Stress management</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Regular blood pressure monitoring</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#diabetes">
                                    Diabetes
                                </button>
                            </h3>
                            <div id="diabetes" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <h5 class="text-danger mb-3">Risk Factors:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-dot"></i> Obesity</li>
                                        <li><i class="bi bi-dot"></i> Physical inactivity</li>
                                        <li><i class="bi bi-dot"></i> Poor diet</li>
                                        <li><i class="bi bi-dot"></i> Family history</li>
                                    </ul>
                                    <h5 class="text-primary mb-3">Prevention:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success"></i> Regular exercise</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Healthy eating habits</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Weight management</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Blood sugar monitoring</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hypertension">
                                    Hypertension (High Blood Pressure)
                                </button>
                            </h3>
                            <div id="hypertension" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <h5 class="text-danger mb-3">Risk Factors:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-dot"></i> High sodium intake</li>
                                        <li><i class="bi bi-dot"></i> Stress and anxiety</li>
                                        <li><i class="bi bi-dot"></i> Family history</li>
                                        <li><i class="bi bi-dot"></i> Age and lifestyle factors</li>
                                    </ul>
                                    <h5 class="text-primary mb-3">Prevention:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success"></i> Regular blood pressure monitoring</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Low-sodium diet</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Regular physical activity</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Stress management techniques</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#arthritis">
                                    Arthritis and Joint Pain
                                </button>
                            </h3>
                            <div id="arthritis" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <h5 class="text-danger mb-3">Risk Factors:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-dot"></i> Age and wear-and-tear</li>
                                        <li><i class="bi bi-dot"></i> Previous joint injuries</li>
                                        <li><i class="bi bi-dot"></i> Excess weight</li>
                                        <li><i class="bi bi-dot"></i> Repetitive movements</li>
                                    </ul>
                                    <h5 class="text-primary mb-3">Prevention:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success"></i> Low-impact exercises</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Maintaining healthy weight</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Proper posture and ergonomics</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Joint-friendly activities</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mentalHealth">
                                    Mental Health Issues
                                </button>
                            </h3>
                            <div id="mentalHealth" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <h5 class="text-danger mb-3">Risk Factors:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-dot"></i> Chronic stress</li>
                                        <li><i class="bi bi-dot"></i> Social isolation</li>
                                        <li><i class="bi bi-dot"></i> Traumatic experiences</li>
                                        <li><i class="bi bi-dot"></i> Genetic predisposition</li>
                                    </ul>
                                    <h5 class="text-primary mb-3">Prevention:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success"></i> Regular exercise and physical activity</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Mindfulness and meditation</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Social connections</li>
                                        <li><i class="bi bi-check-circle text-success"></i> Professional support when needed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nutrition and Diet -->
        <div class="col-md-6">
            <div class="info-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">
                        <i class="bi bi-apple text-success me-2"></i>
                        Nutrition Guidelines
                    </h2>
                    <div class="nutrition-content">
                        <div class="mb-4">
                            <h4 class="text-primary mb-4">
                                <i class="bi bi-grid-3x3-gap me-2"></i>
                                Balanced Diet Components
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="nutrition-item">
                                        <div class="nutrition-icon">
                                            <i class="bi bi-egg-fried"></i>
                                        </div>
                                        <h5 class="text-success">Proteins</h5>
                                        <p class="small mb-0">Essential for muscle building and repair. Sources include lean meat, fish, eggs, legumes.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nutrition-item">
                                        <div class="nutrition-icon">
                                            <i class="bi bi-pie-chart"></i>
                                        </div>
                                        <h5 class="text-success">Carbohydrates</h5>
                                        <p class="small mb-0">Primary energy source. Choose whole grains, fruits, and vegetables.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nutrition-item">
                                        <div class="nutrition-icon">
                                            <i class="bi bi-droplet"></i>
                                        </div>
                                        <h5 class="text-success">Healthy Fats</h5>
                                        <p class="small mb-0">Important for hormone function. Found in avocados, nuts, olive oil.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nutrition-item">
                                        <div class="nutrition-icon">
                                            <i class="bi bi-shield-plus"></i>
                                        </div>
                                        <h5 class="text-success">Vitamins & Minerals</h5>
                                        <p class="small mb-0">Essential for overall health. Obtain from varied fruits and vegetables.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-primary mb-3">
                                <i class="bi bi-droplet-half me-2"></i>
                                Hydration Tips
                            </h4>
                            <ul class="list-unstyled hydration-list">
                                <li><i class="bi bi-droplet-fill text-info me-2"></i> Drink 8-10 glasses of water daily</li>
                                <li><i class="bi bi-droplet-fill text-info me-2"></i> Increase intake during exercise</li>
                                <li><i class="bi bi-droplet-fill text-info me-2"></i> Monitor urine color for hydration</li>
                                <li><i class="bi bi-droplet-fill text-info me-2"></i> Include hydrating foods in diet</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exercise Types -->
        <div class="col-12">
            <div class="info-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">
                        <i class="bi bi-activity text-primary me-2"></i>
                        Types of Exercise
                    </h2>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="exercise-type">
                                <div class="exercise-icon">
                                    <i class="bi bi-heart-pulse"></i>
                                </div>
                                <h4><i class="bi bi-heart me-2"></i>Cardio</h4>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Running</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Swimming</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Cycling</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Dancing</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="exercise-type">
                                <div class="exercise-icon">
                                    <i class="bi bi-lightning"></i>
                                </div>
                                <h4>Strength</h4>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Weight Training</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Bodyweight Exercises</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Resistance Bands</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Circuit Training</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="exercise-type">
                                <div class="exercise-icon">
                                    <i class="bi bi-flower1"></i>
                                </div>
                                <h4>Flexibility</h4>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Yoga</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Stretching</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Pilates</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Tai Chi</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="exercise-type">
                                <div class="exercise-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h4>Sports</h4>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Team Sports</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Racket Sports</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Martial Arts</li>
                                    <li><i class="bi bi-arrow-right-circle text-primary me-2"></i> Group Classes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lifestyle Tips -->
        <div class="col-12">
            <div class="info-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">
                        <i class="bi bi-sun text-warning me-2"></i>
                        Healthy Lifestyle Tips
                    </h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="lifestyle-item">
                                <div class="lifestyle-icon">
                                    <i class="bi bi-moon-stars"></i>
                                </div>
                                <h4 class="text-primary">Sleep Hygiene</h4>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Maintain consistent sleep schedule</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Create a relaxing bedtime routine</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Optimize sleep environment</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Limit screen time before bed</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="lifestyle-item">
                                <div class="lifestyle-icon">
                                    <i class="bi bi-peace"></i>
                                </div>
                                <h4 class="text-primary">Stress Management</h4>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Practice mindfulness</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Regular exercise</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Time management</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Healthy work-life balance</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="lifestyle-item">
                                <div class="lifestyle-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h4 class="text-primary">Social Wellness</h4>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Maintain social connections</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Join community activities</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Volunteer work</li>
                                    <li><i class="bi bi-check2-circle text-success me-2"></i> Family time</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Card Styles */
.hero-card {
    background: linear-gradient(135deg, #3498db, #2ecc71);
    border-radius: 25px;
    color: white;
    transition: transform 0.3s ease;
}

.hero-card:hover {
    transform: translateY(-5px);
}

.info-card {
    background: white;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-5px);
}

/* Custom Accordion Styles */
.custom-accordion .accordion-item {
    border: none;
    margin-bottom: 1rem;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.custom-accordion .accordion-button {
    border-radius: 15px;
    padding: 1rem 1.5rem;
    font-weight: 500;
    background: rgba(52, 152, 219, 0.1);
    color: #2c3e50;
    transition: all 0.3s ease;
}

.custom-accordion .accordion-button:not(.collapsed) {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
    box-shadow: none;
}

.custom-accordion .accordion-button:hover {
    background: rgba(52, 152, 219, 0.15);
}

.custom-accordion .accordion-body {
    padding: 1.5rem;
    background: rgba(236, 240, 241, 0.5);
}

.risk-section, .prevention-section {
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.risk-section {
    background: rgba(231, 76, 60, 0.1);
}

.prevention-section {
    background: rgba(46, 204, 113, 0.1);
}

.risk-list li, .prevention-list li {
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.risk-list li:hover, .prevention-list li:hover {
    transform: translateX(5px);
}

/* Nutrition Items */
.nutrition-item {
    background: rgba(46, 204, 113, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.nutrition-item:hover {
    transform: translateY(-5px);
    background: rgba(46, 204, 113, 0.15);
}

.nutrition-icon {
    font-size: 2rem;
    color: #2ecc71;
    margin-bottom: 1rem;
    text-align: center;
}

/* Exercise Types */
.exercise-type {
    background: rgba(52, 152, 219, 0.1);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.exercise-type:hover {
    transform: translateY(-5px);
    background: rgba(52, 152, 219, 0.15);
}

.exercise-icon {
    font-size: 2.5rem;
    color: #3498db;
    margin-bottom: 1.5rem;
}

.exercise-type h4 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

/* Lifestyle Items */
.lifestyle-item {
    background: rgba(155, 89, 182, 0.1);
    border-radius: 15px;
    padding: 2rem;
    transition: all 0.3s ease;
}

.lifestyle-item:hover {
    transform: translateY(-5px);
    background: rgba(155, 89, 182, 0.15);
}

.lifestyle-icon {
    font-size: 2.5rem;
    color: #9b59b6;
    margin-bottom: 1.5rem;
    text-align: center;
}

/* Dark Mode Styles */
[data-theme="dark"] .info-card {
    background: rgba(44, 62, 80, 0.2);
}

[data-theme="dark"] .custom-accordion .accordion-item {
    background: rgba(44, 62, 80, 0.2);
}

[data-theme="dark"] .custom-accordion .accordion-button {
    background: rgba(52, 152, 219, 0.2);
    color: #ecf0f1;
}

[data-theme="dark"] .custom-accordion .accordion-button:not(.collapsed) {
    background: rgba(52, 152, 219, 0.3);
    color: #3498db;
}

[data-theme="dark"] .custom-accordion .accordion-button:hover {
    background: rgba(52, 152, 219, 0.25);
}

[data-theme="dark"] .custom-accordion .accordion-body {
    background: rgba(44, 62, 80, 0.3);
    color: #ecf0f1;
}

[data-theme="dark"] .risk-section {
    background: rgba(231, 76, 60, 0.15);
}

[data-theme="dark"] .prevention-section {
    background: rgba(46, 204, 113, 0.15);
}

[data-theme="dark"] .risk-list li,
[data-theme="dark"] .prevention-list li {
    color: #ecf0f1;
}

[data-theme="dark"] select,
[data-theme="dark"] .form-select {
    background-color: rgba(44, 62, 80, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
}

[data-theme="dark"] select:hover,
[data-theme="dark"] .form-select:hover {
    background-color: rgba(44, 62, 80, 0.4);
    border-color: rgba(52, 152, 219, 0.3);
}

[data-theme="dark"] select:focus,
[data-theme="dark"] .form-select:focus {
    background-color: rgba(44, 62, 80, 0.5);
    border-color: #3498db;
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
}

[data-theme="dark"] select option,
[data-theme="dark"] .form-select option {
    background-color: rgba(44, 62, 80, 0.95);
    color: #ecf0f1;
}

[data-theme="dark"] .accordion-button::after {
    filter: brightness(2);
}

[data-theme="dark"] h4,
[data-theme="dark"] h5,
[data-theme="dark"] .card-title {
    color: #ecf0f1;
}

[data-theme="dark"] p,
[data-theme="dark"] li {
    color: #bdc3c7;
}
</style> 