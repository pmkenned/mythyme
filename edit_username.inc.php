<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
require '../../php/dbh.inc.php';

$userId = $_SESSION['userId'];

// TODO: make sure username is not taken

if(isset($_POST['edit-username-submit'])) {

    $new_username = $_POST['username'];

    if (empty($new_username)) {
        header("Location: settings.php?error=emptyfields");
        exit();
    } else {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("Location: settings.php?error=sqlerror");
            exit();
        } else {
            mysqli_stmt_bind_param($stmt, "si", $new_username, $userId);
            mysqli_stmt_execute($stmt);
            // TODO: confirm that it was successful
            $_SESSION['username'] = $new_username;
            header("Location: settings.php?result=success");
            exit();
        }
    }

} else {
    header("Location: settings.php");
    exit();
}

?>
