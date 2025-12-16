<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    
    // Delete student
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    
    header("Location: Managestudent.php?msg=deleted");
    exit;
}
?>
