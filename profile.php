<?php
require_once __DIR__ . '/inc/db.php';
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$stmt = $mysqli->prepare("SELECT UserID, UserFullName, UserName, UserEmail, UserRole FROM Users WHERE UserID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$page_css = 'assets/css/profile.css';
require_once __DIR__ . '/inc/header.php';
?>
<h2>Profile</h2>
<p>ID: <?=$user['UserID']?></p>
<p>Full Name: <?=htmlspecialchars($user['UserFullName'])?></p>
<p>Username: <?=htmlspecialchars($user['UserName'])?></p>
<p>Email: <?=htmlspecialchars($user['UserEmail'])?></p>
<p>Role: <?=htmlspecialchars($user['UserRole'])?></p>
<a href="edit_profile.php" class="btn">Edit Profile</a>
<a href="history.php" class="btn" style="background:#444">Order History</a>
<?php require_once __DIR__ . '/inc/footer.php'; ?>