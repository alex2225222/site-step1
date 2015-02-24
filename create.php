<?php
session_start();
if(!isset($_SESSION['user'])) :
  //header ("Location: index.php");
  exit();
endif;
if (!isset($_POST['title'])){
?>
<form name="create" action="create.php" method="post">
    Title<input name="title" type="text" /><br/>
    Body<br/><textarea name="body" rows="8"></textarea><br/>
    <input value="create" name="submit" type="submit" />
</form>
<?php
} else {
  
  if(isset($_POST['title'])){
    print_r($_SESSION); echo 'post';
     print_r($_POST);
    $title  =  stripcslashes($_POST['title']);
    $title  =  htmlspecialchars($title);
    $title  =  trim($title);
    echo $title;
    if (empty($title)): 
      header ("Location: index.php");
      exit();
    endif;
  } else {
      header ("Location: index.php");
      exit();    
  }
  if(isset($_POST['body'])){
    $body  =  stripcslashes($_POST['body']);
    $body  =  htmlspecialchars($body);
    $body  =  trim($body);
    if (empty($body)): 
      header ("Location: index.php");
      exit();
    endif;
  } else {
      header ("Location: index.php");
      exit();    
  }
  $created = time();
  $user = $_SESSION['user']['login'];
  include 'config.php';
  $sth = $dbh->prepare('INSERT INTO article SET title=?,body=?,user=?,created=?');
  $sth->execute(array($title,$body,$user,$created));
  $row = $sth->fetchAll();

//  $sql = "INSERT INTO article SET title='$title',body='$body',user='$user',created='$created'";
//  echo $sql;
//  $dbh->query($sql);
  $insertId=$dbh->lastInsertId();
  $dbh = null;
  header ("Location: index.php?id=$insertId");
      exit(); 
}


?>


