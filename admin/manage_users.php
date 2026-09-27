<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../inc/db.php';
if (empty($_SESSION['user_id'])) header('Location: ../login.php');

$stmt = $mysqli->prepare("SELECT UserRole FROM users WHERE UserID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($r['UserRole'] !== 'Admin') { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $del = trim($_POST['user_id']);
    $d = $mysqli->prepare("DELETE FROM users WHERE UserID = ?");
    $d->bind_param("s", $del);
    $d->execute();
    $d->close();
    header('Location: manage_users.php'); exit;
}

$stmt = $mysqli->prepare("SELECT UserID, UserFullName, UserName, UserEmail, UserRole FROM users ORDER BY UserID ASC");
$stmt->execute();
$res = $stmt->get_result();
require_once __DIR__ . '/../inc/header.php';
?>
<style>
  main { 
    max-width: 1000px; 
    margin: 20px auto; 
    padding: 20px; 
  }
  h2 { 
    color: #6f4e37; 
    margin-bottom: 20px; 
  }
  a.btn { 
    display: inline-block; 
    padding: 10px 20px; 
    background: #6f4e37; 
    color: white; 
    text-decoration: none; 
    border-radius: 4px; 
    margin-bottom: 15px; 
    border: none; 
    cursor: pointer; 
    font-size: 14px; 
  }
  a.btn:hover, button.btn:hover { 
    background: #5a3f2b; 
  }
  table { 
    width: 100%; 
    border-collapse: collapse; 
    background: white; 
  }
  th {
    background: #6f4e37;
    color: white; 
    padding: 12px; 
    text-align: left; 
  }
  td { 
    padding: 12px; 
    border-bottom: 1px solid #ddd; 
  }
  tr:hover { 
    background: #f5f5f5; 
  }
  button.btn-delete { 
    background: #d32f2f; 
    padding: 8px 12px; 
    color: white; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer;
   }
  button.btn-delete:hover {
     background: #b71c1c;
     }
  form { 
    display: inline; 
    }
</style>
<h2>Manage User</h2>
<table>
  <thead>
    <tr>
      <th>UserID</th>
      <th>Full Name</th>
      <th>Username</th>
      <th>Email</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php while ($u = $res->fetch_assoc()): ?>
    <tr>
      <td><?=htmlspecialchars($u['UserID'])?></td>
      <td><?=htmlspecialchars($u['UserFullName'])?></td>
      <td><?=htmlspecialchars($u['UserName'])?></td>
      <td><?=htmlspecialchars($u['UserEmail'])?></td>
      <td>
        <?php if ($u['UserID'] != $_SESSION['user_id']): ?>
          <form method="post" onsubmit="return confirm('Delete this user?');">
            <input type="hidden" name="user_id" value="<?=htmlspecialchars($u['UserID'])?>">
            <button type="submit" name="delete_user" class="btn btn-delete">Delete</button>
          </form>
        <?php else: ?>
          <span style="color:#999;">-</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>