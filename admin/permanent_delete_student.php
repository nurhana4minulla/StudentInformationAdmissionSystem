<?php
require_once "auth.php";
require_once "../classes/student.php";

// Check if an ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: recycle_bin.php?error=Invalid ID provided.");
    exit;
}

$student_id = $_GET['id'];
$studentObj = new Student();

$studentData = $studentObj->viewStudent($student_id); 
$studentName = $studentData ? $studentData['first_name'] . ' ' . $studentData['last_name'] : 'this student';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['student_id']) && $_POST['student_id'] == $student_id) {

        if ($studentObj->permanentlyDeleteStudent($student_id)) {
            // success
            header("Location: recycle_bin.php?message=Student record permanently deleted.");
            exit;
        } else {
            // failed
             header("Location: recycle_bin.php?error=Failed to permanently delete student record.");
             exit;
        }
    } else {
        header("Location: recycle_bin.php?error=Invalid delete request.");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Permanent Delete</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> 
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .confirm-container { background-color: #fff; padding: 2rem 3rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); text-align: center; max-width: 500px; border-top: 5px solid #dc3545; }
        .confirm-container h1 { color: #dc3545; margin-bottom: 1rem; }
        .confirm-container p { margin-bottom: 2rem; font-size: 1.1rem; color: #333; }
        .confirm-container strong { color: #dc3545; } 
        .confirm-actions { display: flex; justify-content: center; gap: 1rem; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; text-decoration: none; transition: background-color 0.3s ease; }
        .btn-danger { background-color: #dc3545; color: #fff; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-secondary { background-color: #6c757d; color: #fff; }
        .btn-secondary:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="confirm-container">
        <h1><i class="fas fa-skull-crossbones"></i> Confirm Permanent Deletion</h1>
        <p>Are you absolutely sure you want to <strong>PERMANENTLY DELETE</strong> the record for <strong><?php echo htmlspecialchars($studentName); ?></strong>?</p>
        <p><strong>This action CANNOT be undone. All associated data will be lost forever.</strong></p>

        <form action="permanent_delete_student.php?id=<?php echo $student_id; ?>" method="POST">
            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
            <div class="confirm-actions">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Yes, Delete Permanently</button>
                <a href="recycle_bin.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>