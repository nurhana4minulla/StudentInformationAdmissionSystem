<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="print_style.css" media="print">
    <link rel="stylesheet" href="../admission/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    function updateNotificationCount() {
        fetch('check_notifications.php')
            .then(response => response.text())
            .then(data => {
                const count = parseInt(data.trim()); 
                const badge = document.getElementById('notification-count');
                
                if (!isNaN(count) && count > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = count;
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    }

    document.addEventListener('DOMContentLoaded', updateNotificationCount);
    setInterval(updateNotificationCount, 5000);
</script>

</head>
<body>
    <div class="admin-layout-wrapper">
        <nav class="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo.png" alt="Logo" class="sidebar-logo"> 
                <h3>Admin Panel</h3>
            </div>
            <ul class="nav-links">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="manage_students.php">
                        <i class="fas fa-users"></i> Student Management
                    </a>
                </li>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
                    <li>
                        <a href="add_student.php">
                            <i class="fas fa-user-plus"></i> Add New Student
                        </a>
                    </li>
                    <li>
                        <a href="manage_admins.php">
                            <i class="fas fa-user-shield"></i> Manage Users
                        </a>
                    </li>
                    
                <?php endif; ?>

                <li>
                        <a href="recycle_bin.php">
                            <i class="fas fa-trash-restore"></i> Recycle Bin
                        </a>
                </li>

            </ul>
        </nav>

        <div class="main-wrapper">
            <header class="admin-header">
                <h1><?php echo $page_title ?? 'Admin'; ?></h1> 
                <div class="user-info">
                    
                    <a href="notifications.php" class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span id="notification-count" class="notification-dot"></span>
                    </a>
                    
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!</span>
                    
                    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </header>
            <main class="main-content">