<?php

require '../../../php/dbh.inc.php';

if (!isset($_POST['reset-request-submit'])) {
    header("Location: ../index.php");
    exit();
}

$selector = bin2hex(random_bytes(8));
$token = random_bytes(32);

$url = "www.paulmkennedy.com/mythyme/new_password.php?selector=$selector&validator=" . bin2hex($token);

$expires = date("U") + 900;

$userEmail = $_POST["email"];

// delete any existing tokens
$query = "DELETE FROM pwdReset WHERE pwdResetEmail = ?;";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $query)) {
    echo "ERROR: database error";
    exit();
}
mysqli_stmt_bind_param($stmt, "s", $userEmail);
mysqli_stmt_execute($stmt);

$query = "INSERT INTO pwdReset (pwdResetEmail, pwdResetSelector, pwdResetToken, pwdResetExpires) VALUES (?, ?, ?, ?);";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $query)) {
    echo "ERROR: database error";
    exit();
}
$hashedToken = password_hash($token, PASSWORD_DEFAULT);
mysqli_stmt_bind_param($stmt, "ssss", $userEmail, $selector, $hashedToken, $expires);
mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);
mysqli_close($conn);

$subject = "Reset your password for MyThyme";
$message = "<p>The link to reset your password is:<br><a href=\"$url\">$url</a></p>";
$headers = "";
#$headers = "From: Paul <paul.kennedy124@gmail.com>\r\n";
#$headers .= "Reply-To: paul.kennedy124@gmail.com\r\n";
$headers .= "Content-Type: text/html\r\n";

mail($userEmail, $subject, $message, $headers);

header("Location: ../reset_password.php?msg=passwordReset");
exit();

?>
