<?php
session_start();
include 'DB_Connection.php';

header('Content-Type: text/plain');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    echo "false";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    $sql = "UPDATE appointment SET status = 'Confirmed' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);

    if ($stmt->execute()) {
        echo "true";
    } else {
        echo "false";
    }
    exit();
}
