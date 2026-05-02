<?php
session_start();
session_unset();
session_destroy();
// Add a status message to the URL
header("Location: index.php?status=logged_out");
exit();
?>