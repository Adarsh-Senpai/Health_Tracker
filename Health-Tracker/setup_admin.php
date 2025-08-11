<?php
require_once '../includes/db.php';

function setupAdminDatabase() {
    $conn = getDBConnection();
    $success = true;
    $messages = [];

    // Add is_admin column if it doesn't exist
    try {
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin BOOLEAN DEFAULT FALSE");
        $messages[] = "✓ Added is_admin column to users table";
    } catch (Exception $e) {
        $success = false;
        $messages[] = "✗ Error adding is_admin column: " . $e->getMessage();
    }

    // Create first admin user if no admin exists
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
        $adminCount = $result->fetch_assoc()['count'];

        if ($adminCount == 0) {
            // Create default admin user
            $email = "admin@fitness.com";
            $password = password_hash("admin123", PASSWORD_DEFAULT);
            $username = "admin";
            
            $stmt = $conn->prepare("INSERT INTO users (email, password, username, is_admin) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("sss", $email, $password, $username);
            $stmt->execute();
            
            $messages[] = "✓ Created default admin user:";
            $messages[] = "   Email: admin@fitness.com";
            $messages[] = "   Password: admin123";
            $messages[] = "   Username: admin";
            $messages[] = "   ⚠️ IMPORTANT: Please change these credentials after first login!";
        } else {
            $messages[] = "✓ Admin user already exists";
        }
    } catch (Exception $e) {
        $success = false;
        $messages[] = "✗ Error creating admin user: " . $e->getMessage();
    }

    return ['success' => $success, 'messages' => $messages];
}

// Run the setup
$result = setupAdminDatabase();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Fitness Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 2rem;
        }
        .setup-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .message-list {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .message-list p {
            margin-bottom: 0.5rem;
            font-family: monospace;
        }
        .success-message {
            color: #198754;
        }
        .error-message {
            color: #dc3545;
        }
        .warning-message {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1 class="h3 mb-4">Admin Setup</h1>
        
        <div class="message-list">
            <?php foreach ($result['messages'] as $message): ?>
                <p class="<?php 
                    echo strpos($message, '✓') === 0 ? 'success-message' : 
                        (strpos($message, '✗') === 0 ? 'error-message' : 
                        (strpos($message, '⚠️') !== false ? 'warning-message' : '')); 
                ?>">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endforeach; ?>
        </div>

        <?php if ($result['success']): ?>
            <div class="alert alert-success">
                Setup completed successfully! You can now 
                <a href="login.php" class="alert-link">login to the admin panel</a>.
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                Setup encountered some errors. Please check the messages above and try again.
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 