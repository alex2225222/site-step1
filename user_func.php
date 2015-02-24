<?php

function access_user($uid = null) {
  if (!$uid || !is_numeric($uid)) {
    if (isset($_SESSION['user'])) {
      $uid = $_SESSION['user']['uid'];
      if (isset($_SESSION['user']['rid']))
        return $_SESSION['user']['rid'];
    }else {
      header("Location: index.php");
      exit();
    }
  }
  $sql = "SELECT rid FROM article WHERE uid='$uid'";
  $rid = array();
  foreach ($dbh->query($sql) as $row) {
    $rid = $row['rid'];
  }
  $_SESSION['user']['rid'] = $rid;
  return $rid;
}

function user_form($uid = null) {
  if ($uid) {
    $rid = access_user();
    if($uid == $_SESSION['user']['uid'] || in_array(1,$rid)) {
       $dbh = new PDO('mysql:host=localhost;dbname=step1', 'root', '2');
       $sql = "SELECT * FROM users WHERE uid='$uid'";
       $user = $dbh->query($sql)->fetch();
    ?>
    <form name="edit-user" action="user.php" method="post">
        <input type="hidden" name="sesion_id" value="<?php echo $session_id; ?>"/>
        Login<input name="login" value="<?php echo $user['login']; ?>" type="text" />
        Password<input name="pass" type="password" />
        Repeat<input name="repeat" type="password" onchange="pass_repeat(this.form)"/>
        E-mail<input name="mail" value="<?php echo $user['mail']; ?>" type="email" />
        <input value="saveedit" name="submit" type="submit" />
    </form>  
    <?php      
    } else {
      echo "<h1>access denied</h1>";
    }
  }
  else {
    ?>
    <form name="create-user" action="user.php" method="post">
        <input type="hidden" name="sesion_id" value="<?php echo $session_id; ?>"/>
        Login<input name="login" type="text" />
        Password<input name="pass" type="password" />
        Repeat<input name="repeat" type="password" onchange="pass_repeat(this.form)"/>
        E-mail<input name="mail" type="email" />
        <input value="save" name="submit" type="submit" />
    </form>  
    <?php
  }
  ?>
  <script type="text/javascript">
    function pass_repeat(form) {
        if (form.pass.value != form.repeat.value) {
            form.repeat.value = '';
            alert("<?php echo $lang['alert']; ?>");
        }
    }
  </script>
  <?php
}
