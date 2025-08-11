<?php
session_start();
require_once '../includes/db.php';

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['is_admin'] === true;
}

function redirectIfNotAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function adminLogin($email, $password) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT user_id, password, is_admin FROM users WHERE email = ? AND is_admin = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['user_id'];
            $_SESSION['is_admin'] = true;
            return true;
        }
    }
    return false;
}

function adminLogout() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['is_admin']);
    session_destroy();
}
?> 