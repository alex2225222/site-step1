<?php

session_start();
if (!isset($uid) || !is_numeric($uid)) :
  header("Location: index.php");
  exit();
endif;

include 'user_func.php';

if (!isset($_POST['login'])) {
  if ($uid == 0) {
    user_form();
  }
  else {
    user_form($uid);
  }
}
else {
  
}



//if (!isset($_POST['title'])) {
//  $sql = "SELECT * FROM article WHERE id='$id'";
//  foreach ($dbh->query($sql) as $row) {
//    
//  }
//}
//else {
//  
//}

