<?php
require 'config.php';

if(is_logged_in()){
    header("Location: dashboard.php");
    exit;
}

$error="";

if(isset($_POST['login'])){
    $email=trim($_POST['email']);
    $pass=$_POST['password'];

    $stmt=$pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);

    $user=$stmt->fetch();

    if($user && password_verify($pass,$user['password'])){
        session_regenerate_id(true);

        $_SESSION['user_id']=$user['id'];
        $_SESSION['username']=$user['name'];

        header("Location: dashboard.php");
        exit;
    }else{
        $error="Invalid email or password!";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Login | ParkMyRide</title>

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

    .login-box{
        width:350px;
        padding:30px;
        border-radius:15px;
        background: rgba(255,255,255,0.15);
        box-shadow: 0px 8px 25px rgba(0,0,0,0.25);
        backdrop-filter: blur(12px);
        text-align:center;
        color:white;
    }
    h1{
        padding:10px;
        color:#48d3ebff;

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
        color: #ace6f5ff;
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
        font-size:14px;
    }

    a ,p{
        color:#fff;
        text-decoration:none;
        font-size:14px;
        display:inline-block;
        margin-top:12px;
    }

    a:hover{
        text-decoration:underline;
        color:#ace6f5ff;
    }
</style>

</head>
<body>
    <h1>Park your Ride!!</h1>

<div class="login-box">
    <h2>Login</h2>

    <?php if(!empty($error)){ ?>
    <div class="error"><?php echo $error; ?></div>
    <?php } ?>


    <form method="post">
        <input type="email" name="email" class="input-field" placeholder="Email" required>
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        <button type="submit" name="login" class="btn">Sign In</button>
    </form>

    <p >Don't have an account?<a href="signup.php">   Sign Up</a></p>
</div>

</body>
</html>
