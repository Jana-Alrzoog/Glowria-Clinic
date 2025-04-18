<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'DB_Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = $_POST['first-name'];
    $lastName = $_POST['last-name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if ($role === 'doctor') {

        $speciality = $_POST['speciality'];
        $photo_name = $_FILES['photo']['name'];
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_ext = pathinfo($photo_name, PATHINFO_EXTENSION);
        $unique_name = uniqid() . '.' . $photo_ext;
        $upload_path = 'images/' . $unique_name;

        move_uploaded_file($photo_tmp, $upload_path);

        $sql = "INSERT INTO Doctor (firstName, lastName, SpecialityID, emailAddress, password, uniqueFileName) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssisss', $firstName, $lastName, $speciality, $email, $password, $unique_name);
        $stmt->execute();

        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_role'] = 'doctor';
        header("Location: Doctor_Page.php");
        exit;
    } else {

        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        $sql = "INSERT INTO Patient (firstName, lastName, gender, DoB, emailAddress, password) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssss', $firstName, $lastName, $gender, $dob, $email, $password);
        $stmt->execute();

        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_role'] = 'patient';
        header("Location: Patient_Page.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign Up</title>

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


<?php if (isset($_GET['error']) && $_GET['error'] === 'email_taken'): ?>
                    <p style="color: red;">This email is already registered. Please try another.</p>
<?php endif; ?>


                <div class="role-selection">
                    <label>
                        <input type="radio" name="role" value="patient" onclick="showForm('patient')"> Patient
                    </label>
                    <label>
                        <input type="radio" name="role" value="doctor" onclick="showForm('doctor')"> Doctor
                    </label>
                </div>

                <!-- Patient Form -->
                <form id="patient-form" style="display: none;" action="Sign-Up_Page.php" method="POST">
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
                <form action="Sign-Up_Page.php" id="doctor-form" style="display: none;" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="role" value="doctor">
                    <label>First Name: <input type="text" name="first-name" required></label>
                    <label>Last Name: <input type="text" name="last-name" required></label>
                    <label>Speciality: 
                        <select name="speciality" required>
                            <option value="1">Skincare Specialist</option>
                            <option value="2">Laser Treatment Specialist</option>
                            <option value="3">Facial Aesthetics</option>
                            <option value="4">Cosmetic Surgery</option>
                        </select>
                    </label>
                    <label>Email: <input type="email" name="email" required></label>
                    <label>Upload Photo: <input type="file" name="photo" accept="image/*" required></label>
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
