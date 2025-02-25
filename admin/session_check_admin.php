<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<h2>You don't have permission to open this file.</h2>";
    echo "<p>Please <a href='/Stockport/admin-login.php'>login</a> to access this page.</p>";
    exit(); // Stop further executi
}


