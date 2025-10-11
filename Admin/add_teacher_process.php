<?php
session_start();
include '../Database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_teacher.php");
    exit;
}

// Helper to rollback and set error
function fail($conn, $msg) {
    if ($conn->errno === 0) {
        // no-op
    }
    $_SESSION['error'] = $msg;
    header("Location: add_teacher.php");
    exit;
}

$teacher_id = isset($_POST['teacher_id']) ? trim($_POST['teacher_id']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password_raw = isset($_POST['password']) ? $_POST['password'] : '';
$specialization = isset($_POST['specialization']) ? trim($_POST['specialization']) : '';
$is_class_teacher = isset($_POST['is_class_teacher']) ? 1 : 0;
$class_teacher_class = isset($_POST['class_teacher_class']) && $_POST['class_teacher_class'] !== '' ? (int)$_POST['class_teacher_class'] : null;

// teaching classes and subjects per class
$teaching_classes = isset($_POST['teaching_classes']) ? $_POST['teaching_classes'] : []; // array of class ids (strings)
if (!is_array($teaching_classes)) $teaching_classes = [$teaching_classes];

$subjects_for_class = isset($_POST['subjects_for_class']) ? $_POST['subjects_for_class'] : []; // associative array class_id => [subject_ids]

// Basic validation
if ($teacher_id === '' || $name === '' || $email === '' || $password_raw === '' || $specialization === '') {
    $_SESSION['error'] = "Please fill all required fields.";
    header("Location: add_teacher.php");
    exit;
}

// If is_class_teacher checked, a class must be selected
if ($is_class_teacher && !$class_teacher_class) {
    $_SESSION['error'] = "You checked Class Teacher — please select the class for which this teacher will be Class Teacher.";
    header("Location: add_teacher.php");
    exit;
}

// If any subjects are provided for a class, ensure that class is included in teaching_classes
foreach ($subjects_for_class as $cid => $subs) {
    if (!in_array($cid, $teaching_classes)) {
        // If subject(s) assigned for a class but that class not marked as teaching class => error.
        $_SESSION['error'] = "You selected subjects for class ID {$cid} but did not check that class under 'Assign Subjects to Classes'. Please check the class checkbox for which the teacher will teach those subjects.";
        header("Location: add_teacher.php");
        exit;
    }
}

// Enforce that class_teacher_class (if set) is either in teaching_classes or we can add it to teaching_classes implicitly
if ($is_class_teacher && $class_teacher_class && !in_array((string)$class_teacher_class, $teaching_classes) && !in_array($class_teacher_class, $teaching_classes)) {
    // We'll allow class_teacher class to be considered as a teaching class even if admin didn't check it,
    // but subjects for that class must still be chosen manually if desired.
    $teaching_classes[] = (string)$class_teacher_class;
}

// start transaction
$conn->begin_transaction();

try {
    // 1) If is_class_teacher, ensure the selected class doesn't already have a class teacher
    if ($is_class_teacher && $class_teacher_class) {
        $check = $conn->prepare("SELECT COUNT(*) FROM class_teachers WHERE class_id = ?");
        if (!$check) throw new Exception("Prepare failed: " . $conn->error);
        $check->bind_param("i", $class_teacher_class);
        $check->execute();
        $check->bind_result($existsCount);
        $check->fetch();
        $check->close();
        if ($existsCount > 0) {
            throw new Exception("Selected class already has a Class Teacher. Please choose another class or uncheck the Class Teacher option.");
        }
    }

    // 2) Insert into teachers
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
    $insertTeacher = $conn->prepare("INSERT INTO teachers (teacher_id, name, email, password, specialization, is_class_teacher) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$insertTeacher) throw new Exception("Prepare failed (insert teacher): " . $conn->error);
    $insertTeacher->bind_param("sssssi", $teacher_id, $name, $email, $password_hash, $specialization, $is_class_teacher);
    if (!$insertTeacher->execute()) {
        // possible duplicate teacher_id or email constraint
        throw new Exception("Error inserting teacher: " . $insertTeacher->error);
    }
    $insertTeacher->close();

    // 3) If is_class_teacher, insert into class_teachers (only one)
    if ($is_class_teacher && $class_teacher_class) {
        $insCT = $conn->prepare("INSERT INTO class_teachers (class_id, teacher_id) VALUES (?, ?)");
        if (!$insCT) throw new Exception("Prepare failed (class_teachers insert): " . $conn->error);
        $insCT->bind_param("is", $class_teacher_class, $teacher_id);
        if (!$insCT->execute()) {
            throw new Exception("Error assigning class teacher: " . $insCT->error);
        }
        $insCT->close();
    }

    // 4) For each teaching class, insert class_subject_teachers entries for selected subjects
    // Also insert into teacher_subjects (unique pairs) to indicate teacher teaches these subjects (optional convenience)
    $insertCST = $conn->prepare("INSERT INTO class_subject_teachers (class_id, subject_id, teacher_id) VALUES (?, ?, ?)");
    if (!$insertCST) throw new Exception("Prepare failed (class_subject_teachers insert): " . $conn->error);

    $insertTS = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
    if (!$insertTS) {
        // If teacher_subjects table not present, we continue without it (just use class_subject_teachers)
        $insertTS = null;
    }

    // To avoid duplicate insertion into teacher_subjects, track inserted subject ids
    $insertedTeacherSubjects = [];

    foreach ($teaching_classes as $classId) {
        $cid = (int)$classId;
        // get subject list for this class (if any)
        $subList = [];
        if (isset($subjects_for_class[$cid]) && is_array($subjects_for_class[$cid])) {
            $subList = $subjects_for_class[$cid];
        } else {
            // Also handle string keys (sometimes POST keys are strings)
            if (isset($subjects_for_class[(string)$cid]) && is_array($subjects_for_class[(string)$cid])) {
                $subList = $subjects_for_class[(string)$cid];
            }
        }

        foreach ($subList as $subId) {
            $sid = (int)$subId;
            // insert into class_subject_teachers
            $insertCST->bind_param("iis", $cid, $sid, $teacher_id);
            if (!$insertCST->execute()) {
                throw new Exception("Error inserting class_subject_teachers for class {$cid}, subject {$sid}: " . $insertCST->error);
            }

            // insert into teacher_subjects if prepared
            if ($insertTS) {
                if (!in_array($sid, $insertedTeacherSubjects, true)) {
                    $insertTS->bind_param("si", $teacher_id, $sid);
                    // ignore duplicate-key error if it exists (unique constraint) — but check execute
                    if (!$insertTS->execute()) {
                        // If duplicate, skip; otherwise throw
                        // MySQL error code 1062 is duplicate entry
                        if ($conn->errno != 1062) {
                            throw new Exception("Error inserting teacher_subjects: " . $insertTS->error);
                        }
                    } else {
                        $insertedTeacherSubjects[] = $sid;
                    }
                }
            }
        }
    }

    if ($insertCST) $insertCST->close();
    if ($insertTS) $insertTS->close();

    // commit
    $conn->commit();
    $_SESSION['success'] = "Teacher added successfully!";
    header("Location: add_teacher.php");
    exit;

} catch (Exception $ex) {
    $conn->rollback();
    $_SESSION['error'] = $ex->getMessage();
    header("Location: add_teacher.php");
    exit;
}
?>
