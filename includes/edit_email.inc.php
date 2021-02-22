<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
require '../../../php/dbh.inc.php';

$userId = $_SESSION['userId'];

if(isset($_POST['edit-email-submit'])) {

    $new_email = $_POST['email'];

    if (empty($new_email)) {
        header("Location: ../settings.php?error=emptyfields");
        exit();
    } else {
        $sql = "UPDATE users SET email = ? WHERE id = ?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("Location: ../settings.php?error=sqlerror");
            exit();
        } else {
            mysqli_stmt_bind_param($stmt, "si", $new_email, $userId);
            mysqli_stmt_execute($stmt);
            // TODO: confirm that it was successful
            header("Location: ../settings.php?result=success");
            exit();
        }
    }

} else {
    header("Location: ../settings.php");
    exit();
}

?>
