<?php
// inc/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

// Determine the base path for assets and links
$base_path = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '';

$user = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $mysqli->prepare("SELECT UserID, UserFullName, UserName, UserEmail, UserRole FROM Users WHERE UserID = ?");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $user_res = $stmt->get_result();
    $user = $user_res->fetch_assoc();
    $stmt->close();
}
// If remember cookie exists and session empty, set it
if (empty($user) && empty($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    $uid = $_COOKIE['remember_me'];
    $_SESSION['user_id'] = $uid;
    $stmt = $mysqli->prepare("SELECT UserID, UserFullName, UserName, UserEmail, UserRole FROM Users WHERE UserID = ?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $user_res = $stmt->get_result();
    $user = $user_res->fetch_assoc();
    $stmt->close();
}

$today = date('l, d F Y'); // e.g. Thursday, 04 December 2025
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>KenanginKopi</title>
  <link rel="stylesheet" href="<?=$base_path?>assets/css/base.css">
  <?php if (isset($page_css)): ?>
  <link rel="stylesheet" href="<?=$page_css?>">
  <?php endif; ?>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    .main-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background: #f5f5f5;
      border-bottom: 2px solid #333;
    }
    .brand {
      font-size: 24px;
      font-weight: bold;
      color: #6f4e37;
    }
    .brand a {
      text-decoration: none;
      color: #6f4e37;
    }
    .date {
      color: #666;
      font-size: 14px;
    }
    nav ul {
      display: flex;
      gap: 20px;
      list-style: none;
      margin: 0;
      padding: 0;
      align-items: center;
    }
    nav a, nav button {
      text-decoration: none;
      color: #333;
      font-size: 14px;
      padding: 8px 12px;
    }
    .dropdown {
      position: relative;
      display: inline-block;
    }
    .dropdown-btn {
      background: #a0826d;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }
    .dropdown-btn:hover {
      background: #8a6f5f;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: white;
      min-width: 160px;
      box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
      padding: 12px 16px;
      z-index: 1;
      border-radius: 4px;
      top: 100%;
      right: 0;
    }
    .dropdown-content a {
      color: #333;
      padding: 8px 0;
      text-decoration: none;
      display: block;
    }
    .dropdown-content a:hover {
      color: #6f4e37;
      font-weight: bold;
    }
    .dropdown:hover .dropdown-content {
      display: block;
    }
    .logout-btn {
      background: #a0826d;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
    }
    .logout-btn:hover {
      background: #8a6f5f;
    }
    .user-name {
      color: #333;
      font-size: 16px;
      font-weight: 600;
      padding: 6px 10px;
      background: rgba(160,128,109,0.08);
      border-radius: 6px;
      min-width: 120px;
      text-align: center;
      display: inline-block;
    }
  </style>
</head>
<body>
  <header class="main-header">
    <div class="brand"><a href="<?=$base_path?>index.php">KenanginKopi</a></div>
    <div class="date"><?=$today?></div>
    <nav>
      <ul>
        <?php if (!$user): ?>
          <!-- bagian untuk guest -->
          <li><a href="<?=$base_path?>login.php">Login</a></li>
        <?php elseif ($user['UserRole'] === 'Admin'): ?>

          <!--bagian admin seharunsya -->
          <li class="dropdown">
            <button class="dropdown-btn">Manage ▼</button>
            <div class="dropdown-content">
              <a href="<?=$base_path?>admin/manage_users.php">Manage User</a>
              <a href="<?=$base_path?>admin/manage_stores.php">Manage Store</a>
              <a href="<?=$base_path?>admin/manage_coffee.php">Manage Coffee</a>
            </div>
          </li>
          <li><span class="user-name"><?=htmlspecialchars($user['UserName'])?></span></li>
          <li><a href="<?=$base_path?>logout.php" class="logout-btn">Logout</a></li>
        <?php elseif ($user['UserRole'] === 'User'): ?>

          <!-- bagian users -->
          <li><a href="<?=$base_path?>cart.php">Cart</a></li>
          <li><a href="<?=$base_path?>profile.php"><?=htmlspecialchars($user['UserFullName'])?></a></li>
          <li><a href="<?=$base_path?>logout.php" class="logout-btn">Logout</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>
  <main class="container">