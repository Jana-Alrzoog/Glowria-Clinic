<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ====== Database Connection (MySQLi) ======
$host = 'localhost';           // الخادم
$db   = 'Glowria_clinic_database'; // اسم قاعدة البيانات
$user = 'root';                // اسم مستخدم قاعدة البيانات
$pass = 'root';                // كلمة المرور (إذا كنت تستخدم XAMPP غالبًا تكون فارغة)
// ملاحظة: إذا كنت تستخدم MAMP قد تكون المنفذ 8889 وكلمة المرور "root" مثلاً.
// يمكنك إضافة المنفذ: $conn = new mysqli($host, $user, $pass, $db, 8889);

$conn = new mysqli($host, $user, $pass, $db,8889);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ====== Handle Form Submission ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. جمع بيانات النموذج
    $firstName = $_POST['first-name'];
    $lastName  = $_POST['last-name'];
    $email     = $_POST['email'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT); // تشفير كلمة المرور
    $role      = $_POST['role']; // يحدد هل المستخدم طبيب أم مريض

    // 2. التحقق من وجود البريد الإلكتروني في قاعدة البيانات
    if ($role === 'doctor') {
        $sql = "SELECT * FROM Doctor WHERE emailAddress = ?";
    } else {
        $sql = "SELECT * FROM Patient WHERE emailAddress = ?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // إذا كان البريد مستخدمًا من قبل، نعيد التوجيه لنفس الصفحة مع بارامتر خطأ
    if ($result->num_rows > 0) {
        header("Location: Sign_Up_Page.php?error=email_taken");
        exit;
    } else {
        // 3. إدخال البيانات الجديدة (طبيب أو مريض)
        if ($role === 'doctor') {
            // إدخال بيانات الطبيب
            $speciality = $_POST['speciality'];  // رقم التخصص (من القائمة المنسدلة)
            $sql = "INSERT INTO Doctor (firstName, lastName, SpecialityID, emailAddress, password) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssiss', $firstName, $lastName, $speciality, $email, $password);
            $stmt->execute();

            // تخزين بيانات الجلسة
            $_SESSION['user_id']   = $conn->insert_id;
            $_SESSION['user_type'] = 'doctor';

            // إعادة التوجيه إلى صفحة الطبيب
            header("Location: Doctor_Page.html");
            exit;
        } else {
            // إدخال بيانات المريض
            $gender = $_POST['gender'];
            $dob    = $_POST['dob'];
            $sql = "INSERT INTO Patient (firstName, lastName, gender, DoB, emailAddress, password) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            // التعديل هنا: تغيير 'sssiss' إلى 'ssssss'
            $stmt->bind_param('ssssss', $firstName, $lastName, $gender, $dob, $email, $password);
            $stmt->execute();

            // تخزين بيانات الجلسة
            $_SESSION['user_id']   = $conn->insert_id;
            $_SESSION['user_type'] = 'patient';

            // إعادة التوجيه إلى صفحة المريض
            header("Location: Patient_Page.html");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- استبدل باسم ملف التنسيق الخاص بك إذا كان مختلفًا -->
    <link rel="stylesheet" href="Signup_Style.css">

    <script>
        function showForm(role) {
            const patientForm = document.getElementById('patient-form');
            const doctorForm = document.getElementById('doctor-form');

            if (role === 'patient') {
                patientForm.style.display = 'block';
                doctorForm.style.display = 'none';
            } else if (role === 'doctor') {
                doctorForm.style.display = 'block';
                patientForm.style.display = 'none';
            }
        }
    </script>
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

    <!-- Sign-up Section -->
    <section id="container">
        <div class="form-container">
            <h1>Sign Up</h1>

            <!-- عرض رسالة خطأ إن كان البريد الإلكتروني مكرر -->
            <?php if (isset($_GET['error']) && $_GET['error'] === 'email_taken'): ?>
                <p style="color: red;">This email is already registered. Please try another.</p>
            <?php endif; ?>

            <!-- اختيار الدور (مريض أم طبيب) -->
            <div class="role-selection">
                <label>
                    <input type="radio" name="role" value="patient" onclick="showForm('patient')"> Patient
                </label>
                <label>
                    <input type="radio" name="role" value="doctor" onclick="showForm('doctor')"> Doctor
                </label>
            </div>

            <!-- Patient Form -->
            <form id="patient-form" style="display: none;" action="Sign_Up_Page.php" method="POST">
                <input type="hidden" name="role" value="patient">
                <label>First Name: <input type="text" name="first-name" required></label>
                <label>Last Name: <input type="text" name="last-name" required></label>
                <label>Gender: 
                    <select name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </label>
                <label>Date of Birth: <input type="date" name="dob" required></label>
                <label>Email: <input type="email" name="email" required></label>
                <label>Password: <input type="password" name="password" required></label>
                <button type="submit">Sign Up</button>
            </form>

            <!-- Doctor Form -->
            <form id="doctor-form" style="display: none;" action="Sign_Up_Page.php" method="POST">
                <input type="hidden" name="role" value="doctor">
                <label>First Name: <input type="text" name="first-name" required></label>
                <label>Last Name: <input type="text" name="last-name" required></label>
                <label>Speciality: 
                    <!-- هنا نفترض أن القيم الرقمية تشير إلى IDs في جدول Speciality -->
                    <select name="speciality" required>
                        <option value="1">Skincare Specialist</option>
                        <option value="2">Laser Treatment Specialist</option>
                        <option value="3">Facial Aesthetics</option>
                        <option value="4">Cosmetic Surgery</option>
                    </select>
                </label>
                <label>Email: <input type="email" name="email" required></label>
                <label>Password: <input type="password" name="password" required></label>
                <button type="submit">Sign Up</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>© 2025 Glowria Clinic. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>