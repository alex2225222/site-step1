<?php
include_once 'user_func.php';
if (isset($_POST['submit']))
  session_start();
if (isset($_SESSION['user']) && in_array(3, $_SESSION['user']['rid'])) {
  if (isset($_POST['translate'])) {
    $access = isset($_POST['access']) ? $_POST['access'] : '';
    if (empty($access) || $access != $_SESSION['access_form']) {
      header("Location: index.php");
      exit;
    }
    include 'config.php';
    $transl = $_SESSION['transl'];
//    print_r($_SESSION['transl']);
//    echo '<br><br>';
//    print_r($_POST);
    foreach ($transl as $key => $value) {
      if (!($value['name'] == $_POST['name' . $key] && $value['lang'] == $_POST['lang' . $key] && $value['transl'] == $_POST['transl' . $key])) {
        if (empty($_POST['name' . $key]) || empty($_POST['lang' . $key]) || empty($_POST['transl' . $key])) {

          $sql = "DELETE FROM local WHERE lid = '$key'";

          $count = $dbh->exec($sql);
        }
        else {
          $name = var_user('transl', $_POST['name' . $key]);
          $lang = var_user('transl', $_POST['lang' . $key]);
          $transl = var_user('transl', $_POST['transl' . $key]);
          $sth = $dbh->prepare('UPDATE local SET name=?,lang=?,transl=? WHERE lid=?');
          $sth->execute(array($name, $lang, $transl, $key));
          $row = $sth->fetchAll();
        }
      }
    }
    if (!(empty($_POST['name_new']) || empty($_POST['lang_new']) || empty($_POST['transl_new']))) {
      $name = var_user('transl', $_POST['name_new']);
      $lang = var_user('transl', $_POST['lang_new']);
      $transl = var_user('transl', $_POST['transl_new']);
      $sth = $dbh->prepare('INSERT INTO local SET name=?,lang=?,transl=?');
      $sth->execute(array($name, $lang, $transl));
    }
    header("Location: index.php?tr=edit");
    exit;
  }
  else {
    $access = gen_access_form();
    $_SESSION['access_form'] = $access;
    echo '<form name="transl" action="translate.php" method="post">';
    echo '<input type="hidden" name="access" value="' . $access . '"/>';
    echo '<input type="hidden" name="translate" value="' . $access . '"/>';
    echo '<table class="translate"><tr><th>' . t('English') . '</th><th>' . t('Lang') . '</th><th>' . t('Translate') . '</th></tr>';
    ?>
    <tr>
        <td class="tname"><input name="name_new" type="text"/></td>
        <td class="tlang"><input name="lang_new" value="ua" type="text"/></td>
        <td class="ttransl"><input name="transl_new" type="text"/></td>
    </tr>
    <?php
    $transl = array();
    include 'config.php';
    $sql = "SELECT * FROM local ORDER BY name";
    foreach ($dbh->query($sql) as $row) {
      $transl[$row['lid']] = array(
        'lid' => $row['lid'],
        'name' => $row['name'],
        'lang' => $row['lang'],
        'translation' => $row['translation'],
      );
      ?>
      <tr>
          <td class="tname"><input name="name<?php echo $row['lid']; ?>" type="text" value="<?php echo $row['name']; ?>"/></td>
          <td class="tlang"><input name="lang<?php echo $row['lid']; ?>" type="text" value="<?php echo $row['lang']; ?>"/></td>
          <td class="ttransl"><input name="transl<?php echo $row['lid']; ?>" type="text" value="<?php echo $row['transl']; ?>"/></td>
      </tr>
      <?php
    }
    echo '</table><input value="save" name="submit" type="submit" /></form>';
    $_SESSION['transl'] = $transl;
  }
}