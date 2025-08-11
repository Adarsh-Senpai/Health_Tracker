<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Get plan from URL
$plan = isset($_GET['plan']) ? $_GET['plan'] : '';
$duration = '';
$price = 0;

switch($plan) {
    case 'monthly':
        $price = 749;
        $duration = 'Monthly';
        break;
    case 'yearly':
        $price = 6749;
        $duration = 'Yearly';
        break;
    case 'lifetime':
        $price = 22499;
        $duration = 'Lifetime';
        break;
}
?>

<style>
.success-page {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.1), rgba(52, 152, 219, 0.1));
    padding: 3rem 0;
    min-height: calc(100vh - 60px);
}

.success-container {
    text-align: center;
    padding: 3rem 2rem;
    max-width: 800px;
    margin: 0 auto;
    background: linear-gradient(135deg, #2c3e50, #3498db);
    border-radius: 20px;
    color: white;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

.success-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="0" cy="0" r="20" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: 50px;
    opacity: 0.3;
    animation: rotate 60s linear infinite;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.success-checkmark {
    width: 100px;
    height: 100px;
    margin: 0 auto 2rem;
    position: relative;
    animation: scale-up 0.5s ease-in-out;
}

.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #2ecc71;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    stroke: #fff;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale-up {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.success-message {
    margin-top: 2rem;
    animation: fade-in 1s ease-in-out;
    position: relative;
    z-index: 1;
}

.success-message h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #f1c40f;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.payment-details {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 500px;
    animation: slide-up 0.8s ease-in-out;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
    z-index: 1;
}

.payment-amount {
    font-size: 3rem;
    font-weight: bold;
    color: #f1c40f;
    margin: 1rem 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

@keyframes fade-in {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

@keyframes slide-up {
    0% {
        transform: translateY(20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

.continue-btn {
    background: linear-gradient(135deg, #f1c40f, #e67e22);
    border: none;
    padding: 1.2rem 2.5rem;
    color: white;
    border-radius: 50px;
    font-weight: 600;
    margin-top: 2rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.continue-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(241, 196, 15, 0.3);
    color: white;
}

.continue-btn::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

.premium-features {
    margin-top: 4rem;
    position: relative;
    z-index: 1;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.feature-item {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: fade-in 0.5s ease-in-out forwards;
    opacity: 0;
}

.feature-item:nth-child(1) { animation-delay: 1.2s; }
.feature-item:nth-child(2) { animation-delay: 1.4s; }
.feature-item:nth-child(3) { animation-delay: 1.6s; }
.feature-item:nth-child(4) { animation-delay: 1.8s; }

.feature-icon {
    font-size: 2rem;
    color: #f1c40f;
    margin-bottom: 1rem;
}

[data-theme="dark"] .success-container {
    background: linear-gradient(135deg, #1a2634, #2c3e50);
}
</style>

<div class="success-page">
    <div class="success-container">
        <div class="success-checkmark">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>
        
        <div class="success-message">
            <h2>Welcome to Premium!</h2>
            <p>Your payment was successful and your account has been upgraded</p>
        </div>
        
        <div class="payment-details">
            <div class="d-flex justify-content-between mb-2">
                <span>Plan:</span>
                <span><?php echo $duration; ?> Premium</span>
            </div>
            <div class="payment-amount">
                ₹<?php echo number_format($price, 2); ?>
            </div>
            <div class="text-center">
                <?php if ($plan === 'yearly'): ?>
                    <div class="badge bg-warning text-dark">
                        <i class="fas fa-tags me-1"></i>You saved 25% with annual billing
                    </div>
                <?php elseif ($plan === 'lifetime'): ?>
                    <div class="badge bg-success">
                        <i class="fas fa-infinity me-1"></i>Lifetime access activated
                    </div>
                <?php else: ?>
                    <div class="badge bg-info">
                        <i class="fas fa-check me-1"></i>Premium features activated
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="premium-features">
            <h4>Your Premium Features Are Now Active</h4>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h5>Advanced Workouts</h5>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Progress Analytics</h5>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5>Custom Scheduling</h5>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h5>HD Video Guides</h5>
                </div>
            </div>
        </div>
        
        <a href="workout.php" class="continue-btn">
            <i class="fas fa-dumbbell me-2"></i>Start Your Premium Workout
        </a>
    </div>
</div> 