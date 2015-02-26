<?php

function gen_access_form() {
  include 'config.php';
  $access = md5(time()) . substr($self, 25, 5);
  return $access;
}

function user_lasttime() {
  if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['uid'];
    $created = time();
    include 'config.php';
    $sth = $dbh->prepare('UPDATE users SET login_time=? WHERE uid=?');
    $sth->execute(array($created, $uid));
    $_SESSION['user']['login_time'] = $created;
  }
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
  user_lasttime();
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
      if (isset($_SESSION['message'])) {
        echo "<div class='message'>" . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
      }
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
    if (isset($_SESSION['message'])) {
      echo "<div class='message'>" . $_SESSION['message'] . '</div>';
      unset($_SESSION['message']);
    }
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
  user_lasttime();
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
      echo '<h1>' . t('Profile of user') . ' "' . $user['login'] . '"</h1>';
      echo "<div class='avatar'>";
      if ($user['avatar']) {
        echo "<a href='img/original/{$user['avatar']}'><img src='img/avatars/{$user['avatar']}' /></a>";
      }
      else {
        echo '<img src="img/avatars/avatar.jpeg" width="125" height="110" />';
      }
      echo '</div>';
      echo "<div class='login'>" . t('Login') . ': ' . $user['login'] . "</div>";
      echo "<div class='mail'>" . t('E-mail') . ': ' . $user['mail'] . "</div>";
      if ($user['name'])
        echo "<div class='name'>" . t('Name') . ': ' . $user['name'] . "</div>";
      if ($user['lastname'])
        echo "<div class='surname'>" . t('Surname') . ': ' . $user['lastname'] . "</div>";
      $lang = tt();
      if ($user['info_' . $lang])
        echo "<div class='info'>" . t('Info') . ': ' . $user['info_' . $lang] . "</div>";
      $created = date('d.m.Y G:i', $user['created']);
      echo "<div class='date'>" . t('Time created') . ': ' . $created . "</div>";
      $login_time = date('d.m.Y G:i', $user['login_time']);
      echo "<div class='date'>" . t('Last time') . ': ' . $login_time . "</div>";
      echo "<div class='link'><a href='index.php?user=$uid&op=edit'>" . t('Edit profile') . "</a></div>";
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

function load_field_view($type, $id, $lang, $teaser = array('field' => 'body', 'max' => 12), $sovpad = false) {
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
    if (strlen($type_array[$teaser['field']]) > $teaser['max']) {
      $type_array['teaser'] = substr($type_array[$teaser['field']], 0, $teaser['max']) . '....';
    }
    else {
      $type_array['teaser'] = $type_array[$teaser['field']];
    }
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

function save_field_prepare($type, $type_id, $lang, $field, $text, $id = false) {
  $type_array = array(
    $lang => array(
      'lang' => $lang,
      'field' => $field,
      'text' => $text,
    )
  );
  if ($id)
    $type_array[$lang]['id'] = $id;
  save_field($type, $type_id, $type_array);
}

function save_article($post) {
  $article = isset($_SESSION['article']) ? $_SESSION['article'] : '';
  unset($_SESSION['article']);
  include_once 'config.php';
  if ($article) {
    $id = (integer) $article['id'];
    foreach ($article['fields'] as $lang => $value) {
      if (empty($post['title_' . $lang]) || empty($post['lang_' . $lang]) || empty($post['body' . $lang])) {
        
      }
      foreach ($value as $name_field => $value1) {
        if ($post[$name_field . '_' . $lang] != $value1['text']) {
          $name = var_user($name_field, $post[$name_field . '_' . $lang]);
          save_field_prepare('article', $id, $lang, $name_field, $name, $value1['id']);
        }
      }
    }
    if (isset($post['title_new']) && isset($post['lang_new']) && isset($post['body_new'])) {
      $title = var_user('title', $post['title_new'], true);
      $lang = var_user('lang', $post['lang_new'], true);
      $body = var_user('body', $post['body_new'], true);
      if ($title && $lang && $body) {
        $field_array = array('title', 'body');
        foreach ($field_array as $value) {
          save_field_prepare('article', $id, $lang, $value, $$value);
        }
      }
    }
  }
  else {
    if (isset($post['title_new']) && isset($post['lang_new']) && isset($post['body_new'])) {
      $title = var_user('title', $post['title_new'], true);
      if (empty($title)) {
        return false;
      }
      $lang = var_user('lang', $post['lang_new'], true);
      if (empty($lang)) {
        return false;
      }
      $body = var_user('body', $post['body_new'], true);
      if (empty($body)) {
        return false;
      }
      $created = time();
      $user = $_SESSION['user']['login'];
      $sth = $dbh->prepare('INSERT INTO article SET user=?,created=?,lk=0');
      $sth->execute(array($user, $created));
      $id = $dbh->lastInsertId();
      $field_array = array('title', 'body');
      foreach ($field_array as $value) {
        save_field_prepare('article', $id, $lang, $value, $$value);
      }
    }
  }
  return $id;
}

function load_article_view($id, $lang) {
  include 'config.php';
  $sql = "SELECT * FROM article WHERE id=$id";
  $article = $dbh->query($sql)->fetch();
  if (empty($article))
    return false;
  $fields = load_field_view('article', $id, $lang);
  $article_full = array(
    'id' => $id,
    'lang' => $lang,
    'autor' => $article['user'],
    'created' => $article['created'],
    'lkup' => $article['lkup'],
    'lkdown' => $article['lkdown'],
    'fields' => $fields,
  );
  return $article_full;
}

function article_view($id, $lang, $teaser = false) {
  $article = load_article_view($id, $lang);
  if (empty($article))
    return false;
  $output = '';
  if ($teaser) {
    $created = date('d.m.Y', $article['created']);
    $output .= "<div class='block-teaser'><h1><a href='index.php?id=$id'>{$article['fields']['title']}</a></h1>"
        . "<div class='autor'>{$article['autor']}</div>"
        . "<div class='date'>$created</div>"
        . "<div class='contetnt-text'>{$article['fields']['teaser']}</div>"
        . "<div class='like'>" . t('good') . '-' . $article['lkup'] . ', ' . t('bad') . '-' . $article['lkdown'] . "</div>"
        . "<div class='more'><a href='index.php?id=$id'>" . t('Read More') . "</a></div>"
        . "</div><hr/>";
  }
  else {
    $created = date('d.m.Y', $article['created']);
    $output .="<h1>{$article['fields']['title']}</h1>"
        . "<div class='autor'>{$article['autor']}</div>"
        . "<div class='date'>$created</div>"
        . "<div class='contetnt-text'>{$article['fields']['body']}</div>"
        . "<div class='like'><form name='like' action='like.php' method='post'>"
        . "<input type='hidden' name='id' value='" . $id . "'/>"
        . '<input value="' . t('good') . '" name="good" type="submit" /> ' . $article['lkup'] . ', '
        . '<input value="' . t('bad') . '" name="bad" type="submit" />' . '-' . $article['lkdown'] . "</form></div>";
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
        $_SESSION['article'] = array('id' => $id, 'fields' => $fields, 'user' => $article['user'], 'created' => $article['created']);
        foreach ($fields as $key => $value) {
          echo '<div class="lang-article">';
          echo t('Title') . '<input name="title_' . $key . '" type="text"  value="' . $value['title']['text'] . '"/><br/>';
          echo t('Lang') . '<input name="lang_' . $key . '" type="text"  value="' . $key . '"/><br/>';
          echo t('Body') . '<textarea name="body_' . $key . '" rows="8">' . $value['body']['text'] . '</textarea><hr/></div>';
        }
      }
      if ($op == 'edit' && $fields)
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
  echo '<input value="' . t('Add lang') . '" name="add_lang" type="submit" />';
  echo '<input value="' . t('Save') . '" name="save" type="submit" /></form>';
}
