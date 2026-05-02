<?php
session_start();
include '../includes/db.php';

if(isset($_SESSION['user'])){
    header("Location: ../pages/dashboard.php");
    exit();
}

$error = "";
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if(mysqli_num_rows($result) == 1){
        $_SESSION['user'] = $username;
        header("Location: ../pages/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login - LedgerSys</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h2><i class="fas fa-book"></i> दैनिकी</h2>
        
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            
            <?php if($error): ?>
                <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-top: 15px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </form>
        
        <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px; font-size: 14px;">
        </div>
    </div>
</div>

</body>
</html>
