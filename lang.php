<?php
session_start();
if (isset($_POST)) {
  $lang = substr(key($_POST), 0, 2);
  $_SESSION['lang'] = $lang;
}
header("Location: index.php");
exit();

