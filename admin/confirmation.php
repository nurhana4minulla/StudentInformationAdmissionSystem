<?php
session_start(); 

$student_id_to_print = $_SESSION['last_student_id'] ?? null;
$student_email = $_SESSION['last_student_email'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted Successfully</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            padding: 20px;
        }
        .confirmation-container {
            background-color: #fff;
            padding: 3rem 4rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            border-top: 5px solid #28a745;
        }
        .confirmation-container .icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }
        .confirmation-container h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        .confirmation-container p {
            color: #555;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
       
        .email-confirm-msg {
            font-size: 1rem;
            color: #333;
            background-color: #e6f7ff; 
            border: 1px solid #b3e0ff; 
            border-radius: 8px;
            padding: 1rem;
            margin-top: -1rem; 
            margin-bottom: 2rem;
            line-height: 1.5;
            text-align: left; 
        }
        .email-confirm-msg strong {
            color: #0056b3; 
        }
        .email-confirm-msg small {
            color: #555;
            font-style: italic;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .action-btn {
            background-color: #007bff;
            color: #fff;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .action-btn.print {
            background-color: #17a2b8;
        }
        .action-btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Application Submitted!</h1>
        <p>Thank you for submitting your admission information. Your application has been received successfully.</p>
        
        <?php if ($student_email): ?>
            <p class="email-confirm-msg">
                <i class="fas fa-envelope-open-text"></i> A confirmation receipt has been sent to your email address:
                <br>
                <strong><?php echo htmlspecialchars($student_email); ?></strong>
                <br>
                <small>Please check your inbox (and spam folder).</small>
            </p>
        <?php endif; ?>
        
        <div class="actions">
            <a href="../index.php" class="action-btn"> 
                <i class="fas fa-home"></i> Go to Homepage 
            </a> 
            <?php if ($student_id_to_print): ?>
                <a href="print_application.php?id=<?php echo $student_id_to_print; ?>" target="_blank" class="action-btn print">
                    <i class="fas fa-print"></i> Print My Application
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>