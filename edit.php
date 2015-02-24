<?php
session_start();
if (!isset($_SESSION['user'])) :
  //header ("Location: index.php");
  exit();
endif;
if (!isset($_POST['title'])) {
  $sql = "SELECT * FROM article WHERE id='$id'";
  foreach ($dbh->query($sql) as $row) {
    ?>
    <form name="create" action="edit.php" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>"/>
        Title<input value="<?php echo $row['title']; ?>" name="title" type="text" /><br/>
        Body<br/><textarea name="body" rows="8"><?php echo $row['body']; ?></textarea><br/>
        <input value="save" name="submit" type="submit" />
    </form>
    <?php
  }
}
else {
  if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = $_POST['id'];
  }
  if (isset($_POST['title'])) {
    print_r($_SESSION);
    echo 'post';
    print_r($_POST);
    $title = stripcslashes($_POST['title']);
    $title = htmlspecialchars($title);
    $title = trim($title);
    echo $title;
    if (empty($title)):
      header("Location: index.php");
      exit();
    endif;
  } else {
    header("Location: index.php");
    exit();
  }
  if (isset($_POST['body'])) {
    $body = stripcslashes($_POST['body']);
    $body = htmlspecialchars($body);
    $body = trim($body);
    if (empty($body)):
      header("Location: index.php");
      exit();
    endif;
  } else {
    header("Location: index.php");
    exit();
  }
  $created = time();
  $user = $_SESSION['user']['login'];
  $dbh = new PDO('mysql:host=localhost;dbname=step1', 'root', '2');
  $sth = $dbh->prepare('UPDATE article SET title=?,body=?,user=?,created=? WHERE id=?');
  $sth->execute(array($title,$body,$user,$created,$id));
  $row = $sth->fetchAll();
  
  
//  $sql = "UPDATE article SET title='$title',body='$body',user='$user',created='$created' WHERE id='$id'";
//  echo $sql;
//  $dbh->query($sql);
  $dbh = null;
  header("Location: index.php?id=$id");
  exit();
}
?>


