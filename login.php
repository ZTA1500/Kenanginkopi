<?php
require_once __DIR__ . '/inc/db.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($username === '' || $password === '') {
        $errors[] = "Please fill username and password.";
    } else {
        $stmt = $mysqli->prepare("SELECT UserID, UserPassword FROM Users WHERE UserName = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['UserPassword'])) {
                $_SESSION['user_id'] = $row['UserID'];
                if ($remember) {
                    setcookie('remember_me', $row['UserID'], time() + 7*24*60*60, "/");
                }
                header('Location: index.php');
                exit;
            } else {
                $errors[] = "Invalid credentials.";
            }
        } else {
            $errors[] = "Invalid credentials.";
        }
        $stmt->close();
    }
}

// If cookie exists set session
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
  $_SESSION['user_id'] = $_COOKIE['remember_me'];
  header('Location: index.php');
  exit;
}

$page_css = 'assets/css/login.css';
require_once __DIR__ . '/inc/header.php';
?>
<h2>Login</h2>
<?php if (!empty($_GET['registered'])) echo "<p class='msg'>Registered successfully. Please login.</p>"; ?>
<?php if (!empty($errors)) foreach ($errors as $e) echo "<p class='err'>".htmlspecialchars($e)."</p>"; ?>
<form method="post">
  <label>Username<input type="text" name="username"></label>
  <label>Password<input type="password" name="password"></label>
  <label><input type="checkbox" name="remember"> Remember me</label>
  <button type="submit">Login</button>
</form>
<p style="text-align:center; margin-top:16px; color:#666;">Don't have an account? <a href="register.php" style="color:#6f4e37; text-decoration:none; font-weight:600;">Sign up</a></p>
<?php require_once __DIR__ . '/inc/footer.php'; ?>