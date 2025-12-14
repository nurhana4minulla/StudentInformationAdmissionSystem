<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "auth.php"; 
require_once "../classes/student.php";

$db = new Database();
$conn = $db->connect();

$userRole = $_SESSION['role'] ?? '';
$assignedCollege = $_SESSION['college_assigned'] ?? '';

$seenColumn = ($userRole === 'Super Admin') ? 'is_seen_by_admin' : 'is_seen_by_officer';

try {
    $currentDate = date('Y-m-d');
    
    $sql = "SELECT COUNT(a.admission_id) 
            FROM admission a
            JOIN student s ON a.student_id = s.student_id
            LEFT JOIN ref_college rc ON a.college_id = rc.college_id
            WHERE a.$seenColumn = 0 
            AND s.deleted_at IS NULL
            AND a.date_submitted <= :currentDate";
            
    $params = [':currentDate' => $currentDate];

    if ($userRole === 'Admissions Officer' && !empty($assignedCollege)) {
        $sql .= " AND rc.college_name = :college";
        $params[':college'] = $assignedCollege;
    }
            
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $count = $stmt->fetchColumn();

    ob_end_clean();
    echo $count;

} catch (PDOException $e) {
    ob_end_clean();
    echo '0'; 
}
?>