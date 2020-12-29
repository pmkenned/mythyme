<?php
if (file_exists("error_log")) {
    $myfile = fopen("error_log", "r") or exit("Error opening error_log");
    echo fread($myfile,filesize("error_log"));
    fclose($myfile);
}
?>
