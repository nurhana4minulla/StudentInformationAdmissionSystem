<?php

require_once "auth.php";

$message = $_GET['message'] ?? 'Action completed successfully.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
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
        .confirmation-container .action-btn {
            background-color: #A40404; 
            color: #fff;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
            display: inline-block;
        }
        .confirmation-container .action-btn:hover {
            background-color: #8c0303;
        }
        .confirmation-container .action-btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Success!</h1>
        <p><?php echo htmlspecialchars($message); ?></p>

        <a href="dashboard.php" class="action-btn">
            <i class="fas fa-tachometer-alt"></i> Return to Dashboard
        </a>
    </div>
</body>
</html>