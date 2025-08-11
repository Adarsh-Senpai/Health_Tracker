<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Initialize response array
$response = ['success' => false];

try {
    // Get the payment data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }

    // Required fields
    $razorpay_payment_id = $input['razorpay_payment_id'];
    $razorpay_order_id = $input['razorpay_order_id'];
    $razorpay_signature = $input['razorpay_signature'];
    $plan = $input['plan'];

    // Initialize Razorpay
    require 'vendor/autoload.php';
    use Razorpay\Api\Api;
    use Razorpay\Api\Errors\SignatureVerificationError;

    $api = new Api('rzp_test_3H8svWC5UPCeZQ', 'YOUR_RAZORPAY_SECRET_KEY');

    // Verify signature
    $attributes = [
        'razorpay_payment_id' => $razorpay_payment_id,
        'razorpay_order_id' => $razorpay_order_id,
        'razorpay_signature' => $razorpay_signature
    ];

    $api->utility->verifyPaymentSignature($attributes);

    // Get payment details
    $payment = $api->payment->fetch($razorpay_payment_id);

    // Set duration based on plan
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
        default:
            throw new Exception('Invalid plan type');
    }

    // Update user's premium status
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    $expiry_date = date('Y-m-d H:i:s', strtotime("+$duration_months months"));
    
    $stmt = $conn->prepare("UPDATE users SET is_premium = 1, premium_expiry = ? WHERE user_id = ?");
    $stmt->bind_param("si", $expiry_date, $user_id);
    
    if ($stmt->execute()) {
        // Record payment in database
        $amount = $payment->amount / 100; // Convert from paisa to rupees
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, plan_type, transaction_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $amount, $plan, $razorpay_payment_id);
        $stmt->execute();
        
        $response['success'] = true;
        $_SESSION['message'] = displayAlert('Payment successful! Welcome to Premium!', 'success');
    } else {
        throw new Exception('Failed to update user status');
    }

    $conn->close();

} catch (SignatureVerificationError $e) {
    $response['error'] = 'Payment signature verification failed';
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 