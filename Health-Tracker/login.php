<?php
require_once 'includes/header.php';

// Redirect if already logged in
redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            loginUser($user);
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
    
    $conn->close();
}
?>

<style>
    /* Dark mode styles */
    [data-theme="dark"] .card {
        background: linear-gradient(145deg, rgba(44, 62, 80, 0.98), rgba(52, 73, 94, 0.98));
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    [data-theme="dark"] .card-body {
        color: #ecf0f1;
    }

    [data-theme="dark"] h2 {
        color: #ffffff;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
    }

    [data-theme="dark"] .form-label {
        color: #ecf0f1;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    [data-theme="dark"] .form-control {
        background-color: rgba(236, 240, 241, 0.1);
        border: 2px solid rgba(52, 152, 219, 0.3);
        color: #ffffff;
        padding: 0.75rem 1rem;
        padding-right: 2.5rem;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .form-control::placeholder {
        color: rgba(236, 240, 241, 0.5);
    }

    [data-theme="dark"] .input-group {
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

    .alert {
        border-radius: 1rem;
        border: none;
    }

    [data-theme="dark"] .alert-danger {
        background-color: rgba(231, 76, 60, 0.2);
        border: 1px solid rgba(231, 76, 60, 0.3);
        color: #e74c3c;
    }

    .btn-close {
        transition: transform 0.3s ease;
    }

    .btn-close:hover {
        transform: rotate(90deg);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card {
        animation: fadeIn 0.5s ease-out;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Welcome Back</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="input-group mb-3">
                            <div class="position-relative w-100">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>
                        <div class="input-group mb-4">
                            <div class="position-relative w-100">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                <i class="fas fa-lock input-icon"></i>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 