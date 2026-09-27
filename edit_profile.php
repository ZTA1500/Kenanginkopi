<?php
require_once __DIR__ . '/inc/db.php';
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$errors = [];
$stmt = $mysqli->prepare("SELECT UserID, UserFullName, UserName, UserEmail FROM Users WHERE UserID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username === '' || !preg_match("/^[a-zA-Z][a-zA-Z0-9_]{2,}$/", $username)) $errors[] = "Username must start with a letter and be at least 3 characters (letters, numbers, underscore only).";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";

    
    if ($username !== $user['UserName']) {
        $s = $mysqli->prepare("SELECT UserID FROM Users WHERE UserName = ?");
        $s->bind_param("s", $username);
        $s->execute();
        $s->store_result();
        if ($s->num_rows > 0) $errors[] = "Username already taken.";
        $s->close();
    }

    if ($email !== $user['UserEmail']) {
        $s = $mysqli->prepare("SELECT UserID FROM Users WHERE UserEmail = ?");
        $s->bind_param("s", $email);
        $s->execute();
        $s->store_result();
        if ($s->num_rows > 0) $errors[] = "Email already used.";
        $s->close();
    }

    if (empty($errors)) {
        $u = $mysqli->prepare("UPDATE Users SET UserName = ?, UserEmail = ? WHERE UserID = ?");
        $u->bind_param("sss", $username, $email, $_SESSION['user_id']);
        $u->execute();
        $u->close();
        header('Location: profile.php');
        exit;
    }
}

$page_css = 'assets/css/edit_profile.css';
require_once __DIR__ . '/inc/header.php';
?>
<h2>Edit Profile</h2>
<?php if ($errors) foreach ($errors as $e) echo "<p class='err'>".htmlspecialchars($e)."</p>"; ?>
<form method="post">
  <label>Username <input type="text" name="username" value="<?=htmlspecialchars($user['UserName'])?>"></label>
  <label>Email <input type="email" name="email" value="<?=htmlspecialchars($user['UserEmail'])?>"></label>
  <button type="submit">Save</button>
  <a href="profile.php" class="small">Back</a>
</form>
<?php require_once __DIR__ . '/inc/footer.php'; ?>