<?php
session_start();
include 'DB_Connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "patient") {
    header("Location: Log_in_Page.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $appointment_id = $_GET['id'];

   
    $sql = "DELETE FROM appointment WHERE id = ? AND PatientID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
}

header("Location: Patient_Page.php");
exit();
?>
