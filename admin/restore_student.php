<?php
require_once "auth.php";
require_once "../classes/student.php";

if (!isset($_GET['id'])) { header("Location: recycle_bin.php"); exit; }

$student_id = $_GET['id'];
$studentObj = new Student();

if ($_SESSION['role'] === 'Admissions Officer') {
    $studentData = $studentObj->viewStudent($student_id); 
}

if ($studentObj->restoreStudent($student_id)) {
    header("Location: recycle_bin.php?message=Student restored successfully.");
} else {
    header("Location: recycle_bin.php?error=Failed to restore student.");
}
?>