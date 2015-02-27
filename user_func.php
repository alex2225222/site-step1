<?php
include 'config.php';

function gen_access_form() {
  global $self;

  $access = md5(time()) . substr($self, 25, 5);
  return $access;
}

function user_lasttime() {
  global $dbh;

  if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['uid'];
    $created = time();
    $sth = $dbh->prepare('UPDATE users SET login_time=? WHERE uid=?');
    $sth->execute(array($created, $uid));
    $_SESSION['user']['login_time'] = $created;
  }
}

function user_rid($uid = null) {
  $edit_session = false;
  if (!$uid || !is_numeric($uid)) {
    if (!empty($_SESSION['user'])) {
      $uid = $_SESSION['user']['uid'];
      if (!empty($_SESSION['user']['rid'])) {
        return $_SESSION['user']['rid'];
      }
      else {
        $edit_session = true;
      }
    }
    else {
      return false;
    }
  }
  global $dbh;
  $sql = "SELECT rid FROM users_roles WHERE uid='$uid'";
  $rid = array();
  foreach ($dbh->query($sql) as $row) {
    $rid[] = $row['rid'];
  }
  if ($edit_session)
    $_SESSION['user']['rid'] = $rid;
  return $rid;
}

function user_access($pid) {
  if (($rid = user_rid()) && is_integer($pid)) {
    include 'config.php';
    $in = str_repeat('?,', count($rid) - 1) . '?';
    $sql = "SELECT * FROM roles_perm WHERE rid IN ($in) and pid=$pid";
    $stm = $dbh->prepare($sql);
    $stm->execute($rid);
    $data = $stm->fetchAll();
    if ($data)
      return true;
  }
  return false;
}

function user_form($uid = null) {
  user_lasttime();
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  if ($uid) {
    $rid = user_rid();
    if (in_array(BLOCKED_USER, $rid)) {
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
          <div class="avatar"><img id="ava" src="img/avatars/<?php echo $user['avatar'] ? : 'avatar.jpeg'; ?>" width="130" height="110" />
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
            $user_rid = user_rid($uid);
            $sql = "SELECT * FROM roles";
            foreach ($dbh->query($sql) as $row) {
              $r = in_array($row['rid'], $user_rid) ? " checked='checked'" : '';
              $u = $row['rid'] == 1 ? ' disabled' : '';
              echo "<input type='checkbox' name='rid{$row['rid']}'$r$u/><label>{$row['roles']}</label><br/>";
            }
            echo '<input type="hidden" name="roles" value="1"/>';
          }
          ?>
          <input value="<?php echo t('Save'); ?>" name="save" type="submit" />
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
    echo "<h1>" . t('Registration of new user') . "</h1>";
    ?>

    <form name="create-user" action="user.php" method="post">
        <input type="hidden" name="access" value="<?php echo $access; ?>"/>
        <?php echo t('Login'); ?><input name="login" type="text" /><br/>
        <?php echo t('Password'); ?><input name="pass" type="password" /><br/>
        <?php echo t('Repeat'); ?><input name="repeat" type="password" onchange="pass_repeat(this.form)"/><br/>
        E-mail<input name="mail" type="email" /><br/>
        <input value="<?php echo t('Save'); ?>" name="add" type="submit" />
    </form>  
    <?php
  }
  ?>
  <script type="text/javascript">
    function pass_repeat(form) {
        if (form.pass.value != form.repeat.value) {
            form.repeat.value = '';
            alert("<?php echo t('password != repeat'); ?>");
        }
    }
  </script>
  <?php
}

function user_page($uid) {
  user_lasttime();
  if ($uid) {
    $rid = user_rid();
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

function l($text, $options = null) {
  $gets = $options ? '?' . http_build_query($options) : '';
  return '<a href="index.php' . $gets . '">' . $text . '</a>';
}

function user_list() {
  if (user_access(9)) {
    global $dbh;
    $sql = "SELECT * FROM users";
    echo '<table class="users">';
    echo "<tr><th>" . t('Login') . "</th><th>" . t('E-mail') . "</th><th>" . t('Name') . "</th><th>" . t('Surname') . "</th><th>" . t('Operation') . "</th></tr>";
    foreach ($dbh->query($sql) as $row) {
      $ed = l(t('Edit'), array('user' => $row['uid'], 'op' => 'edit'));
      $del = "<a href='delete.php?id=" . $row['uid'] . "&type=user'>delete</a>"; // l(t('Delete'),array('user'=>$row['uid'],'op'=>'delete'));
      echo "<tr><td>" . $row['login'] . "</td><td>" . $row['mail'] . "</td><td>" . $row['name'] . "</td><td>" . $row['lastname'] . "</td><td>" . $ed . '/' . $del . "</td></tr>";
    }
    echo '</table>';
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
        if (!preg_match('#^[a-zA-Z]\w{3,15}$#sU', $data)) {
          $_SESSION['message'] = t('Error login: 3-15 symbols; first - a-z,A-Z; other - a-z,A-Z, 0-9, _');
          return '_';
        }
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
      if ($op) {
        if (!preg_match('#^\.{3,15}$#sU', $data)) {
          $_SESSION['message'] = t('error password: 3-15 symbols');
          return false;
        }
      }
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
    $type_array['teaser'] = text_short($type_array[$teaser['field']], $teaser['max']);
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
      $sth = $dbh->prepare('INSERT INTO article SET user=?,created=?,lkup=0,lkdown=0,vot_users=0,vot_sum=0');
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
  $rat = article_rating($id);
  if ($teaser) {
    $created = date('d.m.Y', $article['created']);
    $output .= "<div class='block-teaser'><h1><a href='index.php?id=$id'>{$article['fields']['title']}</a></h1>"
        . "<div class='autor'>{$article['autor']}</div>"
        . "<div class='date'>$created</div>".$rat
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
        . '<input value="' . t('bad') . '" name="bad" type="submit" />' . '-' . $article['lkdown'] . "</form></div>".$rat;
    if (isset($_SESSION['user'])) //prava
      print("<a href='index.php?edit=$id'>edit</a><br/>"
          . "<a href='delete.php?id=$id&type=article'>delete</a>");
  }
  return $output;
}

function article_edit($op, $id = null) {
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  if ($op == 'create') {
    echo '<h1>' . t('Create content') . '</h1>';
  }
  else {
    echo '<h1>' . t('Edit content') . '</h1>';
  }
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

function text_short($text, $max) {
  $count = iconv_strlen($text, 'UTF-8');
  if ($count > $max) {
    if ($max > 10) {
      $space = mb_strpos($text, ' ', $max - 10);
      if ($space) {
        $text = mb_substr($text, 0, $space) . '....';
      }
      else {
        $text = mb_substr($text, 0, $max) . '....';
      }
    }
    else {
      $text = mb_substr($text, 0, $max) . '....';
    }
  }
  return $text;
}

function comments_load($aid) {
  $output = '<h2>' . t('Comments') . '</h2>';
  if (is_numeric($aid)) {
    include 'config.php';
    $lang = tt();
    $perm_comments = user_access(6);
    $sql = "SELECT * FROM comments WHERE id_article='$aid' and lang='$lang'";
    foreach ($dbh->query($sql) as $row) {
      $output .= comment_render($row, $perm_comments);
    }
  }
  if (isset($_SESSION['user']))
    $output .= '<a href="index.php?comment=create&aid=' . $aid . '">' . t('Add comment') . '</a>';
  return $output;
}

function comment_render($row, $permission = false) {
  $row['theme'] = empty($row['theme']) ? text_short($row['body'], 15) : $row['theme'];
  $created = date('d.m.Y', $row['created']);
  $edit = $permission ? "<div class='link'>" . l(t('Edit'), array('comment' => $row['cid'], 'op' => 'edit', 'aid' => $row['id_article'])) . "</div>" : '';
  $output = "<div class='comment'><h3>{$row['theme']}</h3>"
      . "<div class='autor'>{$row['user']}</div>"
      . "<div class='date'>$created</div>"
      . "<div class='comment-text'>{$row['body']}</div>"
      . $edit . "</div><hr/>";
  return $output;
}

function comment_load($id) {
  if (is_numeric($id)) {
    $lang = tt();
    global $dbh;
    $sql = "SELECT * FROM comments WHERE cid='$id'";
    $comment = $dbh->query($sql)->fetch();
    return $comment;
  }
  return false;
}

function comment_form($aid, $id = null) {
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  $com = is_numeric($id) ? comment_load($id) : array();
  if ($com) {
    $_SESSION['comment'] = $com;
    if (user_access(6))
      $del = "<a href='delete.php?id={$com['cid']}&type=comment'>delete</a>";
  }

  $lang = tt();
  //print_r($);
  $theme = isset($com['theme']) ? '  value="' . $com['theme'] . '"' : '';
  $body = isset($com['body']) ? $com['body'] : '';
  $id = $id ? '<input type="hidden" name="id" value="' . $id . '"/>' : '';
  $output = '<form name="comment" action="comment.php" method="post">'
      . '<input type="hidden" name="access" value="' . $access . '"/>'
      . $id . '<input type="hidden" name="aid" value="' . $aid . '"/>'
      . '<input type="lang" name="lang" value="' . $lang . '"/>'
      . t('Theme') . '<input name="theme" type="text"' . $theme . '"/><br/>'
      . t('Body') . '<textarea name="body" rows="8">' . $body . '</textarea>'
      . '<input value="' . t('Save') . '" name="save" type="submit" /></form>';

  if (isset($del))
    $output .= $del;

  return $output;
}

function comment_save($post) {
  $com = isset($_SESSION['comment']) ? $_SESSION['comment'] : '';
  unset($_SESSION['comment']);
  include_once 'config.php';
  if ($com) {
    if (!($com['theme'] == $post['theme'] && $com['body'] == $post['body']) && $post['body']) {
      $theme = var_user('theme', $post['theme']);
      $body = var_user('body', $post['body']);
      if (empty($body))
        return false;
      if ($_SESSION['user']['login'] == $com['user']) {
        $created = time();
      }
      else {
        $created = $com['created'];
      }
      $sth = $dbh->prepare('UPDATE comments SET theme=?,body=?,created=? WHERE cid=?');
      $sth->execute(array($theme, $body, $created, $com['cid']));
      return array('cid' => $com['cid'], 'aid' => $com['id_article']);
    }
    else {
      return array('cid' => $com['cid'], 'aid' => $com['id_article']);
    }
  }
  else {
    $theme = var_user('theme', $post['theme']);
    $body = var_user('body', $post['body']);
    if (empty($body))
      return false;
    $id_article = var_user('id', $post['aid']);
    $user = $_SESSION['user']['login'];
    $created = time();
    $lang = var_user('lang', $post['lang']);
    $sth = $dbh->prepare('INSERT comments SET theme=?,body=?,created=?,user=?,lang=?,id_article=?');
    $sth->execute(array($theme, $body, $created, $user, $lang, $id_article));
    $id = $dbh->lastInsertId();
    if (isset($_SESSION['comments'])) {
      $_SESSION['comments'][$id] = $id;
    }
    return array('cid' => $id, 'aid' => $id_article);
  }
}

function user_permission() {
  
}

function user_permission_save($post) {
  
}

function article_rating($aid) {
    global $dbh;
    $sql = "SELECT vot_users, vot_sum FROM article WHERE id='$aid'";
    $rating = $dbh->query($sql)->fetch(); 
    if (empty($rating['vot_users'])){
      $output = '<div class="rating">'. t('No rating'). '</div>';
    }else{
      $output = '<div class="rating">'. t('voice').' '.$rating['vot_users']. ', ' . t('avegete'). ' '.round($rating['vot_sum']/$rating['vot_users'],1). '</div>';
    }
    return $output;
}

function article_like_views_rat($aid) {
  if (!isset($_SESSION['user']) || !is_numeric($aid))
    return '';
  $uid = $_SESSION['user']['uid'];
  global $dbh;
  $sql = "SELECT rating FROM ratings_articles WHERE aid='$aid' and uid='$uid'";
  $rating = $dbh->query($sql)->fetchColumn();
  if ($rating) {
    $output = t('Your grade of article - ') . $rating
        . "<div class='rating'><form name='rating' action='like.php' method='post'>"
        . "<input type='hidden' name='aid' value='" . $aid . "'/>"
        . '<input value="' . t('Delete rating') . '" name="delete" type="submit" /></form></div>';
    return $output;
  }
  else {
    $output = '<div class="rating-empty">' . t('Rate this article') . '<form name="ratform" action="like.php" method="post">'
        . '<input type="hidden" name="aid" value="' . $aid . '"/>'
        . '<input type="hidden" name="uid" value="' . $uid . '"/>'
        . '<input type="radio" name="rat" value="1">1'
        . '<input type="radio" name="rat" value="2">2'
        . '<input type="radio" name="rat" value="3">3'
        . '<input type="radio" name="rat" value="4">4'
        . '<input type="radio" name="rat" value="5">5'
        . '<input value="' . t('Rating') . '" name="rating" type="submit" /></form></div>';
    return $output;
  }
}

function article_add_rating($post) {
  $uid = var_user('id', $_POST['uid']);
  $aid = var_user('id', $_POST['aid']);
  $rat = var_user('id', $_POST['rat']);
  if ($uid && $aid && $rat) {
    global $dbh;
    $sth = $dbh->prepare('INSERT ratings_articles SET uid=?,aid=?,rating=?');
    $sth->execute(array($uid, $aid, $rat));
    $sql = "SELECT vot_users, vot_sum FROM article WHERE id='$aid'";
    $rating = $dbh->query($sql)->fetch();
    $sum = $rat + (integer) $rating['vot_sum'];
    $users = 1 + (integer) $rating['vot_users'];
    $sth = $dbh->prepare('UPDATE article SET vot_users=?,vot_sum=? WHERE id=?');
    $sth->execute(array($users, $sum, $aid));
  }
}

function article_delete_rating($post) {
  $aid = var_user('id', $_POST['aid']);
  if ($aid) { 
    $uid = $_SESSION['user']['uid'];
    global $dbh;
    $sql = "SELECT rating FROM ratings_articles WHERE aid='$aid' and uid='$uid'";
    $rat = $dbh->query($sql)->fetchColumn();

    $sql = "SELECT vot_users, vot_sum FROM article WHERE id='$aid'";
    $rating = $dbh->query($sql)->fetch();
    $sum = (integer) $rating['vot_sum'] - $rat;
    $users = (integer) $rating['vot_users'] - 1;

    $sth = $dbh->prepare('UPDATE article SET vot_users=?,vot_sum=? WHERE id=?');
    $sth->execute(array($users, $sum, $aid));

    $sql = "DELETE FROM ratings_articles WHERE aid='$aid' and uid='$uid'";
    $count = $dbh->exec($sql);
  }
}

function article_add_like($id, $op) {
  if (is_numeric($id)) {
    if (!isset($_SESSION['like']))
      $_SESSION['like'] = array();
    if (!in_array($id, $_SESSION['like'])) {
      global $dbh;
      switch ($op) {
        case 'up':
          $sql = "SELECT lkup FROM article WHERE id=$id";
          $var = $dbh->query($sql)->fetchColumn();
          $var = (integer) $var + 1;
          $sth = $dbh->prepare('UPDATE article SET lkup=? WHERE id=?');
          $sth->execute(array($var, $id));
          break;
        case 'down':
          $sql = "SELECT lkdown FROM article WHERE id=$id";
          $var = $dbh->query($sql)->fetchColumn();
          $var = (integer) $var + 1;
          $sth = $dbh->prepare('UPDATE article SET lkdown=? WHERE id=?');
          $sth->execute(array($var, $id));
          break;
        default:
          break;
      }
      $_SESSION['like'][] = $id;
    }
  }
}
