<?

$selector = $_GET["selector"];
$validator = $_GET["validator"];

if (empty($selector) || empty($validator)) {
    echo "Could not validate your request!";
    exit();
}

if (!ctype_xdigit($selector) || !ctype_xdigit($validator)) {
    echo "Could not validate your request!";
    exit();
}

?><!DOCTYPE html>

<html>

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Create New Password</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    </head>

    <body class="maxwidth700">

<?
if (isset($_GET["newpwd"])) {
    if ($_GET["newpwd"] == "empty") {
        echo "<p>Not all fields were filled out.</p>";
    } else if ($_GET["newpwd"] == "pwdnotsame") {
        echo "<p>Passwords do not match.</p>";
    }
}
?>

        <form action="includes/reset_password.inc.php" method="post">
            <input type="hidden" name="selector" value="<?php echo $selector; ?>" />
            <input type="hidden" name="validator" value="<?php echo $validator; ?>" />
            <input type="password" name="pwd" placeholder="Enter a new password..." />
            <input type="password" name="pwd-repeat" placeholder="Repeat new password..." />
            <button type="submit" name="reset-password-submit">Reset Password</button>
        </form>

    </body>

</html>
