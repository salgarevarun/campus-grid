<?php
session_start();
include 'db.php';

// Security: Check if current user is admin
$uid = $_SESSION['user_id'] ?? 0;
$res = mysqli_query($conn, "SELECT role FROM users WHERE id = '$uid'");
$user = mysqli_fetch_assoc($res);

if (!$user || $user['role'] !== 'admin') {
    die("Unauthorized Access");
}

if (isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get the target's current role
    $role_check = mysqli_query($conn, "SELECT role FROM users WHERE id = '$target_id'");
    $target = mysqli_fetch_assoc($role_check);
    
    // Toggle: if currently admin, make user; if user, make admin
    $new_role = ($target['role'] === 'admin') ? 'user' : 'admin';
    
    mysqli_query($conn, "UPDATE users SET role = '$new_role' WHERE id = '$target_id'");
}

header("Location: admin_dashboard.php");
exit();
?>