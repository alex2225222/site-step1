<?php

function gen_access_form() {
  include 'config.php';
  $access = md5(time()) . substr($self, 25, 5);
  return $access;
}

function access_user($uid = null) {

  if (!$uid || !is_numeric($uid)) {
    if (isset($_SESSION['user'])) {
      $uid = $_SESSION['user']['uid'];
      if (isset($_SESSION['user']['rid']) && !empty($_SESSION['user']['rid']))
        return $_SESSION['user']['rid'];
    }
    else {
      return false;
    }
  }
  include 'config.php';
  $sql = "SELECT rid FROM users_roles WHERE uid='$uid'";
  $rid = array();
  foreach ($dbh->query($sql) as $row) {
    $rid[] = $row['rid'];
  }
  $_SESSION['user']['rid'] = $rid;
  return $rid;
}

function user_form($uid = null) {
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  if ($uid) {
    $rid = access_user();
    if (in_array(4, $rid)) {
      echo "<h1>access denied. Your profile is blocked.</h1>";
      return;
    }
    if ($uid == $_SESSION['user']['uid'] || in_array(3, $rid)) {
      include 'config.php';
      $sql = "SELECT * FROM users WHERE uid='$uid'";
      $user = $dbh->query($sql)->fetch();
      $_SESSION['user_form'] = $user;
      ?>
      <h1><?php echo t('Edit of profile of user'); ?> "<?php echo $user['login']; ?>"</h1>
      <form name="edit-user" action="user.php" method="post">
          <div class="avatar"><img id="ava" src="img/avatars/<?php echo $user['avatar'] ? : 'avatar.jpeg'; ?>" width="130px" height="110px" />
              <p align="center"><label for="avatar_label" id="photo"><?php echo t('add photo'); ?></label></p>
              <input name="fupload" id="fupload" class="fld" type="file"></div>
          <input type="hidden" name="access" value="<?php echo $access; ?>"/>
          <input type="hidden" name="uid" value="<?php echo $user['uid']; ?>"/>
          <?php echo t('Login'); ?><input name="login" value="<?php echo $user['login']; ?>" type="text" /><br/>
          <?php echo t('Password'); ?><input name="pass" type="password" /><br/>
          <?php echo t('Repeat'); ?><input name="repeat" type="password" onchange="pass_repeat(this.form)"/><br/>
          <?php echo t('Name'); ?><input name="name" value="<?php echo $user['name']; ?>" type="text" /><br/>
          <?php echo t('Surname'); ?><input name="lastname" value="<?php echo $user['lastname']; ?>" type="text" /><br/>
          E-mail<input name="mail" value="<?php echo $user['mail']; ?>" type="email" /><br/>
          <?php echo t('About me'); ?><br/><textarea name="info_en" rows="3"><?php echo $user['info_en']; ?></textarea><br/>
          <?php echo t('Про себе (Ukrainian: about me)'); ?><br/><textarea name="info_ua" rows="3"><?php echo $user['info_ua']; ?></textarea><br/>
          <?php
          if (in_array(3, $rid)) {
            $user_rid = access_user($uid);
            $sql = "SELECT * FROM roles";
            foreach ($dbh->query($sql) as $row) {
              $r = in_array($row['rid'], $user_rid) ? " checked='checked'" : '';
              $u = $row['rid'] == 1 ? ' disabled' : '';
              echo "<label><input type='checkbox' name='rid{$row['rid']}'$r$u/>{$row['roles']}</label><br/>";
            }
          }
          ?>
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
        //document.getElementById('fupload').addEventListener('change', SketchFileSelect, false);
      </script>
      <?php
    }
    else {
      echo "<h1>access denied</h1>";
    }
  }
  else {
    ?>
    <h1>Registration of new user</h1>
    <form name="create-user" action="user.php" method="post">
        <input type="hidden" name="access" value="<?php echo $access; ?>"/>
        <?php echo t('Login'); ?><input name="login" type="text" /><br/>
        <?php echo t('Password'); ?><input name="pass" type="password" /><br/>
        <?php echo t('Repeat'); ?><input name="repeat" type="password" onchange="pass_repeat(this.form)"/><br/>
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

function user_page($uid) {
  $access = gen_access_form();
  $_SESSION['$access_form'] = $access;
  if ($uid) {
    $rid = access_user();
    if (in_array(4, $rid)) {
      echo "<h1>" . t('access denied. Your profile is blocked.') . "</h1>";
      return;
    }
    if ($uid == $_SESSION['user']['uid'] || in_array(3, $rid)) {
      include 'config.php';
      $sql = "SELECT * FROM users WHERE uid='$uid'";
      $user = $dbh->query($sql)->fetch();
      $_SESSION['user_form'] = $user;
      ?>
      <h1>Profile of user "<?php echo $user['login']; ?>"</h1>
      <div class="avatar">
          <?php
          if ($user['avatar']) {
            echo "<a href='img/original/{$user['avatar']}'><img src='img/avatars/{$user['avatar']}' /></a>";
          }
          else {
            ?>
            <img src="img/avatars/avatar.jpg" width="125" height="110" />
          <?php } ?>
      </div>
      <p>
          <label for="name"><b><?php echo $lang['name']; ?>:</b></label>
          <?php
          if (iconv_strlen($user['name'], 'UTF-8') > 17)
            echo '</p><p align="right">';
          echo $user['name'];
          ?>
      </p>
      <p>
          <label for="sname"><b><?php echo $lang['sname']; ?>:</b></label>
          <?php
          if (iconv_strlen($user['sname'], 'UTF-8') > 17)
            echo '</p><p align="right">';
          echo ' ' . $user['sname'];
          ?>
      </p>
      <p>
          <label for="city"><b><?php echo $lang['city']; ?>:</b></label>
          <?php
          if (iconv_strlen($user['city'], 'UTF-8') > 17)
            echo '</p><p align="right">';
          echo $user['city'];
          ?>
      </p> 
      <p>
          <label for="mail"><b><?php echo $lang['mail']; ?>:</b></label>
          <?php
          if (iconv_strlen($user['mail'], 'UTF-8') > 17)
            echo '</p><p align="right">';
          echo $user['mail'];
          ?>
      </p>
      <p>
          <label for="mail"><b><?php echo $lang['created']; ?>:</b></label>
          <?php echo date("d-m-Y H:i", $user['created']); ?>
      </p>
      <p>
          <label for="mail"><b><?php echo $lang['access']; ?>:</b></label>
          <?php echo date("d-m-Y H:i", $user['access']); ?>
      </p>     
      <?php
    }
    else {
      echo "<h1>access denied</h1>";
    }
  }
}

function sec_text($text) {
  $text = stripcslashes($text);
  $text = htmlspecialchars($text);
  $text = trim($text);
  return $text;
}

function var_user($type, $data, $op = false) {
  $data = sec_text($data);
  if (empty($data))
    return false;
  include 'config.php';
  switch ($type) {
    case 'login':
      if ($op) {
        $sql = "SELECT login FROM users WHERE login='$data'";
        if ($dbh->query($sql)->fetchColumn())
          return '_';
      }
      break;
    case 'mail':
      if (!filter_var($data, FILTER_VALIDATE_EMAIL))
        return '_';
      if ($op) {
        $sql = "SELECT mail FROM users WHERE mail='$data'";
        if ($dbh->query($sql)->fetchColumn())
          return false;
      }
      break;
    case 'pass':
      $data = crypt($data, $self);
      break;
    case 'id':
      if (!is_numeric($data))
        return false;
      break;
    default:
      break;
  }
  return $data;
}

function t($string, $lang = null) {
  if (empty($lang)) {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
  }
  if ($lang == 'en')
    return $string;
  include 'config.php';
  $sql = "SELECT transl FROM local WHERE name='$string' and lang='$lang'";
  $translite = $dbh->query($sql)->fetchColumn();
  if ($translite) {
    return $translite;
  }
  else {
    return $string;
  }
}

function tt() {
  $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
  return $lang;
}

function load_field_view($type, $id, $lang, $teaser = true, $sovpad = false) {
  include 'config.php';
  $type_array = array();
  $sql = "SELECT * FROM fields WHERE type='$type' and id_type='$id'";
  if ($sovpad)
    $sql .= " and lang='$lang'";
  foreach ($dbh->query($sql) as $row) {
    if (!isset($type_array[$row['field']]) || $row['lang'] == $lang) {
      $type_array[$row['field']] = $row['text'];
    }
  }
  if ($teaser && isset($type_array[$teaser['field']])) {
    $type_array['teaser'] = substr($teaser['field'], 0, $teaser['max']);
  }
  return $type_array;
}

function load_field_edit($type, $id, $lang = null) {
  include 'config.php';
  $type_array = array();
  $sql = "SELECT * FROM fields WHERE type='$type' and id_type='$id'";
  if ($sovpad)
    $sql .= " and lang='$lang'";
  foreach ($dbh->query($sql) as $row) {
    $type_array[$row['lang']][$row['field']] = array(
      'text' => $row['text'],
      'id' => $row['id'],
      'field' => $row['field'],
      'lang' => $row['lang'],
    );
  }
  return $type_array;
}

function save_field($type, $id, $type_array) {
  include 'config.php';
  foreach ($type_array as $value) {
    if (isset($value['id'])) {
      $sth = $dbh->prepare('UPDATE fields SET lang=?,text=? WHERE id=?');
      $sth->execute(array($value['lang'], $value['text'], $value['id']));
    }
    else {
      $sth = $dbh->prepare('INSERT INTO fields SET type=?,id_type=?,lang=?,field=?,text=?');
      $sth->execute(array($type, $id, $value['lang'], $value['field'], $value['text']));
    }
  }
}

function load_article_view($id, $lang) {
  include 'config.php';
  $sql = "SELECT * FROM article WHERE id=$id";
  $article = $dbh->query($sql)->fetch();
  $fields = load_field_view('article', $id, $lang);
  $article_full = array(
    'id' => $id,
    'lang' => $lang,
    'autor' => $article['user'],
    'created' => $article['created'],
    'lk' => $article['lk'],
    'fields' => $fields,
  );
  return $article_full;
}

function article_view($id, $lang, $teaser = false) {
  $article = load_article_view($id, $lang);
  $output = '';
  if ($teaser) {
    $created = date('d.m.Y', $article['created']);
    $output .= "<div class='block-teaser'><h1><a href='index.php?id=$id'>{$article['fields']['title']}</a></h1>"
        . "<div class='autor'>{$article['user']}</div>"
        . "<div class='date'>$created</div>"
        . "<div class='contetnt-text'>{$article['fields']['teaser']}</div>"
        . "<div class='more'><a href='index.php?id=$id'>" . t('Read More') . "</a></div></div><hr/>";
  }
  else {
    $created = date('d.m.Y', $article['created']);
    $output .="<h1>{$article['fields']['title']}</h1>"
        . "<div class='autor'>{$article['user']}</div>"
        . "<div class='date'>$created</div>"
        . "<div class='contetnt-text'>{$article['fields']['body']}</div>";
    if (isset($_SESSION['user'])) //prava
      print("<a href='index.php?edit=$id'>edit</a><br/>"
          . "<a href='delete.php?id=$id'>delete</a>");
  }
  return $output;
}

function article_edit($op, $id = null) {
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  echo '<form name="article" action="article.php" method="post">';
  echo '<input type="hidden" name="access" value="' . $access . '"/>';
  echo '<input type="hidden" name="id" value="' . $id . '"/>';
  switch ($op) {
    case 'edit':case 'add_lang':
      if (is_numeric($id)) {
        include 'config.php';
        $sql = "SELECT * FROM article WHERE id=$id";
        $article = $dbh->query($sql)->fetch();
        $fields = load_field_edit('article', $id);
        $_SESSION['fields'] = array('id' => $id, 'fields' => $fields);
        foreach ($fields as $key => $value) {
          echo '<div class="lang-article">';
          echo t('Title') . '<input name="title_' . $key . '" type="text"  value="' . $value['title']['text'] . '"/><br/>';
          echo t('Lang') . '<input name="lang_' . $key . '" type="text"  value="' . $key . '"/><br/>';
          echo t('Body') . '<textarea name="body_' . $key . '" rows="8">' . $value['body']['text'] . '</textarea><hr/></div>';
        }
      }
      if ($op == 'edit')
        break;
    case 'create':
      echo '<div class="add-lang">';
      echo t('Title') . '<input name="title_new" type="text" /><br/>';
      echo t('Lang') . '<input name="lang_new" type="text" /><br/>';
      echo t('Body') . '<textarea name="body_new" rows="8"></textarea></div>';
      break;
    default:
      break;
  }
  echo '<input value="'.t('Add lang').'" name="add_lang" type="submit" />';
  echo '<input value="'.t('save').'" name="submit" type="submit" /></form>';
}
