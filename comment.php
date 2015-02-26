<?php
include 'user_func.php';

function comments_load($aid) {
  $output = '';
  if (is_numeric($id)) {
    include 'config.php';
    $sql = "SELECT * FROM comments WHERE id_article='$aid'";
    foreach ($dbh->query($sql) as $row) {
      $output .= comments_render($row);
    }
  }
  return $output;
}

function comment_load($id) {
  if (is_numeric($id)) {
    $lang = tt();
    include 'config.php';
    $sql = "SELECT * FROM comments WHERE cid='$aid' and lang='$lang'";
    $comment = $dbh->query($sql)->fetch;
    return $comment;
  }
  return false;
}

function comment_form($id = null) {
    $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  $com = is_numeric($id) ? comment_load($id) : '';
  $output = '<form name="comment" action="comment.php" method="post">'
      . '<input type="hidden" name="access" value="' . $access . '"/>'
      . '<input type="hidden" name="id" value="' . $id?:'new' . '"/>'
      . t('Theme') . '<input name="theme" type="text"' . isset($com['theme'])?'  value="'.$com['theme'].'"':'' . '"/><br/>'
      . t('Body') . '<textarea name="body" rows="8">' . isset($com['body'])?'  value="'.$com['body'].'"':'' . '</textarea>'
      . '<input value="' . t('Save') . '" name="save" type="submit" /></form>';
  
}
