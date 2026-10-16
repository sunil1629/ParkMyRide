<?php
require 'config.php';

if(!is_logged_in()){
    header('Location: signin.php');
    exit;
}

$uid=$_SESSION['user_id'];

$stmt=$pdo->query("SELECT * FROM parking_slots ORDER BY id");
$slots=$stmt->fetchAll();

$available=false;

foreach($slots as $s){
    if($s['status']=='available'){
        $available=true;
        break;
    }
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Dashboard</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
<body>
<header class="header">
    <div class="container">
        <div class="logo">
           <a href="index.php"><img src="images/image.jpg" style="height:80px"></a>
        </div>
  <div class="link-container">
    <span class="small">Hello, <?=htmlspecialchars($_SESSION['username'])?></span>
    <a class="btn" href="mybookings.php">My Bookings</a>
    <a class="btn" href="logout.php">Logout</a>
  </div>
</div>
</header>
<main class="dashboard-main">
    <h3>Available Slots</h3>
<div class="Available-slots">

  <div class="slot-container">  
  <?php foreach($slots as $s): ?>
  
    <div class="slot-box">       
      <div class="slot-info">
        <strong>Slot <?=htmlspecialchars($s['slot_number'])?></strong>
        <div class="small"><?=htmlspecialchars($s['slot_type'])?></div>
      </div>

   <div class="slot-actions">
        <?php if($s['status']=='available'): ?>
           <a class="btn" href="book.php?slot_id=<?= $s['id'] ?>">Book</a>
        <?php endif; ?>
      </div>
    
      <div class="slot-actions">
      <span class="badge <?= $s['status']=='available'?'available':'occupied' ?>">
          <?= htmlspecialchars($s['status']) ?>
      </span>
      </div>
    </div>
    <?php if($available): ?>
    <!-- <a class="btn" href="book.php">Book Parking</a> -->
    <?php else: ?>
        <p>No parking slots available.</p>
    <?php endif; ?>
  <?php endforeach; ?>
  </div>
</div>
</main>
</body>
</html>
