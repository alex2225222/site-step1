<?php

function access_user($uid = null) {

  if (!$uid || !is_numeric($uid)) {
    if (isset($_SESSION['user'])) {
      $uid = $_SESSION['user']['uid'];
//      if (isset($_SESSION['user']['rid']))
//        return $_SESSION['user']['rid'];
    }
    else {
      header("Location: index.php");
      exit();
    }
  }
  include 'config.php';
  $sql = "SELECT rid FROM article WHERE uid='$uid'";
  $rid = array();
  foreach ($dbh->query($sql) as $row) {
    $rid[] = $row['rid'];
  }
  $_SESSION['user']['rid'] = $rid;
  return $rid;
}

function user_form($uid = null) {
  if ($uid) {
    $rid = access_user($uid);
    if (in_array(4, $rid)) {
      echo "<h1>access denied. Your profile is blocked.</h1>";
      return;
    }
    if ($uid == $_SESSION['user']['uid'] || in_array(1, $rid)) {
      include 'config.php';
      $sql = "SELECT * FROM users WHERE uid='$uid'";
      $user = $dbh->query($sql)->fetch();
      ?>
      <form name="edit-user" action="user.php" method="post">
          <div class="avatar"><img id="ava" src="img/avatar.jpeg" width="130px" height="110px" />
              <p align="center"><label for="avatar_label" id="photo"><?php echo 'add photo'; ?></label>
              </p>
              <input name="fupload" id="fupload" class="fld" type="FILE"></div>
          <input type="hidden" name="sesion_id" value="<?php echo $session_id; ?>"/>
          Login<input name="login" value="<?php echo $user['login']; ?>" type="text" /><br/>
          Password<input name="pass" type="password" /><br/>
          Repeat<input name="repeat" type="password" onchange="pass_repeat(this.form)"/><br/>
          Name<input name="name" value="<?php echo $user['name']; ?>" type="text" /><br/>
          Surname<input name="lastname" value="<?php echo $user['lastname']; ?>" type="text" /><br/>
          E-mail<input name="mail" value="<?php echo $user['mail']; ?>" type="email" /><br/>
          <input value="save" name="submit" type="submit" />
      </form> 
      <script>
        function SketchFileSelect(evt) {
            var files = evt.target.files;
            f = files[0];
            if (f.type.match('image.*')) {
                var reader = new FileReader();
                reader.onload = (function (theFile) {
                    return function (e) {
                        document.getElementById('ava').src = e.target.result;
                        document.getElementById('photo').innerHTML = '<?php echo 'edit photo'; ?>';
                        return false;
                    };
                })(f);
                reader.readAsDataURL(f);
            }
        }
        document.getElementById('fupload').addEventListener('change', SketchFileSelect, false);
      </script>
      <?php
    }
    else {
      echo "<h1>access denied</h1>";
    }
  }
  else {
    ?>
    <form name="create-user" action="user.php" method="post">
        <input type="hidden" name="sesion_id" value="<?php echo $session_id; ?>"/>
        Login<input name="login" type="text" /><br/>
        Password<input name="pass" type="password" /><br/>
        Repeat<input name="repeat" type="password" onchange="pass_repeat(this.form)"/><br/>
        E-mail<input name="mail" type="email" /><br/>
        <input value="add" name="submit" type="submit" />
    </form>  
    <?php
  }
  ?>
  <script type="text/javascript">
    function pass_repeat(form) {
        if (form.pass.value != form.repeat.value) {
            form.repeat.value = '';
            alert("<?php echo 'password != repeat'; ?>");
        }
    }
  </script>
  <?php
}

function sec_text($text) {
  $text = stripcslashes($text);
  $text = htmlspecialchars($text);
  $text = trim($text);
  return $text;
}
