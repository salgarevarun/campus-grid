<?php
include 'db.php';
session_start();

// 1. Security Check
$uid = $_SESSION['user_id'];
$res = mysqli_query($conn, "SELECT role FROM users WHERE id = '$uid'");
$me = mysqli_fetch_assoc($res);

if (!$me || $me['role'] !== 'admin') {
    die("Unauthorized access.");
}

// 2. Role Update Logic
if (isset($_GET['id']) && isset($_GET['role'])) {
    $target_id = intval($_GET['id']);
    $new_role = mysqli_real_escape_string($conn, $_GET['role']);

    // Prevent self-demotion
    if ($target_id == $_SESSION['user_id'] && $new_role == 'user') {
        header("Location: admin_monitor.php?msg=self_demote");
        exit();
    }

    // ... logic above ...
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $target_id);
    
    if ($stmt->execute()) {
        header("Location: admin_monitor.php?msg=user_updated");
    }
    // REMOVED THE ACCIDENTAL PASSWORD_VERIFY BLOCK HERE
    exit();
}
?>