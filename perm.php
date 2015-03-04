<?php

session_start();
if (!isset($_SESSION['user'])) :
  header("Location: index.php");
  exit();
endif;
include_once 'user_func.php';
include 'config.php';

$access = isset($_POST['access']) ? $_POST['access'] : '';
if (empty($access) || $access != $_SESSION['access_form']) {
  header("Location: index.php");
  exit;
}
else {
  unset($_SESSION['access_form']);
}
if (isset($_POST['save'])) {
  user_permission_save($_POST);
  header("Location: index.php?perms=edit");
  exit();    
}
header("Location: index.php");
exit();
