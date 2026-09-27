<?php
require_once __DIR__ . '/inc/db.php';
session_start();

$errors = [];
$full_name = $username = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($full_name === '' || !preg_match("/^[a-zA-Z ]+$/", $full_name)) {
        $errors[] = "Full name must contain alphabetic characters and spaces only.";
    }
    if ($username === '' || !preg_match("/^[a-zA-Z][a-zA-Z0-9_]{2,}$/", $username)) {
        $errors[] = "Username must start with a letter and be at least 3 characters (letters, numbers, underscore only, no spaces).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $errors[] = "Password must be at least 8 chars, include upper, lower, and number.";
    }

    $stmt = $mysqli->prepare("SELECT UserID FROM Users WHERE UserName = ? OR UserEmail = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Username or email already taken.";
    $stmt->close();

    if (empty($errors)) {
    
        $res = $mysqli->query("SELECT MAX(CAST(SUBSTR(UserID, 2) AS UNSIGNED)) as max_id FROM Users");
        $row = $res->fetch_assoc();
        $next_id = ($row['max_id'] ?? 0) + 1;
        $user_id = 'U' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
        
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $role = 'User';
        $ins = $mysqli->prepare("INSERT INTO Users (UserID, UserFullName, UserName, UserEmail, UserPassword, UserRole) VALUES (?,?,?,?,?,?)");
        $ins->bind_param("ssssss", $user_id, $full_name, $username, $email, $hash, $role);
        $ins->execute();
        $ins->close();
        header('Location: login.php?registered=1');
        exit;
    }
}

$page_css = 'assets/css/register.css';
require_once __DIR__ . '/inc/header.php';
?>
<h2>Register</h2>
<?php if (!empty($errors)): ?>
  <div class="errors">
    <ul><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
  </div>
<?php endif; ?>
<form method="post">
  <label>Full Name: <input type="text" name="full_name" value="<?=htmlspecialchars($full_name)?>"></label>
  <label>Username: <input type="text" name="username" value="<?=htmlspecialchars($username)?>"></label>
  <label>Email: <input type="email" name="email" value="<?=htmlspecialchars($email)?>"></label>
  <label>Password: <input type="password" name="password"></label>
  <button type="submit">Register</button>
</form>
<?php require_once __DIR__ . '/inc/footer.php'; ?>