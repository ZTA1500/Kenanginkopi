<?php
require_once __DIR__ . '/inc/db.php';
session_start();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'add') {
    $coffee_id = trim($_POST['coffee_id'] ?? '');
    $store_id = trim($_POST['store_id'] ?? '');
    if ($coffee_id === '' || $store_id === '') {
      $_SESSION['msg'] = "Invalid coffee or store.";
      header('Location: cart.php'); exit;
    }
    $pstmt = $mysqli->prepare("SELECT Price FROM storecoffee WHERE StoreID = ? AND CoffeeID = ?");
    $pstmt->bind_param('ss', $store_id, $coffee_id);
    $pstmt->execute();
    $prow = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    if (!$prow) {
      $_SESSION['msg'] = "This coffee is not available at the selected store.";
      header('Location: cart.php'); exit;
    }
    $price = floatval($prow['Price']);
    // create a unique cart key so each add creates a separate line item
    $key = $store_id . '|' . $coffee_id . '|' . uniqid();
    $_SESSION['cart'][$key] = ['store_id'=>$store_id, 'coffee_id'=>$coffee_id, 'qty'=>1, 'price'=>$price];
    header('Location: cart.php');
    exit;
  } elseif ($action === 'update') {
    $cart_key = trim($_POST['cart_key'] ?? '');
    $new_qty = intval($_POST['new_qty'] ?? 0);
    if ($cart_key !== '' && isset($_SESSION['cart'][$cart_key])) {
      if ($new_qty > 0) {
        $_SESSION['cart'][$cart_key]['qty'] = $new_qty;
      } else {
        unset($_SESSION['cart'][$cart_key]);
      }
    }
    header('Location: cart.php');
    exit;
  } elseif ($action === 'delete') {
    $key = trim($_POST['cart_key'] ?? '');
    if ($key !== '' && isset($_SESSION['cart'][$key])) unset($_SESSION['cart'][$key]);
    header('Location: cart.php');
    exit;
  } elseif ($action === 'pay') {
    if (empty($_SESSION['user_id'])) {
      $_SESSION['msg'] = "Please login to pay.";
      header('Location: login.php');
      exit;
    }
    if (empty($_SESSION['cart'])) {
      $_SESSION['msg'] = "Cart is empty.";
      header('Location: cart.php');
      exit;
    }

    // Calculate total price and get first store ID
    $total_price = 0;
    $first_store_id = null;
    foreach ($_SESSION['cart'] as $item) {
      $total_price += $item['price'] * $item['qty'];
      if ($first_store_id === null) {
        $first_store_id = $item['store_id'];
      }
    }
    
    // Create ONE transaction for all items
    $r = $mysqli->query("SELECT MAX(CAST(SUBSTR(TransactionID,2) AS UNSIGNED)) as max_id FROM transactions");
    $row = $r->fetch_assoc();
    $next_id = ($row['max_id'] ?? 0) + 1;
    $trans_id = 'T' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
    
    $stmt = $mysqli->prepare("INSERT INTO transactions (TransactionID, UserID, StoreID, TransactionDate, TotalPrice) VALUES (?,?,?,CURDATE(),?)");
    $stmt->bind_param('sssd', $trans_id, $_SESSION['user_id'], $first_store_id, $total_price);
    if (!$stmt->execute()) {
      $_SESSION['msg'] = 'Payment failed: ' . $stmt->error;
      $stmt->close();
      header('Location: cart.php'); 
      exit;
    }
    $stmt->close();
    
    // Insert all transaction details
    $stmt2 = $mysqli->prepare("INSERT INTO transactiondetails (TransactionID, CoffeeID, Qty, Subtotal) VALUES (?,?,?,?)");
    foreach ($_SESSION['cart'] as $item) {
      $coffee_id = $item['coffee_id'];
      $qty = $item['qty'];
      $subtotal = $item['price'] * $qty;
      $stmt2->bind_param('ssid', $trans_id, $coffee_id, $qty, $subtotal);
      $stmt2->execute();
    }
    $stmt2->close();
    
    $_SESSION['cart'] = [];
    $_SESSION['msg'] = "Payment successful. Transaction ID: " . $trans_id;
    header('Location: cart.php');
    exit;
  }
}

$page_css = 'assets/css/cart.css';
require_once __DIR__ . '/inc/header.php';
?>
<h2>Shopping Cart</h2>
<?php if (!empty($_SESSION['msg'])) { echo "<p class='msg'>".htmlspecialchars($_SESSION['msg'])."</p>"; unset($_SESSION['msg']); } ?>
<?php if (empty($_SESSION['cart'])): ?>
  <p>Your cart is empty.</p>
<?php else: ?>
  <p><?=count($_SESSION['cart']).' item(s) - Total: Rp '.number_format(array_reduce($_SESSION['cart'], function($s, $i) { return $s + ($i['price'] * $i['qty']); }, 0))?></p>
  <table>
    <thead><tr><th>Store ID</th><th>Coffee</th><th>Description</th><th>Price</th><th>Qty</th><th>Subtotal</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $total = 0;
    foreach ($_SESSION['cart'] as $key => $item):
        $coffee_id = $item['coffee_id'];
        $store_id = $item['store_id'];
        $qty = $item['qty'];
        $price = $item['price'];
        $stmt = $mysqli->prepare("SELECT CoffeeName, CoffeeDesc FROM coffee WHERE CoffeeID = ?");
        $stmt->bind_param('s', $coffee_id);
        $stmt->execute();
        $c = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$c) continue;
        $sub = $price * $qty;
        $total += $sub;
    ?>
    <tr>
      <td><?=htmlspecialchars($store_id)?></td>
      <td><?=htmlspecialchars($c['CoffeeName'])?></td>
      <td><?=htmlspecialchars($c['CoffeeDesc'])?></td>
      <td>Rp <?=number_format($price)?></td>
      <td>
        <form method="post" style="display:inline; display:flex; gap:6px; align-items:center;">
          <input type="hidden" name="cart_key" value="<?=$key?>">
          <input type="number" name="new_qty" value="<?=$qty?>" min="1" style="width:50px;">
          <button type="submit" name="action" value="update" class="btn-update">Update</button>
        </form>
      </td>
      <td>Rp <?=number_format($sub)?></td>
      <td>
        <form method="post" style="display:inline;">
          <input type="hidden" name="cart_key" value="<?=$key?>">
          <button type="submit" name="action" value="delete" class="btn-delete">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p class="total">Total: Rp <?=number_format($total)?></p>
  <div style="margin-top:16px; text-align:center;">
    <form method="post" style="display:inline;">
      <button type="submit" name="action" value="pay" class="btn-pay">Pay</button>
    </form>
  </div>
<?php endif; ?>
<?php require_once __DIR__ . '/inc/footer.php'; ?>