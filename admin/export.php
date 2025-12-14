<?php
ob_start();

require_once "auth.php"; 
require_once "../classes/student.php";

$studentObj = new Student();

$searchTerm = $_GET['search'] ?? '';
$filterProgram = $_GET['program'] ?? '';
$filterClassification = $_GET['classification'] ?? ''; 
$filterCollege = $_GET['college'] ?? '';
$filterTab = $_GET['tab'] ?? ''; 

$students = $studentObj->exportStudents($searchTerm, $filterProgram, $filterClassification, $filterCollege, $filterTab);

ob_end_clean();

$filename = "student_applications_" . date('Y-m-d') . ".csv";

if (empty($students) && !is_array($students)) {
    $students = [];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fputs($output, "\xEF\xBB\xBF");

if (!empty($students)) {
    fputcsv($output, array_keys($students[0]));

    foreach ($students as $row) {
        if (isset($row['first_in_family'])) $row['first_in_family'] = ($row['first_in_family'] == 1) ? 'Yes' : 'No';
        if (isset($row['coastal_area']))    $row['coastal_area'] = ($row['coastal_area'] == 1) ? 'Yes' : 'No';
        if (isset($row['agree_terms']))     $row['agree_terms'] = ($row['agree_terms'] == 1) ? 'Yes' : 'No';

        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No student data found for the selected filters.']);
}

fclose($output);
exit;
?>