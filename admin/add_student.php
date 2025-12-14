<?php
require_once "auth.php"; 

require_once "../classes/student.php";
require_once "../classes/send_email.php";
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

$page_title = "Add New Student";

// path for uploads directory
$uploadDir = __DIR__ . "/../admission/uploads/signatures/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}


$student = [
    "lastName" => "", "firstName" => "", "middleName" => "", "gender" => "", "dob" => "", "pob" => "",
    "mobileNumber" => "", "telephoneNumber" => "", "emailAddress" => "", "nationality" => "",
    "civilStatus" => "", "religion" => "", "ethnicity" => [], "ethnicityOtherText" => "",
    "firstInCollege" => 0, "disability" => [], "disabilityOtherText" => "", "coastalArea" => 0,
    "currentHouseStreet" => "", "currentBarangay" => "", "currentCity" => "", "currentProvince" => "", "currentZipCode" => "",
    "permanentHouseStreet" => "", "permanentBarangay" => "", "permanentCity" => "", "permanentProvince" => "", "permanentZipCode" => "",
    "permanentMobileNumber" => "", "permanentTelephoneNumber" => "",
    "fatherName" => "", "fatherEducation" => "", "fatherOccupation" => "",
    "motherName" => "", "motherEducation" => "", "motherOccupation" => "",
    "guardianName" => "", "guardianRelationship" => "", "guardianAddress" => "", "guardianTelephoneNumber" => "",
    "parentIncome" => "",
    "primarySchool" => "", "primaryPlace" => "", "primaryYear" => "",
    "juniorHighSchool" => "", "juniorHighPlace" => "", "juniorHighYear" => "",
    "seniorHighSchool" => "", "seniorHighPlace" => "", "seniorHighYear" => "",
    "seniorHighTrack" => "", "seniorHighStrand" => "", "collegeAttended" => "",
    "collegeSelection" => "", "academicProgram" => "", "schoolYear" => date("Y") . "-" . (date("Y") + 1),
    "typeAdmission" => "", "enrollmentStatus" => "", "scholarship" => "", "semester" => "",
    "yearLevel" => "", "dateSubmitted" => date('Y-m-d'),
    "agreeTerms" => 0, "studentSignature" => "", "parentSignature" => ""
];


$errors = array_map(function($val) {
    return is_array($val) ? [] : "";
}, $student);

$hasErrors = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    foreach ($student as $key => $val) {
        if (isset($_POST[$key])) {
            if (is_array($student[$key])) {
                $student[$key] = $_POST[$key];
            } else {
                $student[$key] = trim(htmlspecialchars($_POST[$key]));
            }
        }
    }

    $student["firstInCollege"] = isset($_POST["firstInCollege"]) ? 1 : 0;
    $student["coastalArea"] = isset($_POST["coastalArea"]) ? 1 : 0;
    $student["agreeTerms"] = isset($_POST["agreeTerms"]) ? 1 : 0;

    // Handle File Uploads
    if (isset($_FILES['studentSignature']) && $_FILES['studentSignature']['error'] == UPLOAD_ERR_OK) {
        $studentSignatureFileName = uniqid('student_sig_') . '_' . basename($_FILES['studentSignature']['name']);
        $studentSignaturePath = $uploadDir . $studentSignatureFileName;
        if (move_uploaded_file($_FILES['studentSignature']['tmp_name'], $studentSignaturePath)) {
            $student["studentSignature"] = $studentSignaturePath;
        } else {
            $errors['studentSignature'] = "Failed to upload new student signature."; $hasErrors = true;
        }
    } elseif (isset($_POST['studentSignature_hidden']) && !empty($_POST['studentSignature_hidden'])) {
        $student["studentSignature"] = $_POST['studentSignature_hidden'];
    }

    if (isset($_FILES['parentSignature']) && $_FILES['parentSignature']['error'] == UPLOAD_ERR_OK) {
        $parentSignatureFileName = uniqid('parent_sig_') . '_' . basename($_FILES['parentSignature']['name']);
        $parentSignaturePath = $uploadDir . $parentSignatureFileName;
        if (move_uploaded_file($_FILES['parentSignature']['tmp_name'], $parentSignaturePath)) {
            $student["parentSignature"] = $parentSignaturePath;
        } else {
            $errors['parentSignature'] = "Failed to upload new parent signature."; $hasErrors = true;
        }
    } elseif (isset($_POST['parentSignature_hidden']) && !empty($_POST['parentSignature_hidden'])) {
        $student["parentSignature"] = $_POST['parentSignature_hidden'];
    }

    // --- Validation  ---
    if (empty($student["lastName"])){ $errors["lastName"] = "Last Name is required."; $hasErrors = true; }
    if (empty($student["firstName"])) { $errors["firstName"] = "First Name is required."; $hasErrors = true; }
    if (empty($student["gender"])) { $errors["gender"] = "Gender is required."; $hasErrors = true; }
    if (empty($student["dob"])) { $errors["dob"] = "Date of Birth is required."; $hasErrors = true; }
    if (empty($student["pob"])) { $errors["pob"] = "Place of Birth is required."; $hasErrors = true; }
    if (empty($student["mobileNumber"])) { $errors["mobileNumber"] = "Mobile Number is required."; $hasErrors = true; }
    elseif (!preg_match("/^[0-9]{11}$/", $student["mobileNumber"])) { $errors["mobileNumber"] = "Invalid mobile number format (11 digits)."; $hasErrors = true; }
    if (empty($student["emailAddress"])) { $errors["emailAddress"] = "Email Address is required."; $hasErrors = true; }
    elseif (!filter_var($student["emailAddress"], FILTER_VALIDATE_EMAIL)) { $errors["emailAddress"] = "Invalid email format."; $hasErrors = true; }
    if (empty($student["nationality"])) { $errors["nationality"] = "Nationality is required."; $hasErrors = true; }
    if (empty($student["civilStatus"])) { $errors["civilStatus"] = "Civil Status is required."; $hasErrors = true; }
    if (empty($student["religion"])) { $errors["religion"] = "Religion is required."; $hasErrors = true; }
    if (empty($student["ethnicity"])) { $errors["ethnicity"] = "Please select at least one ethnicity."; $hasErrors = true; }
    if (in_array("Other", $student["ethnicity"]) && empty($student["ethnicityOtherText"])) { $errors["ethnicityOtherText"] = "Please specify your ethnicity."; $hasErrors = true; }
    if (empty($student["currentHouseStreet"])) { $errors["currentHouseStreet"] = "Current House/Street Number is required."; $hasErrors = true; }
    if (empty($student["currentBarangay"])) { $errors["currentBarangay"] = "Current Barangay is required."; $hasErrors = true; }
    if (empty($student["currentCity"])) { $errors["currentCity"] = "Current City is required."; $hasErrors = true; }
    if (empty($student["currentProvince"])) { $errors["currentProvince"] = "Current Province is required."; $hasErrors = true; }
    if (empty($student["currentZipCode"])) { $errors["currentZipCode"] = "Current ZIP Code is required."; $hasErrors = true; }
    if (empty($student["permanentHouseStreet"])) { $errors["permanentHouseStreet"] = "Permanent House/Street Number is required."; $hasErrors = true; }
    if (empty($student["permanentBarangay"])) { $errors["permanentBarangay"] = "Permanent Barangay is required."; $hasErrors = true; }
    if (empty($student["permanentCity"])) { $errors["permanentCity"] = "Permanent City is required."; $hasErrors = true; }
    if (empty($student["permanentProvince"])) { $errors["permanentProvince"] = "Permanent Province is required."; $hasErrors = true; }
    if (empty($student["permanentZipCode"])) { $errors["permanentZipCode"] = "Permanent ZIP Code is required."; $hasErrors = true; }
    if (empty($student["permanentMobileNumber"])) { $errors["permanentMobileNumber"] = "Permanent Mobile Number is required."; $hasErrors = true; }
    if (empty($student["fatherName"])) { $errors["fatherName"] = "Father's Name is required."; $hasErrors = true; }
    if (empty($student["fatherEducation"])) { $errors["fatherEducation"] = "Father's Education is required."; $hasErrors = true; }
    if (empty($student["fatherOccupation"])) { $errors["fatherOccupation"] = "Father's Occupation is required."; $hasErrors = true; }
    if (empty($student["motherName"])) { $errors["motherName"] = "Mother's Name is required."; $hasErrors = true; }
    if (empty($student["motherEducation"])) { $errors["motherEducation"] = "Mother's Education is required."; $hasErrors = true; }
    if (empty($student["motherOccupation"])) { $errors["motherOccupation"] = "Mother's Occupation is required."; $hasErrors = true; }
    // guardian fields are optional
    if (empty($student["parentIncome"])) { $errors["parentIncome"] = "Parent Income is required."; $hasErrors = true; }
    if (empty($student["primarySchool"])) { $errors["primarySchool"] = "Primary School Name is required."; $hasErrors = true; }
    if (empty($student["primaryPlace"])) { $errors["primaryPlace"] = "Primary School Place is required."; $hasErrors = true; }
    if (empty($student["primaryYear"])) { $errors["primaryYear"] = "Primary Year Graduated is required."; $hasErrors = true; }
    if (empty($student["juniorHighSchool"])) { $errors["juniorHighSchool"] = "Junior High School Name is required."; $hasErrors = true; }
    if (empty($student["juniorHighPlace"])) { $errors["juniorHighPlace"] = "Junior High School Place is required."; $hasErrors = true; }
    if (empty($student["juniorHighYear"])) { $errors["juniorHighYear"] = "Junior High Year Graduated is required."; $hasErrors = true; }
    if (empty($student["seniorHighSchool"])) { $errors["seniorHighSchool"] = "Senior High School Name is required."; $hasErrors = true; }
    if (empty($student["seniorHighPlace"])) { $errors["seniorHighPlace"] = "Senior High School Place is required."; $hasErrors = true; }
    if (empty($student["seniorHighYear"])) { $errors["seniorHighYear"] = "Senior High Year Graduated is required."; $hasErrors = true; }
    if (empty($student["seniorHighTrack"])) { $errors["seniorHighTrack"] = "Senior High Track is required."; $hasErrors = true; }
    if (empty($student["seniorHighStrand"])) { $errors["seniorHighStrand"] = "Senior High Strand is required."; $hasErrors = true; }
    
    if (empty($student["collegeSelection"])) { $errors["collegeSelection"] = "College selection is required."; $hasErrors = true; }
    if (empty($student["academicProgram"])) { $errors["academicProgram"] = "Academic Program is required."; $hasErrors = true; }
    
    if (empty($student["typeAdmission"])) { $errors["typeAdmission"] = "Type of Admission is required."; $hasErrors = true; }
    if (empty($student["enrollmentStatus"])) { $errors["enrollmentStatus"] = "Enrollment Status is required."; $hasErrors = true; }
    if (empty($student["semester"])) { $errors["semester"] = "Semester is required."; $hasErrors = true; }
    if (empty($student["yearLevel"])) { $errors["yearLevel"] = "Year Level is required."; $hasErrors = true; }
    if ($student["agreeTerms"] !== 1) { $errors["agreeTerms"] = "You must agree to the Terms and Conditions."; $hasErrors = true; }
    if (empty($student["studentSignature"])) { $errors['studentSignature'] = "Student signature is required."; $hasErrors = true; }
    if (empty($student["parentSignature"])) { $errors['parentSignature'] = "Parent/Guardian signature is required."; $hasErrors = true; }


    if (empty(array_filter($errors))){

        $studentObj->mapPropertiesFromUi($student);

        $studentObj->guardianRelationshipToStudent = $student['guardianRelationship'];
        $studentObj->collegeAttendedBefore = $student['collegeAttended'];
        $studentObj->typeOfAdmission = $student['typeAdmission'];
        $studentObj->agreedToTerms = $student['agreeTerms'];

        $studentObj->adminId = $_SESSION['admin_id'];

        if ($studentObj->addStudent()) {
  
            try {
                $studentEmail = $student["emailAddress"];
                $studentName = $student["firstName"] . " " . $student["lastName"];
                
                $programName = "Unknown Program";
                $collegeName = "Unknown College";
                
                foreach ($colleges as $col) {
                    if ($col['college_id'] == $student["collegeSelection"]) {
                        $collegeName = $col['college_name'];
                        break;
                    }
                }
                foreach ($allPrograms as $prog) {
                    if ($prog['program_id'] == $student["academicProgram"]) {
                        $programName = $prog['program_name'];
                        break;
                    }
                }

                $dateSubmitted = $student["dateSubmitted"];
                
                sendConfirmationEmail($studentEmail, $studentName, $programName, $dateSubmitted);
                
            } catch (Exception $e) {
                error_log("Admin-side email sending failed for " . $studentEmail . ": " . $e->getMessage());
            }
          
            header("Location: success.php?message=Student record added successfully by admin.");
            exit;
        } else {
             $errors['database'] = "Failed to add student data. Please try again or contact support.";
             $hasErrors = true;
        }
    }
}

include "template_header.php";
?>

<div class="container" style="margin-top: 2rem;"> 
    <?php if (!empty($errors['database'])): ?>
        <div class="alert alert-danger" style="padding: 1rem; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; text-align: center; margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($errors['database']); ?>
        </div>
    <?php endif; ?>

    <form id="admissionForm" action="" method="POST" enctype="multipart/form-data">

        <fieldset class="step" data-step="1">
            <legend>Student Information</legend>
            <div class="form-grid">
                <div class="form-group <?php echo !empty($errors['lastName']) ? 'has-error' : ''; ?>">
                    <label for="lastName">Last Name <span class="required">*</span></label>
                    <input type="text" id="lastName" name="lastName" value="<?= $student['lastName'] ?? ''; ?>" required>
                    <p class="error"><?php echo $errors['lastName']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['firstName']) ? 'has-error' : ''; ?>">
                    <label for="firstName">First Name <span class="required">*</span></label>
                    <input type="text" id="firstName" name="firstName" value="<?= $student['firstName'] ?? ''; ?>" required>
                    <p class="error"><?php echo $errors['firstName']; ?></p>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" value="<?= $student['middleName'] ?? ''; ?>">
                </div>
                <div class="form-group <?php echo !empty($errors['gender']) ? 'has-error' : ''; ?>">
                    <label>Sex <span class="required">*</span></label>
                    <div class="radio-group">
                        <input type="radio" id="genderMale" name="gender" value="Male" <?= (isset($student['gender']) && $student['gender'] === 'Male') ? 'checked' : ''; ?> >
                        <label for="genderMale">Male</label>
                        <input type="radio" id="genderFemale" name="gender" value="Female" <?= (isset($student['gender']) && $student['gender'] === 'Female') ? 'checked' : ''; ?>>
                        <label for="genderFemale">Female</label>
                    </div>
                    <p class="error"><?php echo $errors['gender']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['dob']) ? 'has-error' : ''; ?>">
                    <label for="dob">Date of Birth <span class="required">*</span></label>
                    <input type="date" id="dob" name="dob" value="<?= $student['dob'] ?? ''; ?>" required>
                    <p class="error"><?php echo $errors['dob']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['pob']) ? 'has-error' : ''; ?>">
                    <label for="pob">Place of Birth <span class="required">*</span></label>
                    <input type="text" id="pob" name="pob" value="<?= $student['pob'] ?? ''; ?>" required>
                    <p class="error"><?php echo $errors['pob']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['mobileNumber']) ? 'has-error' : ''; ?>">
                    <label for="mobileNumber">Mobile Number <span class="required">*</span></label>
                    <input type="tel" id="mobileNumber" name="mobileNumber" pattern="[0-9]{11}" placeholder="e.g., 09123456789" value="<?= $student['mobileNumber'] ?? ''; ?>" required>
                    <p class="error"><?php echo $errors['mobileNumber']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['telephoneNumber']) ? 'has-error' : ''; ?>">
                    <label for="telephoneNumber">Telephone Number</label>
                    <input type="tel" id="telephoneNumber" name="telephoneNumber" pattern="[0-9]{7,10}" placeholder="Optional" value="<?= $student['telephoneNumber'] ?? ''; ?>">
                    <p class="error"><?php echo $errors['telephoneNumber']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['emailAddress']) ? 'has-error' : ''; ?>">
                    <label for="emailAddress">Email Address <span class="required">*</span></label>
                    <input type="email" id="emailAddress" name="emailAddress" value="<?= $student['emailAddress'] ?? ''; ?>" required>
                    <p class="form-helper-text">Please ensure this email is active. A confirmation receipt will be sent here upon submission.</p>
                    <p class="error"><?php echo $errors['emailAddress']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['nationality']) ? 'has-error' : ''; ?>">
                    <label for="nationality">Nationality <span class="required">*</span></label>
                    <input type="text" id="nationality" name="nationality" value="<?= $student['nationality'] ?? ''; ?>" required>
                    <p class="error"><?php echo $errors['nationality']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['civilStatus']) ? 'has-error' : ''; ?>">
                    <label for="civilStatus">Civil Status <span class="required">*</span></label>
                    <select id="civilStatus" name="civilStatus" required>
                        <option value="">Select...</option>
                        <option value="Single" <?= (isset($student['civilStatus']) && $student['civilStatus'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?= (isset($student['civilStatus']) && $student['civilStatus'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?= (isset($student['civilStatus']) && $student['civilStatus'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?= (isset($student['civilStatus']) && $student['civilStatus'] === 'Separated') ? 'selected' : ''; ?>>Separated</option>
                    </select>
                    <p class="error"><?php echo $errors['civilStatus']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['religion']) ? 'has-error' : ''; ?>">
                    <label for="religion">Religion <span class="required">*</span></label>
                    <input type="text" id="religion" name="religion" value="<?= $student['religion'] ?? ''; ?>" required>
                    <p class="error"><?php echo $errors['religion']; ?></p>
                </div>
                <div class="form-group full-width <?php echo !empty($errors['ethnicity']) || !empty($errors['ethnicityOtherText']) ? 'has-error' : ''; ?>">
                    <label>Ethnicity <span class="required">*</span></label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="ethnicity[]" value="Tagalog" <?= (isset($student['ethnicity']) && in_array('Tagalog', $student['ethnicity'])) ? 'checked' : ''; ?>> Tagalog</label>
                        <label><input type="checkbox" name="ethnicity[]" value="Cebuano" <?= (isset($student['ethnicity']) && in_array('Cebuano', $student['ethnicity'])) ? 'checked' : ''; ?>> Cebuano</label>
                        <label><input type="checkbox" name="ethnicity[]" value="Ilocano" <?= (isset($student['ethnicity']) && in_array('Ilocano', $student['ethnicity'])) ? 'checked' : ''; ?>> Ilocano</label>
                        <label><input type="checkbox" name="ethnicity[]" value="Hiligaynon" <?= (isset($student['ethnicity']) && in_array('Hiligaynon', $student['ethnicity'])) ? 'checked' : ''; ?>> Hiligaynon</label>
                        <label><input type="checkbox" name="ethnicity[]" value="Bicolano" <?= (isset($student['ethnicity']) && in_array('Bicolano', $student['ethnicity'])) ? 'checked' : ''; ?>> Bicolano</label>
                        <label><input type="checkbox" name="ethnicity[]" value="Waray" <?= (isset($student['ethnicity']) && in_array('Waray', $student['ethnicity'])) ? 'checked' : ''; ?>> Waray</label>
                        <label><input type="checkbox" name="ethnicity[]" value="Kapampangan" <?= (isset($student['ethnicity']) && in_array('Kapampangan', $student['ethnicity'])) ? 'checked' : ''; ?>> Kapampangan</label>
                        <label><input type="checkbox" id="ethnicityOther" name="ethnicity[]" value="Other" <?= (isset($student['ethnicity']) && in_array('Other', $student['ethnicity'])) ? 'checked' : ''; ?>> Other</label>
                        <input type="text" id="ethnicityOtherText" name="ethnicityOtherText" placeholder="Please specify" style="display: <?= (isset($student['ethnicity']) && in_array('Other', $student['ethnicity'])) ? 'block' : 'none'; ?>;" value="<?= $student['ethnicityOtherText'] ?? ''; ?>">
                    </div>
                    <p class="error"><?php echo is_string($errors['ethnicity']) ? $errors['ethnicity'] : ''; ?></p>
                    <p class="error"><?php echo $errors['ethnicityOtherText']; ?></p>
                </div>
                <div class="form-group toggle-group">
                    <label>Are you the first in your family to attend college? <span class="required">*</span></label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="firstInCollege" name="firstInCollege" class="toggle-switch-checkbox" <?= (isset($student['firstInCollege']) && $student['firstInCollege'] == 1) ? 'checked' : ''; ?>>
                        <label for="firstInCollege" class="toggle-switch-label">
                            <span class="toggle-switch-inner"></span>
                            <span class="toggle-switch-switch"></span>
                        </label>
                        <span class="toggle-text yes">Yes</span>
                        <span class="toggle-text no">No</span>
                    </div>
                </div>
                <div class="form-group full-width <?php echo !empty($errors['disability']) || !empty($errors['disabilityOtherText']) ? 'has-error' : ''; ?>">
                    <label>Disability</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="disability[]" value="Visual Impairment" <?= (isset($student['disability']) && in_array('Visual Impairment', $student['disability'])) ? 'checked' : ''; ?>> Visual Impairment</label>
                        <label><input type="checkbox" name="disability[]" value="Hearing Impairment" <?= (isset($student['disability']) && in_array('Hearing Impairment', $student['disability'])) ? 'checked' : ''; ?>> Hearing Impairment</label>
                        <label><input type="checkbox" name="disability[]" value="Physical Disability" <?= (isset($student['disability']) && in_array('Physical Disability', $student['disability'])) ? 'checked' : ''; ?>> Physical Disability</label>
                        <label><input type="checkbox" name="disability[]" value="Learning Disability" <?= (isset($student['disability']) && in_array('Learning Disability', $student['disability'])) ? 'checked' : ''; ?>> Learning Disability</label>
                        <label><input type="checkbox" id="disabilityOther" name="disability[]" value="Other" <?= (isset($student['disability']) && in_array('Other', $student['disability'])) ? 'checked' : ''; ?>> Other</label>
                        <input type="text" id="disabilityOtherText" name="disabilityOtherText" placeholder="Please specify" style="display: <?= (isset($student['disability']) && in_array('Other', $student['disability'])) ? 'block' : 'none'; ?>;" value="<?= $student['disabilityOtherText'] ?? ''; ?>">
                    </div>
                    <p class="error"><?php echo $errors['disabilityOtherText']; ?></p>
                </div>
                <div class="form-group toggle-group">
                    <label>Do you live in a coastal area? <span class="required">*</span></label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="coastalArea" name="coastalArea" class="toggle-switch-checkbox" <?= (isset($student['coastalArea']) && $student['coastalArea'] == 1) ? 'checked' : ''; ?>>
                        <label for="coastalArea" class="toggle-switch-label">
                            <span class="toggle-switch-inner"></span>
                            <span class="toggle-switch-switch"></span>
                        </label>
                        <span class="toggle-text yes">Yes</span>
                        <span class="toggle-text no">No</span>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="step" data-step="2">
            <legend>Address Information</legend>
            <div class="form-section">
                <h3>Current Address <span class="required">*</span></h3>
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['currentHouseStreet']) ? 'has-error' : ''; ?>">
                        <label for="currentHouseStreet">House/Street Number <span class="required">*</span></label>
                        <input type="text" id="currentHouseStreet" name="currentHouseStreet" value="<?= $student['currentHouseStreet'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['currentHouseStreet']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['currentBarangay']) ? 'has-error' : ''; ?>">
                        <label for="currentBarangay">Barangay <span class="required">*</span></label>
                        <input type="text" id="currentBarangay" name="currentBarangay" value="<?= $student['currentBarangay'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['currentBarangay']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['currentCity']) ? 'has-error' : ''; ?>">
                        <label for="currentCity">City <span class="required">*</span></label>
                        <input type="text" id="currentCity" name="currentCity" value="<?= $student['currentCity'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['currentCity']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['currentProvince']) ? 'has-error' : ''; ?>">
                        <label for="currentProvince">Province <span class="required">*</span></label>
                        <input type="text" id="currentProvince" name="currentProvince" value="<?= $student['currentProvince'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['currentProvince']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['currentZipCode']) ? 'has-error' : ''; ?>">
                        <label for="currentZipCode">ZIP Code <span class="required">*</span></label>
                        <input type="text" id="currentZipCode" name="currentZipCode" pattern="[0-9]{4}" value="<?= $student['currentZipCode'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['currentZipCode']; ?></p>
                    </div>
                </div>
            </div>
    
            <div class="form-group full-width" style="margin-top: 1.5rem; margin-bottom: 0.5rem; background-color: #e9ecef; padding: 10px; border-radius: 5px;">
                <div class="checkbox-align-group">
                    <input type="checkbox" id="sameAsCurrent" name="sameAsCurrent">
                    <label for="sameAsCurrent" style="font-weight: 500;">Permanent Address is the same as Current Address</label>
                </div>
            </div>
    
            <div class="form-section">
                <h3>Permanent Address <span class="required">*</span></h3>
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['permanentHouseStreet']) ? 'has-error' : ''; ?>">
                        <label for="permanentHouseStreet">House/Street Number <span class="required">*</span></label>
                        <input type="text" id="permanentHouseStreet" name="permanentHouseStreet" value="<?= $student['permanentHouseStreet'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['permanentHouseStreet']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['permanentBarangay']) ? 'has-error' : ''; ?>">
                        <label for="permanentBarangay">Barangay <span class="required">*</span></label>
                        <input type="text" id="permanentBarangay" name="permanentBarangay" value="<?= $student['permanentBarangay'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['permanentBarangay']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['permanentCity']) ? 'has-error' : ''; ?>">
                        <label for="permanentCity">City <span class="required">*</span></label>
                        <input type="text" id="permanentCity" name="permanentCity" value="<?= $student['permanentCity'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['permanentCity']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['permanentProvince']) ? 'has-error' : ''; ?>">
                        <label for="permanentProvince">Province <span class="required">*</span></label>
                        <input type="text" id="permanentProvince" name="permanentProvince" value="<?= $student['permanentProvince'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['permanentProvince']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['permanentZipCode']) ? 'has-error' : ''; ?>">
                        <label for="permanentZipCode">ZIP Code <span class="required">*</span></label>
                        <input type="text" id="permanentZipCode" name="permanentZipCode" pattern="[0-9]{4}" value="<?= $student['permanentZipCode'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['permanentZipCode']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['permanentMobileNumber']) ? 'has-error' : ''; ?>">
                        <label for="permanentMobileNumber">Mobile Number <span class="required">*</span></label>
                        <input type="tel" id="permanentMobileNumber" name="permanentMobileNumber" pattern="[0-9]{11}" placeholder="e.g., 09123456789" value="<?= $student['permanentMobileNumber'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['permanentMobileNumber']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['permanentTelephoneNumber']) ? 'has-error' : ''; ?>">
                        <label for="permanentTelephoneNumber">Telephone Number</label>
                        <input type="tel" id="permanentTelephoneNumber" name="permanentTelephoneNumber" pattern="[0-9]{7,10}" placeholder="Optional" value="<?= $student['permanentTelephoneNumber'] ?? ''; ?>">
                        <p class="error"><?php echo $errors['permanentTelephoneNumber']; ?></p>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="step" data-step="3">
            <legend>Parent/Guardian Information</legend>
            <div class="form-section">
                <h3>Father's Information</h3>
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['fatherName']) ? 'has-error' : ''; ?>">
                        <label for="fatherName">Father's Name <span class="required">*</span></label>
                        <input type="text" id="fatherName" name="fatherName" value="<?= $student['fatherName'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['fatherName']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['fatherEducation']) ? 'has-error' : ''; ?>">
                        <label for="fatherEducation">Education <span class="required">*</span></label>
                        <input type="text" id="fatherEducation" name="fatherEducation" value="<?= $student['fatherEducation'] ?? ''; ?>" required>
                         <p class="error"><?php echo $errors['fatherEducation']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['fatherOccupation']) ? 'has-error' : ''; ?>">
                        <label for="fatherOccupation">Occupation <span class="required">*</span></label>
                        <input type="text" id="fatherOccupation" name="fatherOccupation" value="<?= $student['fatherOccupation'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['fatherOccupation']; ?></p>
                    </div>
                </div>
            </div>
        
            <div class="form-section">
                <h3>Mother's Information</h3>
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['motherName']) ? 'has-error' : ''; ?>">
                        <label for="motherName">Mother's Name <span class="required">*</span></label>
                        <input type="text" id="motherName" name="motherName" value="<?= $student['motherName'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['motherName']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['motherEducation']) ? 'has-error' : ''; ?>">
                        <label for="motherEducation">Education <span class="required">*</span></label>
                        <input type="text" id="motherEducation" name="motherEducation" value="<?= $student['motherEducation'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['motherEducation']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['motherOccupation']) ? 'has-error' : ''; ?>">
                        <label for="motherOccupation">Occupation <span class="required">*</span></label>
                        <input type="text" id="motherOccupation" name="motherOccupation" value="<?= $student['motherOccupation'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['motherOccupation']; ?></p>
                    </div>
                </div>
            </div>
        
            <div class="form-section">
                <h3>Guardian's Information (If applicable)</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="guardianName">Guardian's Name</label>
                        <input type="text" id="guardianName" name="guardianName" value="<?= $student['guardianName'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="guardianRelationship">Relationship to Student</label>
                        <input type="text" id="guardianRelationship" name="guardianRelationship" value="<?= $student['guardianRelationship'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="guardianAddress">Address</label>
                        <input type="text" id="guardianAddress" name="guardianAddress" value="<?= $student['guardianAddress'] ?? ''; ?>">
                    </div>
                    <div class="form-group <?php echo !empty($errors['guardianTelephoneNumber']) ? 'has-error' : ''; ?>">
                        <label for="guardianTelephoneNumber">Telephone Number</label>
                        <input type="text" id="guardianTelephoneNumber" name="guardianTelephoneNumber" placeholder="Optional" value="<?= $student['guardianTelephoneNumber'] ?? ''; ?>"> 
                        <p class="error"><?php echo $errors['guardianTelephoneNumber']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['parentIncome']) ? 'has-error' : ''; ?>">
                        <label for="parentIncome">Parent Income <span class="required">*</span></label>
                        <select id="parentIncome" name="parentIncome" required>
                            <option value="">Select...</option>
                            <option value="P25,000 and below" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P25,000 and below') ? 'selected' : ''; ?>>P25,000 and below</option>
                            <option value="P25,000–P50,000" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P25,000–P50,000') ? 'selected' : ''; ?>>P25,000–P50,000</option>
                            <option value="P50,001–P80,000" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P50,001–P80,000') ? 'selected' : ''; ?>>P50,001–P80,000</option>
                            <option value="P80,001–P135,000" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P80,001–P135,000') ? 'selected' : ''; ?>>P80,001–P135,000</option>
                            <option value="P135,001–P250,000" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P135,001–P250,000') ? 'selected' : ''; ?>>P135,001–P250,000</option>
                            <option value="P250,001–P500,000" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P250,001–P500,000') ? 'selected' : ''; ?>>P250,001–P500,000</option>
                            <option value="P500,001–P1,000,000" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P500,001–P1,000,000') ? 'selected' : ''; ?>>P500,001–P1,000,000</option>
                            <option value="P1,000,001 and above" <?= (isset($student['parentIncome']) && $student['parentIncome'] === 'P1,000,001 and above') ? 'selected' : ''; ?>>P1,000,001 and above</option>
                            <option value="4Ps" <?= (isset($student['parentIncome']) && $student['parentIncome'] === '4Ps') ? 'selected' : ''; ?>>4Ps</option>
                        </select>
                        <p class="error"><?php echo $errors['parentIncome']; ?></p>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="step" data-step="4">
            <legend>Educational Background</legend>
            <div class="form-section">
                <h3>Primary School <span class="required">*</span></h3>
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['primarySchool']) ? 'has-error' : ''; ?>">
                        <label for="primarySchool">School Name <span class="required">*</span></label>
                        <input type="text" id="primarySchool" name="primarySchool" value="<?= $student['primarySchool'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['primarySchool']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['primaryPlace']) ? 'has-error' : ''; ?>">
                        <label for="primaryPlace">Place <span class="required">*</span></label>
                        <input type="text" id="primaryPlace" name="primaryPlace" value="<?= $student['primaryPlace'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['primaryPlace']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['primaryYear']) ? 'has-error' : ''; ?>">
                        <label for="primaryYear">Year Graduated <span class="required">*</span></label>
                        <input type="number" id="primaryYear" name="primaryYear" min="1900" max="<?php echo date("Y"); ?>" value="<?= $student['primaryYear'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['primaryYear']; ?></p>
                    </div>
                </div>
            </div>
        
            <div class="form-section">
                <h3>Junior High School <span class="required">*</span></h3>
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['juniorHighSchool']) ? 'has-error' : ''; ?>">
                        <label for="juniorHighSchool">School Name <span class="required">*</span></label>
                        <input type="text" id="juniorHighSchool" name="juniorHighSchool" value="<?= $student['juniorHighSchool'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['juniorHighSchool']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['juniorHighPlace']) ? 'has-error' : ''; ?>">
                        <label for="juniorHighPlace">Place <span class="required">*</span></label>
                        <input type="text" id="juniorHighPlace" name="juniorHighPlace" value="<?= $student['juniorHighPlace'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['juniorHighPlace']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['juniorHighYear']) ? 'has-error' : ''; ?>">
                        <label for="juniorHighYear">Year Graduated <span class="required">*</span></label>
                        <input type="number" id="juniorHighYear" name="juniorHighYear" min="1900" max="<?php echo date("Y"); ?>" value="<?= $student['juniorHighYear'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['juniorHighYear']; ?></p>
                    </div>
                </div>
            </div>
        
            <div class="form-section">
                <h3>Senior High School <span class="required">*</span></h3>
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['seniorHighSchool']) ? 'has-error' : ''; ?>">
                        <label for="seniorHighSchool">School Name <span class="required">*</span></label>
                        <input type="text" id="seniorHighSchool" name="seniorHighSchool" value="<?= $student['seniorHighSchool'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['seniorHighSchool']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['seniorHighPlace']) ? 'has-error' : ''; ?>">
                        <label for="seniorHighPlace">Place <span class="required">*</span></label>
                        <input type="text" id="seniorHighPlace" name="seniorHighPlace" value="<?= $student['seniorHighPlace'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['seniorHighPlace']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['seniorHighYear']) ? 'has-error' : ''; ?>">
                        <label for="seniorHighYear">Year Graduated <span class="required">*</span></label>
                        <input type="number" id="seniorHighYear" name="seniorHighYear" min="1900" max="<?php echo date("Y"); ?>" value="<?= $student['seniorHighYear'] ?? ''; ?>" required>
                        <p class="error"><?php echo $errors['seniorHighYear']; ?></p>
                    </div>
                </div>
            </div>
        
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-group <?php echo !empty($errors['seniorHighTrack']) ? 'has-error' : ''; ?>">
                        <label for="seniorHighTrack">Senior High Track <span class="required">*</span></label>
                        <select id="seniorHighTrack" name="seniorHighTrack" required>
                            <option value="">Select...</option>
                            <option value="Academic" <?= (isset($student['seniorHighTrack']) && $student['seniorHighTrack'] === 'Academic') ? 'selected' : ''; ?>>Academic</option>
                            <option value="Arts and Design" <?= (isset($student['seniorHighTrack']) && $student['seniorHighTrack'] === 'Arts and Design') ? 'selected' : ''; ?>>Arts and Design</option>
                            <option value="Sports" <?= (isset($student['seniorHighTrack']) && $student['seniorHighTrack'] === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                            <option value="TVL" <?= (isset($student['seniorHighTrack']) && $student['seniorHighTrack'] === 'TVL') ? 'selected' : ''; ?>>TVL</option>
                        </select>
                        <p class="error"><?php echo $errors['seniorHighTrack']; ?></p>
                    </div>
                    <div class="form-group <?php echo !empty($errors['seniorHighStrand']) ? 'has-error' : ''; ?>">
                        <label for="seniorHighStrand">Senior High Strand <span class="required">*</span></label>
                        <select id="seniorHighStrand" name="seniorHighStrand" required>
                            <option value="">Select...</option>
                            <option value="ABM" <?= (isset($student['seniorHighStrand']) && $student['seniorHighStrand'] === 'ABM') ? 'selected' : ''; ?>>ABM</option>
                            <option value="STEM" <?= (isset($student['seniorHighStrand']) && $student['seniorHighStrand'] === 'STEM') ? 'selected' : ''; ?>>STEM</option>
                            <option value="HUMSS" <?= (isset($student['seniorHighStrand']) && $student['seniorHighStrand'] === 'HUMSS') ? 'selected' : ''; ?>>HUMSS</option>
                            <option value="GAS" <?= (isset($student['seniorHighStrand']) && $student['seniorHighStrand'] === 'GAS') ? 'selected' : ''; ?>>GAS</option>
                            <option value="Agrifishery" <?= (isset($student['seniorHighStrand']) && $student['seniorHighStrand'] === 'Agrifishery') ? 'selected' : ''; ?>>Agrifishery</option>
                            <option value="HE" <?= (isset($student['seniorHighStrand']) && $student['seniorHighStrand'] === 'HE') ? 'selected' : ''; ?>>HE</option>
                            <option value="ICT" <?= (isset($student['seniorHighStrand']) && $student['seniorHighStrand'] === 'ICT') ? 'selected' : ''; ?>>ICT</option>
                        </select>
                        <p class="error"><?php echo $errors['seniorHighStrand']; ?></p>
                    </div>
                    <div class="form-group full-width">
                        <label for="collegeAttended">College (Optional, if applicable)</label>
                        <input type="text" id="collegeAttended" name="collegeAttended" placeholder="e.g., if you are a transferee or shifter" value="<?= $student['collegeAttended'] ?? ''; ?>">
                    </div>
                </div>
            </div>
        </fieldset>
        
        <fieldset class="step" data-step="5">
            <legend>Admission Details</legend>
            <div class="form-grid">
                
                <div class="form-group <?php echo !empty($errors['collegeSelection']) ? 'has-error' : ''; ?>">
                    <label for="collegeSelection">College <span class="required">*</span></label>
                    <select id="collegeSelection" name="collegeSelection" required>
                        <option value="">Select College...</option>
                        <?php foreach ($colleges as $col): ?>
                            <option value="<?php echo $col['college_id']; ?>" 
                                <?= (isset($student['collegeSelection']) && $student['collegeSelection'] == $col['college_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($col['college_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="error"><?php echo $errors['collegeSelection']; ?></p>
                </div>

                <div class="form-group <?php echo !empty($errors['academicProgram']) ? 'has-error' : ''; ?>">
                    <label for="academicProgram">Academic Program <span class="required">*</span></label>
                    <select id="academicProgram" name="academicProgram" required>
                        <option value="">Select Academic Program...</option>
                    </select>
                    <p class="error"><?php echo $errors['academicProgram']; ?></p>
                </div>

                <div class="form-group">
                    <label for="schoolYear">School Year <span class="required">*</span></label>
                    <input type="text" id="schoolYear" name="schoolYear" value="<?= date("Y") . "-" . (date("Y") + 1); ?>" required readonly>
                </div>
                <div class="form-group <?php echo !empty($errors['typeAdmission']) ? 'has-error' : ''; ?>">
                    <label for="typeAdmission">Type of Admission <span class="required">*</span></label>
                    <select id="typeAdmission" name="typeAdmission" required>
                        <option value="">Select...</option>
                        <option value="Regular" <?= (isset($student['typeAdmission']) && $student['typeAdmission'] === 'Regular') ? 'selected' : ''; ?>>Regular</option>
                        <option value="Probational" <?= (isset($student['typeAdmission']) && $student['typeAdmission'] === 'Probational') ? 'selected' : ''; ?>>Probational</option>
                    </select>
                    <p class="error"><?php echo $errors['typeAdmission']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['enrollmentStatus']) ? 'has-error' : ''; ?>">
                    <label for="enrollmentStatus">Enrollment Status <span class="required">*</span></label>
                    <select id="enrollmentStatus" name="enrollmentStatus" required>
                        <option value="">Select...</option>
                        <option value="Freshman" <?= (isset($student['enrollmentStatus']) && $student['enrollmentStatus'] === 'Freshman') ? 'selected' : ''; ?>>Freshman</option>
                        <option value="Transferee" <?= (isset($student['enrollmentStatus']) && $student['enrollmentStatus'] === 'Transferee') ? 'selected' : ''; ?>>Transferee</option>
                        <option value="Shifter" <?= (isset($student['enrollmentStatus']) && $student['enrollmentStatus'] === 'Shifter') ? 'selected' : ''; ?>>Shifter</option>
                        <option value="Returning/Continuing" <?= (isset($student['enrollmentStatus']) && $student['enrollmentStatus'] === 'Returning/Continuing') ? 'selected' : ''; ?>>Returning/Continuing</option>
                        <option value="Second Courser" <?= (isset($student['enrollmentStatus']) && $student['enrollmentStatus'] === 'Second Courser') ? 'selected' : ''; ?>>Second Courser</option>
                        <option value="Cross-Enrollee" <?= (isset($student['enrollmentStatus']) && $student['enrollmentStatus'] === 'Cross-Enrollee') ? 'selected' : ''; ?>>Cross-Enrollee</option>
                    </select>
                    <p class="error"><?php echo $errors['enrollmentStatus']; ?></p>
                </div>
                <div class="form-group">
                    <label for="scholarship">Scholarship (if any)</label>
                    <input type="text" id="scholarship" name="scholarship" value="<?= $student['scholarship'] ?? ''; ?>">
                </div>
                <div class="form-group <?php echo !empty($errors['semester']) ? 'has-error' : ''; ?>">
                    <label for="semester">Semester <span class="required">*</span></label>
                    <select id="semester" name="semester" required>
                        <option value="">Select...</option>
                        <option value="1st" <?= (isset($student['semester']) && $student['semester'] === '1st') ? 'selected' : ''; ?>>1st</option>
                        <option value="2nd" <?= (isset($student['semester']) && $student['semester'] === '2nd') ? 'selected' : ''; ?>>2nd</option>
                        <option value="Summer" <?= (isset($student['semester']) && $student['semester'] === 'Summer') ? 'selected' : ''; ?>>Summer</option>
                    </select>
                    <p class="error"><?php echo $errors['semester']; ?></p>
                </div>
                <div class="form-group <?php echo !empty($errors['yearLevel']) ? 'has-error' : ''; ?>">
                    <label for="yearLevel">Year Level <span class="required">*</span></label>
                    <select id="yearLevel" name="yearLevel" required>
                        <option value="">Select...</option>
                        <option value="1st Year" <?= (isset($student['yearLevel']) && $student['yearLevel'] === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?= (isset($student['yearLevel']) && $student['yearLevel'] === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?= (isset($student['yearLevel']) && $student['yearLevel'] === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?= (isset($student['yearLevel']) && $student['yearLevel'] === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                        <option value="5th Year" <?= (isset($student['yearLevel']) && $student['yearLevel'] === '5th Year') ? 'selected' : ''; ?>>5th Year</option>
                    </select>
                    <p class="error"><?php echo $errors['yearLevel']; ?></p>
                </div>
                <div class="form-group full-width checkbox-group-inline <?php echo !empty($errors['agreeTerms']) ? 'has-error' : ''; ?>">
                    <div class="checkbox-align-group">
                        <input type="checkbox" id="agreeTerms" name="agreeTerms" value="Yes" <?= (isset($student['agreeTerms']) && $student['agreeTerms'] == 1) ? 'checked' : ''; ?>>
                        <label for="agreeTerms">I agree to the <a href="#" id="termsLink">Terms and Conditions</a> <span class="required">*</span></label>
                    </div>
                    <p class="error"><?php echo $errors['agreeTerms']; ?></p>
                </div>
                <div class="form-group upload-group <?php echo !empty($errors['studentSignature']) ? 'has-error' : ''; ?>">
                    <label for="studentSignature">Student Signature <span class="required">*</span></label>
                    <input type="file" id="studentSignature" name="studentSignature" accept="image/*">
                    <input type="hidden" name="studentSignature_hidden" value="<?= htmlspecialchars($student['studentSignature'] ?? ''); ?>">
                    <div class="signature-preview" id="studentSignaturePreview">
                        <?php
                        if (!empty($student['studentSignature']) && file_exists($student['studentSignature'])) {
                            $webPath = '../admission/uploads/signatures/' . basename($student['studentSignature']);
                            echo '<img src="' . htmlspecialchars($webPath) . '" alt="Student Signature Preview" style="max-width: 150px; height: auto;">';
                        }
                        ?>
                    </div>
                    <p class="error"><?php echo $errors['studentSignature']; ?></p>
                </div>

                <div class="form-group upload-group <?php echo !empty($errors['parentSignature']) ? 'has-error' : ''; ?>">
                    <label for="parentSignature">Parent/Guardian Signature <span class="required">*</span></label>
                    <input type="file" id="parentSignature" name="parentSignature" accept="image/*">
                    <input type="hidden" name="parentSignature_hidden" value="<?= htmlspecialchars($student['parentSignature'] ?? ''); ?>">
                    <div class="signature-preview" id="parentSignaturePreview">
                         <?php
                        if (!empty($student['parentSignature']) && file_exists($student['parentSignature'])) {
                            $webPath = '../admission/uploads/signatures/' . basename($student['parentSignature']);
                            echo '<img src="' . htmlspecialchars($webPath) . '" alt="Parent Signature Preview" style="max-width: 150px; height: auto;">';
                        }
                        ?>
                    </div>
                    <p class="error"><?php echo $errors['parentSignature']; ?></p>
                </div>
            </div>
        </fieldset>

        <div class="form-navigation">
            <button type="submit" class="submit-btn" id="submitForm"><i class="fas fa-plus"></i> Add Student Record</button>
        </div>
    
    </form>
</div>

<div id="termsModal" class="modal">
    <div class="modal-content">
         <span class="close-button">&times;</span>
         <h2>Data Privacy Consent</h2>
         <p>Thank you for choosing Western Mindanao State University (WMSU) as your educational institution. We understand the importance of your privacy and are committed to protecting your personal information in accordance with the Data Privacy Act of 2012.</p>
        <p>Before proceeding with the collection of your personal data, we kindly request your permission to do so. Your consent allows us to securely store, process, and utilize your personal information for admission purposes and to provide you with the best possible educational experience at WMSU.</p>
        <p>By providing your consent, you acknowledge that you have read and understood the terms of this privacy Notice.</p>
        <p>Please note that by providing your consent, you agree to receive communication from WMSU via the contact details you have provided.</p>
        <p>Thank you for your cooperation.</p>
         <button class="modal-close-btn">Close</button>
     </div>
</div>

<script src="../admission/script.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dbPrograms = <?php echo json_encode($programsByCollege); ?>;
        const selectedProgramId = "<?php echo $student['academicProgram'] ?? ''; ?>";

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

<?php

include "template_footer.php";
?>