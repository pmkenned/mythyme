<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Index</title>
        <!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
    </head>

    <body>

<?php
    require '../../php/dbh.inc.php';

    $sql = "SELECT * FROM users";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: index.php?error=sqlerror");
        exit();
    } else {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while(($row = mysqli_fetch_assoc($result)) != NULL) {
            echo '<p>' . $row['uidUsers'] . '</p>';
        }
    }
?>

    </body>
</html>
