<?php
require_once "auth.php";
require_once "../classes/database.php";

$page_title = "Notifications";
$notifications = [];
$db = new Database();
$conn = $db->connect();

$userRole = $_SESSION['role'] ?? '';
$assignedCollege = $_SESSION['college_assigned'] ?? '';

$seenColumn = ($userRole === 'Super Admin') ? 'is_seen_by_admin' : 'is_seen_by_officer';

try {
    $sql_get = "SELECT 
                    s.student_id, 
                    s.first_name, 
                    s.last_name, 
                    adm.date_submitted,
                    adm.$seenColumn AS is_seen, 
                    rc.college_name AS college
                FROM student s
                JOIN admission adm ON s.student_id = adm.student_id
                LEFT JOIN ref_college rc ON adm.college_id = rc.college_id
                WHERE s.deleted_at IS NULL";
    
    $params = [];

    // Filter for AO
    if ($userRole === 'Admissions Officer' && !empty($assignedCollege)) {
        $sql_get .= " AND rc.college_name = :college";
        $params[':college'] = $assignedCollege;
    }

    $sql_get .= " ORDER BY adm.date_submitted DESC, s.student_id DESC LIMIT 50";
    
    $stmt_get = $conn->prepare($sql_get);
    $stmt_get->execute($params);
    $notifications = $stmt_get->fetchAll(PDO::FETCH_ASSOC);

    if ($userRole === 'Admissions Officer' && !empty($assignedCollege)) {
        $sql_update = "UPDATE admission adm
                       LEFT JOIN ref_college rc ON adm.college_id = rc.college_id
                       SET adm.$seenColumn = 1 
                       WHERE adm.$seenColumn = 0 
                       AND rc.college_name = :college";
        
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':college' => $assignedCollege]);
    } else {
        $sql_update = "UPDATE admission SET $seenColumn = 1 WHERE $seenColumn = 0";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute();
    }

} catch (Exception $e) {
    error_log("Error on notifications page: " . $e->getMessage());
    $error_message = "A database error occurred.";
}

include "template_header.php";
?>

<style>
    .notification-item { transition: background-color 0.3s; border-left: 4px solid transparent; }
    .notification-item.unread { background-color: #f0f8ff; border-left-color: #007bff; }
    .notification-item.read { background-color: #ffffff; opacity: 0.85; }
    .badge-new { background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; margin-left: 8px; vertical-align: text-top; text-transform: uppercase; }
</style>

<div class="notification-page-container">
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h2 style="color: #A40404; margin:0;">
            Recent Activity 
            <?php if(!empty($assignedCollege)) echo "<small style='font-size:0.6em; color:#666;'>(" . htmlspecialchars($assignedCollege) . ")</small>"; ?>
        </h2>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="card no-notifications">
            <i class="fas fa-bell-slash"></i>
            <h3>No Notifications</h3>
            <p>There are no recent applications to show.</p>
        </div>
    <?php else: ?>
        <div class="notification-list">
            <?php foreach ($notifications as $notification): ?>
                <?php 
                    $is_unread = ($notification['is_seen'] == 0);
                    $item_class = $is_unread ? 'unread' : 'read';
                ?>
                <div class="notification-item <?php echo $item_class; ?>">
                    <div class="notification-icon">
                        <i class="fas fa-user-plus" style="color: <?php echo $is_unread ? '#007bff' : '#6c757d'; ?>"></i>
                    </div>
                    <div class="notification-content">
                        <strong>
                            New Application
                            <?php if($is_unread): ?><span class="badge-new">NEW</span><?php endif; ?>
                        </strong> 
                        <br>
                        Applicant: <strong><?php echo htmlspecialchars($notification['first_name'] . ' ' . $notification['last_name']); ?></strong>
                        <span class="notification-date">
                            Submitted: <?php echo date("M j, Y", strtotime($notification['date_submitted'])); ?>
                        </span>
                        <?php if($userRole !== 'Admissions Officer'): ?>
                            <br><small style="color:#888;"><?php echo htmlspecialchars($notification['college'] ?? 'Unknown College'); ?></small>
                        <?php endif; ?>
                    </div>
                    <a href="view_student.php?id=<?php echo $notification['student_id']; ?>" class="btn-view">
                        View <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include "template_footer.php"; ?>