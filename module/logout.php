<?php
session_start();

session_unset();
session_destroy();

header("Location: ../system/login/login.php");
exit;
