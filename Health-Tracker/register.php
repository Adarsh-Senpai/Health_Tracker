<?php
require_once 'includes/header.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    
    // Validate input
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $conn = getDBConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Username already taken';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
                
                if ($stmt->execute()) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<style>
    /* Dark mode styles */
    [data-theme="dark"] .card {
        background: linear-gradient(145deg, rgba(44, 62, 80, 0.98), rgba(52, 73, 94, 0.98));
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        border-radius: 1.5rem;
    }

    [data-theme="dark"] .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 2rem 2rem 1rem;
    }

    [data-theme="dark"] .card-body {
        color: #ecf0f1;
        padding: 2rem;
    }

    [data-theme="dark"] h3 {
        color: #ffffff;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        font-size: 2rem;
    }

    [data-theme="dark"] .form-label {
        color: #ecf0f1;
        font-weight: 500;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        opacity: 0.9;
    }

    [data-theme="dark"] .form-control {
        background-color: rgba(236, 240, 241, 0.1);
        border: 2px solid rgba(52, 152, 219, 0.3);
        color: #ffffff;
        padding: 0.75rem 1rem;
        padding-right: 2.5rem;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .form-control:focus {
        background-color: rgba(236, 240, 241, 0.15);
        border-color: #3498db;
        box-shadow: 0 0 15px rgba(52, 152, 219, 0.3);
    }

    [data-theme="dark"] .form-control::placeholder {
        color: rgba(236, 240, 241, 0.5);
    }

    [data-theme="dark"] .input-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .input-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(52, 152, 219, 0.5);
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }

    .form-control:focus + .input-icon {
        color: #3498db;
    }

    [data-theme="dark"] .btn-primary {
        background: linear-gradient(135deg, #3498db, #2980b9);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 0.75rem;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    [data-theme="dark"] a {
        color: #3498db;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] a:hover {
        color: #2ecc71;
        text-decoration: none;
    }

    .alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    [data-theme="dark"] .alert-danger {
        background-color: rgba(231, 76, 60, 0.2);
        border: 1px solid rgba(231, 76, 60, 0.3);
        color: #e74c3c;
    }

    [data-theme="dark"] .alert-success {
        background-color: rgba(46, 204, 113, 0.2);
        border: 1px solid rgba(46, 204, 113, 0.3);
        color: #2ecc71;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card {
        animation: fadeIn 0.5s ease-out;
    }

    .form-field {
        position: relative;
        margin-bottom: 1.5rem;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
                <div class="card-header border-0 text-center">
                    <h3>Create Account</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-field">
                            <label for="username" class="form-label">Username</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required>
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="email" class="form-label">Email</label>
                            <div class="position-relative">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="full_name" class="form-label">Full Name</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" required>
                                <i class="fas fa-user-circle input-icon"></i>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="password" class="form-label">Password</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                                <i class="fas fa-lock input-icon"></i>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                <i class="fas fa-lock input-icon"></i>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 