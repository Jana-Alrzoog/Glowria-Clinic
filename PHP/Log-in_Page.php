<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ====== Database Connection (MySQLi) ======
$host = 'localhost';
$db   = 'Glowria_clinic_database';
$user = 'root';
$pass = 'root'; // عدل كلمة المرور بحسب إعداداتك
$conn = new mysqli($host, $user, $pass, $db, 8889);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ====== Handle Login ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password_input = $_POST['password'];
    $role = $_POST['role'];

    // تحديد الاستعلام بناءً على نوع المستخدم
    if ($role === 'doctor') {
        $sql = "SELECT * FROM Doctor WHERE emailAddress = ?";
    } else {
        $sql = "SELECT * FROM Patient WHERE emailAddress = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user_row = $result->fetch_assoc();
        // التحقق من كلمة المرور باستخدام password_verify
        if (password_verify($password_input, $user_row['password'])) {
            $_SESSION['user_id'] = $user_row['id'];
            $_SESSION['user_type'] = $role;
            if ($role === 'doctor') {
                header("Location: Doctor_Page.html");
                exit;
            } else {
                header("Location: Patient_Page.html");
                exit;
            }
        } else {
            header("Location: Log-in_Page.php?error=invalid_credentials");
            exit;
        }
    } else {
        header("Location: Log-in_Page.php?error=invalid_credentials");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="Login_Style.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo">
            <img src="images/logo.png" alt="Glowria Logo">
        </div>
        <nav class="navigation">
            <a href="Home.html">Home</a>
            <a href="about.html">About</a>
        </nav>
    </header>

    <!-- Login Section -->
    <section id="login-container">
        <div class="form-container">
            <h1>Log In</h1>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
                <p style="color:red;">Invalid email or password. Please try again.</p>
            <?php endif; ?>
            <form action="Log-in_Page.php" method="POST">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <div class="role-selection">
                    <label>
                        <input type="radio" name="role" value="patient" required> Patient
                    </label>
                    <label>
                        <input type="radio" name="role" value="doctor" required> Doctor
                    </label>
                </div>
                <button type="submit">Log In</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-main">
                <p>Glowria Clinic • Beauty & Wellness</p>
            </div>
            <div class="footer-links">
                <a href="#">About Us</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
            </div>
            <div class="social-icons">
                <a href="https://www.facebook.com" target="_blank">
                    <img src="images/facebook-icon.png" alt="Facebook">
                </a>
                <a href="https://www.twitter.com" target="_blank">
                    <img src="images/twitter-icon.png" alt="Twitter">
                </a>
                <a href="https://www.instagram.com" target="_blank">
                    <img src="images/instagram-icon.png" alt="Instagram">
                </a>
                <a href="mailto:info@glowria.com">
                    <img src="images/email-icon.png" alt="Email">
                </a>
            </div>
            <p>© 2025 Glowria Clinic. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

