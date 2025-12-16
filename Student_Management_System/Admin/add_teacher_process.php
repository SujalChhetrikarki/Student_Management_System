<?php
session_start();
include '../Database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $teacher_id = $_POST['teacher_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $specialization = $_POST['specialization'];
    $is_class_teacher = isset($_POST['is_class_teacher']) ? 1 : 0;

    $class_ids = isset($_POST['class_id']) ? $_POST['class_id'] : [];
    if (!is_array($class_ids)) $class_ids = [$class_ids];

    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    if (!is_array($subjects)) $subjects = [$subjects];

    // 1. Insert teacher
    $sql = "INSERT INTO teachers (teacher_id, name, email, password, specialization, is_class_teacher) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare failed (teachers insert): " . $conn->error);
    $stmt->bind_param("sssssi", $teacher_id, $name, $email, $password, $specialization, $is_class_teacher);
    if (!$stmt->execute()) die("Execute failed (teachers insert): " . $stmt->error);

    // 2. Assign class teacher
    if ($is_class_teacher && !empty($class_ids)) {
        $stmt2 = $conn->prepare("INSERT INTO class_teachers (class_id, teacher_id) VALUES (?, ?)");
        if (!$stmt2) die("Prepare failed (class_teachers insert): " . $conn->error);
        foreach ($class_ids as $cid) {
            $cid = (int)$cid;
            $stmt2->bind_param("is", $cid, $teacher_id);
            if (!$stmt2->execute()) die("Execute failed (class_teachers insert): " . $stmt2->error);
        }
    }

    // 3. Assign teacher subjects
    if (!empty($subjects)) {
        $stmt3 = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
        if (!$stmt3) die("Prepare failed (teacher_subjects insert): " . $conn->error);
        foreach ($subjects as $sub_id) {
            $sub_id = (int)$sub_id;
            $stmt3->bind_param("si", $teacher_id, $sub_id);
            if (!$stmt3->execute()) die("Execute failed (teacher_subjects insert): " . $stmt3->error);
        }
    }

    // 4. Assign class + subject + teacher mapping
    if (!empty($class_ids) && !empty($subjects)) {
        $stmt4 = $conn->prepare("INSERT INTO class_subject_teachers (class_id, subject_id, teacher_id) VALUES (?, ?, ?)");
        if (!$stmt4) die("Prepare failed (class_subject_teachers insert): " . $conn->error);
        foreach ($class_ids as $cid) {
            foreach ($subjects as $sub_id) {
                $cid = (int)$cid;
                $sub_id = (int)$sub_id;
                $stmt4->bind_param("iis", $cid, $sub_id, $teacher_id);
                if (!$stmt4->execute()) die("Execute failed (class_subject_teachers insert): " . $stmt4->error);
            }
        }
    }

    $_SESSION['success'] = "Teacher added successfully!";
    header("Location: add_teacher.php");
    exit;
}
?>
