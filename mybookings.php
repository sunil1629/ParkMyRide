<?php
require 'config.php';
if(!is_logged_in()){ header('Location: signin.php'); exit; }
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        p.slot_number,
        v.number_plate
    FROM bookings b
    JOIN parking_slots p ON p.id = b.slot_id
    LEFT JOIN vehicles v ON v.id = b.vehicle_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$uid]);
$bookings = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>My Bookings</title>
<link rel="stylesheet" href="assets/style.css">

<style>
    body{
        margin:0;
        padding-top:100px;
        font-family: Arial;
        background:#0d1e39;
        min-height:100vh;
        display:flex;
        flex-direction:column;
        align-items:center;
    }

    h3{
        margin-top:25px;
        color:#48d3ebff;
        font-size:30px;
        letter-spacing:1px;
    }

    .notice{
        background:rgba(0,255,130,0.25);
        color:#baffd8;
        padding:10px 20px;
        border-radius:8px;
        margin-top:10px;
        backdrop-filter:blur(6px);
    }

    .grid{
        width:90%;
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));
        gap:20px;
        margin-top:30px;
    }

    .card{
        background:rgba(255,255,255,0.15);
        backdrop-filter:blur(10px);
        padding:20px;
        border-radius:15px;
        color:white;
        box-shadow:0 8px 20px rgba(0,0,0,0.25);
        transition:0.3s;
    }

    .card:hover{
        transform:translateY(-5px);
        box-shadow:0 12px 25px rgba(0,0,0,0.35);
    }

    .slot-title{
        font-size:20px;
        font-weight:bold;
        margin-bottom:5px;
    }

    .info{
        font-size:15px;
        margin-bottom:10px;
        color:#d8f7ff;
    }

    .grid .btn{
        padding:10px 15px;
        background:#00c4ff;
        border:none;
        border-radius:8px;
        font-size:15px;
        font-weight:bold;
        color:white;
        cursor:pointer;
        text-decoration:none;
        transition:0.3s;
        display:inline-block;
        margin-top:8px;
    }

    .grid .btn:hover{
        background:#009bc5;
        box-shadow:0px 4px 15px rgba(0,0,0,0.35);
    }

    .completed{
        color:#9be8ff;
        font-size:14px;
        background:rgba(255,255,255,0.18);
        padding:7px 12px;
        border-radius:8px;
        display:inline-block;
        margin-top:10px;
    }

</style>
</head>
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

<body>

<h3>My Bookings</h3>

<?php if(!empty($_GET['paid'])): ?>
    <div class="notice">Payment received and booking completed.</div>
<?php endif; ?>

<div class="grid">

<?php foreach($bookings as $b): ?>
    <div class="card">

        <div class="slot-title">
            Booking #<?= $b['id'] ?> — Slot <?= htmlspecialchars($b['slot_number']) ?>
        </div>

        <div class="info">
            From: <?= htmlspecialchars($b['start_time']) ?><br>
        </div>

        <div class="info">
    <b>Vehicle:</b> 
    <?= $b['number_plate'] ? htmlspecialchars($b['number_plate']) : 'Not Available' ?>
</div>


        <?php if($b['status']==='active'): ?>
            <a class="btn" href="unpark.php?booking_id=<?= $b['id'] ?>">Unpark</a>
        <?php else: ?>
            <div class="completed">Completed</div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</div>

</body>
</html>
