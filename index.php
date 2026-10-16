<?php
require 'config.php';
if(is_logged_in()){
    header('Location: dashboard.php'); exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>ParkMyRide</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>


<header class="header">
    <div class="container">
        <div class="logo">
           <a href="index.php"><img src="images/image.jpg" style="height:80px"></a>
        </div>
        <div class="link-container">
            <a href="index.php">Home</a>
            <a href="aboutus.html">About Us</a>
            <a href="contactus.html">Contact</a>

            <?php if(is_logged_in()) { ?>
                
                <span class="user-name">👋 <?php echo $_SESSION['username']; ?></span>
                <a href="logout.php">Logout</a>
            <?php } else { ?>
              
                <a href="signin.php">Sign In</a>
            <?php } ?>
        </div>
    </div>
</header>



<section class="hero-section">
    <div class="hero-content">
        <h1>Smart Parking Made Easy</h1>
        <p>Book available slots instantly & enjoy a hassle-free parking experience.</p>

        <div class="hero-buttons">
            <a href="signin.php" class="btn-primary">Book Parking</a>
            <a href="signup.php" class="btn-secondary">Create Account</a>
        </div>
    </div>
</section>


<section class="features-section container">

    <div class="feature-box">
        <i class="fa-solid fa-car"></i>
        <h3>Easy Booking</h3>
        <p>Select your slot & confirm instantly.</p>
    </div>

    <div class="feature-box">
        <i class="fa-solid fa-clock"></i>
        <h3>24/7 Access</h3>
        <p>Parking available any time of the day.</p>
    </div>

    <div class="feature-box">
        <i class="fa-solid fa-credit-card"></i>
        <h3>Pay on Exit</h3>
        <p>Simple & fast checkout when leaving.</p>
    </div>

</section>


<footer class="main-footer">
    <p>© 2025 ParkMyRide | All Rights Reserved</p>
</footer>

</body>
</html>
