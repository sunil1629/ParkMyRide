<?php
require 'config.php';

if(!is_logged_in()){
    header('Location: signin.php');
    exit;
}

$uid=$_SESSION['user_id'];
$booking_id=intval($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);

if(!$booking_id){
    die('Booking missing');
}

$stmt=$pdo->prepare("
    SELECT b.*,p.slot_number,v.number_plate
    FROM bookings b
    JOIN parking_slots p ON p.id=b.slot_id
    LEFT JOIN vehicles v ON v.id=b.vehicle_id
    WHERE b.id=? AND b.user_id=? AND b.status='active'
    LIMIT 1
");
$stmt->execute([$booking_id,$uid]);
$booking=$stmt->fetch();

if(!$booking){
    die('Booking not found');
}

$err="";
$success=false;
$amount=0;
$hours=0;
$now=null;
$otp_message="";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action'] ?? '';

    if($action==='generate_otp'){
        $otp=(string)random_int(100000,999999);
        $otp_hash=password_hash($otp,PASSWORD_DEFAULT);
        $otp_expires_at=date('Y-m-d H:i:s',time()+300);

        $stmt=$pdo->prepare("
            UPDATE bookings
            SET otp_hash=?,otp_expires_at=?,otp_attempts=0
            WHERE id=? AND user_id=? AND status='active'
        ");
        $stmt->execute([$otp_hash,$otp_expires_at,$booking_id,$uid]);

        $otp_message=$otp;
    }

    elseif($action==='otp'){
        $otp=trim($_POST['otp'] ?? '');

        if($booking['otp_hash']==null){
            $err='Please generate OTP first.';
        }elseif($booking['otp_expires_at']<date('Y-m-d H:i:s')){
            $err='OTP expired. Please generate a new OTP.';
        }elseif($booking['otp_attempts']>=5){
            $err='Too many attempts. Please generate a new OTP.';
        }else{
            $stmt=$pdo->prepare("
                UPDATE bookings
                SET otp_attempts=otp_attempts+1
                WHERE id=?
            ");
            $stmt->execute([$booking_id]);

            if(password_verify($otp,$booking['otp_hash'])){
                $_SESSION['unpark_verified'][$booking_id]=true;

                $now=date('Y-m-d H:i:s');

                $t=$pdo->prepare("
                    SELECT TIMESTAMPDIFF(MINUTE,start_time,?) AS minutes
                    FROM bookings
                    WHERE id=?
                ");
                $t->execute([$now,$booking_id]);
                $row=$t->fetch();

                $hours=max(1,ceil(($row['minutes'] ?? 0)/60));
                $rate_per_hour=30;
                $amount=$hours*$rate_per_hour;
            }else{
                $err='Invalid OTP';
            }
        }
    }

    elseif($action==='pay'){
        if(empty($_SESSION['unpark_verified'][$booking_id])){
            $err='OTP not verified.';
        }else{
            $method=$_POST['method'] ?? 'cash';

            try{
                $pdo->beginTransaction();

                $t=$pdo->prepare("
                    SELECT *
                    FROM bookings
                    WHERE id=? AND user_id=? AND status='active'
                    FOR UPDATE
                ");
                $t->execute([$booking_id,$uid]);
                $row=$t->fetch();

                if(!$row){
                    throw new Exception('Booking already completed.');
                }

                $now=date('Y-m-d H:i:s');

                $minutes=(strtotime($now)-strtotime($row['start_time']))/60;
                $hours=max(1,ceil($minutes/60));

                $rate_per_hour=30;
                $amount=$hours*$rate_per_hour;

                $ps=$pdo->prepare("
                    INSERT INTO parking_sessions
                    (booking_id,checkin_time,checkout_time,total_hours,amount)
                    VALUES(?,?,?,?,?)
                ");
                $ps->execute([
                    $booking_id,
                    $row['start_time'],
                    $now,
                    $hours,
                    $amount
                ]);

                $session_id=$pdo->lastInsertId();

                $pay=$pdo->prepare("
                    INSERT INTO payments
                    (session_id,amount,method,status)
                    VALUES(?,?,?,'paid')
                ");
                $pay->execute([$session_id,$amount,$method]);

                $up=$pdo->prepare("
                    UPDATE bookings
                    SET end_time=?,status='completed',otp_hash=NULL,otp_expires_at=NULL,otp_attempts=0
                    WHERE id=? AND status='active'
                ");
                $up->execute([$now,$booking_id]);

                $free=$pdo->prepare("
                    UPDATE parking_slots
                    SET status='available'
                    WHERE id=?
                ");
                $free->execute([$row['slot_id']]);

                $pdo->commit();

                unset($_SESSION['unpark_verified'][$booking_id]);

                $success=true;
            }catch(Exception $e){
                if($pdo->inTransaction()){
                    $pdo->rollBack();
                }

                $err='Payment / Unpark failed.';
            }
        }
    }

    $stmt=$pdo->prepare("
        SELECT b.*,p.slot_number,v.number_plate
        FROM bookings b
        JOIN parking_slots p ON p.id=b.slot_id
        LEFT JOIN vehicles v ON v.id=b.vehicle_id
        WHERE b.id=? AND b.user_id=?
        LIMIT 1
    ");
    $stmt->execute([$booking_id,$uid]);
    $booking=$stmt->fetch();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Unpark | ParkMyRide</title>
<link rel="stylesheet" href="assets/style.css">
<style>
    body{
        margin:0;
        padding:50px;
        font-family: Arial;
        background:#0d1e39;
        height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
        color:white;
    }

    .card{
        width:380px;
        padding:25px;
        border-radius:15px;
        background: rgba(255,255,255,0.15);
        box-shadow: 0px 8px 25px rgba(0,0,0,0.25);
        backdrop-filter: blur(12px);
        text-align:center;
    }

    h2{
        margin-bottom:15px;
        font-size:28px;
        color:#48d3ebff;
    }

    .info{
        font-size:15px;
        color:#d8f7ff;
        margin-bottom:15px;
    }

    .input{
        width:100%;
        padding:12px;
        margin-top:8px;
        background:rgba(255,255,255,0.2);
        border:none;
        border-radius:8px;
        color:white;
        font-size:15px;
    }

    .input::placeholder{
        color:#ace6f5ff;
    }

    .card .btn{
        width:100%;
        padding:12px;
        margin-top:15px;
        background:#00c4ff;
        border:none;
        border-radius:8px;
        font-size:16px;
        font-weight:bold;
        color:white;
        cursor:pointer;
        transition:0.3s;
    }
    .card .btn:hover{
        background:#009bc5;
        box-shadow:0px 4px 15px rgba(0,0,0,0.3);
    }

    .notice{
        background:#ff4d4d;
        padding:10px;
        border-radius:6px;
        margin-bottom:12px;
    }

    .success-box{
        background:rgba(0,255,130,0.25);
        padding:15px;
        border-radius:10px;
        backdrop-filter:blur(10px);
        margin-bottom:15px;
        color:#c6ffe4;
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

<div class="card">

    <h2>Unpark Vehicle</h2>

    <div class="info">
        <b>Booking #<?= $booking['id'] ?></b><br>
        Slot: <?= htmlspecialchars($booking['slot_number']) ?><br>
        Started at: <?= htmlspecialchars($booking['start_time']) ?>
    </div>

   <?php if(!$success): ?>

<?php if($otp_message): ?>
    <div class="success-box">
        Demo OTP: <b><?=htmlspecialchars($otp_message)?></b><br>
        Valid for 5 minutes.
    </div>
<?php endif; ?>

<?php if($err): ?>
    <div class="notice"><?=htmlspecialchars($err)?></div>
<?php endif; ?>

<?php if(empty($_SESSION['unpark_verified'][$booking_id])): ?>

    <form method="post">
        <input type="hidden" name="booking_id" value="<?=htmlspecialchars($booking_id)?>">
        <input type="hidden" name="action" value="generate_otp">
        <button class="btn" type="submit">Generate OTP</button>
    </form>

    <form method="post">
        <input type="hidden" name="booking_id" value="<?=htmlspecialchars($booking_id)?>">
        <input type="hidden" name="action" value="otp">

        <input type="text" name="otp" class="input" placeholder="Enter OTP" maxlength="6" required>

        <button class="btn" type="submit">Verify OTP</button>
    </form>

<?php else: ?>

    <div class="info">
        <strong>Hours:</strong> <?=$hours?><br>
        <strong>Amount:</strong> ₹<?=$amount?>
    </div>

    <form method="post">
        <input type="hidden" name="booking_id" value="<?=htmlspecialchars($booking_id)?>">
        <input type="hidden" name="action" value="pay">

        <select name="method" class="input" required>
            <option value="card">Card</option>
            <option value="upi">UPI</option>
            <option value="cash">Cash</option>
        </select>

        <button class="btn" type="submit">Pay ₹<?=$amount?> & Unpark</button>
    </form>

<?php endif; ?>

<?php endif; ?>

   <?php if($success): ?>

<div class="success-box">
    Vehicle successfully unparked!<br><br>
    <b>End Time:</b> <?=htmlspecialchars($now)?><br>
    <b>Hours:</b> <?=$hours?><br>
    <b>Amount:</b> ₹<?=$amount?>
</div>

<a class="btn" href="mybookings.php?paid=1">Back to My Bookings</a>

<?php endif; ?>

</div>

</body>
</html>
