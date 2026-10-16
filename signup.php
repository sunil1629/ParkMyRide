<?php
require 'config.php';

if(is_logged_in()){
    header("Location: dashboard.php");
    exit;
}

if(isset($_POST['signup'])){
    $name=trim($_POST['name']);
    $email=trim($_POST['email']);
    $phone=trim($_POST['phone']);
    $pass=$_POST['password'];
    $cpass=$_POST['cpassword'];

    if($pass!==$cpass){
        $error="Passwords do not match!";
    }else{
        $stmt=$pdo->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);

        if($stmt->fetch()){
            $error="Email already registered!";
        }else{
            $hash=password_hash($pass,PASSWORD_DEFAULT);

            $stmt=$pdo->prepare("INSERT INTO users(name,email,phone,password) VALUES(?,?,?,?)");
            $stmt->execute([$name,$email,$phone,$hash]);

            $success="Account created successfully! Please login.";
        }
    }
}
?>



<!DOCTYPE html>
<html>
<head>
<title>Sign Up | ParkMyRide</title>

<style>
    body{
        margin:0;
        padding:0;
        font-family: Arial;
        background:#0d1e39;
        height:100vh;
        display:flex;
        flex-direction:column;
        align-items:center;
    }

    h1{
        padding:10px;
        color:#48d3ebff;
        margin-top:20px;
    }

    .signup-box{
        width:350px;
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

    .btn{
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

    .btn:hover{
        background:#009bc5;
        box-shadow:0px 4px 15px rgba(0,0,0,0.3);
    }

    .error{
        background:#ff4d4d;
        padding:10px;
        margin-bottom:10px;
        border-radius:5px;
    }

    .success{
        background:#4dff6a;
        padding:10px;
        margin-bottom:10px;
        border-radius:5px;
        color:#004d00;
    }

    p,a{
        color:#fff;
        text-decoration:none;
        font-size:14px;
        margin-top:12px;
        display:inline-block;
    }

    a:hover{
        text-decoration:underline;
        color:#ace6f5ff;
    }
</style>

</head>

<body>

<h1>Park your Ride!!</h1>

<div class="signup-box">
    <h2>Sign Up</h2>

    <?php if(isset($error)){ ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if(isset($success)){ ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <form method="post">
        <input type="text" name="name" class="input-field" placeholder="Full Name" required>
        <input type="email" name="email" class="input-field" placeholder="Email" required>
        <input type="tel" name="phone" class="input-field" placeholder="Mobile no." required>
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        <input type="password" name="cpassword" class="input-field" placeholder="Confirm Password" required>
        <button type="submit" name="signup" class="btn">Create Account</button>
    </form>

    <p>Already have an account? <a href="signin.php">Sign in</a></p>
</div>

</body>
</html>
