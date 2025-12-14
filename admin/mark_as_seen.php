<?php
require_once "auth.php"; 
require_once "../classes/student.php";

$db = new Database();
$conn = $db->connect();

$userRole = $_SESSION['role'] ?? '';
$seenColumn = ($userRole === 'Super Admin') ? 'is_seen_by_admin' : 'is_seen_by_officer';

try {
    $currentDate = date('Y-m-d');
    
    $sql = "UPDATE admission a
            JOIN student s ON a.student_id = s.student_id
            SET a.$seenColumn = 1 
            WHERE a.$seenColumn = 0 
            AND s.deleted_at IS NULL
            AND a.date_submitted <= :currentDate";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':currentDate', $currentDate);
    $stmt->execute();
    
    header("Location: manage_students.php");
    exit;

} catch (PDOException $e) {
    error_log("Error in mark_as_seen.php: " . $e->getMessage());
    header("Location: dashboard.php?error=Could not update notifications.");
    exit;
}
?>