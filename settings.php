<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
require '../../php/dbh.inc.php';

$userId = $_SESSION['userId'];
$username = $_SESSION['username'];
$email ='';

$sql = "SELECT email FROM users WHERE id=?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    //header("Location: signup.php?error=sqlerror");
    echo 'Database error.';
    exit();
} else {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $email = $row['email'];
    }
}

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>MyThyme</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<style>
#edit_username_img, #edit_email_img {
    cursor: pointer;
}
#edit_username_div {
    display: none;
}
#edit_email_div {
    display: none;
}
</style>
    </head>

    <body class="settings">

        <h1 id="top">Settings</h1>

        <div><a href="mythyme.php">&larr; Back</a></div>

        <div>Username:
<?php
echo $username;
?> <img id="edit_username_img" class="icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABHNCSVQICAgIfAhkiAAAAAFzUkdCAK7OHOkAAAAEZ0FNQQAAsY8L/GEFAAAACXBIWXMAAA7EAAAOxAGVKw4bAAABaklEQVQ4T6WUO86CQBCABy1MiImxtDDhCBQ2NFZUHoDExngGD+ApCFBohYo2XoDGI1h5ARNCY3xAP//OspifgPLwS5bszOx+zCQEwIbEcYzj8RhHoxFer1eRRWxBA+73O3S7XVBVFWazGQyHQ7hcLklRiCvDZEjXVquVyCCeTieeo05rCVMZrel0KrIJlmUh67j6yI/HA/r9Pti2TU2A53kwmUxENam32+1qI7PDvCvTNEUmgXKGYaDv+3x/u93KR/4kS6EarSAIkpg/P/B8Pr/KttttRkZ8FJbJXNfNyYhC4ev1+irbbDaFMiInLJMVjfmfjDCKop9kxFtYJtvtdqUy4i2kw47jiCgL+4gryQguTC8Usd/vK8sIbtF1HXu9Xk56OBxqyQhuSEWLxeK9Px6PtWUEnM/nTGeDwYDHTWREa71egyzL0Ol0QJIkUBQF2L+O3gBMzrz1kJbLJYZhCPP5HDRNE+mmAPwBGGmDeoQgM2UAAAAASUVORK5CYII=" alt="Edit" title="Edit" />
            <div id="edit_username_div">
                <form action="edit_username.inc.php" method="POST">
                    <input type="text" name="username" placeholder="Username" />
                    <button type="submit" name="edit-username-submit">Submit</button>
                </form>
            </div>
        </div>

        <div>Email:
<?php
echo $email;
?> <img id="edit_email_img" class="icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABHNCSVQICAgIfAhkiAAAAAFzUkdCAK7OHOkAAAAEZ0FNQQAAsY8L/GEFAAAACXBIWXMAAA7EAAAOxAGVKw4bAAABaklEQVQ4T6WUO86CQBCABy1MiImxtDDhCBQ2NFZUHoDExngGD+ApCFBohYo2XoDGI1h5ARNCY3xAP//OspifgPLwS5bszOx+zCQEwIbEcYzj8RhHoxFer1eRRWxBA+73O3S7XVBVFWazGQyHQ7hcLklRiCvDZEjXVquVyCCeTieeo05rCVMZrel0KrIJlmUh67j6yI/HA/r9Pti2TU2A53kwmUxENam32+1qI7PDvCvTNEUmgXKGYaDv+3x/u93KR/4kS6EarSAIkpg/P/B8Pr/KttttRkZ8FJbJXNfNyYhC4ev1+irbbDaFMiInLJMVjfmfjDCKop9kxFtYJtvtdqUy4i2kw47jiCgL+4gryQguTC8Usd/vK8sIbtF1HXu9Xk56OBxqyQhuSEWLxeK9Px6PtWUEnM/nTGeDwYDHTWREa71egyzL0Ol0QJIkUBQF2L+O3gBMzrz1kJbLJYZhCPP5HDRNE+mmAPwBGGmDeoQgM2UAAAAASUVORK5CYII=" alt="Edit" title="Edit" />
            <div id="edit_email_div">
                <form action="edit_email.inc.php" method="POST">
                    <input type="text" name="email" placeholder="E-mail" />
                    <button type="submit" name="edit-email-submit">Submit</button>
                </form>
            </div>
        </div>

        <div>
        </div>

        <div><a href="reset_password.php">Reset password</a></div>
<script>
$(function() {
    $('#edit_username_img').click(function() {
        if ($("#edit_username_div").is(":hidden")) {
            $("#edit_username_div").slideDown();
        } else {
            $("#edit_username_div").slideUp();
        }
    });
    $('#edit_email_img').click(function() {
        if ($("#edit_email_div").is(":hidden")) {
            $("#edit_email_div").slideDown();
        } else {
            $("#edit_email_div").slideUp();
        }
    });
});
</script>

    </body>

</html>
