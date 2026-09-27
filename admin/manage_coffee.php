<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../inc/db.php';
if (empty($_SESSION['user_id'])) header('Location: ../login.php');

$stmt = $mysqli->prepare("SELECT UserRole FROM Users WHERE UserID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($r['UserRole'] !== 'Admin') { die('Access denied'); }


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_coffee'])) {
  $coffee_id = trim($_POST['coffee_id']);
  $store_id = trim($_POST['store_id'] ?? '');
  if ($store_id !== '') {
    $d = $mysqli->prepare("DELETE FROM storecoffee WHERE StoreID = ? AND CoffeeID = ?");
    $d->bind_param("ss", $store_id, $coffee_id);
    $d->execute();
    $d->close();
    header('Location: manage_coffee.php?store=' . urlencode($store_id)); exit;
  } else {
    $d = $mysqli->prepare("DELETE FROM coffee WHERE CoffeeID = ?");
    $d->bind_param("s", $coffee_id);
    $d->execute();
    $d->close();
    header('Location: manage_coffee.php'); exit;
  }
}


$store_filter = trim($_GET['store'] ?? '');
if ($store_filter !== '') {

  $stmt = $mysqli->prepare("SELECT StoreName FROM store WHERE StoreID = ?");
  $stmt->bind_param('s', $store_filter);
  $stmt->execute();
  $sr = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$sr) {
    require_once __DIR__ . '/../inc/header.php';
    echo "<p class='err'>Store not found.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
  }
  $store_name = $sr['StoreName'];
  $res = $mysqli->prepare("SELECT c.CoffeeID, c.CoffeeName, c.CoffeeDesc, sc.Price FROM coffee c JOIN storecoffee sc ON sc.CoffeeID = c.CoffeeID WHERE sc.StoreID = ? ORDER BY c.CoffeeID ASC");
  $res->bind_param('s', $store_filter);
  $res->execute();
  $result = $res->get_result();
  require_once __DIR__ . '/../inc/header.php';
} else {
  $result = $mysqli->query("SELECT c.CoffeeID, c.CoffeeName, c.CoffeeDesc FROM coffee c ORDER BY c.CoffeeID ASC");
  require_once __DIR__ . '/../inc/header.php';
}
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
<?php if (!empty($store_filter)): ?>
  <h2>Manage Coffee — <?=htmlspecialchars($store_name)?> (<?=htmlspecialchars($store_filter)?>)</h2>
  <a href="manage_stores.php" class="btn">Back to Stores</a>
  <a href="add_coffee.php?store=<?=urlencode($store_filter)?>" class="btn">Add Coffee to Store</a>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Action</th></tr></thead>
    <tbody>
    <?php while ($c = $result->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($c['CoffeeID'])?></td>
        <td><?=htmlspecialchars($c['CoffeeName'])?></td>
        <td><?=htmlspecialchars($c['CoffeeDesc'])?></td>
        <td>Rp <?=number_format($c['Price'] ?? 0)?></td>
        <td>
          <form method="post" onsubmit="return confirm('Remove this coffee from store?');">
            <input type="hidden" name="coffee_id" value="<?=htmlspecialchars($c['CoffeeID'])?>">
            <input type="hidden" name="store_id" value="<?=htmlspecialchars($store_filter)?>">
            <button type="submit" name="delete_coffee" class="btn btn-delete">Remove</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
<?php else: ?>
  <h2>Manage Coffee</h2>
  <a href="add_coffee.php" class="btn">Add Coffee</a>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Action</th></tr></thead>
    <tbody>
    <?php while ($c = $result->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($c['CoffeeID'])?></td>
        <td><?=htmlspecialchars($c['CoffeeName'])?></td>
        <td><?=htmlspecialchars($c['CoffeeDesc'])?></td>
        <td>
          <form method="post" onsubmit="return confirm('Delete this coffee?');">
            <input type="hidden" name="coffee_id" value="<?=htmlspecialchars($c['CoffeeID'])?>">
            <button type="submit" name="delete_coffee" class="btn btn-delete">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>