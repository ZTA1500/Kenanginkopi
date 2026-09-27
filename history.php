<?php
require_once __DIR__ . '/inc/db.php';
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$stmt = $mysqli->prepare("
  SELECT t.TransactionID, t.TransactionDate, t.TotalPrice, s.StoreName, s.StoreLocation
  FROM transactions t
  LEFT JOIN Store s ON s.StoreID = t.StoreID
  WHERE t.UserID = ?
  ORDER BY t.TransactionDate DESC
");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$transactions = $stmt->get_result();
$stmt->close();

$page_css = 'assets/css/history.css';
require_once __DIR__ . '/inc/header.php';
?>
<h2>Order History</h2>
<?php if ($transactions->num_rows == 0): ?>
  <p>No transactions yet.</p>
<?php else: ?>
  <?php while ($t = $transactions->fetch_assoc()): ?>
    <div class="trans">
      <p><strong>ID:</strong> <?=$t['TransactionID']?> — <strong>Date:</strong> <?=$t['TransactionDate']?> — <strong>Store:</strong> <?=htmlspecialchars($t['StoreName'])?></p>
      <?php
      $stmt2 = $mysqli->prepare("SELECT td.Qty, td.Subtotal, c.CoffeeName FROM transactiondetails td JOIN coffee c ON c.CoffeeID = td.CoffeeID WHERE td.TransactionID = ?");
      $stmt2->bind_param("s", $t['TransactionID']); $stmt2->execute();
      $details = $stmt2->get_result();
      ?>
      <ul>
      <?php while ($d = $details->fetch_assoc()): ?>
        <li><?=htmlspecialchars($d['CoffeeName'])?> — Qty <?=$d['Qty']?> — Subtotal Rp <?=number_format($d['Subtotal'])?></li>
      <?php endwhile; ?>
      </ul>
      <p><strong>Total:</strong> Rp <?=number_format($t['TotalPrice'])?></p>
    </div>
  <?php endwhile; ?>
<?php endif; ?>
<a href="profile.php" class="small">Back</a>
<?php require_once __DIR__ . '/inc/footer.php'; ?>