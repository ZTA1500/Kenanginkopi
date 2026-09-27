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

$errors = [];
$name = $price = $description = '';
$preselect_store = trim($_GET['store'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $store_id = $_POST['store_id'] ?? '';

    if ($name === '' || !preg_match("/^[a-zA-Z ]+$/", $name)) $errors[] = "Name must be alphabetic (letters and spaces).";
    if ($price < 10000 || $price > 100000) $errors[] = "Price must be between 10,000 and 100,000.";
    if (str_word_count($description) < 3) $errors[] = "Description must be at least 3 words.";
    if (empty($store_id)) $errors[] = "Please select a store.";

    if (empty($errors)) {
        // buat id kopi baru (pls jalan)
        $res = $mysqli->query("SELECT MAX(CAST(SUBSTR(CoffeeID, 2) AS UNSIGNED)) as max_id FROM coffee");
        $row = $res->fetch_assoc();
        $next_id = ($row['max_id'] ?? 0) + 1;
        $coffee_id = 'C' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
        
        $ins = $mysqli->prepare("INSERT INTO Coffee (CoffeeID, CoffeeName, CoffeeDesc) VALUES (?,?,?)");
        $ins->bind_param("sss", $coffee_id, $name, $description);
        $ins->execute();
        $ins->close();

        $sc = $mysqli->prepare("INSERT INTO storecoffee (StoreID, CoffeeID, Price) VALUES (?,?,?)");
        $sc->bind_param("ssd", $store_id, $coffee_id, $price);
        $sc->execute();
        $sc->close();

        header('Location: manage_coffee.php?store=' . urlencode($store_id)); 
        exit;
    }
}

$stores = $mysqli->query("SELECT StoreID, StoreName FROM store ORDER BY StoreName ASC");
require_once __DIR__ . '/../inc/header.php';
?>
<style>
  main {
     max-width: 600px;
      margin: 30px auto; 
      padding: 20px; 
    }

  h2 { 
    color: #6f4e37; 
    margin-bottom: 20px;
   }
  form { 
    background: #f5f5f5; 
    padding: 20px; 
    border-radius: 8px;
   }
  label { 
    display: block; 
    margin-bottom: 15px; 
    font-weight: 500;
   }

  input[type="text"], input[type="number"], textarea, select { 
    width: 100%; 
    padding: 8px; 
    border: 1px solid #ddd; 
    border-radius: 4px; 
    font-size: 14px; 
    box-sizing: border-box; 
  }
  textarea { 
    resize: vertical; 
  }
  button { 
    background: #6f4e37;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer; 
    font-size: 14px;
   }
  button:hover { 
    background: #5a3f2b; 
  }
  a.small { 
    display: inline-block; 
    margin-top: 15px; 
    color: #6f4e37; 
    text-decoration: none;
   }
  a.small:hover { 
    text-decoration: underline;
   }
  p.err { 
    color: #d32f2f;
    margin-bottom: 10px; 
    padding: 10px;
    background: #ffebee;
    border-radius: 4px; 
    }
</style>
<h2>Add Coffee</h2>
<?php if ($errors) foreach ($errors as $e) echo "<p class='err'>".htmlspecialchars($e)."</p>"; ?>
<form method="post">
  <label>Name: <input type="text" name="name" value="<?=htmlspecialchars($name)?>" required></label>
  <label>Price (Rp): <input type="number" name="price" value="<?=htmlspecialchars($price)?>" required></label>
  <label>Description: <textarea name="description" rows="3" required><?=htmlspecialchars($description)?></textarea></label>
  <label>Store:
    <select name="store_id" required>
      <option value="">-- Select store --</option>
      <?php while ($s = $stores->fetch_assoc()): ?>
        <option value="<?=$s['StoreID']?>" <?=($s['StoreID'] === $preselect_store) ? 'selected' : ''?>><?=htmlspecialchars($s['StoreName'])?></option>
      <?php endwhile; ?>
    </select>
  </label>
  <button type="submit">Add Coffee</button>
  <a href="manage_coffee.php" class="small">Back</a>
</form>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>