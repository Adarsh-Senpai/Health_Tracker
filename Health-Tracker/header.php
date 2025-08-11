<?php
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <!-- Dark Mode CSS -->
    <link href="css/dark-mode.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #2c3e50;
            --primary-light: #3498db;
            --text-light: #ecf0f1;
            --text-dark: #2c3e50;
            --danger: #e74c3c;
            --success: #2ecc71;
            --nav-height: 60px;
            --nav-bg: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            --nav-bg-scrolled: rgba(44, 62, 80, 0.98);
        }

        /* Dark mode variables */
        [data-theme="dark"] {
            --primary-dark: #1a1a1a;
            --primary-light: #2980b9;
            --text-light: #ecf0f1;
            --text-dark: #bdc3c7;
            --nav-bg: linear-gradient(135deg, #1a1a1a 0%, #2c3e50 100%);
            --nav-bg-scrolled: rgba(26, 26, 26, 0.98);
        }

        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 60px;
            transition: background-color 0.3s ease;
        }

        body[data-theme="dark"] {
            background-color: #121212;
            color: var(--text-light);
        }

        .navbar {
            background: var(--nav-bg);
            padding: 0.5rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            min-height: 60px;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: var(--nav-bg-scrolled);
            backdrop-filter: blur(10px);
            padding: 0.5rem 0;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.2rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0;
        }

        .navbar-brand i {
            font-size: 1.4rem;
            color: #3498db;
            filter: drop-shadow(0 2px 4px rgba(52, 152, 219, 0.3));
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem !important;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin: 0 0.15rem;
            position: relative;
            overflow: hidden;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: #3498db;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        .nav-link.active {
            color: white !important;
            background: rgba(52, 152, 219, 0.15);
            font-weight: 600;
        }

        .nav-link.active::after {
            width: 80%;
        }

        .nav-link i {
            margin-right: 0.4rem;
            font-size: 1.1rem;
            vertical-align: -2px;
            opacity: 0.9;
            transition: transform 0.3s ease;
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        .navbar-toggler {
            border: none;
            padding: 0.4rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-toggler:hover {
            transform: scale(1.1);
        }

        /* Profile and User Menu styles */
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-button {
            background: rgba(52, 152, 219, 0.15);
            border: none;
            border-radius: 50px;
            padding: 0.4rem 1.2rem !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.2);
        }

        .profile-button:hover {
            background: rgba(52, 152, 219, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .profile-button i {
            font-size: 1.3rem;
            color: var(--primary-light);
        }

        /* Logout button styles */
        .logout-button {
            background: #e74c3c;
            border: none;
            border-radius: 50px;
            padding: 0.4rem 1.2rem !important;
            color: white !important;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.2);
        }

        .logout-button:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .logout-button i {
            font-size: 1.2rem;
            color: white;
        }

        /* Theme toggle button */
        .theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text-light);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(45deg);
        }

        .theme-toggle i {
            transition: all 0.5s ease;
        }

        /* Premium Menu Styles */
        .dropdown-menu-dark {
            background: var(--nav-bg-scrolled);
            border: none;
            border-radius: 12px;
            padding: 0.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            margin-top: 0.5rem;
        }

        .dropdown-item {
            color: var(--text-light) !important;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: rgba(52, 152, 219, 0.15);
            transform: translateX(5px);
        }

        .dropdown-item.active {
            background: rgba(52, 152, 219, 0.2);
            font-weight: 600;
        }

        .dropdown-item i {
            font-size: 1.1rem;
            vertical-align: -2px;
        }

        /* Premium Badge */
        .nav-link .bi-star-fill {
            color: #f1c40f;
            filter: drop-shadow(0 0 5px rgba(241, 196, 15, 0.5));
            margin-right: 0.3rem;
        }

        .nav-link .bi-star {
            color: #f1c40f;
            margin-right: 0.3rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Mobile styles */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: var(--nav-bg);
                padding: 0.75rem;
                border-radius: 8px;
                margin-top: 0.5rem;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .nav-link {
                padding: 0.5rem 0.75rem !important;
                margin: 0.2rem 0;
            }

            .user-menu {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
                margin-top: 0.5rem;
            }

            .profile-button,
            .logout-button {
                justify-content: center;
                width: 100%;
            }

            .theme-toggle {
                width: 100%;
                border-radius: 6px;
                height: 40px;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body data-theme="light">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-activity"></i>
                Fitness
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'calories.php' ? 'active' : ''; ?>" href="calories.php">
                                <i class="bi bi-pie-chart"></i>Calories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'workout.php' ? 'active' : ''; ?>" href="workout.php">
                                <i class="bi bi-activity"></i>Workout
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'sleep.php' ? 'active' : ''; ?>" href="sleep.php">
                                <i class="bi bi-moon"></i>Sleep
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'health_calculator.php' ? 'active' : ''; ?>" href="health_calculator.php">
                                <i class="bi bi-calculator"></i>Health Calculator
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'diet_plan.php' ? 'active' : ''; ?>" href="diet_plan.php">
                                <i class="bi bi-journal-text"></i>Diet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'health_info.php' ? 'active' : ''; ?>" href="health_info.php">
                                <i class="bi bi-info-circle"></i>Info
                            </a>
                        </li>
                        <?php
                        // Check if user is premium
                        $conn = getDBConnection();
                        $user_id = $_SESSION['user_id'];
                        $stmt = $conn->prepare("SELECT is_premium FROM users WHERE user_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        $conn->close();

                        if ($user['is_premium']): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="premiumDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-star-fill text-warning"></i>Premium
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li>
                                        <a class="dropdown-item <?php echo $current_page === 'video_guides.php' ? 'active' : ''; ?>" href="video_guides.php">
                                            <i class="bi bi-play-circle me-2"></i>HD Video Guides
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?php echo $current_page === 'schedule.php' ? 'active' : ''; ?>" href="schedule.php">
                                            <i class="bi bi-calendar-check me-2"></i>Custom Schedule
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?php echo $current_page === 'advanced_workouts.php' ? 'active' : ''; ?>" href="advanced_workouts.php">
                                            <i class="bi bi-lightning me-2"></i>Advanced Workouts
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="premium.php" style="color: #f1c40f !important;">
                                    <i class="bi bi-star"></i>Go Premium
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if ($isLoggedIn): ?>
                        <div class="user-menu">
                            <li class="nav-item">
                                <a class="nav-link profile-button <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                                    <i class="bi bi-person-circle"></i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link logout-button" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <button class="theme-toggle" id="theme-toggle" title="Toggle dark mode">
                                    <i class="bi bi-moon-fill"></i>
                                </button>
                            </li>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <li class="nav-item">
                                <a class="nav-link profile-button <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="login.php">
                                    <i class="bi bi-box-arrow-in-right"></i>Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link logout-button <?php echo $current_page === 'register.php' ? 'active' : ''; ?>" href="register.php">
                                    <i class="bi bi-person-plus"></i>Register
                                </a>
                            </li>
                            <li class="nav-item">
                                <button class="theme-toggle" id="theme-toggle" title="Toggle dark mode">
                                    <i class="bi bi-moon-fill"></i>
                                </button>
                            </li>
                        </div>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Dark mode toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');

            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme') || 'light';
            body.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);

            themeToggle.addEventListener('click', function() {
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                body.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });

            function updateThemeIcon(theme) {
                icon.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
            }
        });
    </script>
</body>
</html> 