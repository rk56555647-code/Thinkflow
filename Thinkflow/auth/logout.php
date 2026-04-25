<?php
include '../includes/auth.php';
logoutUser();
header("Location: ../auth/login.php");
exit();
?>