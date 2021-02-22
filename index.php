<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: mythyme.php");
    exit();
}
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="description" content="Login Page" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    </head>

    <body class="centerForm">

        <div class="centerForm" id="loginDiv">
<?php
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] == "loggedOut") {
            echo 'You are logged out.';
        } elseif ($_GET['msg'] == "signedUp") {
            echo 'Sign up successful.<br>You may now log in.';
        } elseif ($_GET['msg'] == "passwordUpdated") {
            echo 'You may now log in with your new password.';
        }
    }
    if (isset($_GET['error'])) {
        if ($_GET['error'] == "emptyfields") {
            echo 'Fill in all the fields.';
        } elseif ($_GET['error'] == "sqlerror") {
            echo 'Database error.';
        } elseif ($_GET['error'] == "wrongpwd") {
            echo 'Incorrect username/password.';
        } elseif ($_GET['error'] == "nouser") {
            echo 'No such user "' . $_GET['uid'] . '".';
        }
    }
?>
            <form action="includes/login.inc.php" method="POST">
                <input type="text" name="uid" placeholder="Username..." /><br />
                <input type="password" name="pwd" placeholder="Password..." /><br />
                <button type="submit" name="login-submit">Login</button>
            </form>
            <p><a href="reset_password.php">Forgot password?</a></p>
            <p><a href="signup.php">Create Account</a></p>
        </div>

    </body>

</html>
