<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
require '../../php/dbh.inc.php';

$userId = $_SESSION['userId'];

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    header("Location: settings.php?error=sqlerror");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $email = $row['email'];
} else {
    header("Location: settings.php?error=sqlerror");
    exit();
}

/*
CREATE TABLE pwdReset (
	pwdResetId int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
	pwdResetEmail TEXT NOT NULL,
	pwdResetSelector TEXT NOT NULL,
	pwdResetToken LONGTEXT NOT NULL,
	pwdResetExpires TEXT NOT NULL
);
*/

/*
$selector = bin2hex(random_bytes(8));
$token = random_bytes(32);

$url = "www.paulmkennedy.com/mythyme/new_password.php?selector=$selector&validator=" . bin2hex($token);

$expires = date("U") + 900;

$userEmail = $_POST["email"];

$sql = "DELETE FROM pwdReset WHERE pwdResetEmail = ?";
$stmt = mysqli_stmt_init($conn);
if (
*/

mail($email,"My subject","Click here to reset your password");
header("Location: index.php?msg=passwordReset");
exit();
?>
