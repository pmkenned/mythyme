<?php
ini_set('session.save_path', '/home4/paulkenn/sessions');
ini_set("session.gc_maxlifetime", 30);
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?><!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8" />
        <meta name="description" content="MyThyme" />
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1" /> -->
        <title>MyThyme</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    </head>

    <body class="mythyme">

        <div class="topDiv">
            <div class="alignRight">
                <span class="loggedInAs">
<?php
    echo 'Logged in as <span class="username">' . $_SESSION['username'] . '</span>';
?>
                </span>
                <a href="settings.php"><img class="icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABHNCSVQICAgIfAhkiAAAAAFzUkdCAK7OHOkAAAAEZ0FNQQAAsY8L/GEFAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACX0lEQVQ4T6WUv0tyURjHL2FoDonNRlBRIDi0ORUo9A84FNGW0OAgBDk02Cy5WCA0iTgHNogSItVSiZOD+KMiBM1BIfyFoPJ9ex7PtavvTXh5P3C8z/d7nvM9F+89V8IMKpUKVlZWMD8/j4WFBezv74uZ35kZuLS0BJfLhVKphHw+D0mSEIlExKw6MwMpoFqtCgU4HA4cHx8Lpc44MBwOY29vD9lslrXT6eRAJc/Pz+wVi0XWoVAIh4eHaDabrAlecXd3x41yCA2NRoNOp8NNSoLB4ESP3W7nWoYrMqLRKBvTvL+/4/r6GrFYTDh/Q//1yckJ11IymZzYQQk9YZpbW1vjK41UKiVmf/j4+BhnSP1+n8Xl5SUbMiaTCZubm0KNoDDqzeVywhlBfaurq1xz7OfnJzfe3t6yKb8iavh8PhgMBqEAi8WC5eVloUQgEQgExnd0cXGBra0trtVQbja98dy3wbRaLUmr1XK9uLgo1et1rv8ZSn16euKdXl5eeJfhcMh6MBiwVkLvnljG7OzsQKfTCfV9x19fX9ww/fToyJHfaDSEA6TTafYeHx+FM2J7extGo5FrSW5Sw+Px8Jxy3NzciNkf3t7exhn8S8Lv97NBlMtlUY14eHjgY6ekVquJCtDr9Tg7O+OaA+lrQqFWq3V8JzReX1+5SYnX653oodeGrjLjKpPJ4Pz8HN1ul/XV1dVEI3F/f89eu91mTafs9PQUdDhkJldMQYsLhYJQwMHBAY6OjoRSZ2ag2WzG7u4uEokE4vE4b/BfH9herwebzYb19XVsbGzA7XaLmd8A/gCvYsuBQ1/WDAAAAABJRU5ErkJggg==" alt="Settings" title="Settings" /></a>
                <form action="includes/logout.inc.php" method="POST"><button type="submit" name="logout-submit">Logout</button></form>
            </div>
        </div>

        <canvas id="myCanvas"></canvas>

        <script src="js/constants.js"></script>
        <script src="js/globalState.js"></script>
        <script src="js/helpers.js"></script>
        <script src="js/mt_api.js"></script>
        <script src="js/CalendarView.js"></script>
        <script src="js/eventHandlers.js"></script>
        <script src="js/mythyme.js"></script>

    </body>

</html>
