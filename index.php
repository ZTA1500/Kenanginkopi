<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/inc/db.php';

$res = $mysqli->query("SELECT StoreID, StoreName, StoreLocation FROM store ORDER BY StoreID ASC");
$db_error = '';
if ($res === false) {
  $db_error = $mysqli->error;
  $stores_count = 0;
} else {
  $stores_count = $res->num_rows;
}


$page_css = 'assets/css/index.css';
require_once __DIR__ . '/inc/header.php';
?>

<div class="store-wrapper">
<h1>KenanginKopi</h1>
<h3>Favourable taste for your mood</h3>
  <div class="stores">
  <?php if ($stores_count <= 0): ?>
    <p class="no-data">No stores available. <?php if (!empty($db_error)) echo '(DB error: '.htmlspecialchars($db_error).')'; ?></p>
  <?php else: ?>
  <?php while ($row = $res->fetch_assoc()): ?>
    <div class="store-card">
      <div>
        <h3><?=htmlspecialchars($row['StoreName'])?></h3>
        <p class="small"><?=htmlspecialchars($row['StoreLocation'])?></p>
      </div>
      <div>
        <a class="btn" href="store.php?id=<?=$row['StoreID']?>">View Store</a>
      </div>
    </div>
  <?php endwhile; ?>
  <?php endif; ?>
  </div>
</div>  
<?php require_once __DIR__ . '/inc/footer.php'; ?>