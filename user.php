<?php

session_start();
if (!((isset($uid) && is_numeric($uid)) || isset($_POST['submit']))) :
  header("Location: index.php");
  exit();
endif;

include_once 'user_func.php';

if (!isset($_POST['login'])) {

  if ($uid == 0) {
    user_form();
  }
  else {
    $op = isset($_GET['op']) ? $_GET['op'] : '';
    switch ($op) {
      case 'edit':
        user_form($uid);
        break;
      case 'list':
        user_list();
        break;
      case 'delete':
        header("Location: index.php");
        exit();
        break;
      default:
        user_page($uid);
        break;
    }
  }
}
else {
  $access = isset($_POST['access']) ? $_POST['access'] : '';
  if (empty($access) || $access != $_SESSION['access_form']) {
    header("Location: index.php");
    exit;
  }
  else {
    unset($_SESSION['access_form']);
  }
  include 'config.php';
  if ((!isset($_SESSION['user']) || in_array(3, $_SESSION['user']['rid'])) && isset($_POST['submit']) && $_POST['submit'] == 'add') {
    $login = var_user('login', $_POST['login'], true);
    if (empty($login)) {
      header("Location: index.php?user=0");
      exit;
    }
    if ($login == '_')
      unset($login);
    if ($_POST['pass'] != $_POST['repeat']) {
      header("Location: index.php?user=0");
      exit;
    }
    $pass = var_user('pass', $_POST['pass']);
    if (empty($pass)) {
      header("Location: index.php?user=0");
      exit;
    }
    $mail = var_user('mail', $_POST['mail'], true);
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
//print_r($_POST);
//echo '<br>';
//echo '<br>';
////print_r($_SESSION);
  //phpinfo();
//print_r($_FILES);
//exit;
  if ((isset($_SESSION['user']) || in_array(3, $_SESSION['user']['rid'])) && isset($_POST['submit']) && $_POST['submit'] == 'save') {
    $uid = var_user('id', $_POST['uid']);
//print_r($_FILES);
//exit;
    if (empty($uid) || $uid != $_SESSION['user_form']['uid']) {
      header("Location: index.php");
      exit;
    }

    $sql_array = array();
    $sql_add = array();
    $login = var_user('login_info', $_POST['login']);
    if ($login != $_SESSION['user_form']['login']) {
      $login = var_user('login', $_POST['login'], true);
      if (empty($login)) {
        header("Location: index.php?user=$uid");
        exit;
      }
      if ($login == '_') {
        unset($login);
      }
      else {
        $sql_add[] = 'login=?';
        $sql_array[] = $login;
      }
    }

    if ($_POST['pass']) {
      if ($_POST['pass'] != $_POST['repeat']) {
        header("Location: index.php?user=$uid");
        exit;
      }
      $pass = var_user('pass', $_POST['pass']);
      if (empty($pass)) {
        header("Location: index.php?user=$uid");
        exit;
      }
      $sql_add[] = 'password=?';
      $sql_array[] = $pass;
    }
//        print_r($_FILES);
//    exit;     
    $mail = var_user('mail_info', $_POST['mail']);
    if ($mail != $_SESSION['user_form']['mail']) {
      $mail = var_user('mail', $_POST['mail'], true);
      if (empty($mail)) {
        header("Location: index.php?user=$uid");
        exit;
      }
      if ($mail == '_') {
        unset($mail);
      }
      else {
        $sql_add[] = 'mail=?';
        $sql_array[] = $mail;
      }
    }
//        print_r($_FILES);
//    exit;     
    $array_add_field = array('name', 'lastname', 'info_en', 'info_ua');
    foreach ($array_add_field as $value) {
      if ($_POST[$value]) {
        $name = var_user($value, $_POST[$value]);
        if (empty($name)) {
          header("Location: index.php?user=$uid");
          exit;
        }
        $sql_add[] = $value . '=?';
        $sql_array[] = $name;
      }
    }
//    print_r($sql_array);
//    print_r($_FILES);
//    exit;
    if ($_FILES['fupload']['name']) {

      include ("avatar.php");
      $filename = $login;
      $avatar = create_avatar($filename);
      $sql_add[] = 'avatar=?';
      $sql_array[] = $avatar;
    }

    if ($sql_array) {
      $sql_array[] = $uid;
      include 'config.php';
      $sql = 'UPDATE users SET ' . implode(',', $sql_add) . ' WHERE uid=?';
      $sth = $dbh->prepare($sql);
      $sth->execute($sql_array);
      $row = $sth->fetchAll();
    }
    header("Location: index.php?user=$uid&op=edit");
    exit;
  }
}

