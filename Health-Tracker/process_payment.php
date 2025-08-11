<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Initialize response array
$response = ['success' => false];

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file for payment debugging
$log_file = 'payment_debug.log';
file_put_contents($log_file, "=== New Payment Request ===\n", FILE_APPEND);
file_put_contents($log_file, "Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($log_file, "POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents($log_file, "Session Data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

try {
    // Validate input
    if (!isset($_POST['razorpay_payment_id'], $_POST['plan'], $_POST['amount'])) {
        $missing = [];
        if (!isset($_POST['razorpay_payment_id'])) $missing[] = 'razorpay_payment_id';
        if (!isset($_POST['plan'])) $missing[] = 'plan';
        if (!isset($_POST['amount'])) $missing[] = 'amount';
        
        file_put_contents($log_file, "Missing fields: " . implode(', ', $missing) . "\n", FILE_APPEND);
        throw new Exception('Missing required payment information: ' . implode(', ', $missing));
    }

    $payment_id = $_POST['razorpay_payment_id'];
    $plan = $_POST['plan'];
    $amount = (float)$_POST['amount']; // Ensure amount is float

    file_put_contents($log_file, "Processing payment: ID=$payment_id, Plan=$plan, Amount=$amount\n", FILE_APPEND);

    // Validate plan
    switch($plan) {
        case 'monthly':
            $duration_months = 1;
            $expected_amount = 749.00; // Add decimal points for exact comparison
            break;
        case 'yearly':
            $duration_months = 12;
            $expected_amount = 6749.00;
            break;
        case 'lifetime':
            $duration_months = 999;
            $expected_amount = 22499.00;
            break;
        default:
            file_put_contents($log_file, "Invalid plan type: $plan\n", FILE_APPEND);
            throw new Exception('Invalid plan type: ' . $plan);
    }

    // Verify amount with some tolerance for floating-point comparison
    if (abs($amount - $expected_amount) > 0.01) {
        file_put_contents($log_file, "Amount mismatch: Expected $expected_amount, Got $amount\n", FILE_APPEND);
        throw new Exception("Invalid payment amount: Expected ₹$expected_amount, Got ₹$amount");
    }

    // Update user's premium status
    $conn = getDBConnection();
    if (!$conn) {
        file_put_contents($log_file, "Database connection failed\n", FILE_APPEND);
        throw new Exception('Database connection failed');
    }

    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if (!$user_id) {
        file_put_contents($log_file, "No user_id found in session\n", FILE_APPEND);
        throw new Exception('User session not found');
    }

    // Verify user exists
    $check_user = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $check_user->bind_param("i", $user_id);
    $check_user->execute();
    $user_result = $check_user->get_result();
    
    if ($user_result->num_rows === 0) {
        file_put_contents($log_file, "User ID $user_id not found in database\n", FILE_APPEND);
        throw new Exception('User not found in database');
    }

    $expiry_date = date('Y-m-d H:i:s', strtotime("+$duration_months months"));
    file_put_contents($log_file, "Updating premium status for user $user_id until $expiry_date\n", FILE_APPEND);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update user status
        $update_user = $conn->prepare("UPDATE users SET is_premium = 1, premium_expiry = ? WHERE user_id = ?");
        if (!$update_user) {
            file_put_contents($log_file, "Prepare failed for user update: " . $conn->error . "\n", FILE_APPEND);
            throw new Exception('Failed to prepare user update statement');
        }
        
        $update_user->bind_param("si", $expiry_date, $user_id);
        if (!$update_user->execute()) {
            file_put_contents($log_file, "Failed to update user status: " . $update_user->error . "\n", FILE_APPEND);
            throw new Exception('Failed to update user status: ' . $update_user->error);
        }
        
        if ($update_user->affected_rows === 0) {
            file_put_contents($log_file, "User update did not affect any rows\n", FILE_APPEND);
            throw new Exception('User update failed: No rows affected');
        }
        
        // Record payment
        $insert_payment = $conn->prepare("INSERT INTO payments (user_id, amount, plan_type, transaction_id) VALUES (?, ?, ?, ?)");
        if (!$insert_payment) {
            file_put_contents($log_file, "Prepare failed for payment insert: " . $conn->error . "\n", FILE_APPEND);
            throw new Exception('Failed to prepare payment insert statement');
        }
        
        $insert_payment->bind_param("idss", $user_id, $amount, $plan, $payment_id);
        if (!$insert_payment->execute()) {
            file_put_contents($log_file, "Failed to record payment: " . $insert_payment->error . "\n", FILE_APPEND);
            throw new Exception('Failed to record payment: ' . $insert_payment->error);
        }
        
        if ($insert_payment->affected_rows === 0) {
            file_put_contents($log_file, "Payment insert did not affect any rows\n", FILE_APPEND);
            throw new Exception('Payment insert failed: No rows affected');
        }
        
        // Commit transaction
        $conn->commit();
        
        file_put_contents($log_file, "Payment recorded successfully\n", FILE_APPEND);
        $response['success'] = true;
        $_SESSION['message'] = displayAlert('Payment successful! Welcome to Premium!', 'success');
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        file_put_contents($log_file, "Transaction rolled back: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    } finally {
        if (isset($update_user)) $update_user->close();
        if (isset($insert_payment)) $insert_payment->close();
        if (isset($check_user)) $check_user->close();
    }

    $conn->close();

} catch (Exception $e) {
    file_put_contents($log_file, "Payment processing error: " . $e->getMessage() . "\n", FILE_APPEND);
    $response['error'] = $e->getMessage();
    $response['debug'] = [
        'post_data' => $_POST,
        'session_data' => [
            'user_id' => $_SESSION['user_id'] ?? null,
            'logged_in' => isset($_SESSION['user_id'])
        ]
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
file_put_contents($log_file, "Response sent: " . json_encode($response) . "\n\n", FILE_APPEND); 