<?php

session_start();

$_SESSION = [];

setcookie(session_name(), '', time()-1, '/');

session_destroy();

header("Location:login.php");

exit;

?>