<?php
require_once "auth.php";
require_once "../classes/student.php";

$studentObj = new Student();
$page_title = "Recycle Bin";

$userRole = $_SESSION['role'] ?? '';
$assignedCollege = $_SESSION['college_assigned'] ?? '';

$filterCollege = ($userRole === 'Admissions Officer') ? $assignedCollege : null;

// fetch data
$deletedStudents = $studentObj->viewDeletedStudents($filterCollege);

include "template_header.php";
?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 15px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #A40404; padding-bottom: 10px;">
        <h2 style="color: #A40404; margin: 0; font-size: 1.8rem;">
            <i class="fas fa-trash-restore"></i> Recycle Bin
            <?php if ($filterCollege): ?>
                <small style="font-size: 0.6em; color: #666; vertical-align: middle;">
                    (<?php echo htmlspecialchars($filterCollege); ?>)
                </small>
            <?php endif; ?>
        </h2>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <span><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <span><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_GET['error']); ?></span>
        </div>
    <?php endif; ?>

    <div class="student-list" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left; color: #555;">Student Name</th>
                    <th style="padding: 12px; text-align: left; color: #555;">Academic Program</th>
                    <th style="padding: 12px; text-align: left; color: #555;">Date Deleted</th>
                    <th style="padding: 12px; text-align: left; color: #555;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deletedStudents)): ?>
                    <tr>
                        <td colspan="4" style="padding: 40px; text-align: center; color: #6c757d;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                            Recycle Bin is empty.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deletedStudents as $student): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px;">
                                <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo htmlspecialchars($student['academic_program']); ?>
                                <?php if ($userRole === 'Super Admin' && !empty($student['college'])): ?>
                                    <div style="font-size: 0.85em; color: #888; margin-top: 4px;">
                                        <?php echo htmlspecialchars($student['college']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; color: #dc3545;">
                                <?php echo date('M d, Y h:i A', strtotime($student['deleted_at'])); ?>
                            </td>
                            <td style="padding: 12px; white-space: nowrap;">
                                <a href="restore_student.php?id=<?php echo $student['student_id']; ?>" 
                                   style="display: inline-block; background-color: #28a745; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 0.9em; margin-right: 5px;"
                                   onclick="return confirm('Are you sure you want to restore this student?');">
                                    <i class="fas fa-undo"></i> Restore
                                </a>

                                <?php if ($userRole === 'Super Admin'): ?>
                                    <a href="permanent_delete_student.php?id=<?php echo $student['student_id']; ?>" 
                                       style="display: inline-block; background-color: #dc3545; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 0.9em;"
                                       onclick="return confirm('WARNING: This cannot be undone.\n\nAre you sure you want to permanently delete this record?');">
                                        <i class="fas fa-times"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "template_footer.php"; ?>