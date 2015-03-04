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
  switch ($_POST['type']) {
    case 'article-edit':
      $id = article_save($_POST);
      header("Location: index.php?id=$id");
      break;
    case 'static-page-edit':
      $id = static_page_save($_POST);
      header("Location: index.php?st=$id");
      break;
    default:
      break;
  }  
  exit();
}
if (isset($_POST['add_lang'])) {
  switch ($_POST['type']) {
    case 'article-edit':
      $id = article_save($_POST);
      header("Location: index.php?edit=$id&add_field=1");
      break;
    case 'static-page-edit':
      $id = static_page_save($_POST);
      header("Location: index.php?st=$id&op=add_lang");
      break;
    default:
      break;
  }
  exit();
}
//if (isset($_POST['add_stat'])) {
//  $id = save_stat($_POST);
//  header("Location: index.php?edit=$id&add_field=1");
//  exit();
//}
header("Location: index.php");
exit();
