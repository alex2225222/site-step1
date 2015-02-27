<?php
session_start();
if (!isset($_SESSION['user'])) :
  header("Location: index.php");
  exit();
endif;
include 'user_func.php';
if (!isset($_POST['id']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  ?>
  <form name="delete" action="delete.php" method="post">
      You shure?
      <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>"/>
      <input type="hidden" name="access" value="<?php echo $access; ?>"/>
      <input type="hidden" name="type" value="<?php echo $_GET['type']; ?>"/>
      <input value="cancel" name="submit" type="submit" />
      <input value="delete" name="submit" type="submit" />
  </form>
  <?php
}
else {
  if (isset($_SESSION['user']) && isset($_POST['submit']) && $_POST['submit'] == 'delete') :
    $access = isset($_POST['access']) ? $_POST['access'] : '';
    if (empty($access) || $access != $_SESSION['access_form']) {
      header("Location: index.php");
      exit;
    }
    else {
      unset($_SESSION['access_form']);
    }
    if (isset($_POST['id']) && is_numeric($_POST['id'])):
      $id = $_POST['id'];
    else:
      header("Location: index.php");
      exit();
    endif;
    delete_id($_POST['type'], $id);
  endif;
  header("Location: index.php");
  exit();
}

function delete_id($type, $id) {
  include 'config.php';
  switch ($type) {
    case 'article':
      $sql = "DELETE FROM fields WHERE type='article' and id_type = '$id'";
      $count = $dbh->exec($sql);
      $sql = "DELETE FROM article WHERE id = '$id'";
      $count = $dbh->exec($sql);
      $_SESSION['message'] = t('Article ' . $id . ' deleted');
      break;
    case 'user':
      $sql = "DELETE FROM users WHERE uid = '$id'";
      $count = $dbh->exec($sql);
      $_SESSION['message'] = t('User ' . $id . ' deleted');
      break;
    case 'comment':
      $sql = "DELETE FROM comments WHERE cid = '$id'";
      $count = $dbh->exec($sql);
      $_SESSION['message'] = t('Comment ' . $id . ' deleted');
      break;
    default:
      break;
  }
}
?>
