<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stockport", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input values
    $username = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // 🚨 Prevent login if fields are empty
    if (empty($username) || empty($password) || strlen($username) === 0 || strlen($password) === 0) {
        $_SESSION['error'] = "Email and Password are required!";
        header("Location: admin-login.php");
        die(); // 🚨 Stop further execution
    }


    $query = "SELECT adminID, adminPassword FROM admin WHERE BINARY TRIM(adminUser) = BINARY TRIM(?)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

  
    if (!$user) {
        $_SESSION['error'] = "No admin found with this email.";
        header("Location: admin-login.php");
        die();
    }

    
    if (!password_verify($password, $user['adminPassword'])) {
        $_SESSION['error'] = "Incorrect email or password.";
        header("Location: admin-login.php");
        die();
    }

    
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['adminID'];

   
    header("Location: admin/dashboard.php");
    die();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Split Layout Form -->
    <div class="container">
        <div class="split-form">
            <div class="image-side">
                <h2>Admin Login Page</h2>
                <p>Please enter your credentials.</p>
            </div>
            <div class="form-side">
                <h2>Sign In</h2>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']); 
                        ?>
                    </div>
                <?php endif; ?>
                <form action="" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button onclick="window.location.href='admin/dashboard.php'" class="btn mt-3">Submit</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
