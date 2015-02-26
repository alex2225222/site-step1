<?php

session_start();
if (!isset($_SESSION['user'])) :
  header("Location: index.php");
  exit();
endif;
include_once 'user_func.php';
$access = isset($_POST['access']) ? $_POST['access'] : '';
if (empty($access) || $access != $_SESSION['access_form']) {
  header("Location: index.php");
  exit;
}
else {
  unset($_SESSION['access_form']);
}
if (isset($_POST['save'])){
  $id = save_article($_POST);
  header("Location: index.php?id=$id");
  exit();    
}
if (isset($_POST['add_lang'])){
  $id = save_article($_POST);
  header("Location: index.php?edit=$id&add_field=1");
  exit();    
}
header("Location: index.php");
exit();
