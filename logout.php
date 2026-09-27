<?php
session_start();
$_SESSION = [];
session_destroy();
setcookie('remember_me', '', time() - 3600, "/");
$page_css = 'assets/css/logout.css';
require_once __DIR__ . '/inc/header.php';
header('Location: index.php');
exit;