<?php
require_once "auth.php"; 
require_once "../classes/student.php";

$studentObj = new Student();
$page_title = "Student Management";

$currentTab = $_GET['tab'] ?? 'Pending'; 

$allColleges = $studentObj->getColleges(); 
$allPrograms = $studentObj->getPrograms();

$programsByCollege = [];
if ($allPrograms) {
    foreach ($allPrograms as $prog) {
        $programsByCollege[$prog['college_id']][] = [
            'id' => $prog['program_id'],
            'name' => $prog['program_name']
        ];
    }
}

//  FILTER LOGIC 
$searchTerm = $_GET['search'] ?? '';
$filterProgram = $_GET['program'] ?? ''; 
$filterClassification = $_GET['classification'] ?? ''; 

$userRole = $_SESSION['role'] ?? 'Admissions Officer';
$assignedCollegeName = $_SESSION['college_assigned'] ?? '';

$filterCollege = $_GET['college'] ?? '';

if ($userRole === 'Admissions Officer') {
    foreach ($allColleges as $col) {
        if ($col['college_name'] === $assignedCollegeName) {
            $filterCollege = $col['college_id'];
            break;
        }
    }
}

function getDistinctClassifications($conn) {
    try {
        $sql = "SELECT DISTINCT enrollment_status FROM admission 
                WHERE enrollment_status IS NOT NULL AND enrollment_status != '' 
                ORDER BY enrollment_status ASC";
        return $conn->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) { return []; }
}
$classifications = getDistinctClassifications($studentObj->connect());

$students = $studentObj->viewAllStudents($searchTerm, $filterProgram, $filterClassification, $filterCollege, $currentTab);

$query_params = [
    'search' => $searchTerm, 
    'program' => $filterProgram, 
    'classification' => $filterClassification, 
    'college' => $filterCollege, 
    'tab' => $currentTab
];
$export_query_string = http_build_query($query_params);

include "template_header.php";
?>

<style>
    .tabs { 
        display: flex; 
        align-items: center; 
        border-bottom: 2px solid #ddd; 
        margin-bottom: 20px; 
    }
    
    .tab-link {
        padding: 12px 25px;
        text-decoration: none;
        color: #555;
        font-weight: 600;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-bottom: none;
        margin-right: 5px;
        border-radius: 5px 5px 0 0;
        transition: all 0.2s;
    }
    .tab-link:hover { background: #e2e6ea; color: #333; }
    .tab-link.active {
        background: #fff;
        color: #A40404;
        border-color: #ddd #ddd #fff #ddd;
        margin-bottom: -2px;
        border-top: 3px solid #A40404;
    }

 
    .header-export-btn {
        margin-left: auto;
        background-color: #28a745;
        color: white;
        padding: 8px 15px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.9em;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background 0.3s;
    }
    .header-export-btn:hover {
        background-color: #218838;
        color: white;
    }
    .header-export-btn i {
        margin-right: 5px;
    }
</style>

<div class="tabs">
    <a href="manage_students.php?tab=Pending" class="tab-link <?php echo ($currentTab == 'Pending') ? 'active' : ''; ?>">
        <i class="fas fa-clock"></i> Pending Applicants
    </a>
    <a href="manage_students.php?tab=Enrolled" class="tab-link <?php echo ($currentTab == 'Enrolled') ? 'active' : ''; ?>">
        <i class="fas fa-user-graduate"></i> Enrolled Students
    </a>

    <a href="export.php?<?php echo $export_query_string; ?>" class="header-export-btn">
        <i class="fas fa-file-csv"></i> Export CSV
    </a>
</div>

<div class="toolbar">
    <div class="filter-controls">
        <form action="manage_students.php" method="GET" class="filter-form">
            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($currentTab); ?>">
            
            <input type="search" name="search" placeholder="Search name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            
            <?php if ($userRole === 'Super Admin'): ?>
                <select name="college" id="collegeFilter">
                    <option value="">Filter by College</option>
                    <?php foreach ($allColleges as $col): ?>
                        <option value="<?php echo $col['college_id']; ?>" <?php echo ($filterCollege == $col['college_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($col['college_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="college" id="collegeFilter" value="<?php echo htmlspecialchars($filterCollege); ?>">
            <?php endif; ?>

            <select name="program" id="programFilter">
                <option value="">Filter by Program</option>
                <?php foreach ($allPrograms as $prog): ?>
                    <option value="<?php echo $prog['program_id']; ?>" <?php echo ($filterProgram == $prog['program_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($prog['program_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="classification">
                <option value="">Student Type (All)</option>
                <?php foreach ($classifications as $cls): ?>
                    <option value="<?php echo htmlspecialchars($cls); ?>" <?php echo ($filterClassification == $cls) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cls); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Apply</button>
            <a href="manage_students.php?tab=<?php echo $currentTab; ?>" class="reset-btn btn"><i class="fas fa-undo"></i> Reset</a>
        </form>
    </div>
    
    

<?php if (!empty($searchTerm) || !empty($filterProgram) || !empty($filterClassification) || (!empty($filterCollege) && $userRole === 'Super Admin')): ?>
    <div class="filter-status">
        Showing <strong><?php echo htmlspecialchars($currentTab); ?></strong> results.
        (<a href="manage_students.php?tab=<?php echo $currentTab; ?>">Clear Filters</a>)
    </div>
<?php endif; ?>

<div class="student-list">
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Academic Program</th>
                <th>Classification</th>
                <th>Date Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="6" class="no-records">
                        No <strong><?php echo htmlspecialchars($currentTab); ?></strong> students found matching your filters.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                            <br><small style="color:#777;"><?php echo htmlspecialchars($student['college'] ?? 'No College'); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($student['academic_program'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['enrollment_status'] ?? 'N/A'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($student['date_submitted'])); ?></td>
                        <td>
                            <?php 
                                $status = $student['admission_status'] ?: 'Pending';
                                $color = ($status === 'Enrolled') ? 'green' : '#d9534f';
                            ?>
                            <span style="color: <?php echo $color; ?>; font-weight: bold; font-size: 0.9em;">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <td class="action-links">
                            <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="view" title="View"><i class="fas fa-eye"></i></a>
                            <a href="edit_student.php?id=<?php echo $student['student_id']; ?>" class="edit" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="delete_student.php?id=<?php echo $student['student_id']; ?>" class="delete" onclick="return confirm('Move to Recycle Bin?');" title="Delete"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dbPrograms = <?php echo json_encode($programsByCollege); ?>;
    const collegeSelect = document.getElementById('collegeFilter');
    const programSelect = document.getElementById('programFilter');
    const currentProgram = "<?php echo $filterProgram; ?>"; 

    function populatePrograms(collegeId) {
        programSelect.innerHTML = '<option value="">Filter by Program</option>';
        
        // collegeId is now guaranteed to be numeric (ID) or empty
        if (collegeId && dbPrograms[collegeId]) {
            dbPrograms[collegeId].forEach(prog => {
                const option = document.createElement('option');
                option.value = prog.id;
                option.textContent = prog.name;
                if (prog.id == currentProgram) { option.selected = true; }
                programSelect.appendChild(option);
            });
        }
    }

    if (collegeSelect) {
        // Run on page load (Handles both Admin selection and Officer hidden input)
        const initialVal = collegeSelect.value;
        if (initialVal) {
            populatePrograms(initialVal);
        }

        // Run on change (For Super Admin dropdown)
        collegeSelect.addEventListener('change', function() {
            populatePrograms(this.value);
        });
    }
});
</script>

<?php include "template_footer.php"; ?>