<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Get the plan details from the URL
$plan = isset($_GET['plan']) ? $_GET['plan'] : '';
$price = 0;
$duration = '';

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
    default:
        header("Location: premium.php");
        exit();
}

// Get user details
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$conn->close();

// Convert price to paisa
$price_in_paisa = $price * 100;

// Generate order ID
$order_id = 'ORD_' . time() . '_' . $user_id;
?>

<style>
.premium-page {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.1), rgba(52, 152, 219, 0.1));
    padding: 2rem 0;
    min-height: calc(100vh - 60px);
}

.payment-card {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    border: none;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    max-width: 600px;
    margin: 0 auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.payment-header {
    background: linear-gradient(135deg, #e67e22, #f1c40f);
    padding: 2.5rem 2rem;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.payment-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="0" cy="0" r="20" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: 50px;
    opacity: 0.3;
}

.payment-body {
    padding: 2.5rem;
    color: #ecf0f1;
}

.payment-summary {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.payment-amount {
    font-size: 3rem;
    font-weight: bold;
    color: #f1c40f;
    text-align: center;
    margin: 1.5rem 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.pay-btn {
    background: linear-gradient(135deg, #f1c40f, #e67e22);
    border: none;
    padding: 1.2rem 2.5rem;
    color: white;
    border-radius: 50px;
    width: 100%;
    font-weight: 600;
    margin-top: 1.5rem;
    transition: all 0.3s ease;
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
}

.pay-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(241, 196, 15, 0.3);
    color: white;
}

.pay-btn::after {
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

.secure-badge {
    text-align: center;
    margin-top: 1.5rem;
    color: #bdc3c7;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.secure-badge i {
    color: #2ecc71;
}

.payment-methods {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 1.5rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.payment-method-icon {
    width: 45px;
    height: auto;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.payment-method-icon:hover {
    opacity: 1;
    transform: translateY(-2px);
}

.premium-features {
    margin-top: 3rem;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #f1c40f, #e67e22);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.5rem;
}

.savings-badge {
    background: #e74c3c;
    color: white;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    display: inline-block;
    margin-top: 0.5rem;
}

[data-theme="dark"] .payment-card {
    background: linear-gradient(135deg, #1a2634, #2c3e50);
}

[data-theme="dark"] .feature-card {
    background: #2c3e50;
    color: white;
}
</style>

<div class="premium-page">
    <div class="container">
        <div class="payment-card">
            <div class="payment-header">
                <h2><i class="fas fa-crown me-2"></i>Upgrade to Premium</h2>
                <p class="mb-0">Unlock all premium features and transform your fitness journey</p>
            </div>
            <div class="payment-body">
                <div class="payment-summary">
                    <h4 class="text-center mb-3">Order Summary</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Plan:</span>
                        <span><?php echo $duration; ?> Premium</span>
                    </div>
                    <div class="payment-amount">
                        ₹<?php echo number_format($price, 2); ?>
                    </div>
                    <div class="text-center">
                        <?php if ($plan === 'yearly'): ?>
                            <div class="savings-badge">
                                <i class="fas fa-tags me-1"></i>Save 25% with annual billing
                            </div>
                        <?php elseif ($plan === 'lifetime'): ?>
                            <div class="savings-badge" style="background: #27ae60;">
                                <i class="fas fa-infinity me-1"></i>Best Value
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <button id="rzp-button" class="pay-btn">
                    <i class="fas fa-lock me-2"></i>Secure Checkout
                </button>

                <div class="secure-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Bank-grade security with Razorpay</span>
                </div>

                <div class="payment-methods">
                    <img src="https://razorpay.com/assets/razorpay-upi.png" alt="UPI" class="payment-method-icon" title="UPI">
                    <img src="https://razorpay.com/assets/razorpay-cards.png" alt="Cards" class="payment-method-icon" title="Credit/Debit Cards">
                    <img src="https://razorpay.com/assets/razorpay-netbanking.png" alt="Net Banking" class="payment-method-icon" title="Net Banking">
                    <img src="https://razorpay.com/assets/razorpay-wallet.png" alt="Wallet" class="payment-method-icon" title="Wallets">
                </div>
            </div>
        </div>

        <div class="premium-features">
            <h3 class="text-center mb-4">Premium Features You'll Unlock</h3>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h4>Advanced Workouts</h4>
                    <p>Access premium workout plans designed by fitness experts</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Progress Analytics</h4>
                    <p>Detailed progress tracking and performance analytics</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h4>Custom Scheduling</h4>
                    <p>Create and customize your workout schedule</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h4>HD Video Guides</h4>
                    <p>Access HD exercise videos and expert tutorials</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzp-button').onclick = function(e) {
    var options = {
        "key": "rzp_test_3H8svWC5UPCeZQ",
        "amount": "<?php echo $price_in_paisa; ?>",
        "currency": "INR",
        "name": "Fitness Premium",
        "description": "<?php echo $duration; ?> Premium Plan",
        "handler": function (response) {
            // On successful payment
            if (response.razorpay_payment_id) {
                console.log('Payment successful, ID:', response.razorpay_payment_id);
                
                // Create form data
                const data = new URLSearchParams();
                data.append('razorpay_payment_id', response.razorpay_payment_id);
                data.append('plan', '<?php echo $plan; ?>');
                data.append('amount', '<?php echo $price; ?>');
                
                console.log('Sending payment data:', {
                    payment_id: response.razorpay_payment_id,
                    plan: '<?php echo $plan; ?>',
                    amount: <?php echo $price; ?>
                });
                
                // Send to server
                fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: data
                })
                .then(response => {
                    console.log('Raw response:', response);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Server response:', data);
                    if (data.success) {
                        window.location.href = 'payment_success.php?plan=<?php echo $plan; ?>';
                    } else {
                        console.error('Payment verification failed:', data);
                        alert(data.error || 'Payment verification failed. Please contact support.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during payment processing. Please try again or contact support.');
                });
            } else {
                console.error('No payment ID received');
                alert('Payment failed. Please try again.');
            }
        },
        "prefill": {
            "name": "<?php echo htmlspecialchars($user['full_name']); ?>",
            "email": "<?php echo htmlspecialchars($user['email']); ?>"
        },
        "theme": {
            "color": "#f1c40f"
        },
        "modal": {
            "ondismiss": function() {
                console.log('Payment cancelled by user');
            }
        }
    };
    
    console.log('Initializing payment with options:', {
        amount: options.amount,
        currency: options.currency,
        plan: '<?php echo $plan; ?>'
    });
    
    var rzp1 = new Razorpay(options);
    rzp1.on('payment.failed', function (response){
        console.error('Payment failed:', response.error);
        alert('Payment failed. Error: ' + response.error.description);
    });
    rzp1.open();
    e.preventDefault();
}
</script> 