<?php
session_start();
include 'DB_Connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "patient") {
    header("Location: Log_in_Page.php");
    exit();
}

$specialities = $conn->query("SELECT * FROM Speciality");

$doctors = [];
$selected_speciality = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['speciality'])) {
    $selected_speciality = $_POST['speciality'];
    $stmt = $conn->prepare("SELECT id, firstName, lastName FROM Doctor WHERE SpecialityID = ?");
    $stmt->bind_param("i", $selected_speciality);
    $stmt->execute();
    $doctors = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book an Appointment</title>
    <link rel="stylesheet" href="Form_Style.css">
</head>
<body>
<header class="main-header">
    <div class="logo">
        <img src="images/logo.png" alt="Glowria Logo">
    </div>
</header>

<div id="appointment-container">
    <div class="form-container">
        <h1>Book an Appointment</h1>

        <!-- First Form: Select Speciality -->
        <form method="POST" action="">
            <label for="speciality">Select Speciality:</label>
            <select id="speciality" name="speciality" required>
                <option value="">-- Select --</option>
                <?php while ($row = $specialities->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= ($selected_speciality == $row['id']) ? "selected" : "" ?>>
                        <?= $row['speciality'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Submit</button>
        </form>

        <!-- Second Form: Book Appointment -->
        <?php if (!empty($doctors) && $doctors->num_rows > 0): ?>
            <form method="POST" action="Add_Appointment.php">
                <label for="doctor">Select Doctor:</label>
                <select id="doctor" name="doctor_id" required>
                    <option value="">-- Select Doctor --</option>
                    <?php while ($doc = $doctors->fetch_assoc()): ?>
                        <option value="<?= $doc['id'] ?>">
                            Dr. <?= $doc['firstName'] . " " . $doc['lastName'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Date:</label>
                <input type="date" name="date" required>

                <label>Time:</label>
                <input type="time" name="time" required>

                <label>Reason for Visit:</label>
                <textarea name="reason" required></textarea>

                <button type="submit">Submit Booking</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
