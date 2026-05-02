<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "college_grid";

// ... connection logic
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Add this line to support emojis and special characters
$conn->set_charset("utf8mb4");
?>