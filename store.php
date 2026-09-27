<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/inc/db.php';

$store_id = trim($_GET['id'] ?? '');
if (empty($store_id)) {
    echo "<p class='err'>Store not found.</p>";
    require_once __DIR__ . '/inc/footer.php';
    exit;
}
$stmt = $mysqli->prepare("
  SELECT s.StoreID as store_id, s.StoreName as store_name, s.StoreLocation,
         c.CoffeeID as coffee_id, c.CoffeeName as coffee_name, sc.Price, c.CoffeeDesc
  FROM store s
  LEFT JOIN storecoffee sc ON sc.StoreID = s.StoreID
  LEFT JOIN Coffee c ON c.CoffeeID = sc.CoffeeID
  WHERE s.StoreID = ?
");
$stmt->bind_param("s", $store_id);
$stmt->execute();
$res = $stmt->get_result();

$store = null;
$coffees = [];
while ($r = $res->fetch_assoc()) {
    if (!$store) {
        $store = ['id'=>$r['store_id'],'name'=>$r['store_name'],'location'=>$r['StoreLocation']];
    }
    if ($r['coffee_id']) {
        $coffees[] = [
          'id'=>$r['coffee_id'],
          'name'=>$r['coffee_name'],
          'price'=>$r['Price'],
          'description'=>$r['CoffeeDesc']
        ];
    }
}
$stmt->close();

if (!$store) {
    echo "<p class='err'>Store not found.</p>";
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

$page_css = 'assets/css/store.css';
require_once __DIR__ . '/inc/header.php';
?>
<h2><?=htmlspecialchars($store['name'])?> — <?=htmlspecialchars($store['location'])?></h2>
<ul>
<?php if (empty($coffees)): ?>
  <li>No coffees available in this store.</li>
<?php endif; ?>
<?php foreach ($coffees as $c): ?>
  <li class="store-card" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
      <strong><?=htmlspecialchars($c['name'])?></strong>
      <div class="small"><?=htmlspecialchars($c['description'])?></div>
      <div class="small">Rp <?=number_format($c['price'])?></div>
    </div>
    <div>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <form method="post" action="cart.php">
        <input type="hidden" name="coffee_id" value="<?=$c['id']?>">
        <input type="hidden" name="store_id" value="<?=$store['id']?>">
        <button type="submit" name="action" value="add" class="btn">Add to Cart</button>
      </form>
    <?php else: ?>
      <small class="small">Login to add to cart</small>
    <?php endif; ?>
    </div>
  </li>
<?php endforeach; ?>
</ul>
<a href="index.php" class="small">Back to stores</a>
<?php require_once __DIR__ . '/inc/footer.php'; ?>