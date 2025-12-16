<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $teacher_id = $_POST['teacher_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $specialization = $_POST['specialization'];
    $is_class_teacher = isset($_POST['is_class_teacher']) ? 1 : 0;

    $class_ids = isset($_POST['classes']) ? $_POST['classes'] : [];
    if (!is_array($class_ids)) $class_ids = [$class_ids];

    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    if (!is_array($subjects)) $subjects = [$subjects];

    // 1. Update teacher info
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE teachers SET name=?, email=?, specialization=?, is_class_teacher=?, password=? WHERE teacher_id=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) die("Prepare failed (teachers update with password): " . $conn->error);
        $stmt->bind_param("sssiss", $name, $email, $specialization, $is_class_teacher, $password, $teacher_id);
    } else {
        $sql = "UPDATE teachers SET name=?, email=?, specialization=?, is_class_teacher=? WHERE teacher_id=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) die("Prepare failed (teachers update): " . $conn->error);
        $stmt->bind_param("sssis", $name, $email, $specialization, $is_class_teacher, $teacher_id);
    }
    if (!$stmt->execute()) die("Execute failed (teachers update): " . $stmt->error);

    // 2. Update class assignments
    $stmtDelClass = $conn->prepare("DELETE FROM class_teachers WHERE teacher_id=?");
    if (!$stmtDelClass) die("Prepare failed (delete class_teachers): " . $conn->error);
    $stmtDelClass->bind_param("s", $teacher_id);
    if (!$stmtDelClass->execute()) die("Execute failed (delete class_teachers): " . $stmtDelClass->error);

    if (!empty($class_ids)) {
        $stmt2 = $conn->prepare("INSERT INTO class_teachers (class_id, teacher_id) VALUES (?, ?)");
        if (!$stmt2) die("Prepare failed (insert class_teachers): " . $conn->error);
        foreach ($class_ids as $cid) {
            $cid = (int)$cid;
            $stmt2->bind_param("is", $cid, $teacher_id);
            if (!$stmt2->execute()) die("Execute failed (insert class_teachers): " . $stmt2->error);
        }
    }

    // 3. Update subject assignments
    $stmtDelSub = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id=?");
    if (!$stmtDelSub) die("Prepare failed (delete teacher_subjects): " . $conn->error);
    $stmtDelSub->bind_param("s", $teacher_id);
    if (!$stmtDelSub->execute()) die("Execute failed (delete teacher_subjects): " . $stmtDelSub->error);

    if (!empty($subjects)) {
        $stmt3 = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
        if (!$stmt3) die("Prepare failed (insert teacher_subjects): " . $conn->error);
        foreach ($subjects as $sub_id) {
            $sub_id = (int)$sub_id;
            $stmt3->bind_param("si", $teacher_id, $sub_id);
            if (!$stmt3->execute()) die("Execute failed (insert teacher_subjects): " . $stmt3->error);
        }
    }

    // 4. Update class + subject + teacher mapping
    $stmtDelMap = $conn->prepare("DELETE FROM class_subject_teachers WHERE teacher_id=?");
    if (!$stmtDelMap) die("Prepare failed (delete class_subject_teachers): " . $conn->error);
    $stmtDelMap->bind_param("s", $teacher_id);
    if (!$stmtDelMap->execute()) die("Execute failed (delete class_subject_teachers): " . $stmtDelMap->error);

    if (!empty($class_ids) && !empty($subjects)) {
        $stmt4 = $conn->prepare("INSERT INTO class_subject_teachers (class_id, subject_id, teacher_id) VALUES (?, ?, ?)");
        if (!$stmt4) die("Prepare failed (insert class_subject_teachers): " . $conn->error);
        foreach ($class_ids as $cid) {
            foreach ($subjects as $sub_id) {
                $cid = (int)$cid;
                $sub_id = (int)$sub_id;
                $stmt4->bind_param("iis", $cid, $sub_id, $teacher_id);
                if (!$stmt4->execute()) die("Execute failed (insert class_subject_teachers): " . $stmt4->error);
            }
        }
    }

    $_SESSION['success'] = "Teacher updated successfully!";
    header("Location: Teachersshow.php");
    exit;
}
?>
