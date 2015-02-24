<?php

session_start();
if (!isset($_SESSION['user'])) :
  header ("Location: index.php");
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
  
}

