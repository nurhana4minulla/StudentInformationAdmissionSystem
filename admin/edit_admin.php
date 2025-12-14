<?php
require_once "auth.php";
require_once "../classes/database.php";

// --- SECURITY CHECK ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Edit Admin User";
$message = "";
$error = "";

$db = new Database();
$conn = $db->connect();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_admins.php");
    exit;
}
$admin_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $college = ($role === 'Super Admin') ? null : $_POST['college'];
    $new_password = $_POST['password']; 

    if (empty($name) || empty($username)) {
        $error = "Name and Username are required.";
    } else {
        try {
            $check = $conn->prepare("SELECT admin_id FROM admin WHERE username = :u AND admin_id != :id");
            $check->execute([':u' => $username, ':id' => $admin_id]);
            
            if ($check->rowCount() > 0) {
                $error = "Username already exists.";
            } else {
               
                if (!empty($new_password)) {
                    $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE admin SET name=:nm, username=:usr, password=:pass, role=:rl, college_assigned=:col WHERE admin_id=:id";
                    $params = [
                        ':nm' => $name, ':usr' => $username, ':pass' => $hashed_pass, 
                        ':rl' => $role, ':col' => $college, ':id' => $admin_id
                    ];
                } else {
                    $sql = "UPDATE admin SET name=:nm, username=:usr, role=:rl, college_assigned=:col WHERE admin_id=:id";
                    $params = [
                        ':nm' => $name, ':usr' => $username, 
                        ':rl' => $role, ':col' => $college, ':id' => $admin_id
                    ];
                }

                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $message = "User updated successfully!";
                
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}


try {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = :id");
    $stmt->execute([':id' => $admin_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("Error fetching user.");
}

include "template_header.php";
?>

<div class="container" style="max-width: 800px; margin-top: 2rem;">
    <a href="manage_admins.php" style="text-decoration: none; color: #666; margin-bottom: 20px; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>

    <?php if ($message): ?>
        <div class="alert alert-success" style="padding: 15px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="color: #A40404; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 15px;">Edit User: <?php echo htmlspecialchars($user['username']); ?></h3>
        
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>Password <small style="color: #888;">(Leave blank to keep current)</small></label>
                    <input type="password" name="password" placeholder="New password..." class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="roleSelect" class="form-control" onchange="toggleCollege()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="Admissions Officer" <?php echo ($user['role'] === 'Admissions Officer') ? 'selected' : ''; ?>>Admissions Officer</option>
                        <option value="Super Admin" <?php echo ($user['role'] === 'Super Admin') ? 'selected' : ''; ?>>Super Admin</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="collegeGroup" style="margin-top: 20px;">
                <label>Assigned College</label>
                <select name="college" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">-- Select College --</option>
                    <?php 
                    $colleges = [
                        "College of Agriculture", "College of Architecture", "College of Asian and Islamic Studies",
                        "College of Computing Studies", "College of Criminal Justice Education", "College of Engineering",
                        "College of Forestry and Environmental Studies", "College of Home Economics", "College of Liberal Arts",
                        "College of Nursing", "College of Public Administration and Development Studies", "College of Science and Mathematics",
                        "College of Social Work and Community Development", "College of Sports Science and Physical Education", "College of Teacher Education"
                    ];
                    foreach($colleges as $col) {
                        $selected = ($user['college_assigned'] === $col) ? 'selected' : '';
                        echo "<option value=\"$col\" $selected>$col</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="margin-top: 25px; text-align: right;">
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 1rem;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleCollege() {
        var role = document.getElementById("roleSelect").value;
        var collegeGroup = document.getElementById("collegeGroup");
        if (role === "Super Admin") {
            collegeGroup.style.display = "none";
        } else {
            collegeGroup.style.display = "block";
        }
    }
    toggleCollege(); 
</script>

<?php include "template_footer.php"; ?>