<?php
    ini_set('session.save_path', '/home4/paulkenn/sessions');
    ini_set("session.gc_maxlifetime", 30);
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../index.php?msg=loggedOut");
    exit();
?>
