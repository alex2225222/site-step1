<?php

session_start();

function add_like($id, $op) {
  if (is_numeric($id)) {
    if (!isset($_SESSION['like']))
      $_SESSION['like'] = array();
    if (!in_array($id, $_SESSION['like'])) {
      include 'config.php';
      switch ($op) {
        case 'up':
          $sql = "SELECT lkup FROM article WHERE id=$id";
          $var = $dbh->query($sql)->fetchColumn();
          $var = (integer) $var + 1;
          $sth = $dbh->prepare('UPDATE article SET lkup=? WHERE id=?');
          $sth->execute(array($var, $id));
          break;
        case 'down':
          $sql = "SELECT lkdown FROM article WHERE id=$id";
          $var = $dbh->query($sql)->fetchColumn();
          $var = (integer) $var + 1;
          $sth = $dbh->prepare('UPDATE article SET lkdown=? WHERE id=?');
          $sth->execute(array($var, $id));
          break;
        default:
          break;
      }
      $_SESSION['like'][] = $id;
    }
  }
}

if (isset($_POST['good'])) {
  $id = $_POST['id'];
  add_like($id, 'up');
  header("Location: index.php?id=$id");
  exit();
}
if (isset($_POST['bad'])) {
  $id = $_POST['id'];
  add_like($id, 'down');
  header("Location: index.php?id=$id");
  exit();
}
