<?php
session_start();
if(!isset($_SESSION['user'])) :
  header ("Location: index.php");
  exit();
endif;
if (!isset($_POST['id']) && isset($_GET['id']) && is_numeric($_GET['id'])){
?>
<form name="delete" action="delete.php" method="post">
    You shure?
    <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>"/>
    <input value="cancel" name="submit" type="submit" />
    <input value="delete" name="submit" type="submit" />
</form>
<?php
} else {
  if(isset($_SESSION['user']) && isset($_POST['submit']) && $_POST['submit'] =='delete') :
    if (isset($_POST['id']) && is_numeric($_POST['id'])): 
      $id = $_POST['id'];
    else:
      header ("Location: index.php");
      exit();      
    endif;
    include 'config.php';
    $sql = "DELETE FROM article WHERE id = '$id'";
    //echo $sql;
    $count = $dbh->exec($sql);
    //if (count($count)) 
    $dbh = null;
  endif;
header ("Location: index.php");
exit();  
}


?>
