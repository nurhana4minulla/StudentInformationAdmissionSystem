<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once "../classes/database.php";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $db = new Database();
        $conn = $db->connect();

        $sql = "SELECT * FROM admin WHERE username = :username LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            
            // SAVE ROLE & COLLEGE 
            $_SESSION['role'] = $admin['role']; 
            $_SESSION['college_assigned'] = $admin['college_assigned'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "Invalid username or password.";
        }

    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            font-weight: 500;
            background-image: url('../images/wmsubg.jpg'); 
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat; 
            background-attachment: fixed; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.9); 
            padding: 2rem 3rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-logo {
            max-width: 100px; 
            height: auto;
            margin-bottom: 1.5rem; 
        }
        .login-container h1 {
            color: #A40404;
            margin-bottom: 2rem;
            font-size: 1.6rem; 
        }
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333; 
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc; 
            border-radius: 6px;
            box-sizing: border-box;
            background-color: #fff; 
        }
        .login-btn {
            background-color: #A40404;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            transition: background-color 0.3s ease;
            font-weight: 500; 
        }
        .login-btn:hover {
            background-color: #8c0303;
        }
        .error {
            color: red;
            background-color: #ffebee;
            border: 1px solid red;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem; 
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="../images/logo.png" alt="School Logo" class="login-logo"> <h1>Admin Login</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
</body>
</html>