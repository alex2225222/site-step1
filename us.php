<?php

session_start();
if (isset($_SESSION['user']) && isset($_POST['submit']) && $_POST['submit'] == 'signout') :
  unset($_SESSION['user']);
  session_destroy();
  header("Location: index.php");
  exit();
endif;

include 'user_func.php';
if (!isset($_SESSION['user']) && isset($_POST['submit']) && $_POST['submit'] == 'signin') {
  if (isset($_POST['login'])) {
    $login = var_user('login', $_POST['login']);
    if (empty($login)):
      $_SESSION['message']=t('error login');
      header("Location: index.php");
      exit();
    endif;
  } else {
    header("Location: index.php");
    exit();
  }
  if (isset($_POST['pass'])) {
    $pass = var_user('pass', $_POST['pass']);
    if (empty($pass)):
      header("Location: index.php");
      exit();
    endif;
  } else {
    header("Location: index.php");
    exit();
  }

    include 'config.php';
    $sql = "SELECT * FROM users WHERE login='$login'";
    foreach ($dbh->query($sql) as $row) {
      if ($pass == $row['password']) {
        $_SESSION['user'] = $row;
        
        user_rid();
        header("Location: index.php");
        exit();
      }
    }
  }

header("Location: index.php");
exit();
?>