<?php
include 'db.php';
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: index.php");
    exit();
}

$post_id = intval($_GET['id']);
$action = $_GET['action'];
$uid = $_SESSION['user_id'];

// 2. Verify Ownership (Security: Only the owner can update)
$check = mysqli_query($conn, "SELECT user_id, type FROM posts WHERE id='$post_id' AND user_id='$uid'");

if (mysqli_num_rows($check) == 1) {
    $row = mysqli_fetch_assoc($check);
    $current_type = $row['type'];
    $new_type = "";

    // 3. Status Logic
    if ($action === 'mark_found' && $current_type === 'lost') {
        $new_type = 'found';
    } 
    elseif ($action === 'mark_sold' && $current_type === 'selling') {
        $new_type = 'sold'; // 'sold' items will hide from the main feed
    }
    elseif ($action === 'reopen' && $current_type === 'found') {
        $new_type = 'lost';
    }

    // 4. Execute Update
    if ($new_type) {
        mysqli_query($conn, "UPDATE posts SET type='$new_type' WHERE id='$post_id'");
        header("Location: index.php?msg=status_updated");
    } else {
        header("Location: index.php?msg=invalid_action");
    }
} else {
    // Post doesn't exist or you don't own it
    header("Location: index.php?msg=access_denied");
}
exit();
?>