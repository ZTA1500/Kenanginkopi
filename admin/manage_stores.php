<?php
require_once __DIR__ . '/../inc/db.php';
session_start();
if (empty($_SESSION['user_id'])) header('Location: ../login.php');

$stmt = $mysqli->prepare("SELECT UserRole FROM Users WHERE UserID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($r['UserRole'] !== 'Admin') { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_store'])) {
    $del = $_POST['store_id'];
    $d = $mysqli->prepare("DELETE FROM store WHERE StoreID = ?");
    $d->bind_param("s", $del);
    $d->execute();
    $d->close();
    header('Location: manage_stores.php'); exit;
}

$stmt = $mysqli->prepare("SELECT StoreID, StoreName, StoreLocation FROM store ORDER BY StoreID ASC");
$stmt->execute();
$res = $stmt->get_result();
$page_css = '../assets/css/manage_stores.css';
require_once __DIR__ . '/../inc/header.php';
?>
<main class="admin-container">
<h2>Manage Store</h2>
<a href="add_store.php" class="btn btn-add">Add Store</a>
<table class="admin-table">
  <thead>
    <tr>
      <th>Store ID</th>
      <th>Store Name</th>
      <th>Location</th>
      <th>Coffee</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php while ($s = $res->fetch_assoc()): ?>
    <tr>
      <td><?=htmlspecialchars($s['StoreID'])?></td>
      <td><?=htmlspecialchars($s['StoreName'])?></td>
      <td><?=htmlspecialchars($s['StoreLocation'])?></td>
      <td><a href="manage_coffee.php?store=<?=urlencode($s['StoreID'])?>" class="btn btn-manage">Manage</a></td>
      <td>
        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this store?');">
          <input type="hidden" name="store_id" value="<?=htmlspecialchars($s['StoreID'])?>">
          <button type="submit" name="delete_store" class="btn btn-delete">Delete</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</main>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>