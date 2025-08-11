<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Check if user is already premium
$stmt = $conn->prepare("SELECT is_premium, premium_expiry FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle premium upgrade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_plan'])) {
    $plan = $_POST['plan'];
    $duration_months = 0;
    
    switch($plan) {
        case 'monthly':
            $duration_months = 1;
            break;
        case 'yearly':
            $duration_months = 12;
            break;
        case 'lifetime':
            $duration_months = 999; // Effectively lifetime
            break;
    }
    
    if ($duration_months > 0) {
        $expiry_date = date('Y-m-d H:i:s', strtotime("+$duration_months months"));
        $stmt = $conn->prepare("UPDATE users SET is_premium = 1, premium_expiry = ? WHERE user_id = ?");
        $stmt->bind_param("si", $expiry_date, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = displayAlert('Successfully upgraded to premium!', 'success');
            header("Location: workout.php");
            exit();
        } else {
            $message = displayAlert('Error processing upgrade.', 'danger');
        }
    }
}

$conn->close();
?>

<style>
.premium-card {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.premium-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.premium-header {
    background: linear-gradient(135deg, #e67e22, #f1c40f);
    padding: 2rem;
    color: white;
    text-align: center;
}

.premium-features {
    padding: 2rem;
}

.premium-feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    color: #ecf0f1;
}

.premium-feature-item i {
    margin-right: 1rem;
    color: #f1c40f;
}

.premium-price {
    font-size: 2.5rem;
    font-weight: bold;
    color: #f1c40f;
    margin: 1rem 0;
}

.premium-btn {
    background: linear-gradient(135deg, #f1c40f, #e67e22);
    border: none;
    padding: 0.8rem 2rem;
    color: white;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.premium-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(241, 196, 15, 0.3);
    color: white;
}

.plan-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.plan-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.15);
}

.plan-price {
    font-size: 2.5rem;
    font-weight: bold;
    color: #f1c40f;
    margin: 1rem 0;
}

.plan-title {
    color: #ecf0f1;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 2rem 0;
}

.plan-features li {
    color: #ecf0f1;
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.plan-features li i {
    margin-right: 0.5rem;
    color: #f1c40f;
}

[data-theme="dark"] .premium-card {
    background: linear-gradient(135deg, #1a2634, #2c3e50);
}

[data-theme="dark"] .premium-header {
    background: linear-gradient(135deg, #d35400, #e67e22);
}

[data-theme="dark"] .plan-card {
    background: rgba(0, 0, 0, 0.2);
}

[data-theme="dark"] .plan-card:hover {
    background: rgba(0, 0, 0, 0.3);
}
</style>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="premium-card">
                <div class="premium-header">
                    <h2><i class="fas fa-crown me-2"></i>Upgrade to Premium</h2>
                    <p class="mb-0">Unlock exclusive features and take your fitness journey to the next level</p>
                </div>
                <div class="premium-features">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-white mb-4">Premium Features</h4>
                            <div class="premium-feature-item">
                                <i class="fas fa-chart-line"></i>
                                Advanced Workout Analytics
                            </div>
                            <div class="premium-feature-item">
                                <i class="fas fa-dumbbell"></i>
                                Custom Workout Plans
                            </div>
                            <div class="premium-feature-item">
                                <i class="fas fa-history"></i>
                                Extended Workout History
                            </div>
                            <div class="premium-feature-item">
                                <i class="fas fa-file-export"></i>
                                Export Workout Data
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="premium-feature-item">
                                <i class="fas fa-ad"></i>
                                Ad-Free Experience
                            </div>
                            <div class="premium-feature-item">
                                <i class="fas fa-headset"></i>
                                Priority Support
                            </div>
                            <div class="premium-feature-item">
                                <i class="fas fa-star"></i>
                                Premium Workout Types
                            </div>
                            <div class="premium-feature-item">
                                <i class="fas fa-mobile-alt"></i>
                                Mobile App Access
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Plan -->
        <div class="col-md-4 mb-4">
            <div class="plan-card">
                <h3 class="plan-title">Monthly</h3>
                <div class="plan-price">$9.99</div>
                <p class="text-muted">per month</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i>All Premium Features</li>
                    <li><i class="fas fa-check"></i>Cancel Anytime</li>
                    <li><i class="fas fa-check"></i>30-Day Money Back</li>
                </ul>
                <a href="payment.php?plan=monthly" class="btn premium-btn">Choose Monthly</a>
            </div>
        </div>

        <!-- Yearly Plan -->
        <div class="col-md-4 mb-4">
            <div class="plan-card">
                <div class="badge bg-warning text-dark position-absolute top-0 end-0 m-3">Best Value</div>
                <h3 class="plan-title">Yearly</h3>
                <div class="plan-price">$89.99</div>
                <p class="text-muted">per year (Save 25%)</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i>All Premium Features</li>
                    <li><i class="fas fa-check"></i>2 Months Free</li>
                    <li><i class="fas fa-check"></i>Priority Support</li>
                </ul>
                <a href="payment.php?plan=yearly" class="btn premium-btn">Choose Yearly</a>
            </div>
        </div>

        <!-- Lifetime Plan -->
        <div class="col-md-4 mb-4">
            <div class="plan-card">
                <h3 class="plan-title">Lifetime</h3>
                <div class="plan-price">$299.99</div>
                <p class="text-muted">one-time payment</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i>All Premium Features</li>
                    <li><i class="fas fa-check"></i>Lifetime Access</li>
                    <li><i class="fas fa-check"></i>VIP Support</li>
                </ul>
                <a href="payment.php?plan=lifetime" class="btn premium-btn">Choose Lifetime</a>
            </div>
        </div>
    </div>
</div> 