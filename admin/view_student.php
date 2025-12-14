<?php
// secure 
require_once "auth.php"; 
require_once "../classes/student.php";

$page_title = "View Student Application";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php"); 
    exit;
}

$student_id = $_GET['id'];
$studentObj = new Student();

$colleges = $studentObj->getColleges();
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

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admit_student') {
    $admissionData = [
        'college' => $_POST['college'],        
        'program' => $_POST['academicProgram'], 
        'school_year' => $_POST['schoolYear'],
        'admission_type' => $_POST['typeAdmission'],
        'enrollment_status' => $_POST['enrollmentStatus'], 
        'admission_status' => $_POST['admissionStatus'],
        'scholarship' => $_POST['scholarship'],
        'semester' => $_POST['semester'],
        'year_level' => $_POST['yearLevel']
    ];

    $oldData = $studentObj->viewStudent($student_id);
    $oldAdmStatus = $oldData['admission_status'];

    if ($studentObj->admitStudent($student_id, $admissionData)) {
        
        if ($oldAdmStatus !== 'Enrolled' && $admissionData['admission_status'] === 'Enrolled') {
            require_once "../classes/send_email.php";
            
            $programName = "Academic Program"; 
            foreach ($allPrograms as $prog) {
                if ($prog['program_id'] == $admissionData['program']) {
                    $programName = $prog['program_name'];
                    break;
                }
            }
            sendEnrollmentNotification(
                $oldData['email'], 
                $oldData['first_name'] . ' ' . $oldData['last_name'], 
                $programName 
            );
            
            $success_msg = "Student successfully ENROLLED and notification email sent!";
        } else {
            $success_msg = "Student details updated successfully!";
        }
    } else {
        $error_msg = "Failed to update record.";
    }
}

$student = $studentObj->viewStudent($student_id);

if (!$student) {
    echo "No student record found.";
    exit;
}

function e($value) {
    return htmlspecialchars($value ?? ''); 
}

$ethnicity = !empty($student['ethnicity']) ? json_decode($student['ethnicity'], true) : [];
$disability = !empty($student['disability']) ? json_decode($student['disability'], true) : [];

include "template_header.php";
?>

<style>
    .view-container { 
        max-width: 1000px; margin: 0 auto; background-color: #fff;
        border-radius: 10px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden; margin-bottom: 50px;
    }
    .view-header { 
        display: flex; justify-content: space-between; align-items: center; 
        border-bottom: 2px solid #A40404; padding: 1.5rem 2rem; background-color: #fcfcfc;
    }
    .view-header h2 { margin: 0; color: #A40404; }
    
    .back-btn { background-color: #6c757d; color: #fff; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500; }
    .back-btn:hover { background-color: #5a6268; }
    .back-btn i { margin-right: 8px; }

    .print-btn {
        background-color: #007bff; color: #fff; padding: 0.6rem 1.2rem; border: none;
        border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500;
        cursor: pointer; margin-left: 10px;
    }
    .print-btn:hover { background-color: #0056b3; }
    .print-btn i { margin-right: 8px; }
    
    .data-card { border: none; border-bottom: 1px solid #eee; margin: 0; padding: 0; }
    .data-card legend { 
        font-size: 1.3em; color: #A40404; font-weight: 600; padding: 1rem 2rem 0 2rem; margin: 0;
    }
    .data-card-body { 
        padding: 1rem 2rem 2rem 2rem; display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; 
    }
    .data-item { display: flex; flex-direction: column; }
    .data-item label { font-size: 0.9rem; font-weight: 500; color: #555; margin-bottom: 5px; }
    .data-item span { 
        font-size: 1rem; color: #000; word-wrap: break-word; 
        padding: 10px 12px; background-color: #f9f9f9; 
        border: 1px solid #f0f0f0; border-radius: 5px; min-height: 20px; 
    }
    .data-item.full-width { grid-column: 1 / -1; }
    .data-item ul { padding-left: 20px; margin: 0; }
    .data-item .signature-img { 
        max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px; padding: 5px;
    }
    .data-section-title { 
        font-size: 1.1rem; font-weight: 600; color: #333; 
        grid-column: 1 / -1; border-bottom: 1px dashed #ccc; padding-bottom: 8px; margin-top: 1rem; 
    }
    .data-section-title:first-of-type { margin-top: 0; }

    /* ACTION PANEL STYLES */
    .action-panel { background-color: #fff9f9; border-top: 4px solid #A40404; padding: 2rem; }
    .action-header { display: flex; align-items: center; margin-bottom: 1.5rem; }
    .action-header i { font-size: 1.5rem; color: #A40404; margin-right: 10px; }
    .action-header h3 { margin: 0; color: #333; }
    
    .admit-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { font-weight: 600; font-size: 0.9rem; color: #444; margin-bottom: 5px; }
    .form-group select, .form-group input { padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; }
    .form-group select:focus, .form-group input:focus { border-color: #A40404; outline: none; }
    .form-actions { grid-column: 1 / -1; margin-top: 1.5rem; text-align: right; padding-top: 1.5rem; border-top: 1px solid #ddd; }
    .btn-save { background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 5px; font-size: 1rem; cursor: pointer; transition: 0.3s; }
    .btn-save:hover { background-color: #218838; }
    
    .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.85rem; font-weight: 600; display: inline-block; }
    .status-pending { background-color: #ffeeba; color: #856404; }
    .status-enrolled { background-color: #d4edda; color: #155724; }
    .status-rejected { background-color: #f8d7da; color: #721c24; }
</style>

<div class="view-container">
    
    <?php if ($success_msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; text-align: center; border-bottom: 1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; text-align: center; border-bottom: 1px solid #f5c6cb;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div class="view-header">
        <div>
            <h2>Student Application Details</h2>
            <div style="margin-top: 5px;">
                Status: 
                <?php 
                    $stat = $student['enrollment_status'] ?? 'Pending';
                    $cls = ($stat == 'Enrolled') ? 'status-enrolled' : (($stat == 'Rejected') ? 'status-rejected' : 'status-pending');
                ?>
                <span class="status-badge <?php echo $cls; ?>"><?php echo e($stat); ?></span>
            </div>
        </div>
        <div> 
            <a href="manage_students.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="print_student.php?id=<?php echo $student_id; ?>" target="_blank" class="print-btn"><i class="fas fa-print"></i> Print</a>
        </div>
    </div>

    <fieldset class="data-card">
        <legend>Student Information</legend>
        <div class="data-card-body">
            <div class="data-item" style="grid-row: span 3; justify-self: center;">
                <label style="text-align: center;">ID Photo</label>
                <div style="width: 150px; height: 150px; overflow: hidden; border: 3px solid #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center; background: #f9f9f9;">
                    <?php 
                    $photoDisplay = '<span style="color:#ccc; font-size:0.8rem;">No Photo</span>';
                    if (!empty($student['photo_path'])) {
                        $pFilename = basename($student['photo_path']);
                        if (file_exists(__DIR__ . "/../admission/uploads/photos/" . $pFilename)) {
                            $imgSrc = "../admission/uploads/photos/" . $pFilename;
                            $photoDisplay = '<img src="' . htmlspecialchars($imgSrc) . '" style="width: 100%; height: 100%; object-fit: cover;">';
                        }
                    }
                    echo $photoDisplay; 
                    ?>
                </div>
            </div>
            <div class="data-item"><label>Last Name</label><span><?php echo e($student['last_name']); ?></span></div>
            <div class="data-item"><label>First Name</label><span><?php echo e($student['first_name']); ?></span></div>
            <div class="data-item"><label>Middle Name</label><span><?php echo e($student['middle_name']); ?></span></div>
            
            <div class="data-item"><label>Sex</label><span><?php echo e($student['gender']); ?></span></div>
            <div class="data-item"><label>Date of Birth</label><span><?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></span></div>
            <div class="data-item"><label>Place of Birth</label><span><?php echo e($student['place_of_birth']); ?></span></div>

            <div class="data-item"><label>Mobile Number</label><span><?php echo e($student['mobile_no']); ?></span></div>
            <div class="data-item"><label>Telephone Number</label><span><?php echo e($student['tel_no']); ?></span></div>
            <div class="data-item"><label>Email Address</label><span><?php echo e($student['email']); ?></span></div>

            <div class="data-item"><label>Nationality</label><span><?php echo e($student['nationality']); ?></span></div>
            <div class="data-item"><label>Civil Status</label><span><?php echo e($student['civil_status']); ?></span></div>
            <div class="data-item"><label>Religion</label><span><?php echo e($student['religion']); ?></span></div>

            <div class="data-item full-width"><label>Ethnicity</label>
                <span>
                    <?php 
                    if (!empty($ethnicity)) {
                        echo "<ul>";
                        foreach ($ethnicity as $item) echo "<li>" . e($item) . "</li>";
                        if (!empty($student['ethnicity_other'])) echo "<li>Other: " . e($student['ethnicity_other']) . "</li>";
                        echo "</ul>";
                    } else { echo "N/A"; }
                    ?>
                </span>
            </div>

            <div class="data-item full-width"><label>Disability</label>
                <span>
                    <?php 
                    if (!empty($disability)) {
                        echo "<ul>";
                        foreach ($disability as $item) echo "<li>" . e($item) . "</li>";
                        if (!empty($student['disability_other'])) echo "<li>Other: " . e($student['disability_other']) . "</li>";
                        echo "</ul>";
                    } else { echo "None"; }
                    ?>
                </span>
            </div>

            <div class="data-item"><label>First in Family to College?</label><span><?php echo ($student['first_in_family'] == 1) ? 'Yes' : 'No'; ?></span></div>
            <div class="data-item"><label>Lives in Coastal Area?</label><span><?php echo ($student['coastal_area'] == 1) ? 'Yes' : 'No'; ?></span></div>
        </div>
    </fieldset>

    <fieldset class="data-card">
        <legend>Address Information</legend>
        <div class="data-card-body">
            <h3 class="data-section-title">Current Address</h3>
            <div class="data-item"><label>House/Street No.</label><span><?php echo e($student['current_house_street_no']); ?></span></div>
            <div class="data-item"><label>Barangay</label><span><?php echo e($student['current_barangay']); ?></span></div>
            <div class="data-item"><label>City</label><span><?php echo e($student['current_city']); ?></span></div>
            <div class="data-item"><label>Province</label><span><?php echo e($student['current_province']); ?></span></div>
            <div class="data-item"><label>ZIP Code</label><span><?php echo e($student['current_zip']); ?></span></div>

            <h3 class="data-section-title">Permanent Address</h3>
            <div class="data-item"><label>House/Street No.</label><span><?php echo e($student['permanent_house_street_no']); ?></span></div>
            <div class="data-item"><label>Barangay</label><span><?php echo e($student['permanent_barangay']); ?></span></div>
            <div class="data-item"><label>City</label><span><?php echo e($student['permanent_city']); ?></span></div>
            <div class="data-item"><label>Province</label><span><?php echo e($student['permanent_province']); ?></span></div>
            <div class="data-item"><label>ZIP Code</label><span><?php echo e($student['permanent_zip']); ?></span></div>
            <div class="data-item"><label>Permanent Mobile</label><span><?php echo e($student['permanent_mobile']); ?></span></div>
            <div class="data-item"><label>Permanent Tel</label><span><?php echo e($student['permanent_tel']); ?></span></div>
        </div>
    </fieldset>

    <fieldset class="data-card">
        <legend>Parent/Guardian Information</legend>
        <div class="data-card-body">
            <h3 class="data-section-title">Father's Information</h3>
            <div class="data-item"><label>Father's Name</label><span><?php echo e($student['father_name']); ?></span></div>
            <div class="data-item"><label>Education</label><span><?php echo e($student['father_education']); ?></span></div>
            <div class="data-item"><label>Occupation</label><span><?php echo e($student['father_occupation']); ?></span></div>
            
            <h3 class="data-section-title">Mother's Information</h3>
            <div class="data-item"><label>Mother's Name</label><span><?php echo e($student['mother_name']); ?></span></div>
            <div class="data-item"><label>Education</label><span><?php echo e($student['mother_education']); ?></span></div>
            <div class="data-item"><label>Occupation</label><span><?php echo e($student['mother_occupation']); ?></span></div>

            <h3 class="data-section-title">Guardian's Information</h3>
            <div class="data-item"><label>Guardian's Name</label><span><?php echo e($student['guardian_name']); ?></span></div>
            <div class="data-item"><label>Relationship</label><span><?php echo e($student['guardian_relationship']); ?></span></div>
            <div class="data-item full-width"><label>Address</label><span><?php echo e($student['guardian_address']); ?></span></div>
            <div class="data-item"><label>Telephone No.</label><span><?php echo e($student['guardian_tel']); ?></span></div>
            <div class="data-item"><label>Parent Income</label><span><?php echo e($student['parent_income']); ?></span></div>
        </div>
    </fieldset>
    
    <fieldset class="data-card">
        <legend>Educational Background</legend>
        <div class="data-card-body">
            <h3 class="data-section-title">Primary School</h3>
            <div class="data-item"><label>School Name</label><span><?php echo e($student['primary_school']); ?></span></div>
            <div class="data-item"><label>Place</label><span><?php echo e($student['primary_place']); ?></span></div>
            <div class="data-item"><label>Year Graduated</label><span><?php echo e($student['primary_year']); ?></span></div>

            <h3 class="data-section-title">Junior High School</h3>
            <div class="data-item"><label>School Name</label><span><?php echo e($student['junior_school']); ?></span></div>
            <div class="data-item"><label>Place</label><span><?php echo e($student['junior_place']); ?></span></div>
            <div class="data-item"><label>Year Graduated</label><span><?php echo e($student['junior_year']); ?></span></div>

            <h3 class="data-section-title">Senior High School</h3>
            <div class="data-item"><label>School Name</label><span><?php echo e($student['senior_school']); ?></span></div>
            <div class="data-item"><label>Place</label><span><?php echo e($student['senior_place']); ?></span></div>
            <div class="data-item"><label>Year Graduated</label><span><?php echo e($student['senior_year']); ?></span></div>
            <div class="data-item"><label>Track</label><span><?php echo e($student['track']); ?></span></div>
            <div class="data-item"><label>Strand</label><span><?php echo e($student['strand']); ?></span></div>
            
            <h3 class="data-section-title">Other</h3>
            
            <div class="data-item full-width">
                <label>College Attended Before</label>
                <span><?php echo e($student['college_attended_before_education'] ?? 'N/A'); ?></span>
            </div>
        </div>
    </fieldset>

    <fieldset class="data-card">
        <legend>Signatures & Consent</legend>
        <div class="data-card-body">
            <div class="data-item"><label>Agreed to Terms?</label><span><?php echo ($student['agree_terms'] == 1) ? 'Yes' : 'No'; ?></span></div>
            <div class="data-item">
                <label>Student Signature</label>
                <?php if (!empty($student['student_signature']) && file_exists($student['student_signature'])): ?>
                    <img src="../admission/uploads/signatures/<?php echo basename(e($student['student_signature'])); ?>" class="signature-img">
                <?php else: ?>
                    <span>No signature found.</span>
                <?php endif; ?>
            </div>
            <div class="data-item">
                <label>Parent/Guardian Signature</label>
                <?php if (!empty($student['parent_guardian_signature']) && file_exists($student['parent_guardian_signature'])): ?>
                    <img src="../admission/uploads/signatures/<?php echo basename(e($student['parent_guardian_signature'])); ?>" class="signature-img">
                <?php else: ?>
                    <span>No signature found.</span>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <div class="action-panel">
        <div class="action-header">
            <i class="fas fa-user-check"></i>
            <h3>Official Admission Evaluation</h3>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="admit_student">
            
            <div class="admit-form">
                <div class="form-group">
                    <label>College Department</label>
                    <?php 
                        // Logic: If Officer, locked to their session college. If Admin, can change.
                        $userRole = $_SESSION['role'] ?? 'Admissions Officer';
                        $assignedCollege = $_SESSION['college_assigned'] ?? '';
                        $isLocked = ($userRole === 'Admissions Officer');
                        
                        // Current ID (Not name anymore)
                        $currentVal = $student['college_id']; 
                    ?>
                    
                    <select name="college" id="collegeSelection" required <?php echo $isLocked ? 'style="background:#eee; pointer-events:none;"' : ''; ?>>
                        <option value="">Select College...</option>
                        <?php foreach ($colleges as $col): ?>
                            <option value="<?php echo $col['college_id']; ?>" 
                                <?php echo ($currentVal == $col['college_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($col['college_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if($isLocked): ?><input type="hidden" name="college" value="<?php echo $currentVal; ?>"><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Academic Program</label>
                    <select name="academicProgram" id="academicProgram" required>
                        <option value="">Select Academic Program...</option>
                        </select>
                </div>

                <div class="form-group">
                    <label>Student Classification</label>
                    <select name="enrollmentStatus" required>
                        <option value="Freshman" <?= ($student['enrollment_status'] == 'Freshman') ? 'selected' : ''; ?>>Freshman</option>
                        <option value="Transferee" <?= ($student['enrollment_status'] == 'Transferee') ? 'selected' : ''; ?>>Transferee</option>
                        <option value="Shifter" <?= ($student['enrollment_status'] == 'Shifter') ? 'selected' : ''; ?>>Shifter</option>
                        <option value="Returning/Continuing" <?= ($student['enrollment_status'] == 'Returning/Continuing') ? 'selected' : ''; ?>>Returning/Continuing</option>
                        <option value="Second Courser" <?= ($student['enrollment_status'] == 'Second Courser') ? 'selected' : ''; ?>>Second Courser</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="color: #A40404;">Admission Decision</label>
                    <select name="admissionStatus" required style="border: 2px solid #A40404; font-weight: bold;">
                        <option value="Pending" <?= ($student['admission_status'] == 'Pending') ? 'selected' : ''; ?>>Pending Evaluation</option>
                        <option value="Enrolled" <?= ($student['admission_status'] == 'Enrolled') ? 'selected' : ''; ?>>Enrolled / Approved</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Admission Type</label>
                    <select name="typeAdmission" required>
                        <option value="Regular" <?php echo ($student['admission_type'] == 'Regular') ? 'selected' : ''; ?>>Regular</option>
                        <option value="Probational" <?php echo ($student['admission_type'] == 'Probational') ? 'selected' : ''; ?>>Probational</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Year Level</label>
                    <select name="yearLevel" required>
                        <option value="1st Year" <?php echo ($student['year_level'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo ($student['year_level'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo ($student['year_level'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo ($student['year_level'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester" required>
                        <option value="1st" <?php echo ($student['semester'] == '1st') ? 'selected' : ''; ?>>1st Semester</option>
                        <option value="2nd" <?php echo ($student['semester'] == '2nd') ? 'selected' : ''; ?>>2nd Semester</option>
                        <option value="Summer" <?php echo ($student['semester'] == 'Summer') ? 'selected' : ''; ?>>Summer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>School Year</label>
                    <input type="text" name="schoolYear" value="<?php echo e($student['school_year']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Scholarship</label>
                    <input type="text" name="scholarship" value="<?php echo e($student['scholarship']); ?>" placeholder="None">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save" onclick="return confirm('Are you sure you want to update the official admission record?');">
                    <i class="fas fa-save"></i> Save Admission Details
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dbPrograms = <?php echo json_encode($programsByCollege); ?>;
        // Current Program ID
        const selectedProgramId = "<?php echo $student['program_id'] ?? ''; ?>";

        const collegeSelect = document.getElementById('collegeSelection');
        const programSelect = document.getElementById('academicProgram');

        function updateAcademicProgramsDB() {
            const selectedCollegeId = collegeSelect.value;
            
            programSelect.innerHTML = '<option value="">Select Academic Program...</option>';
            
            if (selectedCollegeId && dbPrograms[selectedCollegeId]) {
                dbPrograms[selectedCollegeId].forEach(prog => {
                    const option = document.createElement('option');
                    option.value = prog.id; 
                    option.textContent = prog.name; 
                    
                    if (prog.id == selectedProgramId) {
                        option.selected = true;
                    }
                    programSelect.appendChild(option);
                });
            }
        }

        if(collegeSelect) {
            collegeSelect.addEventListener('change', updateAcademicProgramsDB);
            updateAcademicProgramsDB();
        }
    });
</script>

<?php include "template_footer.php"; ?>