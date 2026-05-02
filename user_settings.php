<?php
// 1. Force Error Reporting (So we see why it crashes)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// 2. Security Check
if (!isset($_SESSION['user_id'])) {
    die("Error: Not logged in. <a href='login.php'>Login here</a>");
}

$uid = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// 3. Form Handling (Email/Password Update)
if (isset($_POST['save_changes'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Update Email
    if (!empty($email)) {
        // Check for duplicates
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND id != '$uid'");
        if (mysqli_num_rows($check) > 0) {
            $message = "Email already taken!";
            $msg_type = "error";
        } else {
            mysqli_query($conn, "UPDATE users SET email='$email' WHERE id='$uid'");
            $message = "Settings Saved!";
            $msg_type = "success";
        }
    }

    // Update Password
    if (!empty($new_pass)) {
        if ($new_pass === $confirm_pass) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id='$uid'");
            $message = "Password Updated!";
            $msg_type = "success";
        } else {
            $message = "Passwords do not match!";
            $msg_type = "error";
        }
    }
}

// 4. Safe Data Fetching
$query = mysqli_query($conn, "SELECT username, email FROM users WHERE id='$uid'");
if (!$query) {
    die("Database Error: " . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($query);

// Safety Fallback if data is missing
$username_display = isset($user['username']) ? $user['username'] : "Unknown User";
$email_display = isset($user['email']) ? $user['email'] : "";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings | Campus Grid</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="auth-container">
        <div class="auth-card" style="max-width: 600px;">
            
            <h2 style="margin-bottom: 25px; color: var(--text-main);">Profile Settings</h2>

            <?php if ($message): ?>
                <div style="padding: 10px; margin-bottom: 20px; border-radius: 8px; font-weight:bold; 
                    background: <?php echo ($msg_type == 'success') ? '#dcfce7' : '#fee2e2'; ?>; 
                    color: <?php echo ($msg_type == 'success') ? '#166534' : '#991b1b'; ?>;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <div class="input-group">
                    <label class="form-label">Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($username_display); ?>" class="input-readonly" readonly>
                    <small style="color: var(--text-muted); display: block; margin-top: 5px; font-size: 0.8rem;">
                        Unique ID cannot be changed.
                    </small>
                </div>

                <div class="input-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email_display); ?>" required>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 30px 0;">

                <div class="input-group">
                    <label class="form-label">Change Password</label>
                    <div class="icon-input">
                        <span class="material-icons">lock</span>
                        <input type="password" name="new_pass" placeholder="New password">
                    </div>
                </div>

                <div class="input-group">
                    <div class="icon-input">
                        <span class="material-icons">lock_reset</span>
                        <input type="password" name="confirm_pass" placeholder="Confirm new password">
                    </div>
                </div>

                <button type="submit" name="save_changes" class="btn-login">
                    Save Changes
                </button>

            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>