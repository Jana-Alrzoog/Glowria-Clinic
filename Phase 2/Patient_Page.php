<?php
session_start();
include 'DB_Connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "patient") {
    header("Location: Log_in_Page.php");
    exit();
}

$patient_id = $_SESSION['user_id'];


$sql = "SELECT firstName, lastName, emailAddress, gender, DoB FROM patient WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();


$sql_appointments = "SELECT appointment.id, appointment.date, appointment.time, appointment.status,
                            doctor.firstName AS doctorFirstName, doctor.lastName AS doctorLastName, doctor.uniqueFileName
                    FROM appointment
                    JOIN doctor ON appointment.DoctorID = doctor.id
                    WHERE appointment.PatientID = ? AND appointment.status != 'Done'
                    ORDER BY appointment.date, appointment.time";

$stmt = $conn->prepare($sql_appointments);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Home - Glowria Clinic</title>
    <link rel="stylesheet" href="Patient_Style.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo">
            <img src="images/logo.png" alt="Glowria Logo">
        </div>
        <nav class="navigation">
            <a href="Home.html">Home</a>
            <a href="About.html">About</a>
        </nav>
    </header>

    <main class="main">
        <div class="wrapper">
            <div class="left"></div>

            <div class="middle">
                <div class="Welcome">
                    <h3>Welcome, <?php echo htmlspecialchars($patient['firstName']); ?></h3>
                </div>

                <div class="info">
                    <h3>Information</h3>
                    <div class="info_data">
                        <div class="data"><h4>Name:</h4><p><?php echo $patient['firstName'] . " " . $patient['lastName']; ?></p></div>
                        <div class="data"><h4>ID:</h4><p><?php echo $patient_id; ?></p></div>
                        <div class="data"><h4>Gender:</h4><p><?php echo $patient['gender']; ?></p></div>
                        <div class="data"><h4>Email:</h4><p><?php echo $patient['emailAddress']; ?></p></div>
                        <div class="data"><h4>DoB:</h4><p><?php echo $patient['DoB']; ?></p></div>
                        <button class="LogOut" role="button"><a href="Signout.php" class="LogOut2">Log out</a></button>
                    </div>
                </div>
            </div>

            <div class="right"></div>
        </div>

      
        <button class="book-appointment" role="button">
            <a href="Appointment_Booking_Page.php" class="Book2">Book an appointment</a>
        </button>

     <?php if (isset($_GET['msg']) && $_GET['msg'] == "appointment_booked"): ?>
    <div id="appointment-alert" style="
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin: 20px auto;
        width: 60%;
        text-align: center;
        border: 1px solid #c3e6cb;
        font-weight: bold;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    ">
        ✅ Your appointment has been booked and is pending confirmation.
    </div>

    <script>
        setTimeout(() => {
            const alertBox = document.getElementById('appointment-alert');
            if(alertBox) {
                alertBox.style.display = 'none';
            }
        }, 3000); 
    </script>
<?php endif; ?>


        
      
        <div class="table-main">
            <table class="table-area">
                <thead>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor's Name</th>
                    <th>Doctor's Photo</th>
                    <th>Status</th>
                    <th>Cancel Booking</th>
                </thead>
                <tbody>
                    <?php while ($row = $appointments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo date("g:i A", strtotime($row['time'])); ?></td>
                            <td>Dr. <?php echo $row['doctorFirstName'] . ' ' . $row['doctorLastName']; ?></td>
                            <td><img src="images/<?php echo $row['uniqueFileName']; ?>" alt="Doctor Photo" width="80" height="80"></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <a class="cancel" href="Cancel_Appointment.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>© 2025 Glowria Clinic. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
