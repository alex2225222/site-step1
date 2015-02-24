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
  include 'config.php';
  if ((!isset($_SESSION['user']) || $_SESSION['user']['uid'] == 1) && isset($_POST['submit']) && $_POST['submit'] == 'add') {
    $login = sec_text($_POST['login']);
    if (empty($login)):
      header("Location: index.php");
      exit();
    endif;
    $sql = "SELECT login FROM users WHERE login='$login'";
    if ($dbh->query($sql)->fetchColumn())
      unset($login);
    if (isset($_POST['pass'])) {
      $pass = sec_text($_POST['pass']);
      if (empty($pass)):
        header("Location: index.php?user=0");
        exit();
      endif;
      $pass = crypt($pass, '$5$rounds=5000$usesomesillystringforsalt$');
    } else {
      header("Location: index.php?user=0");
      exit();
    }
    if (isset($_POST['mail'])) {
      $mail = sec_text($_POST['mail']);
      if (empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?user=0");
        exit();
      }
      $sql = "SELECT mail FROM users WHERE mail='$mail'";
      if ($dbh->query($sql)->fetchColumn())
        unset($mail);
    }
    else {
      header("Location: index.php?user=0");
      exit();
    }
    if ($login && $mail) {
      $created = $login_time = time();
      $sth = $dbh->prepare('INSERT INTO users SET login=?,password=?,mail=?,created=?,login_time=?');
      $sth->execute(array($login, $pass, $mail, $created, $login_time));
      $row = $sth->fetchAll();
      print_r($row);
      exit;
      $uid = $dbh->lastInsertId();
      if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = array(
          'uid' => $uid,
          'login' => $login,
          'password' => $pass,
          'created' => $created,
          'login_time' => $login_time,
        );
      }
      header("Location: index.php?user=$uid");
    }

    try {
      include 'config.php';
      $sql = "SELECT * FROM users WHERE login='$login'";
      foreach ($dbh->query($sql) as $row) {
        if (crypt($pass, '$5$rounds=5000$usesomesillystringforsalt$') == $row['password']) {
          $_SESSION['user'] = $row;
          header("Location: index.php");
          exit();
        }
      }
    }
    catch (PDOException $e) {
      die('error connect: ' . $e->getMessage());
    }
    $dbh = null;
  }
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

