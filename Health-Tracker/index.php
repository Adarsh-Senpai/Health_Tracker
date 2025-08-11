<?php
require_once 'includes/header.php';
?>

<style>
    /* Dark mode variables */
    [data-theme="dark"] {
        --card-bg: #2c3e50;
        --card-text: #ecf0f1;
        --section-bg: #1a1a1a;
        --muted-text: #95a5a6;
        --primary-color: #3498db;
    }

    /* Card styles */
    .card {
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .card {
        background-color: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    [data-theme="dark"] .card-body {
        color: var(--card-text);
    }

    [data-theme="dark"] .text-muted {
        color: var(--muted-text) !important;
    }

    /* Background sections */
    [data-theme="dark"] .bg-light {
        background-color: var(--section-bg) !important;
    }

    [data-theme="dark"] h2, 
    [data-theme="dark"] h3, 
    [data-theme="dark"] h6 {
        color: var(--card-text);
    }

    [data-theme="dark"] .bg-primary {
        background-color: var(--primary-color) !important;
    }

    /* Feature icons */
    [data-theme="dark"] .feature-icon i {
        color: var(--primary-color) !important;
    }

    /* Testimonial cards */
    [data-theme="dark"] .card .rounded-circle {
        background-color: var(--primary-color) !important;
    }

    [data-theme="dark"] .premium-section {
        background: linear-gradient(135deg, rgba(44, 62, 80, 0.98), rgba(52, 73, 94, 0.98)) !important;
        border-radius: 20px;
        margin: 2rem 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    [data-theme="dark"] .premium-section .display-4 {
        color: #ffffff;
        text-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
        font-weight: 800;
        font-size: 2.8rem;
        margin-bottom: 2rem;
    }

    [data-theme="dark"] .premium-section h3 {
        color: #ffffff;
        font-size: 2.2rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        margin-bottom: 1.5rem;
    }

    [data-theme="dark"] .premium-section .lead {
        color: #ecf0f1;
        font-size: 1.25rem;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        max-width: 800px;
        margin: 0 auto 3rem;
    }

    [data-theme="dark"] .premium-features {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    [data-theme="dark"] .premium-features li {
        background: rgba(52, 152, 219, 0.15);
        border: 2px solid rgba(52, 152, 219, 0.2);
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        list-style: none;
    }

    [data-theme="dark"] .premium-features li.featured {
        background: rgba(52, 152, 219, 0.25);
        border-color: rgba(52, 152, 219, 0.4);
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.25);
    }

    [data-theme="dark"] .premium-features li.featured:hover {
        transform: scale(1.08) translateY(-5px);
    }

    @media (max-width: 992px) {
        [data-theme="dark"] .premium-features {
            grid-template-columns: repeat(1, 1fr);
            max-width: 500px;
        }
        
        [data-theme="dark"] .premium-features li.featured {
            transform: scale(1.02);
            order: -1;
        }
        
        [data-theme="dark"] .premium-features li.featured:hover {
            transform: scale(1.04) translateY(-5px);
        }
    }

    [data-theme="dark"] .premium-features .feature-title {
        color: #ffffff;
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 1rem;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        display: block;
    }

    [data-theme="dark"] .premium-features .feature-description {
        color: #ecf0f1;
        font-size: 1.1rem;
        line-height: 1.6;
        margin: 0;
    }

    [data-theme="dark"] .premium-features li i {
        color: #2ecc71 !important;
        font-size: 2.2rem;
        filter: drop-shadow(0 2px 4px rgba(46, 204, 113, 0.3));
        margin-bottom: 1rem;
        display: block;
    }

    [data-theme="dark"] .premium-section .btn-primary {
        background: linear-gradient(135deg, #3498db, #2980b9);
        border: none;
        padding: 1rem 2.5rem;
        font-size: 1.2rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .premium-section .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    /* Hero section enhancements */
    .hero-image {
        max-width: 100%;
        height: auto;
        border-radius: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        transform: perspective(1000px) rotateY(-5deg);
        transition: all 0.3s ease;
    }

    .hero-image:hover {
        transform: perspective(1000px) rotateY(0deg);
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    @media (max-width: 991px) {
        .hero-image {
            margin-top: 2rem;
            transform: none;
        }
        .hero-image:hover {
            transform: none;
        }
    }
</style>

<div class="container-fluid p-0">
    <!-- Hero Section -->
    <div class="position-relative">
        <div class="bg-dark text-white py-5" style="background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('assets/images/fitness-hero.jpg') center/cover;">
            <div class="container py-5">
                <div class="row py-5 align-items-center">
                    <div class="col-lg-6 hero-content">
                        <h1 class="display-4 fw-bold mb-4">Transform Your Life with Our Fitness Tracker</h1>
                        <p class="lead mb-4">Track your workouts, monitor your nutrition, analyze your sleep patterns, and achieve your fitness goals with our comprehensive fitness tracking platform.</p>
                        <?php if (!isLoggedIn()): ?>
                            <div class="d-grid gap-2 d-md-flex">
                                <a href="register.php" class="btn btn-primary btn-lg px-4 me-md-2">Get Started</a>
                                <a href="login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
                            </div>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-primary btn-lg px-4">Go to Dashboard</a>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-6 text-center">
                        <img src="assets/images/hero.jpg" alt="Fitness Dashboard" class="hero-image">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <h2 class="text-center mb-5">Why Choose Our Fitness Tracker?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-dumbbell fa-3x text-primary"></i>
                        </div>
                        <h3 class="h5 mb-3">Workout Tracking</h3>
                        <p class="text-muted">Log your exercises, track your progress, and get personalized workout recommendations.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-utensils fa-3x text-primary"></i>
                        </div>
                        <h3 class="h5 mb-3">Nutrition Tracking</h3>
                        <p class="text-muted">Monitor your calorie intake, track macronutrients, and maintain a balanced diet.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-moon fa-3x text-primary"></i>
                        </div>
                        <h3 class="h5 mb-3">Sleep Analysis</h3>
                        <p class="text-muted">Track your sleep patterns and get insights to improve your rest quality.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Features -->
    <div class="premium-section py-5" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
        <div class="container">
            <h2 class="text-center display-4">Premium Features</h2>
            <p class="lead text-center">Upgrade your fitness journey with our premium features designed to optimize your nutrition, calculate health metrics, and provide an ad-free experience.</p>
            <div class="premium-features">
                <li>
                    <i class="fas fa-utensils"></i>
                    <span class="feature-title">Comprehensive meal planning tools</span>
                    <p class="feature-description">Create personalized meal plans and track your nutrition goals with our advanced tools</p>
                </li>
                <li class="featured">
                    <i class="fas fa-ban"></i>
                    <span class="feature-title">Ad-free experience</span>
                    <p class="feature-description">Enjoy an uninterrupted fitness journey without any advertisements</p>
                </li>
                <li>
                    <i class="fas fa-calculator"></i>
                    <span class="feature-title">Advanced health calculator</span>
                    <p class="feature-description">Calculate BMI, BMR, and other important health metrics with precision</p>
                </li>
            </div>
            <div class="text-center">
                <a href="premium.php" class="btn btn-primary rounded-pill">
                    <i class="fas fa-crown me-2"></i>Upgrade to Premium
                </a>
            </div>
        </div>
    </div>

    <!-- Testimonials -->
    <div class="container py-5">
        <h2 class="text-center mb-5">What Our Users Say</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            ★★★★★
                        </div>
                        <p class="mb-3">"This fitness tracker has completely transformed my workout routine. The AI chat feature is incredibly helpful!"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">JD</div>
                            <div class="ms-3">
                                <h6 class="mb-0">John Doe</h6>
                                <small class="text-muted">Premium Member</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            ★★★★★
                        </div>
                        <p class="mb-3">"The sleep tracking feature has helped me improve my rest quality significantly. Highly recommended!"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">JS</div>
                            <div class="ms-3">
                                <h6 class="mb-0">Jane Smith</h6>
                                <small class="text-muted">Premium Member</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            ★★★★★
                        </div>
                        <p class="mb-3">"The nutrition tracking is detailed and easy to use. It's helped me stay on track with my diet goals."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">MP</div>
                            <div class="ms-3">
                                <h6 class="mb-0">Mike Parker</h6>
                                <small class="text-muted">Premium Member</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-primary text-white py-5">
        <div class="container text-center">
            <h2 class="mb-4">Start Your Fitness Journey Today</h2>
            <p class="lead mb-4">Join thousands of users who have already transformed their lives with our fitness tracker.</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-light btn-lg px-4">Sign Up Now</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-light btn-lg px-4">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php require_once 'includes/footer.php'; ?> 