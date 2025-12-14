<?php

require_once "database.php";

class Student extends Database {

    public $studentId;
    public $adminId = null;

    // Student Info
    public $lastName; public $firstName; public $middleName;
    public $gender; public $dateOfBirth; public $placeOfBirth;
    public $mobileNumber; public $telephoneNumber; public $emailAddress;
    public $nationality; public $civilStatus; public $religion;
    public $ethnicity; 
    public $ethnicityOtherText;
    public $firstInFamilyToAttendCollege;
    public $disability; 
    public $disabilityOtherText;
    public $livesInCoastalArea;
    public $photoPath;

    // IDs
    public $collegeSelection; 
    public $academicProgram;  

    // Addresses
    public $currentHouseStreetNumber; public $currentBarangay; public $currentCity; public $currentProvince; public $currentZipCode;
    public $permanentHouseStreetNumber; public $permanentBarangay; public $permanentCity; public $permanentProvince; public $permanentZipCode;
    public $permanentMobileNumber; public $permanentTelephoneNumber;

    // Parents/Guardian
    public $fatherName; public $fatherEducation; public $fatherOccupation;
    public $motherName; public $motherEducation; public $motherOccupation;
    public $guardianName; public $guardianRelationshipToStudent; public $guardianAddress; public $guardianTelephoneNumber;
    public $parentIncome;

    // Education
    public $primarySchool; public $primaryPlace; public $primaryYear;
    public $juniorHighSchool; public $juniorHighPlace; public $juniorHighYear;
    public $seniorHighSchool; public $seniorHighPlace; public $seniorHighYear;
    public $seniorHighTrack; public $seniorHighStrand;
    public $collegeAttendedBefore;

    // Admission
    public $schoolYear;
    public $typeOfAdmission; public $enrollmentStatus; public $scholarship;
    public $semester; public $yearLevel; public $dateSubmitted; public $agreedToTerms;
    public $studentSignaturePath; public $parentGuardianSignaturePath;
    public $admissionStatus;

    public function mapPropertiesFromUi($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        if(isset($data['dob'])) $this->dateOfBirth = $data['dob'];
        if(isset($data['pob'])) $this->placeOfBirth = $data['pob'];
        if(isset($data['firstInCollege'])) $this->firstInFamilyToAttendCollege = $data['firstInCollege'];
        if(isset($data['coastalArea'])) $this->livesInCoastalArea = $data['coastalArea'];
        if(isset($data['currentHouseStreet'])) $this->currentHouseStreetNumber = $data['currentHouseStreet'];
        if(isset($data['permanentHouseStreet'])) $this->permanentHouseStreetNumber = $data['permanentHouseStreet'];
        if(isset($data['studentSignature'])) $this->studentSignaturePath = $data['studentSignature'];
        if(isset($data['parentSignature'])) $this->parentGuardianSignaturePath = $data['parentSignature'];
    }

    public function addStudent() {
        $conn = $this->connect(); 
        $conn->beginTransaction();

        try {
            $sql = "INSERT INTO student (
                    last_name, first_name, middle_name, gender, date_of_birth, place_of_birth,
                    mobile_no, tel_no, email, photo_path, nationality, civil_status, religion, 
                    ethnicity_other, disability_other, first_in_family, coastal_area
                ) VALUES (
                    :lastName, :firstName, :middleName, :gender, :dateOfBirth, :placeOfBirth,
                    :mobileNumber, :telephoneNumber, :emailAddress, :photoPath, :nationality, :civilStatus, :religion,
                    :ethnicityOther, :disabilityOther, :firstInFamily, :coastalArea
                )";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':lastName' => $this->lastName, ':firstName' => $this->firstName, ':middleName' => $this->middleName,
                ':gender' => $this->gender, ':dateOfBirth' => $this->dateOfBirth, ':placeOfBirth' => $this->placeOfBirth,
                ':mobileNumber' => $this->mobileNumber, ':telephoneNumber' => $this->telephoneNumber,
                ':emailAddress' => $this->emailAddress, ':photoPath'    => $this->photoPath, ':nationality' => $this->nationality,
                ':civilStatus' => $this->civilStatus, ':religion' => $this->religion,
                ':ethnicityOther' => $this->ethnicityOtherText, ':disabilityOther' => $this->disabilityOtherText,
                ':firstInFamily' => $this->firstInFamilyToAttendCollege, ':coastalArea' => $this->livesInCoastalArea
            ]);
            $studentId = $conn->lastInsertId();

            $ethArray = is_string($this->ethnicity) ? json_decode($this->ethnicity, true) : $this->ethnicity;
            if (!empty($ethArray) && is_array($ethArray)) {
                $ethStmt = $conn->prepare("INSERT INTO student_ethnicities (student_id, ethnicity_name) VALUES (?, ?)");
                foreach ($ethArray as $eth) $ethStmt->execute([$studentId, $eth]);
            }

            $disArray = is_string($this->disability) ? json_decode($this->disability, true) : $this->disability;
            if (!empty($disArray) && is_array($disArray)) {
                $disStmt = $conn->prepare("INSERT INTO student_disabilities (student_id, disability_name) VALUES (?, ?)");
                foreach ($disArray as $dis) $disStmt->execute([$studentId, $dis]);
            }

            $addrSql = "INSERT INTO addresses (student_id, address_type, house_street_no, barangay, city, province, zip_code, contact_mobile, contact_tel) 
                        VALUES (:sid, :type, :street, :brgy, :city, :prov, :zip, :mob, :tel)";
            $addrStmt = $conn->prepare($addrSql);
            $addrStmt->execute([
                ':sid' => $studentId, ':type' => 'Current',
                ':street' => $this->currentHouseStreetNumber, ':brgy' => $this->currentBarangay,
                ':city' => $this->currentCity, ':prov' => $this->currentProvince,
                ':zip' => $this->currentZipCode, ':mob' => null, ':tel' => null
            ]);
            $addrStmt->execute([
                ':sid' => $studentId, ':type' => 'Permanent',
                ':street' => $this->permanentHouseStreetNumber, ':brgy' => $this->permanentBarangay,
                ':city' => $this->permanentCity, ':prov' => $this->permanentProvince,
                ':zip' => $this->permanentZipCode, 
                ':mob' => $this->permanentMobileNumber, ':tel' => $this->permanentTelephoneNumber
            ]);

            $pgSql = "INSERT INTO parent_guardian (student_id, father_name, father_education, father_occupation, mother_name, mother_education, mother_occupation, guardian_name, guardian_relationship, guardian_address, guardian_tel, parent_income) 
                      VALUES (:sid, :fn, :fe, :fo, :mn, :me, :mo, :gn, :gr, :ga, :gt, :pi)";
            $conn->prepare($pgSql)->execute([
                ':sid' => $studentId,
                ':fn' => $this->fatherName, ':fe' => $this->fatherEducation, ':fo' => $this->fatherOccupation,
                ':mn' => $this->motherName, ':me' => $this->motherEducation, ':mo' => $this->motherOccupation,
                ':gn' => $this->guardianName, ':gr' => $this->guardianRelationshipToStudent,
                ':ga' => $this->guardianAddress, ':gt' => $this->guardianTelephoneNumber,
                ':pi' => $this->parentIncome
            ]);

            $eduSql = "INSERT INTO educational_background (student_id, school_level, school_name, school_address, year_graduated, track, strand) 
                       VALUES (:sid, :level, :name, :place, :year, :track, :strand)";
            $eduStmt = $conn->prepare($eduSql);
            $eduStmt->execute([':sid' => $studentId, ':level' => 'Primary', ':name' => $this->primarySchool, ':place' => $this->primaryPlace, ':year' => $this->primaryYear, ':track' => null, ':strand' => null]);
            $eduStmt->execute([':sid' => $studentId, ':level' => 'Junior High', ':name' => $this->juniorHighSchool, ':place' => $this->juniorHighPlace, ':year' => $this->juniorHighYear, ':track' => null, ':strand' => null]);
            $eduStmt->execute([':sid' => $studentId, ':level' => 'Senior High', ':name' => $this->seniorHighSchool, ':place' => $this->seniorHighPlace, ':year' => $this->seniorHighYear, ':track' => $this->seniorHighTrack, ':strand' => $this->seniorHighStrand]);
            if (!empty($this->collegeAttendedBefore)) {
                $eduStmt->execute([':sid' => $studentId, ':level' => 'College', ':name' => $this->collegeAttendedBefore, ':place' => '', ':year' => 0, ':track' => null, ':strand' => null]);
            }

            $admSql = "INSERT INTO admission (
                        student_id, admin_id, college_id, school_year, admission_type,
                        enrollment_status, admission_status, scholarship, semester, program_id, year_level,
                        date_submitted, is_seen_by_admin, agree_terms, student_signature, parent_guardian_signature
                    ) VALUES (
                        :sid, :aid, :colId, :sy, :type, :stat, 'Pending', :schol, :sem, :progId, :yl, :date, :seen, :agree, :ssig, :psig
                    )";
            $conn->prepare($admSql)->execute([
                ':sid' => $studentId, 
                ':aid' => $this->adminId, 
                ':colId' => $this->collegeSelection, 
                ':sy' => $this->schoolYear, 
                ':type' => $this->typeOfAdmission, 
                ':stat' => $this->enrollmentStatus,
                ':schol' => $this->scholarship, 
                ':sem' => $this->semester, 
                ':progId' => $this->academicProgram,
                ':yl' => $this->yearLevel, 
                ':date' => $this->dateSubmitted, 
                ':seen' => ($this->adminId === null) ? 0 : 1,
                ':agree' => $this->agreedToTerms, 
                ':ssig' => $this->studentSignaturePath,
                ':psig' => $this->parentGuardianSignaturePath
            ]);

            $conn->commit();
            return $studentId;

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    public function viewStudent($studentId) { 
        $conn = $this->connect();
        try {
            $sql = "SELECT 
                        s.*,
                        pg.father_name, pg.father_education, pg.father_occupation,
                        pg.mother_name, pg.mother_education, pg.mother_occupation,
                        pg.guardian_name, pg.guardian_relationship, pg.guardian_address, pg.guardian_tel, pg.parent_income,
                        adm.admin_id, 
                        adm.college_id, rc.college_name AS college_selection_admission,
                        adm.program_id, rp.program_name AS academic_program,
                        adm.school_year, adm.admission_type,
                        adm.enrollment_status, adm.admission_status, adm.scholarship, adm.semester, adm.year_level,
                        adm.date_submitted, adm.agree_terms, adm.student_signature, adm.parent_guardian_signature
                    FROM student s
                    LEFT JOIN parent_guardian pg ON s.student_id = pg.student_id
                    LEFT JOIN admission adm ON s.student_id = adm.student_id
                    LEFT JOIN ref_college rc ON adm.college_id = rc.college_id
                    LEFT JOIN ref_program rp ON adm.program_id = rp.program_id
                    WHERE s.student_id = :studentId";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":studentId", $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return false;

            $addrSql = "SELECT * FROM addresses WHERE student_id = :sid";
            $addrStmt = $conn->prepare($addrSql);
            $addrStmt->execute([':sid' => $studentId]);
            $addresses = $addrStmt->fetchAll(PDO::FETCH_ASSOC);

            $data['current_house_street_no'] = ''; $data['current_barangay'] = ''; $data['current_city'] = ''; $data['current_province'] = ''; $data['current_zip'] = '';
            $data['permanent_house_street_no'] = ''; $data['permanent_barangay'] = ''; $data['permanent_city'] = ''; $data['permanent_province'] = ''; $data['permanent_zip'] = ''; $data['permanent_mobile'] = ''; $data['permanent_tel'] = '';

            foreach ($addresses as $addr) {
                if ($addr['address_type'] === 'Current') {
                    $data['current_house_street_no'] = $addr['house_street_no'];
                    $data['current_barangay'] = $addr['barangay'];
                    $data['current_city'] = $addr['city'];
                    $data['current_province'] = $addr['province'];
                    $data['current_zip'] = $addr['zip_code'];
                } elseif ($addr['address_type'] === 'Permanent') {
                    $data['permanent_house_street_no'] = $addr['house_street_no'];
                    $data['permanent_barangay'] = $addr['barangay'];
                    $data['permanent_city'] = $addr['city'];
                    $data['permanent_province'] = $addr['province'];
                    $data['permanent_zip'] = $addr['zip_code'];
                    $data['permanent_mobile'] = $addr['contact_mobile'];
                    $data['permanent_tel'] = $addr['contact_tel'];
                }
            }

            $eduSql = "SELECT * FROM educational_background WHERE student_id = :sid";
            $eduStmt = $conn->prepare($eduSql);
            $eduStmt->execute([':sid' => $studentId]);
            $educations = $eduStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($educations as $edu) {
                if ($edu['school_level'] === 'Primary') {
                    $data['primary_school'] = $edu['school_name'];
                    $data['primary_place'] = $edu['school_address'];
                    $data['primary_year'] = $edu['year_graduated'];
                } elseif ($edu['school_level'] === 'Junior High') {
                    $data['junior_school'] = $edu['school_name'];
                    $data['junior_place'] = $edu['school_address'];
                    $data['junior_year'] = $edu['year_graduated'];
                } elseif ($edu['school_level'] === 'Senior High') {
                    $data['senior_school'] = $edu['school_name'];
                    $data['senior_place'] = $edu['school_address'];
                    $data['senior_year'] = $edu['year_graduated'];
                    $data['track'] = $edu['track'];
                    $data['strand'] = $edu['strand'];
                } elseif ($edu['school_level'] === 'College') {
                    $data['college_attended_before_education'] = $edu['school_name'];
                }
            }

            $ethSql = "SELECT ethnicity_name FROM student_ethnicities WHERE student_id = :sid";
            $ethStmt = $conn->prepare($ethSql);
            $ethStmt->execute([':sid' => $studentId]);
            $data['ethnicity'] = json_encode($ethStmt->fetchAll(PDO::FETCH_COLUMN));

            $disSql = "SELECT disability_name FROM student_disabilities WHERE student_id = :sid";
            $disStmt = $conn->prepare($disSql);
            $disStmt->execute([':sid' => $studentId]);
            $data['disability'] = json_encode($disStmt->fetchAll(PDO::FETCH_COLUMN));

            return $data;

        } catch (PDOException $e) {
            error_log("Error viewing student record: " . $e->getMessage());
            return false;
        }
    }

    public function viewAllStudents($searchTerm = '', $filterProgram = '', $filterClassification = '', $filterCollege = '', $filterAdmissionStatus = '') {
        $conn = $this->connect();
        try {
            $currentDate = date('Y-m-d');
            $sql = "SELECT s.student_id, s.first_name, s.last_name, 
                           rp.program_name AS academic_program, 
                           rc.college_name AS college,          
                           adm.enrollment_status, adm.admission_status, adm.date_submitted
                    FROM student s
                    LEFT JOIN admission adm ON s.student_id = adm.student_id
                    LEFT JOIN ref_college rc ON adm.college_id = rc.college_id
                    LEFT JOIN ref_program rp ON adm.program_id = rp.program_id
                    WHERE s.deleted_at IS NULL AND adm.date_submitted <= :currentDate";
            
            $params = [':currentDate' => $currentDate];

            if (!empty($searchTerm)) {
                $sql .= " AND (s.first_name LIKE :s OR s.last_name LIKE :s OR rp.program_name LIKE :s)";
                $params[':s'] = "%$searchTerm%";
            }
            if (!empty($filterProgram)) { 
                if (is_numeric($filterProgram)) {
                    $sql .= " AND adm.program_id = :p";
                } else {
                    $sql .= " AND rp.program_name = :p";
                }
                $params[':p'] = $filterProgram; 
            }
            if (!empty($filterCollege)) { 
                if (is_numeric($filterCollege)) {
                    $sql .= " AND adm.college_id = :c";
                } else {
                    $sql .= " AND rc.college_name = :c";
                }
                $params[':c'] = $filterCollege; 
            }
            if (!empty($filterClassification)) { 
                $sql .= " AND adm.enrollment_status = :st"; 
                $params[':st'] = $filterClassification; 
            }
            
            if (!empty($filterAdmissionStatus)) {
                if ($filterAdmissionStatus === 'Pending') {
                    $sql .= " AND (adm.admission_status = 'Pending' OR adm.admission_status IS NULL)";
                } else {
                    $sql .= " AND adm.admission_status = :ads";
                    $params[':ads'] = $filterAdmissionStatus;
                }
            }

            $sql .= " ORDER BY adm.date_submitted DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error viewing all students: " . $e->getMessage());
            return [];
        }
    }

    public function updateStudent() {
        $conn = $this->connect(); 
        $conn->beginTransaction();

        try {
            $studentSql = "UPDATE student SET 
                            last_name = :lastName, first_name = :firstName, middle_name = :middleName, 
                            gender = :gender, date_of_birth = :dateOfBirth, place_of_birth = :placeOfBirth,
                            mobile_no = :mobileNumber, tel_no = :telephoneNumber, email = :emailAddress, 
                            nationality = :nationality, civil_status = :civilStatus, religion = :religion, 
                            ethnicity_other = :ethnicityOtherText, first_in_family = :firstInFamily, 
                            disability_other = :disabilityOtherText, coastal_area = :coastalArea, photo_path = :photoPath 
                        WHERE student_id = :studentId";
            $stmt = $conn->prepare($studentSql);
            $stmt->execute([
                ':lastName' => $this->lastName, ':firstName' => $this->firstName, ':middleName' => $this->middleName,
                ':gender' => $this->gender, ':dateOfBirth' => $this->dateOfBirth, ':placeOfBirth' => $this->placeOfBirth,
                ':mobileNumber' => $this->mobileNumber, ':telephoneNumber' => $this->telephoneNumber,
                ':emailAddress' => $this->emailAddress, ':nationality' => $this->nationality,
                ':civilStatus' => $this->civilStatus, ':religion' => $this->religion,
                ':ethnicityOtherText' => $this->ethnicityOtherText, ':firstInFamily' => $this->firstInFamilyToAttendCollege,
                ':disabilityOtherText' => $this->disabilityOtherText, ':coastalArea' => $this->livesInCoastalArea, ':photoPath' => $this->photoPath, 
                ':studentId' => $this->studentId
            ]);

            $conn->prepare("DELETE FROM student_ethnicities WHERE student_id = ?")->execute([$this->studentId]);
            $ethArray = is_string($this->ethnicity) ? json_decode($this->ethnicity, true) : $this->ethnicity;
            if (!empty($ethArray) && is_array($ethArray)) {
                $ethStmt = $conn->prepare("INSERT INTO student_ethnicities (student_id, ethnicity_name) VALUES (?, ?)");
                foreach ($ethArray as $eth) $ethStmt->execute([$this->studentId, $eth]);
            }

            $conn->prepare("DELETE FROM student_disabilities WHERE student_id = ?")->execute([$this->studentId]);
            $disArray = is_string($this->disability) ? json_decode($this->disability, true) : $this->disability;
            if (!empty($disArray) && is_array($disArray)) {
                $disStmt = $conn->prepare("INSERT INTO student_disabilities (student_id, disability_name) VALUES (?, ?)");
                foreach ($disArray as $dis) $disStmt->execute([$this->studentId, $dis]);
            }

            $addrUpd = "UPDATE addresses SET house_street_no=?, barangay=?, city=?, province=?, zip_code=?, contact_mobile=?, contact_tel=? WHERE student_id=? AND address_type=?";
            $addrStmt = $conn->prepare($addrUpd);
            $addrStmt->execute([
                $this->currentHouseStreetNumber, $this->currentBarangay, $this->currentCity, $this->currentProvince, $this->currentZipCode, 
                null, null, $this->studentId, 'Current'
            ]);
            $addrStmt->execute([
                $this->permanentHouseStreetNumber, $this->permanentBarangay, $this->permanentCity, $this->permanentProvince, $this->permanentZipCode, 
                $this->permanentMobileNumber, $this->permanentTelephoneNumber, $this->studentId, 'Permanent'
            ]);

            $parentSql = "UPDATE parent_guardian SET 
                            father_name = :fatherName, father_education = :fatherEducation, father_occupation = :fatherOccupation,
                            mother_name = :motherName, mother_education = :motherEducation, mother_occupation = :motherOccupation,
                            guardian_name = :guardianName, guardian_relationship = :guardianRelationshipToStudent, 
                            guardian_address = :guardianAddress, guardian_tel = :guardianTelephoneNumber, 
                            parent_income = :parentIncome
                        WHERE student_id = :studentId";
            $conn->prepare($parentSql)->execute([
                ':fatherName' => $this->fatherName, ':fatherEducation' => $this->fatherEducation, ':fatherOccupation' => $this->fatherOccupation,
                ':motherName' => $this->motherName, ':motherEducation' => $this->motherEducation, ':motherOccupation' => $this->motherOccupation,
                ':guardianName' => $this->guardianName, ':guardianRelationshipToStudent' => $this->guardianRelationshipToStudent,
                ':guardianAddress' => $this->guardianAddress, ':guardianTelephoneNumber' => $this->guardianTelephoneNumber,
                ':parentIncome' => $this->parentIncome, ':studentId' => $this->studentId
            ]);

            $conn->prepare("DELETE FROM educational_background WHERE student_id = ?")->execute([$this->studentId]);
            $eduSql = "INSERT INTO educational_background (student_id, school_level, school_name, school_address, year_graduated, track, strand) 
                       VALUES (:sid, :level, :name, :place, :year, :track, :strand)";
            $eduStmt = $conn->prepare($eduSql);
            $eduStmt->execute([':sid' => $this->studentId, ':level' => 'Primary', ':name' => $this->primarySchool, ':place' => $this->primaryPlace, ':year' => $this->primaryYear, ':track' => null, ':strand' => null]);
            $eduStmt->execute([':sid' => $this->studentId, ':level' => 'Junior High', ':name' => $this->juniorHighSchool, ':place' => $this->juniorHighPlace, ':year' => $this->juniorHighYear, ':track' => null, ':strand' => null]);
            $eduStmt->execute([':sid' => $this->studentId, ':level' => 'Senior High', ':name' => $this->seniorHighSchool, ':place' => $this->seniorHighPlace, ':year' => $this->seniorHighYear, ':track' => $this->seniorHighTrack, ':strand' => $this->seniorHighStrand]);
            if (!empty($this->collegeAttendedBefore)) {
                $eduStmt->execute([':sid' => $this->studentId, ':level' => 'College', ':name' => $this->collegeAttendedBefore, ':place' => '', ':year' => 0, ':track' => null, ':strand' => null]);
            }

            $admSql = "UPDATE admission SET 
                        college_id = :colId,
                        program_id = :progId,
                        school_year = :schoolYear, admission_type = :typeOfAdmission,
                        enrollment_status = :enrollmentStatus, admission_status = :admissionStatus, 
                        scholarship = :scholarship, semester = :semester, 
                        year_level = :yearLevel, date_submitted = :dateSubmitted, 
                        agree_terms = :agreedToTerms, student_signature = :studentSignaturePath, 
                        parent_guardian_signature = :parentGuardianSignaturePath
                    WHERE student_id = :studentId";
            
            $conn->prepare($admSql)->execute([
                ':colId' => $this->collegeSelection,
                ':progId' => $this->academicProgram,
                ':schoolYear' => $this->schoolYear,
                ':typeOfAdmission' => $this->typeOfAdmission, 
                ':enrollmentStatus' => $this->enrollmentStatus,
                ':admissionStatus' => $this->admissionStatus,
                ':scholarship' => $this->scholarship, 
                ':semester' => $this->semester,
                ':yearLevel' => $this->yearLevel,
                ':dateSubmitted' => $this->dateSubmitted, 
                ':agreedToTerms' => $this->agreedToTerms,
                ':studentSignaturePath' => $this->studentSignaturePath,
                ':parentGuardianSignaturePath' => $this->parentGuardianSignaturePath,
                ':studentId' => $this->studentId
            ]);

            $conn->commit();
            return true;

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error updating student record: " . $e->getMessage());
            return false;
        }
    }

    public function getColleges() {
        $conn = $this->connect();
        return $conn->query("SELECT * FROM ref_college ORDER BY college_name")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPrograms() {
        $conn = $this->connect();
        return $conn->query("SELECT * FROM ref_program ORDER BY program_name")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteStudent($studentId) {
        $conn = $this->connect();
        try {
            $sql = "UPDATE student SET deleted_at = CURRENT_TIMESTAMP WHERE student_id = :studentId";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error soft deleting: " . $e->getMessage());
            return false;
        }
    }

    public function viewDeletedStudents($filterCollege = null) {
        $conn = $this->connect();
        try {
            $sql = "SELECT s.student_id, s.first_name, s.last_name, s.deleted_at, 
                           rp.program_name AS academic_program,
                           rc.college_name AS college
                    FROM student s
                    LEFT JOIN admission adm ON s.student_id = adm.student_id
                    LEFT JOIN ref_college rc ON adm.college_id = rc.college_id
                    LEFT JOIN ref_program rp ON adm.program_id = rp.program_id
                    WHERE s.deleted_at IS NOT NULL";
            
            $params = [];

            if (!empty($filterCollege)) {
                $sql .= " AND rc.college_name = :col";
                $params[':col'] = $filterCollege;
            }

            $sql .= " ORDER BY s.deleted_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            error_log("Recycle Bin Error: " . $e->getMessage());
            return []; 
        }
    }

    public function restoreStudent($studentId) {
        $conn = $this->connect();
        try {
            $sql = "UPDATE student SET deleted_at = NULL WHERE student_id = :studentId";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function permanentlyDeleteStudent($studentId) {
        $conn = $this->connect();
        $conn->beginTransaction();
        try {
            $tables = ['admission', 'educational_background', 'parent_guardian', 'addresses', 'student_ethnicities', 'student_disabilities'];
            foreach($tables as $tbl) {
                $conn->prepare("DELETE FROM $tbl WHERE student_id = ?")->execute([$studentId]);
            }
            $conn->prepare("DELETE FROM student WHERE student_id = ?")->execute([$studentId]);
            $conn->commit();
            return true;
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error permanently deleting: " . $e->getMessage());
            return false;
        }
    }

    public function getDashboardStatistics($filterCollege = null) {
        $conn = $this->connect();
        $stats = [
            'total_applicants' => 0, 
            'total_scholarships' => 0, 
            'total_enrolled' => 0,
            'total_pending' => 0,
            
            'by_college' => [], 
            'by_program' => [],
            'by_admission_type' => [], 
            'by_enrollment_status' => [], 
            'by_admission_status' => [],
            'by_semester' => [], 
            'by_school_year' => []
        ];
        
        try {
            $currentDate = date('Y-m-d');
            
            $baseWhere = " FROM student s 
                           JOIN admission adm ON s.student_id = adm.student_id 
                           LEFT JOIN ref_college rc ON adm.college_id = rc.college_id
                           LEFT JOIN ref_program rp ON adm.program_id = rp.program_id
                           WHERE s.deleted_at IS NULL AND adm.date_submitted <= :currentDate";
            
            $params = [':currentDate' => $currentDate];

            if (!empty($filterCollege)) {
                if (is_numeric($filterCollege)) {
                    $baseWhere .= " AND adm.college_id = :filterCollege";
                } else {
                    $baseWhere .= " AND rc.college_name = :filterCollege";
                }
                $params[':filterCollege'] = $filterCollege;
            }

            // 1. Total Applicants
            $stmt = $conn->prepare("SELECT COUNT(s.student_id) $baseWhere"); 
            $stmt->execute($params); 
            $stats['total_applicants'] = $stmt->fetchColumn();

            // 2. By College
            $stmt = $conn->prepare("SELECT rc.college_name AS college, COUNT(s.student_id) as count $baseWhere GROUP BY rc.college_name"); 
            $stmt->execute($params);
            $stats['by_college'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. By Academic Program
            $stmt = $conn->prepare("SELECT rp.program_name AS academic_program, COUNT(s.student_id) as count $baseWhere GROUP BY rp.program_name"); 
            $stmt->execute($params);
            $stats['by_program'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Admission Type
            $stmt = $conn->prepare("SELECT adm.admission_type, COUNT(s.student_id) as count $baseWhere GROUP BY adm.admission_type"); 
            $stmt->execute($params);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { 
                if ($row['admission_type']) $stats['by_admission_type'][$row['admission_type']] = $row['count']; 
            }

            // 5. Enrollment Status
            $stmt = $conn->prepare("SELECT adm.enrollment_status, COUNT(s.student_id) as count $baseWhere GROUP BY adm.enrollment_status"); 
            $stmt->execute($params);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { 
                if ($row['enrollment_status']) $stats['by_enrollment_status'][$row['enrollment_status']] = $row['count']; 
            }

            // 6. Admission Status 
            $stmt = $conn->prepare("SELECT adm.admission_status, COUNT(s.student_id) as count $baseWhere GROUP BY adm.admission_status"); 
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) { 
                $status = $row['admission_status'];
                $count = $row['count'];
                
                if (empty($status) || $status === 'Pending') {
                    $stats['total_pending'] += $count;
                    $stats['by_admission_status']['Pending'] = ($stats['by_admission_status']['Pending'] ?? 0) + $count;
                } 
                elseif ($status === 'Enrolled') {
                    $stats['total_enrolled'] += $count;
                    $stats['by_admission_status']['Enrolled'] = $count;
                }
                else {
                    $stats['by_admission_status'][$status] = $count;
                }
            }

            // 7. Semester
            $stmt = $conn->prepare("SELECT adm.semester, COUNT(s.student_id) as count $baseWhere GROUP BY adm.semester"); 
            $stmt->execute($params);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { 
                if ($row['semester']) $stats['by_semester'][$row['semester']] = $row['count']; 
            }

            // 8. School Year
            $stmt = $conn->prepare("SELECT adm.school_year, COUNT(s.student_id) as count $baseWhere GROUP BY adm.school_year"); 
            $stmt->execute($params);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { 
                if ($row['school_year']) $stats['by_school_year'][$row['school_year']] = $row['count']; 
            }

            // 9. Scholarships
            $stmt = $conn->prepare("SELECT COUNT(s.student_id) $baseWhere AND adm.scholarship IS NOT NULL AND adm.scholarship != ''"); 
            $stmt->execute($params);
            $stats['total_scholarships'] = $stmt->fetchColumn();

            return $stats;
        } catch (PDOException $e) { 
            error_log("Stats Error: " . $e->getMessage());
            return $stats; 
        }
    }

    public function exportStudents($searchTerm = '', $filterProgram = '', $filterStatus = '', $filterCollege = '', $filterTab = '') {
        $conn = $this->connect();
        try {
            // fetch ALL data
            $sql = "SELECT 
                        -- 1. Basic Student Info
                        s.student_id, s.last_name, s.first_name, s.middle_name,
                        s.email, s.mobile_no, s.gender, s.date_of_birth,
                        s.place_of_birth, s.civil_status, s.religion, s.nationality,
                        s.first_in_family, s.coastal_area,
                        
                        -- 2. Admission Details
                        rc.college_name AS college,
                        rp.program_name AS academic_program,
                        adm.school_year, adm.admission_type,
                        adm.enrollment_status, adm.admission_status,
                        adm.scholarship, adm.semester, adm.year_level,
                        adm.date_submitted, adm.agree_terms,

                        -- 3. Current Address
                        curr.house_street_no AS current_street, 
                        curr.barangay AS current_barangay, 
                        curr.city AS current_city, 
                        curr.province AS current_province, 
                        curr.zip_code AS current_zip,

                        -- 4. Permanent Address
                        perm.house_street_no AS permanent_street, 
                        perm.barangay AS permanent_barangay, 
                        perm.city AS permanent_city, 
                        perm.province AS permanent_province, 
                        perm.zip_code AS permanent_zip,

                        -- 5. Parent / Guardian Info
                        pg.father_name, pg.father_occupation,
                        pg.mother_name, pg.mother_occupation,
                        pg.guardian_name, pg.guardian_relationship, pg.parent_income

                    FROM student s
                    -- Join Admission Info
                    LEFT JOIN admission adm ON s.student_id = adm.student_id
                    LEFT JOIN ref_college rc ON adm.college_id = rc.college_id
                    LEFT JOIN ref_program rp ON adm.program_id = rp.program_id
                    
                    -- Join Addresses (Filter by Type)
                    LEFT JOIN addresses curr ON s.student_id = curr.student_id AND curr.address_type = 'Current'
                    LEFT JOIN addresses perm ON s.student_id = perm.student_id AND perm.address_type = 'Permanent'

                    -- Join Parent Info
                    LEFT JOIN parent_guardian pg ON s.student_id = pg.student_id

                    WHERE s.deleted_at IS NULL"; 
            
            $params = [];

            // 1. Search Logic
            if (!empty($searchTerm)) {
                $sql .= " AND (s.first_name LIKE :s OR s.last_name LIKE :s OR rp.program_name LIKE :s)";
                $params[':s'] = "%$searchTerm%";
            }

            // 2. Program Filter
            if (!empty($filterProgram)) { 
                $sql .= " AND (adm.program_id = :p OR rp.program_name = :p)"; 
                $params[':p'] = $filterProgram; 
            }

            // 3. College Filter
            if (!empty($filterCollege)) { 
                $sql .= " AND (adm.college_id = :c OR rc.college_name = :c)"; 
                $params[':c'] = $filterCollege; 
            }

            // 4. Classification Filter
            if (!empty($filterStatus)) { 
                $sql .= " AND adm.enrollment_status = :st"; 
                $params[':st'] = $filterStatus; 
            }

            // 5. Tab Filter
            if (!empty($filterTab)) {
                if ($filterTab === 'Pending') {
                    $sql .= " AND (adm.admission_status = 'Pending' OR adm.admission_status IS NULL)";
                } else {
                    $sql .= " AND adm.admission_status = :tab";
                    $params[':tab'] = $filterTab;
                }
            }

            $sql .= " ORDER BY s.last_name ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) { 
            return []; 
        }
    }

    public function admitStudent($studentId, $data) {
        $conn = $this->connect();
        try {
            $sql = "UPDATE admission SET 
                    college = :col, 
                    academic_program = :prog, 
                    school_year = :sy, 
                    admission_type = :type, 
                    enrollment_status = :enrol_stat, 
                    admission_status = :adm_stat, 
                    scholarship = :schol, 
                    semester = :sem, 
                    year_level = :yl,
                    admin_id = :aid
                    WHERE student_id = :sid";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':col' => $data['college'],
                ':prog' => $data['program'],
                ':sy' => $data['school_year'],
                ':type' => $data['admission_type'],
                ':enrol_stat' => $data['enrollment_status'], 
                ':adm_stat' => $data['admission_status'],    
                ':schol' => $data['scholarship'],
                ':sem' => $data['semester'],
                ':yl' => $data['year_level'],
                ':aid' => $_SESSION['admin_id'],
                ':sid' => $studentId
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Admit Error: " . $e->getMessage());
            return false;
        }
    }

}
?>