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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    if ($name === '' || str_word_count($name) < 1) $errors[] = "Name is required.";
    $valid_locations = ['Jakarta','Bandung','Surabaya','Bali','Medan'];
    if (!in_array($location, $valid_locations)) $errors[] = "Invalid location chosen.";
    if (empty($errors)) {
        // buat masukin store id 
        $res = $mysqli->query("SELECT MAX(CAST(SUBSTR(StoreID, 2) AS UNSIGNED)) as max_id FROM store");
        $row = $res->fetch_assoc();
        $next_id = ($row['max_id'] ?? 0) + 1;
        $store_id = 'S' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
        
        $insert = $mysqli->prepare("INSERT INTO store (StoreID, StoreName, StoreLocation) VALUES (?,?,?)");
        if ($insert === false) {
            $errors[] = "Database error: " . $mysqli->error;
        } else {
            $insert->bind_param("sss", $store_id, $name, $location);
            if (!$insert->execute()) {
                $errors[] = "Failed to add store: " . $insert->error;
            } else {
                $insert->close();
                header('Location: manage_stores.php'); exit;
            }
        }
    }
}
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
  input[type="text"], select { 
    width: 100%; 
    padding: 8px; 
    border: 1px solid #ddd; 
    border-radius: 4px; 
    font-size: 14px; 
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
<h2>Add Store</h2>
<?php if ($errors) foreach ($errors as $e) echo "<p class='err'>".htmlspecialchars($e)."</p>"; ?>
<form method="post">
  <label>Name: <input type="text" name="name" required></label>
  <label>Location:
    <select name="location" required>
      <option value="">-- Select location --</option>
      <option>Jakarta</option>
      <option>Bandung</option>
      <option>Surabaya</option>
      <option>Bali</option>
      <option>Medan</option>
    </select>
  </label>
  <button type="submit">Add Store</button>
  <a href="manage_stores.php" class="small">Back</a>
</form>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>