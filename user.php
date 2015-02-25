<?php

session_start();
if (!((isset($uid) && is_numeric($uid)) || isset($_POST['submit']))) :
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
  $access = isset($_POST['access']) ? $_POST['access'] : '';
  if (empty($access) || $access != $_SESSION['$access_form']) {
    header("Location: index.php");
    exit;
  }
  else {
    unset($_SESSION['$access_form']);
  }
  include 'config.php';
  if ((!isset($_SESSION['user']) || in_array(3, $_SESSION['user']['rid'])) && isset($_POST['submit']) && $_POST['submit'] == 'add') {
    $login = var_user('login', $_POST['login'], true);
    // print_r($login);
    // echo '<br>$login- ' . $login;
    if (empty($login)) {
      header("Location: index.php?user=0");
      exit;
    }
    if ($login == '_')
      unset($login);
    $pass = var_user('pass', $_POST['pass']);
    // echo '<br>$pass- ' . $pass;
    if (empty($pass)) {
      header("Location: index.php?user=0");
      exit;
    }
    $mail = var_user('mail', $_POST['mail'], true);
    //echo '<br>$mail- ' . $mail;
    if (empty($mail)) {
      header("Location: index.php?user=0");
      exit;
    }
    if ($mail == '_')
      unset($mail);

    if ($login && $mail) {
      $created = $login_time = time();
      $sth = $dbh->prepare('INSERT INTO users SET login=?,password=?,mail=?,created=?,login_time=?');
      $sth->execute(array($login, $pass, $mail, $created, $login_time));
      $row = $sth->fetchAll();
      //print_r($row);
      //exit;
      $uid = $dbh->lastInsertId();
      $sth = $dbh->prepare('INSERT INTO users_roles SET uid=?,rid=1');
      $sth->execute(array($uid));
      if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = array(
          'uid' => $uid,
          'login' => $login,
          'password' => $pass,
          'created' => $created,
          'login_time' => $login_time,
        );
        access_user();
      }
      header("Location: index.php?user=$uid&op=edit");
      exit;
    }
    else {
      header("Location: index.php?user=0");
      exit;
    }
  }
//    try {
//      include 'config.php';
//      $sql = "SELECT * FROM users WHERE login='$login'";
//      foreach ($dbh->query($sql) as $row) {
//        if (crypt($pass, '$5$rounds=5000$usesomesillystringforsalt$') == $row['password']) {
//          $_SESSION['user'] = $row;
//          header("Location: index.php");
//          exit();
//        }
//      }
//    }
//    catch (PDOException $e) {
//      die('error connect: ' . $e->getMessage());
//    }
//    $dbh = null;  
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

