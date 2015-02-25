<?php

session_start();
if (isset($_SESSION['user']) && isset($_POST['submit']) && $_POST['submit'] == 'signout') :
  unset($_SESSION['user']);
  session_destroy();
  header("Location: index.php");
  exit();
endif;


if (!isset($_SESSION['user']) && isset($_POST['submit']) && $_POST['submit'] == 'signin') {
  if (isset($_POST['login'])) {
    $login = stripcslashes($_POST['login']);
    $login = htmlspecialchars($login);
    $login = trim($login);
    if (empty($login)):
      header("Location: index.php");
      exit();
    endif;
  } else {
    header("Location: index.php");
    exit();
  }
  if (isset($_POST['pass'])) {
    $pass = stripcslashes($_POST['pass']);
    $pass = htmlspecialchars($pass);
    $pass = trim($pass);
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
      if (crypt($pass, $self) == $row['password']) {
        $_SESSION['user'] = $row;
        include 'user_func.php';
        access_user();
        header("Location: index.php");
        exit();
      }
    }
  }

header("Location: index.php");
exit();
?>