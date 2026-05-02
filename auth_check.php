<?php
// Start session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kick out unauthenticated users to the correct login page
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}
?>