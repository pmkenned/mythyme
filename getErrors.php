<?php
//$myfile = fopen("error_log", "r") or die("Unable to open file!");
$myfile = fopen("error_log", "r") or exit("No error_log");
echo fread($myfile,filesize("error_log"));
fclose($myfile);
?>
