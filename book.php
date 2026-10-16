<?php
require 'config.php';

if(!is_logged_in()){
    header("Location: signin.php");
    exit;
}

$user_id=$_SESSION["user_id"];
$error="";

if(isset($_POST["book_now"])){
    $number_plate=trim($_POST["number_plate"]);
    $type=trim($_POST["type"]);

    if($number_plate=="" || $type==""){
        $error="Please fill all fields!";
    }else{
        $slot_type="";

        if($type=="bike"){
            $slot_type="two-wheeler";
        }elseif($type=="compact"){
            $slot_type="compact";
        }elseif($type=="suv"){
            $slot_type="suv";
        }

        try{
            $pdo->beginTransaction();

            $stmt=$pdo->prepare("SELECT * FROM parking_slots WHERE slot_type=? AND status='available' ORDER BY id LIMIT 1 FOR UPDATE");
            $stmt->execute([$slot_type]);
            $slot=$stmt->fetch();

            if(!$slot){
                $pdo->rollBack();
                $error="No suitable parking slot available!";
            }else{
                $v=$pdo->prepare("INSERT INTO vehicles(user_id,number_plate,type) VALUES(?,?,?)");
                $v->execute([$user_id,$number_plate,$type]);
                $vehicle_id=$pdo->lastInsertId();

                $start_time=date("Y-m-d H:i:s");

                $b=$pdo->prepare("INSERT INTO bookings(user_id,slot_id,vehicle_id,start_time,status) VALUES(?,?,?,?, 'active')");
                $b->execute([$user_id,$slot["id"],$vehicle_id,$start_time]);

                $up=$pdo->prepare("UPDATE parking_slots SET status='occupied' WHERE id=? AND status='available'");
                $up->execute([$slot["id"]]);

                if($up->rowCount()!=1){
                    throw new Exception("Slot allocation failed");
                }

                $pdo->commit();

                header("Location: dashboard.php");
                exit;
            }
        }catch(Exception $e){
            if($pdo->inTransaction()){
                $pdo->rollBack();
            }

            $error="Booking failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Confirm Booking</title>
<link rel="stylesheet" href="assets/style.css">

<style>
    body{
        margin:0;
        padding-top:100px;
        font-family: Arial;
        background:#0d1e39;
        height:100vh;
        display:flex;
        flex-direction:column;
        align-items:center;
    }

    h1{
        padding:15px;
        color:#48d3ebff;
        margin-top:20px;
    }

    .login-box{
        width:380px;
        padding:30px;
        border-radius:15px;
        background: rgba(255,255,255,0.15);
        box-shadow: 0px 8px 25px rgba(0,0,0,0.25);
        backdrop-filter: blur(12px);
        text-align:center;
        color:white;
        margin-top:10px;
    }

    h2{
        margin:0 0 15px;
        font-size:28px;
        letter-spacing:1px;
    }

    .input-field{
        width:100%;
        padding:13px 0px 13px 6px;
        margin:10px 0;
        border:none;
        outline:none;
        border-radius:8px;
        background: rgba(255,255,255,0.2);
        color:white;
        font-size:15px;
    }

    .input-field::placeholder{
        color:#ace6f5ff;
    }

    select.input-field option{
        color:black;
    }

    .login-box .btn{
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

    .login-box .btn:hover{
        background:#009bc5;
        box-shadow:0px 4px 15px rgba(0,0,0,0.3);
    }

    p{
        font-size:15px;
        color:#e3faff;
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
    <h1>Park your Ride!!</h1>

<div class="login-box">
    <h2>Confirm Booking</h2>

    <p><b>Parking Slot:</b> Slot will be assigned automatically based on vehicle type.</p>

    <form method="post">
        <input type="text" name="number_plate" class="input-field" placeholder="Enter Vehicle Number Plate" required>

        <select name="type" class="input-field" required>
            <option value="">Select Vehicle Type</option>
            <option value="compact">Compact Car</option>
            <option value="suv">SUV</option>
            <option value="bike">Bike</option>
        </select>

        <button name="book_now" class="btn">Book Now</button>
    </form>
</div>

</body>
</html>
