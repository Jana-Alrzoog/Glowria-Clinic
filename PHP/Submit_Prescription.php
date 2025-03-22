<?php
session_start();
include 'DB_Connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "doctor") {
    header("Location: Home.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = $_POST['appointment_id'];
    $medications = $_POST['medications'] ?? [];

    // حفظ كل دواء تم اختياره
    foreach ($medications as $medicationName) {
        // جلب ID الدواء
        $stmt = $conn->prepare("SELECT id FROM medication WHERE medicationName = ?");
        $stmt->bind_param("s", $medicationName);
        $stmt->execute();
        $result = $stmt->get_result();
        $med = $result->fetch_assoc();

        if ($med) {
            $med_id = $med['id'];

            // إدخال الدواء في جدول prescription
            $stmt = $conn->prepare("INSERT INTO prescription (AppointmentID, MedicationID) VALUES (?, ?)");
            $stmt->bind_param("ii", $appointment_id, $med_id);
            $stmt->execute();
        }
    }

    // تحديث حالة الموعد إلى Done
    $stmt = $conn->prepare("UPDATE appointment SET status = 'Done' WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    // إعادة التوجيه إلى صفحة الدكتور
    header("Location: Doctor_Page.php");
    exit();
}
?>
