<?php
include 'db.php';
session_start();



// 1. Server-Side Security Gate
include 'auth_check.php';

if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']); // Security: Force ID to be an integer
    $user_id = $_SESSION['user_id'];

    // 2. Fetch User Role
    $user_check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $user_check->bind_param("i", $user_id);
    $user_check->execute();
    $result = $user_check->get_result()->fetch_assoc();
    $role = $result['role'];

    // 3. Logic Execution
    if ($role === 'admin') {
        // Admins: Global delete power
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
    } else {
        // Users: Ownership-restricted delete
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
    }

    if ($stmt->execute()) {
        // Redirect back to referring page (Index or Admin Panel) with success flag
        $redirect = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        
        // Clean the URL of old messages and add the new one
        $clean_url = strtok($redirect, '?');
        header("Location: " . $clean_url . "?msg=deleted");
    } else {
        header("Location: index.php?msg=error");
    }
    exit();
}
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('msg') === 'deleted') {
        // Create a temporary toast notification
        const toast = document.createElement('div');
        toast.innerHTML = "🗑️ Post removed successfully!";
        toast.style = "position:fixed; bottom:20px; right:20px; background:#ef4444; color:white; padding:15px 25px; border-radius:10px; box-shadow:0 10px 15px rgba(0,0,0,0.1); z-index:10000; font-weight:bold;";
        document.body.appendChild(toast);
        
        // Fade out and remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s ease';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});
</script>