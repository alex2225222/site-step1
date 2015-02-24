<?php

function access_user($uid = null){
  if (!$uid || !is_numeric($uid)){
    if (isset($_SESSION['user'])){
      $uid = $_SESSION['user']['uid'];
    }else{
      header ("Location: index.php");
      exit();
    }
  }
  $sql = "SELECT rid FROM article WHERE uid='$uid'";
  $rid = array();
  foreach ($dbh->query($sql) as $row){
    $rid = $row['rid'];
  }
  return $rid;
}

