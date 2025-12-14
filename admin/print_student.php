<?php

require_once "auth.php"; 
require_once "../classes/student.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No student ID was provided.");
}
$student_id = $_GET['id'];
$studentObj = new Student();

$student = $studentObj->viewStudent($student_id);

if (!$student) {
    die("Error: Could not retrieve application data for ID: " . htmlspecialchars($student_id));
}

function e($value) {
    return htmlspecialchars($value ?? '');
}

function check($value, $target) {
    if (is_array($target)) {
        return in_array($value, $target) ? 'checked' : '';
    }
    return (strcasecmp($value, $target) == 0) ? 'checked' : '';
}

function checkBool($value, $target = 1) { 
    return ($value == $target) ? 'checked' : '';
}

// decode JSON data for display
$ethnicity = !empty($student['ethnicity']) ? json_decode($student['ethnicity'], true) : [];
$disability = !empty($student['disability']) ? json_decode($student['disability'], true) : [];

$isVisayan = (in_array('Cebuano', $ethnicity) || 
              in_array('Hiligaynon', $ethnicity) || 
              in_array('Waray', $ethnicity) || 
              in_array('Visayan', $ethnicity));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Application - <?php echo e($student['last_name']); ?></title>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body {
            background-color: #dcdcdc;
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 15mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
            font-size: 11pt;
            color: #000;
        }

        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .page {
                width: 100%;
                min-height: auto;
                height: 297mm; 
                margin: 0;
                padding: 15mm; 
                box-shadow: none;
                border: none;
            }
            .page:nth-of-type(1) {
                page-break-after: always;
            }
            input[type="text"],
            input[type="date"],
            input[type="email"],
            input[type="tel"] {
                border-bottom: 1px solid #000 !important;
                background: #fff !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact; 
                color-adjust: exact; 
            }
            input[type="radio"],
            input[type="checkbox"] {
                -webkit-print-color-adjust: exact;
                color-adjust: exact; 
            }
            .privacy-notice {
                 background: #f9f9f9 !important;
                 -webkit-print-color-adjust: exact;
                 color-adjust: exact; 
            }
        }
        
        h1, h2, h3, p { margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-sm { font-size: 9pt; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-5 { margin-bottom: 20px; }
        .mt-3 { margin-top: 12px; }
        
        .form-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-left { display: flex; gap: 10px; }
        .header-left img { width: 70px; height: 70px; }
        .header-center { text-align: center; padding-top: 5px; }
        .header-center h1 { font-size: 16pt; font-weight: bold; }
        .header-center h2 { font-size: 11pt; }
        .header-center h3 { font-size: 18pt; font-weight: bold; letter-spacing: 1px; margin-top: 10px; }
        .header-center h4 { font-size: 14pt; font-weight: bold; }
        .header-right { width: 144px; height: 144px; border: 2px solid #000; display: flex; justify-content: center; align-items: center; text-align: center; font-size: 10pt; color: #999; }
        .section-title { font-size: 12pt; font-weight: bold; margin-top: 15px; margin-bottom: 8px; }
        .form-grid { display: grid; gap: 10px 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 9pt; color: #333; margin-bottom: 2px; }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="email"],
        .form-group input[type="tel"] {
            border: none;
            border-bottom: 1px solid #555;
            padding: 4px 2px;
            font-family: 'Poppins', 'Times New Roman', Times, serif;
            font-size: 11pt;
            font-weight: 500;
            color: #000;
            width: 100%;
            box-sizing: border-box;
            background: transparent;
        }
        .checkbox-group, .radio-group { display: flex; flex-wrap: wrap; gap: 5px 15px; }
        .checkbox-group label, .radio-group label { font-size: 10pt; display: flex; align-items: center; gap: 4px; }


        .admission-grid { display: grid; grid-template-columns: 1fr 1fr 1.2fr 0.8fr; gap: 10px 15px; border: 1px solid #000; padding: 10px; margin-top: 10px; }
        .admission-grid .v-group { display: flex; flex-direction: column; gap: 5px; }
        .personal-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px 15px; }
        .span-2 { grid-column: span 2; }
        .span-3 { grid-column: span 3; }
        .ethnicity-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 5px 10px; }
        .address-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 8px 15px; }
        .address-grid .form-group { display: flex; flex-direction: column-reverse; }
        .other-info-grid { display: grid; grid-template-columns: 1fr 1.5fr 1fr; gap: 10px 15px; margin-top: 15px; }
        .parent-grid { display: grid; grid-template-columns: 1.5fr 1.5fr 1fr; gap: 10px 15px; }
        .income-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 5px 15px; }
        .edu-grid { display: grid; grid-template-columns: 0.8fr 2fr 2fr 1fr; gap: 8px 15px; align-items: flex-end; }
        .edu-grid .header { font-size: 10pt; font-weight: bold; text-align: center; margin-bottom: 5px; }
        .edu-grid .level-label { font-weight: bold; font-size: 11pt; align-self: center; }
        .track-strand-grid { display: grid; grid-template-columns: 100px 1fr; gap: 10px; margin-top: 10px; }
        .privacy-notice { border: 1px solid #000; padding: 15px; margin-top: 20px; background: #f9f9f9; }
        .privacy-notice p { font-size: 10pt; text-align: justify; margin-bottom: 10px; line-height: 1.4; }
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 40px; margin-top: 30px; }
        .signature-box { text-align: center; }
        .sig-line {
            border-bottom: 1px solid #000;
            height: 60px;
            margin-bottom: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .sig-line img {
            max-height: 55px;
            max-width: 200px;
        }
    </style>
</head>
<body>
    
    <div class="no-print" style="text-align: center; padding: 20px; background-color: #34495e; color: white;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 1em; cursor: pointer; background-color: #007bff; color: white; border: none; border-radius: 5px; font-family: 'Poppins', sans-serif;">
            <i class="fas fa-print"></i> Print Application
        </button>
        <a href="view_student.php?id=<?php echo $student_id; ?>" style="padding: 10px 20px; font-size: 1em; cursor: pointer; background-color: #6c757d; color: white; border: none; border-radius: 5px; text-decoration: none; margin-left: 10px; font-family: 'Poppins', sans-serif;">
            <i class="fas fa-arrow-left"></i> Back to View
        </a>
        <p style="font-family: 'Poppins', sans-serif; font-size: 10pt; margin-top: 10px;">
            **In your browser's print preview, set Paper Size to 'A4' and uncheck 'Headers and footers'.**
        </p>
    </div>

    <div class="page">
        <form id="admissionForm">
            <div class="form-header">
                <div class="header-left">
                    <img src="../images/logo.png" alt="WMSU Logo">
                </div>
                <div class="header-center">
                    <h1>WESTERN MINDANAO STATE UNIVERSITY</h1>
                    <h2>OFFICE OF THE DIRECTOR FOR ADMISSIONS</h2>
                    <h3 class="mb-1 mt-3">ADMISSION FORM</h3>
                    <h4>• UNDERGRADUATE •</h4>
                </div>
        <div class="header-right" style="padding: 0; overflow: hidden; display: flex; justify-content: center; align-items: center;">
            <?php 
            $photoUrl = '';
            if (!empty($student['photo_path'])) {
                $filename = basename($student['photo_path']);
                // Path relative to the admin folder
                $targetPath = "../admission/uploads/photos/" . $filename;
                
                // Check if file exists
                if (file_exists($targetPath)) {
                    $photoUrl = $targetPath;
                }
            }
            ?>

            <?php if ($photoUrl): ?>
                <img src="<?php echo $photoUrl; ?>" alt="ID Photo" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <span style="color: #999;">2x2 Photo</span>
            <?php endif; ?>
        </div>
            </div>

            <p class="section-title text-center mb-2" style="margin-top: 20px;">
                STUDENT'S PERSONAL DATA
            </p>
            
            <div class="admission-grid">
                <div class="v-group">
                    <label class="text-bold">Type of Admission</label>
                    <div class="radio-group">
                        <label><input type="radio" name="admission_type" value="regular" <?php echo check($student['admission_type'], 'Regular'); ?> disabled> Regular</label>
                        <label><input type="radio" name="admission_type" value="probation" <?php echo check($student['admission_type'], 'Probational'); ?> disabled> Probation</label>
                    </div>
                </div>
                <div class="v-group">
                    <label class="text-bold">Enrollment Status</label>
                    <div class="checkbox-group" style="flex-direction: column;">
                        <label><input type="checkbox" <?php echo check($student['enrollment_status'], 'Freshman'); ?> disabled> Freshman</label>
                        <label><input type="checkbox" <?php echo check($student['enrollment_status'], 'Transferee'); ?> disabled> Transferee</label>
                        <label><input type="checkbox" <?php echo check($student['enrollment_status'], 'Shifter'); ?> disabled> Shifter</label>
                        <label><input type="checkbox" <?php echo check($student['enrollment_status'], 'Returning/Continuing'); ?> disabled> Returning/Continuing</label>
                        <label><input type="checkbox" <?php echo check($student['enrollment_status'], 'Second Courser'); ?> disabled> Second Courser</label>
                        <label><input type="checkbox" <?php echo check($student['enrollment_status'], 'Cross-Enrollee'); ?> disabled> Cross-Enrollee</label>
                    </div>
                </div>
                <div class="v-group">
                    <div class="form-group">
                        <label for="college_of">COLLEGE OF</label>
                        <input type="text" id="college_of" name="college_of" value="<?php echo e(str_replace('College of ', '', $student['college_selection_admission'])); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="school_year">SCHOOL YEAR</label>
                        <input type="text" id="school_year" name="school_year" value="<?php echo e($student['school_year']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="scholarship">Scholarship (if any) Specify</label>
                        <input type="text" id="scholarship" name="scholarship" value="<?php echo e($student['scholarship']); ?>" readonly>
                    </div>
                </div>
                <div class="v-group">
                    <label class="text-bold">Semester</label>
                    <div class="radio-group" style="flex-direction: column;">
                        <label><input type="radio" name="semester" <?php echo check($student['semester'], '1st'); ?> disabled> 1st Semester</label>
                        <label><input type="radio" name="semester" <?php echo check($student['semester'], '2nd'); ?> disabled> 2nd Semester</label>
                        <label><input type="radio" name="semester" <?php echo check($student['semester'], 'Summer'); ?> disabled> Summer</label>
                    </div>
                </div>
            </div>

            <div class="form-grid personal-grid mb-3 mt-3">
                <div class="form-group">
                    <label for="student_id">Student I.D. No.</label>
                    <input type="text" id="student_id" name="student_id" readonly> </div>
                <div class="form-group">
                    <label for="academic_program">Academic Program:</label>
                    <input type="text" id="academic_program" name="academic_program" value="<?php echo e($student['academic_program']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="year_level">Year level:</label>
                    <input type="text" id="year_level" name="year_level" value="<?php echo e($student['year_level']); ?>" readonly>
                </div>
            </div>

            <div class="form-grid personal-grid">
                <div class="form-group span-3">
                    <label for="family_name">Family Name:</label>
                    <input type="text" id="family_name" name="family_name" value="<?php echo e($student['last_name']); ?>" readonly>
                </div>
                <div class="form-group span-3">
                    <label for="given_name">Given Name:</label>
                    <input type="text" id="given_name" name="given_name" value="<?php echo e($student['first_name']); ?>" readonly>
                </div>
                <div class="form-group span-3">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo e($student['middle_name']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Sex:</label>
                    <div class="radio-group" style="padding-top: 5px;">
                        <label><input type="radio" name="gender" value="male" <?php echo check($student['gender'], 'Male'); ?> disabled> Male</label>
                        <label><input type="radio" name="gender" value="female" <?php echo check($student['gender'], 'Female'); ?> disabled> Female</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="text" id="dob" name="dob" value="<?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="pob">Place of Birth:</label>
                    <input type="text" id="pob" name="pob" value="<?php echo e($student['place_of_birth']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="mobile_no">Mobile No.</label>
                    <input type="tel" id="mobile_no" name="mobile_no" value="<?php echo e($student['mobile_no']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="tel_no">Tel. No.</label>
                    <input type="tel" id="tel_no" name="tel_no" value="<?php echo e($student['tel_no']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" value="<?php echo e($student['email']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="nationality">Nationality:</label>
                    <input type="text" id="nationality" name="nationality" value="<?php echo e($student['nationality']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="civil_status">Civil Status:</label>
                    <input type="text" id="civil_status" name="civil_status" value="<?php echo e($student['civil_status']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="religion">Religion:</label>
                    <input type="text" id="religion" name="religion" value="<?php echo e($student['religion']); ?>" readonly>
                </div>
            </div>
            
            <div class="form-group mt-3">
                <label class="text-bold mb-2">Ethnicity / Tribe:</label>
                <div class="ethnicity-grid">
                    <label><input type="checkbox" <?php echo check('Badjao', $ethnicity); ?> disabled> Badjao</label>
                    <label><input type="checkbox" <?php echo check('Tausug', $ethnicity); ?> disabled> Tausug</label>
                    <label><input type="checkbox" <?php echo check('Yakan', $ethnicity); ?> disabled> Yakan</label>
                    <label><input type="checkbox" <?php echo check('Zamboangueño', $ethnicity); ?> disabled> Zamboangueño</label>
                    <label><input type="checkbox" <?php echo check('Maranao', $ethnicity); ?> disabled> Maranao</label>
                    <label><input type="checkbox" <?php echo check('Subanen', $ethnicity); ?> disabled> Subanen</label>
                    <label><input type="checkbox" <?php echo check('Maguindanaoan', $ethnicity); ?> disabled> Maguindanaoan</label>
                    <label><input type="checkbox" <?php echo check('Tagalog', $ethnicity); ?> disabled> Tagalog</label>
                    <label><input type="checkbox" <?php echo $isVisayan ? 'checked' : ''; ?> disabled> Visayan</label>
                    <label><input type="checkbox" <?php echo check('Other', $ethnicity); ?> disabled> Others</label>
                </div>
                <div class="form-group mt-3" style="width: 50%;">
                    <label for="eth_other">Others, please specify:</label>
                    <input type="text" id="eth_other" name="eth_other" value="<?php echo e($student['ethnicity_other']); ?>" readonly>
                </div>
            </div>

            <div class="form-group mt-3">
                <label class="section-title mb-2">CURRENT ADDRESS (City Address)</label>
                <div class="address-grid">
                    <div class="form-group span-2">
                        <input type="text" id="current_street" name="current_street" value="<?php echo e($student['current_house_street_no']); ?>" readonly>
                        <label for="current_street" class="text-center text-sm">House and Street Number</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="current_brgy" name="current_brgy" value="<?php echo e($student['current_barangay'] . ', ' . $student['current_city']); ?>" readonly>
                        <label for="current_brgy" class="text-center text-sm">Barangay, Town, City</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="current_zip" name="current_zip" value="<?php echo e($student['current_zip']); ?>" readonly>
                        <label for="current_zip" class="text-center text-sm">Zip Code</label>
                    </div>
                    <div class="form-group span-2">
                        <input type="text" id="current_prov" name="current_prov" value="<?php echo e($student['current_province']); ?>" readonly>
                        <label for="current_prov" class="text-center text-sm">Provincial Address</label>
                    </div>
                </div>
            </div>
            
            <div class="other-info-grid">
                <div class="form-group">
                    <label>Are you the first in the family to enroll in college?</label>
                    <div class="radio-group mt-3">
                        <label><input type="radio" name="first_in_family" value="yes" <?php echo checkBool($student['first_in_family'], 1); ?> disabled> Yes</label>
                        <label><input type="radio" name="first_in_family" value="no" <?php echo checkBool($student['first_in_family'], 0); ?> disabled> No</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Disability:</label>
                    <div class="checkbox-group mt-3">
                        <label><input type="checkbox" <?php echo check('Hearing Impairment', $disability); ?> disabled> Hearing Impaired</label>
                        <label><input type="checkbox" <?php echo check('Physical Disability', $disability); ?> disabled> Orthopedic Disability</label>
                        <label><input type="checkbox" <?php echo check('Cleft Palate', $disability); ?> disabled> Cleft palate</label>
                        <label><input type="checkbox" <?php echo check('Visual Impairment', $disability); ?> disabled> Visually Impaired</label>
                        <label><input type="checkbox" <?php echo (empty($disability) || $disability[0] == '') ? 'checked' : ''; ?> disabled> None</label>
                    </div>
                    <div class="form-group mt-3">
                        <label for="dis_other">Others, please specify:</label>
                        <input type="text" id="dis_other" name="dis_other" value="<?php echo e($student['disability_other']); ?>" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Coastal Area?<br><span class="text-sm">(Are you living 1-2 km from shoreline or river?)</span></label>
                    <div class="radio-group mt-3">
                        <label><input type="radio" name="coastal" value="yes" <?php echo checkBool($student['coastal_area'], 1); ?> disabled> Yes</label>
                        <label><input type="radio" name="coastal" value="no" <?php echo checkBool($student['coastal_area'], 0); ?> disabled> No</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-3">
                <label class="section-title mb-2">PERMANENT ADDRESS (Parents Address)</label>
                <div class="address-grid">
                    <div class="form-group span-2">
                        <input type="text" id="perm_street" name="perm_street" value="<?php echo e($student['permanent_house_street_no']); ?>" readonly>
                        <label for="perm_street" class="text-center text-sm">House and Street Number</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="perm_brgy" name="perm_brgy" value="<?php echo e($student['permanent_barangay'] . ', ' . $student['permanent_city']); ?>" readonly>
                        <label for="perm_brgy" class="text-center text-sm">Barangay, Town, City</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="perm_zip" name="perm_zip" value="<?php echo e($student['permanent_zip']); ?>" readonly>
                        <label for="perm_zip" class="text-center text-sm">Zip Code</label>
                    </div>
                    <div class="form-group span-2">
                        <input type="text" id="perm_prov" name="perm_prov" value="<?php echo e($student['permanent_province']); ?>" readonly>
                        <label for="perm_prov" class="text-center text-sm">Provincial Address</label>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="perm_mobile" name="perm_mobile" value="<?php echo e($student['permanent_mobile']); ?>" readonly>
                        <label for="perm_mobile" class="text-center text-sm">Mobile Phone No.</label>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="perm_tel" name="perm_tel" value="<?php echo e($student['permanent_tel']); ?>" readonly>
                        <label for="perm_tel" class="text-center text-sm">Telephone No.</label>
                    </div>
                </div>
            </div>
            
        </form>
    </div>

    <div class="page">
        <div class="parent-grid">
            <div class="form-group">
                <label for="father_name">Father's Name:</label>
                <input type="text" id="father_name" name="father_name" value="<?php echo e($student['father_name']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="father_edu">Educational Attainment:</label>
                <input type="text" id="father_edu" name="father_edu" value="<?php echo e($student['father_education']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="father_occ">Occupation:</label>
                <input type="text" id="father_occ" name="father_occ" value="<?php echo e($student['father_occupation']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="mother_name">Mother's Name:</label>
                <input type="text" id="mother_name" name="mother_name" value="<?php echo e($student['mother_name']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="mother_edu">Educational Attainment:</label>
                <input type="text" id="mother_edu" name="mother_edu" value="<?php echo e($student['mother_education']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="mother_occ">Occupation:</label>
                <input type="text" id="mother_occ" name="mother_occ" value="<?php echo e($student['mother_occupation']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="guardian_name">Guardian's Name:</label>
                <input type="text" id="guardian_name" name="guardian_name" value="<?php echo e($student['guardian_name']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="guardian_rel">Relationship:</label>
                <input type="text" id="guardian_rel" name="guardian_rel" value="<?php echo e($student['guardian_relationship']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="guardian_tel">Telephone No.:</label>
                <input type="tel" id="guardian_tel" name="guardian_tel" value="<?php echo e($student['guardian_tel']); ?>" readonly>
            </div>
            <div class="form-group span-3">
                <label for="guardian_addr">Address:</label>
                <input type="text" id="guardian_addr" name="guardian_addr" value="<?php echo e($student['guardian_address']); ?>" readonly>
            </div>
        </div>

        <div class="form-group mt-3">
            <label class="text-bold mb-2">Parent's Annual Gross Income:</label>
            <div class="income-grid">
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P25,000 and below'); ?> disabled> P25,000 and below</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P135,001–P250,000'); ?> disabled> P135,001 - P250,000</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P1,000,001 and above'); ?> disabled> P1,000,001 and above</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P25,000–P50,000'); ?> disabled> P25,001-P50,000</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P250,001–P500,000'); ?> disabled> P250,001 - P500,000</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], '4Ps'); ?> disabled> 4P'S</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P50,001–P80,000'); ?> disabled> P50,001-P80,000</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P500,001–P1,000,000'); ?> disabled> P500,001 - P1,000,000</label>
                <label><input type="radio" name="income" <?php echo check($student['parent_income'], 'P80,001–P135,000'); ?> disabled> P80,001-P135,000</label>
            </div>
        </div>

        <div class="form-group mt-3">
            <label class="section-title text-center mb-3">APPLICANT'S EDUCATIONAL BACKGROUND</label>
            <div class="edu-grid">
                <div class="header"></div>
                <div class="header">Name of School</div>
                <div class="header">Place of School</div>
                <div class="header">Year Completed</div>
                
                <div class="level-label">Primary:</div>
                <input type="text" name="primary_name" value="<?php echo e($student['primary_school']); ?>" readonly>
                <input type="text" name="primary_place" value="<?php echo e($student['primary_place']); ?>" readonly>
                <input type="text" name="primary_year" value="<?php echo e($student['primary_year']); ?>" readonly>
                
                <div class="level-label">Junior High School:</div>
                <input type="text" name="jhs_name" value="<?php echo e($student['junior_school']); ?>" readonly>
                <input type="text" name="jhs_place" value="<?php echo e($student['junior_place']); ?>" readonly>
                <input type="text" name="jhs_year" value="<?php echo e($student['junior_year']); ?>" readonly>

                <div class="level-label">Senior High School:</div>
                <input type="text" name="shs_name" value="<?php echo e($student['senior_school']); ?>" readonly>
                <input type="text" name="shs_place" value="<?php echo e($student['senior_place']); ?>" readonly>
                <input type="text" name="shs_year" value="<?php echo e($student['senior_year']); ?>" readonly>
            </div>
            
            <div class="track-strand-grid">
                <label class="text-bold">TRACK:</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" <?php echo check($student['track'], 'Academic'); ?> disabled> Academic Track</label>
                    <label><input type="checkbox" <?php echo check($student['track'], 'Arts and Design'); ?> disabled> Arts and Design</label>
                    <label><input type="checkbox" <?php echo check($student['track'], 'Sports'); ?> disabled> Sport Track</label>
                    <label><input type="checkbox" <?php echo check($student['track'], 'TVL'); ?> disabled> TVL</label>
                </div>

                <label class="text-bold">STRAND:</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" <?php echo check($student['strand'], 'ABM'); ?> disabled> ABM</label>
                    <label><input type="checkbox" <?php echo check($student['strand'], 'STEM'); ?> disabled> STEM</label>
                    <label><input type="checkbox" <?php echo check($student['strand'], 'HUMSS'); ?> disabled> HUMSS</label>
                    <label><input type="checkbox" <?php echo check($student['strand'], 'GAS'); ?> disabled> GAS</label>
                    <label><input type="checkbox" <?php echo check($student['strand'], 'Agrifishery'); ?> disabled> Agri-fishery</label>
                    <label><input type="checkbox" <?php echo check($student['strand'], 'HE'); ?> disabled> HE</label>
                    <label><input type="checkbox" <?php echo check($student['strand'], 'ICT'); ?> disabled> ICT</label>
                </div>
            </div>

            <div class="edu-grid mt-3">
                <div class="level-label">College:</div>
                <input type="text" name="college_name" value="<?php echo e($student['college_attended_before_education']); ?>" readonly>
                <input type="text" name="college_place" readonly>
                <input type="text" name="college_year" readonly>
            </div>
        </div>

        <div class="privacy-notice">
            <h3 class="text-center text-bold mb-3">PRIVACY NOTICE</h3>
            <p>
                Thank you for choosing Western Mindanao State University (WMSU) as your educational institution. We understand the importance of your privacy and are committed to protecting your personal information in accordance with the Data Privacy Act of 2012.
            </p>
            <p>
                Before proceeding with the collection of your personal data, we kindly request your permission to do so. Your consent allows us to securely store, process, and utilize your personal information for admission purposes and to provide you with the best possible educational experience at WMSU. By providing your consent, you acknowledge that you have read and understood the terms of this Privacy Notice.
            </p>
            <p>
                Please note that by providing your consent, you agree to receive communication from WMSU via the contact details you have provided.
            </p>
            <p>
                Thank you for your cooperation.
            </form>
        </div>
        
        <div class="signature-grid">
            <div class="signature-box">
                <label>Attested by:</label>
                <div class="sig-line">
                    <?php if (!empty($student['parent_guardian_signature']) && file_exists($student['parent_guardian_signature'])): ?>
                        <img src="../admission/uploads/signatures/<?php echo basename(e($student['parent_guardian_signature'])); ?>" alt="Parent Signature" style="max-height: 55px; max-width: 200px;">
                    <?php endif; ?>
                </div>
                <label class="text-sm">Parent's / Guardian's signature</label>
            </div>
            <div class="signature-box">
                <div class="sig-line" style="margin-top: 19px;">
                    <?php if (!empty($student['student_signature']) && file_exists($student['student_signature'])): ?>
                        <img src="../admission/uploads/signatures/<?php echo basename(e($student['student_signature'])); ?>" alt="Student Signature" style="max-height: 55px; max-width: 200px;">
                    <?php endif; ?>
                </div>
                <label class="text-sm">Student's signature</label>
            </div>
        </div>
        
    

    </div>
    
</body>
</html>