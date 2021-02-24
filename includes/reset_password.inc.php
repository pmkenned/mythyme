<?php

// updates password in database, then redirects to login page

require '../../../php/dbh.inc.php';

if (!isset($_POST['reset-password-submit'])) {
    header("Location: ../index.php");
    exit();
}

$selector = $_POST["selector"];
$validator = $_POST["validator"];
$password = $_POST["pwd"];
$passwordRepeat = $_POST["pwd-repeat"];

$new_pwd_url = "../new_password.php?selector=$selector&validator=$validator";
if (empty($password) || empty($passwordRepeat)) {
    header("Location: $new_pwd_url&newpwd=empty");
    exit();
} else if ($password != $passwordRepeat) {
    header("Location: $new_pwd_url&newpwd=pwdnotsame");
    exit();
}

$currentDate = date("U");

$query = "SELECT * FROM pwdReset WHERE pwdResetSelector = ? AND pwdResetExpires >= ?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $query)) {
    echo "ERROR: database error";
    exit();
}
mysqli_stmt_bind_param($stmt, "ss", $selector, $currentDate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$row = mysqli_fetch_assoc($result)) {
    echo "You need to re-submit your reset request.";
    exit();
}

$tokenBin = hex2bin($validator);
$tokenCheck = password_verify($tokenBin, $row['pwdResetToken']);

if ($tokenCheck !== true) {
    echo "You need to re-submit your reset request.";
    exit();
}

$tokenEmail = $row['pwdResetEmail'];

$query = "SELECT * FROM users WHERE email = ?;";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $query)) {
    echo "ERROR: database error";
    exit();
}
mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$row = mysqli_fetch_assoc($result)) {
    echo "There was an error!";
    exit();
}

$query = "UPDATE users SET password = ? WHERE email = ?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $query)) {
    echo "ERROR: database error";
    exit();
}
$newPwdHash = password_hash($password, PASSWORD_DEFAULT);
mysqli_stmt_bind_param($stmt, "ss", $newPwdHash, $tokenEmail);
mysqli_stmt_execute($stmt);

$query = "DELETE FROM pwdReset WHERE pwdResetEmail = ?;";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $query)) {
    echo "ERROR: database error";
    exit();
}
mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
mysqli_stmt_execute($stmt);
header("Location: ../index.php?msg=passwordUpdated");
exit();

?>
