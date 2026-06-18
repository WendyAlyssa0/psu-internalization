<?php
session_start();
session_destroy();
header("Location: ../user/user_login.php");
exit();
?>
