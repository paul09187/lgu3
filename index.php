<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on user role
    if ($_SESSION['role'] === 'admin') {
        header('Location: pages/admin/dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'user') {
        header('Location: pages/user/dashboard.php');
        exit;
    }
} else {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}
