<?php
require_once "auth.php";
require_once "../classes/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Manage Admin Users";
$message = "";
$error = "";

$db = new Database();
$conn = $db->connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $college = ($role === 'Super Admin') ? null : $_POST['college'];

    if (empty($name) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // check for duplicate username
            $check = $conn->prepare("SELECT admin_id FROM admin WHERE username = :u");
            $check->execute([':u' => $username]);
            
            if ($check->rowCount() > 0) {
                $error = "Username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO admin (name, username, password, role, college_assigned) 
                        VALUES (:name, :username, :pass, :role, :college)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':username' => $username,
                    ':pass' => $hashed_password,
                    ':role' => $role,
                    ':college' => $college
                ]);
                
                $message = "New user created successfully!";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

$admins = $conn->query("SELECT * FROM admin ORDER BY role, name")->fetchAll(PDO::FETCH_ASSOC);

include "template_header.php";
?>

<div class="container" style="max-width: 1200px; margin-top: 2rem;">
    
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

    <div class="card" style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3 style="color: #A40404; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 15px;">Create New Admin User</h3>
        
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="roleSelect" class="form-control" onchange="toggleCollege()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="Admissions Officer">Admissions Officer</option>
                        <option value="Super Admin">Super Admin</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="collegeGroup" style="margin-top: 20px;">
                <label>Assigned College <small style="color: #666;">(User can only see students applying to this college)</small></label>
                <select name="college" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">-- Select College --</option>
                    <option value="College of Agriculture">College of Agriculture</option>
                    <option value="College of Architecture">College of Architecture</option>
                    <option value="College of Asian and Islamic Studies">College of Asian and Islamic Studies</option>
                    <option value="College of Computing Studies">College of Computing Studies</option>
                    <option value="College of Criminal Justice Education">College of Criminal Justice Education</option>
                    <option value="College of Engineering">College of Engineering</option>
                    <option value="College of Forestry and Environmental Studies">College of Forestry and Environmental Studies</option>
                    <option value="College of Home Economics">College of Home Economics</option>
                    <option value="College of Liberal Arts">College of Liberal Arts</option>
                    <option value="College of Nursing">College of Nursing</option>
                    <option value="College of Public Administration and Development Studies">College of Public Administration and Development Studies</option>
                    <option value="College of Science and Mathematics">College of Science and Mathematics</option>
                    <option value="College of Social Work and Community Development">College of Social Work and Community Development</option>
                    <option value="College of Sports Science and Physical Education">College of Sports Science and Physical Education</option>
                    <option value="College of Teacher Education">College of Teacher Education</option>
                </select>
            </div>

            <div style="margin-top: 25px; text-align: right;">
                <button type="submit" style="background: #A40404; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 1rem;">
                    <i class="fas fa-plus"></i> Create User
                </button>
            </div>
        </form>
    </div>

    <div class="card" style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="color: #333; margin-top: 0;">Existing Users</h3>
        
        <div class="student-list">
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background: #f8f9fa; text-align: left;">
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Name</th>
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Role</th>
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Assigned College</th>
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $user): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">
                            <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                            <small style="color: #888;">(<?php echo htmlspecialchars($user['username']); ?>)</small>
                        </td>
                        <td style="padding: 12px;">
                            <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; 
                                background: <?php echo ($user['role'] == 'Super Admin') ? '#e3f2fd' : '#fff3cd'; ?>; 
                                color: <?php echo ($user['role'] == 'Super Admin') ? '#0d47a1' : '#856404'; ?>;">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($user['college_assigned'] ?? 'All Access'); ?></td>
                        <td style="padding: 12px;">
                            <a href="edit_admin.php?id=<?php echo $user['admin_id']; ?>" 
                               style="display: inline-block; border: 1px solid #004a99; background: #fff; padding: 5px 10px; border-radius: 4px; color: #004a99; text-decoration: none; font-size: 0.9rem;">
                               <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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