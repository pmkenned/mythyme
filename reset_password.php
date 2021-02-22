<!DOCTYPE html>

<html>

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Reset Password</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    </head>

    <body class="maxwidth700">
        <h1>Reset Your Password</h1>

        <p>An email will be sent to you with instructions on how to reset your password.</p>

<?php
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] == "passwordReset") {
            echo '<p>Check your email for a password reset link.</p>';
        }
    }
?>

        <form action="includes/reset_request.inc.php" method="post">
            <input type="text" name="email" placeholder="E-mail address" />
            <button type="submit" name="reset-request-submit">Submit</button>
        </form>

    </body>

</html>
