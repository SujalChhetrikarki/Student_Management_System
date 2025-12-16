
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if (isset($_POST['student_id'], $_POST['new_class_id'])) {
    $student_id = intval($_POST['student_id']);
    $new_class_id = intval($_POST['new_class_id']);

    $sql = "UPDATE students SET class_id=? WHERE student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_class_id, $student_id);

    if ($stmt->execute()) {
        header("Location: Managestudent.php?success=Class upgraded successfully");
        exit;
    } else {
        die("❌ Error upgrading class: " . $conn->error);
    }
}
?>
