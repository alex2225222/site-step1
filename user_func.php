<?php

function menu_created() {
  if (!isset($_SESSION['user'])) {
    $output = '<div class="login-form"><form name="authrization" action="us.php" method="post"><div>' . t('Login')
        . '</div><div><input name="login" type="text" /></div><div>' . t('Password')
        . '</div><div><input name="pass" type="password" /></div><div><input value="signin" name="submit" type="submit" /></div>'
        . '<div><a href="index.php?user=0">' . t('Registration') . '</a></div></form></div>';
  }
  elseif (in_array(4, $_SESSION['user']['rid'])) {
    $output = '<h3>' . t('You profile is blocked') . '</h1>';
  }
  else {
    $output = '';
    if (user_access(2))
      $output .= '<li><a href="index.php?id=create">' . t('Create content') . '</a></li>';
    if (user_access(7))
      $output .= '<li><a href="index.php?tr=edit">' . t('Edit translate') . '</a></li>';
    if (user_access(4))
      $output .= '<li><a href="index.php?user=0">' . t('Add of new user') . '</a></li>';
    if (user_access(9))
      $output .= '<li><a href="index.php?user=all">' . t('List of users') . '</a></li>';
    if (user_access(11))
      $output .= '<li><a href="index.php?st=all">' . t('List of static page') . '</a></li>';
    if (user_access(8))
      $output .= '<li><a href="index.php?perms=edit">' . t('Edit permissions') . '</a></li>';
    if ($output) {
      $output = '<h3>' . t('Menu') . '</h3><ul>' . $output . '</ul>';
    }
  }
  return $output;
}

function page_created() {
  if (isset($_SESSION['message'])) {
    echo "<div class='message'>" . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
  }
  include 'config.php';

  if (isset($_GET['id'])) {
    if (is_numeric($_GET['id'])) {
      $id = $_GET['id'];
      $lang = tt();

      if ($article = article_view($id, $lang)) {
        echo $article;
        echo comments_load($id);
      }
    }
    elseif ($_GET['id'] == 'create' && isset($_SESSION['user'])) {
      article_edit('create');
    }
    else {
      echo 'id error';
    }
  }
  elseif ($_GET['edit']) {
    if (is_numeric($_GET['edit'])) {
      $id = $_GET['edit'];
      if (isset($_GET['add_field'])) {
        article_edit('add_lang', $id);
      }
      else {
        article_edit('edit', $id);
      }
    }
    else {
      echo 'edit_id error';
    }
  }
  elseif ($_GET['st']) {
    if (is_numeric($_GET['st'])) {
      $id = $_GET['st'];
      if (isset($_GET['op'])) {
        $op = $_GET['op'];
        static_page_edit($op, $id);
      }
      else {
        $lang = tt();
        echo static_page_view($id, $lang);
      }
    }
    elseif (user_access(11)) {
      echo static_page_list();
    }
    else {
      echo 'st_id error';
    }
  }
  elseif ($_GET['user'] || is_numeric($_GET['user'])) {
    if (is_numeric($_GET['user'])) {
      $uid = $_GET['user'];
      include 'user.php';
    }
    elseif ($_GET['user'] == 'all') {
      echo user_list();
    }
    else {
      echo 'user_id error';
    }
  }
  elseif ($_GET['tr']) {
    include 'translate.php';
  }
  elseif ($_GET['perms']) {
    echo user_permission();
  }
  elseif ($_GET['comment']) {
    if (is_numeric($_GET['comment']) && $_GET['op'] == 'edit' && is_numeric($_GET['aid'])) {
      if (isset($_SESSION['comments'][$_GET['comment']]) || user_access(6)) {
        echo comment_form($_GET['aid'], $_GET['comment']);
      }
      else {
        echo "<h1>access blocked</h1>";
      }
    }
    elseif ($_GET['comment'] == 'create' && is_numeric($_GET['aid'])) {
      echo comment_form($_GET['aid']);
    }
    else {
      echo 'comment_info error';
    }
  }
  elseif ($_GET['tr']) {
    include 'translate.php';
  }
  else {
    $pager_limit = 4;
    if (isset($_GET['page']) && is_numeric($_GET['page'])):
      $page = $_GET['page'];
    else:
      $page = 1;
    endif;
    $id_min = ($page - 1) * $pager_limit;
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM article LIMIT $id_min, $pager_limit";
    $lang = tt();
    foreach ($dbh->query($sql) as $row) {
      echo article_view($row['id'], $lang, true);
    }
    $sql = "SELECT FOUND_ROWS()";
    $count_article = $dbh->query($sql)->fetchColumn();
    $page_all = round(($count_article + 1) / $pager_limit);

    //echo "page_all - $page_all, count_article - $count_article, id_min - $id_min <br/>";
    if ($page_all > 1) {
      echo '<div class="pager"><a href="index.php">' . t('First') . ' </a>';
      for ($x = 0; $x++ < $page_all;) {
        if ($x == $page) : echo $x . ' ';
        else: echo "<a href='index.php?page=$x'>$x</a> ";
        endif;
      }
      echo '<a href="index.php?page=' . $page_all . '"> ' . t('Last') . ' </a>';
      echo '</div>';
    }
  }
}

function gen_access_form() {
  global $self;
  $access = md5(time()) . substr($self, 25, 5);
  return $access;
}

function gen_access_form_add_input() {
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  $output = '<input type="hidden" name="access" value="' . $access . '"/>';
  return $output;
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
    global $dbh;
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
  if ($uid) {
    $rid = user_rid();
    if (in_array(BLOCKED_USER, $rid)) {
      echo "<h1>access denied. Your profile is blocked.</h1>";
      return;
    }
    if ($uid == $_SESSION['user']['uid'] || in_array(3, $rid)) {
      global $dbh;
      $sql = "SELECT * FROM users WHERE uid='$uid'";
      $user = $dbh->query($sql)->fetch();
      $_SESSION['user_form'] = $user;
      ?>
      <h1><?php echo t('Edit of profile of user'); ?> "<?php echo $user['login']; ?>"</h1>
      <form name="edit-user" action="user.php" method="post" enctype="multipart/form-data">
          <div class="avatar"><img id="ava" src="img/avatars/<?php echo $user['avatar'] ? : 'avatar.jpeg'; ?>" width="130" height="110" />
              <p align="center"><label for="avatar_label" id="photo"><?php echo t('add photo'); ?></label></p>
              <input name="fupload" id="fupload" class="fld" type="file"></div>
              <?php echo gen_access_form_add_input(); ?>
          <input type="hidden" name="uid" value="<?php echo $user['uid']; ?>"/>
          <span class="pre-input"><?php echo t('Login'); ?></span><input name="login" value="<?php echo $user['login']; ?>" type="text" /><br/>
          <span class="pre-input"><?php echo t('Password'); ?></span><input name="pass" type="password" /><br/>
          <span class="pre-input"><?php echo t('Repeat'); ?></span><input name="repeat" type="password" onchange="pass_repeat(this.form)"/><br/>
          <span class="pre-input"><?php echo t('Name'); ?></span><input name="name" value="<?php echo $user['name']; ?>" type="text" /><br/>
          <span class="pre-input"><?php echo t('Surname'); ?></span><input name="lastname" value="<?php echo $user['lastname']; ?>" type="text" /><br/>
          <span class="pre-input">E-mail</span><input name="mail" value="<?php echo $user['mail']; ?>" type="email" /><br/>
          <span class="pre-input"><?php echo t('About me'); ?></span><br/><textarea name="info_en" rows="3"><?php echo $user['info_en']; ?></textarea><br/>
          <span class="pre-input"><?php echo t('Про себе (Ukrainian: about me)'); ?></span><br/><textarea name="info_ua" rows="3"><?php echo $user['info_ua']; ?></textarea><br/>
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
                        document.getElementById('photo').innerHTML = '<?php echo t('edit photo'); ?>';
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
    echo "<h1>" . t('Registration of new user') . "</h1>";
    ?>

    <form name="create-user" action="user.php" method="post" onsubmit="return checkForm(this)">
        <?php echo gen_access_form_add_input(); ?>
        <span class="pre-input"><?php echo t('Login'); ?> </span><input name ="login" type="text" /><br/>
        <span class="pre-input"><?php echo t('Password'); ?></span><input name="pass" type="password" /><br/>
        <span class="pre-input"><?php echo t('Repeat'); ?></span><input name="repeat" type="password" onchange="pass_repeat(this.form)"/><br/>
        <span class="pre-input">E-mail</span><input name="mail" type="email" /><br/>
        <input value="<?php echo t('Save'); ?>" name="add" type="submit" />
    </form> 
    <?php
          print ('<script type="text/javascript">
  function pass_repeat(form) {
    if (form.pass.value != form.repeat.value) {
      form.repeat.value = \'\';
      alert("' . t('password != repeat') . '");
    }
  }
 </script>');
  print ('<script type="text/javascript">
  function checkForm(form){
	var output = [];
	if (/^[A-Za-z0-9]{3,}$/.test(form.login.value) === false)
		{output.push(\'' . t('Login') . '\');}
	if (/^\w{5,25}$/.test(form.pass.value) === false)
		{output.push(\'' . t('Password') . '\');}
	if (form.pass.value != form.repeat.value) 
		{form.repeat.value = \'\'; output.push(\'' . t('error repeat') . '\');}
	if (/^[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?[\ .A-Za-z0-9]{2,}$/.test(form.mail.value) === false)
		{output.push(\'Error e-mail\');}
      alert(output);    
	if (output.length == 0)
		{return true;}	
	else 
		{alert(\'' . t('Error form') . '\' + output.join(\', \')); return false;}
}</script>');
  }
}

function user_page($uid) {
  if ($uid) {
    $rid = user_rid();
    if ($rid) {
      user_lasttime();
      if (in_array(4, $rid)) {
        echo "<h1>" . t('access denied. Your profile is blocked.') . "</h1>";
        return;
      }
      if ($uid == $_SESSION['user']['uid'] || in_array(3, $rid)) {
        global $dbh;
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
        echo "<div class='login'><strong>" . t('Login') . ':</strong> ' . $user['login'] . "</div>";
        echo "<div class='mail'><strong>" . t('E-mail') . ':</strong> ' . $user['mail'] . "</div>";
        if ($user['name'])
          echo "<div class='name'><strong>" . t('Name') . ':</strong> ' . $user['name'] . "</div>";
        if ($user['lastname'])
          echo "<div class='surname'><strong>" . t('Surname') . ':</strong> ' . $user['lastname'] . "</div>";
        $created = date('d.m.Y G:i', $user['created']);
        echo "<div class='date'><strong>" . t('Time created') . ':</strong> ' . $created . "</div>";
        $login_time = date('d.m.Y G:i', $user['login_time']);
        echo "<div class='date'><strong>" . t('Last time') . ':</strong> ' . $login_time . "</div>";
        $lang = tt();
        if ($user['info_' . $lang])
          echo "<div class='info'><strong>" . t('Info') . ':</strong> ' . $user['info_' . $lang] . "</div>";
        echo "<div class='link'><a href='index.php?user=$uid&op=edit'>" . t('Edit profile') . "</a></div>";
      }
      else {
        echo "<h1>access denied</h1>";
      }
    }
    else {
      echo "<h1>access denied</h1>";
    }
  }
  else {
    echo "<h1>access denied</h1>";
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
  global $dbh;
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
      global $self;
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
  global $dbh;
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

function load_field_view($type, $id, $lang, $teaser = array('field' => 'body', 'max' => 150), $sovpad = false) {
  global $dbh;
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
  global $dbh;
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
  global $dbh;
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

function article_save($post) {
  $article = isset($_SESSION['article']) ? $_SESSION['article'] : '';
  unset($_SESSION['article']);
  global $dbh;
  if ($article) {
    $id = (integer) $article['id'];
    if ($_FILES['fupload']['name']) {
      include ("avatar.php");
      $filename = time();
      $avatar = create_avatar($filename);
      $sth = $dbh->prepare('UPDATE article SET photo=? WHERE id=?');
      $sth->execute(array($avatar, $id));
    }
    foreach ($article['fields'] as $lang => $value) {
      if (empty($post['title_' . $lang]) || empty($post['lang_' . $lang]) || empty($post['body_' . $lang])) {
        $sql = "DELETE FROM fields WHERE type='article' and id_type = '$id' and lang='$lang'";
        $count = $dbh->exec($sql);
        continue;
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
  global $dbh;
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
    'photo' => $article['photo'],
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
    $rat = article_rating($id, 'rating-teaser');
    $img = $article['photo'] ? "<img src='img/avatars/{$article['photo']}' />" : '<img src="img/avatars/nofoto.jpg" />';

    $created = date('d.m.Y', $article['created']);
    $output .= "<div class='block-teaser-width'><div class='block-teaser'><div class='avatar-teaser'>" . $img . "</div>"
        . "<h5><a href='index.php?id=$id'>{$article['fields']['title']}</a></h5>"
        . "<div class='autor'>{$article['autor']}</div>"
        . "<div class='date'>$created</div><br/>" . $rat
        . "<div class='contetnt-text'>{$article['fields']['teaser']}</div>"
        //    . "<div class='like'>" . t('good') . '-' . $article['lkup'] . ', ' . t('bad') . '-' . $article['lkdown'] . "</div>"
        . "<div class='more'><a href='index.php?id=$id'>" . t('Read More') . "</a></div>"
        . "</div></div>";
  }
  else {
    $img = $article['photo'] ? "<a href='img/original/{$article['photo']}'><img src='img/original/{$article['photo']} ' width='200' /></a>" :
        '<img src="img/avatars/nofoto.jpg" />';
    $rat = article_rating($id, 'rating-sum');
    $created = date('d.m.Y', $article['created']);
    $output .="<h1>{$article['fields']['title']}</h1>"
        . "<div class='avatar-views'>" . $img . "</div>"
        . "<div class='autor'>{$article['autor']}</div>"
        . "<div class='date'>$created</div><br/>" . $rat
        . "<div class='contetnt-text'>{$article['fields']['body']}</div>"
        . "<div class='like'><form name='like' action='like.php' method='post'>"
        . "<input type='hidden' name='id' value='" . $id . "'/>"
        . '<input value="' . t('good') . '" name="good" type="submit" /> ' . $article['lkup'] . ', '
        . '<input value="' . t('bad') . '" name="bad" type="submit" />' . '-' . $article['lkdown'] . "</form></div>";
    if (user_access(1)) //prava
      $output .="<div id='edit'><a href='index.php?edit=$id'>edit</a></div>";
    if (user_access(3))
      $output .= "<div id='delete'><a href='delete.php?id=$id&type=article'>delete</a></div>";
    if (isset($_SESSION['user']))
      $output .= article_like_views_rat($id);
  }
  return $output;
}

function article_edit($op, $id = null) {
  if ($op == 'create') {
    echo '<h1>' . t('Create content') . '</h1>';
  }
  else {
    echo '<h1>' . t('Edit content') . '</h1>';
  }
  echo '<form name="article" action="article.php" method="post"  enctype="multipart/form-data">';
  echo gen_access_form_add_input();
  echo '<input type="hidden" name="type" value="article-edit"/>';
  echo '<input type="hidden" name="id" value="' . $id . '"/>';
  switch ($op) {
    case 'edit':case 'add_lang':
      if (is_numeric($id)) {
        global $dbh;
        $sql = "SELECT * FROM article WHERE id=$id";
        $article = $dbh->query($sql)->fetch();
        $fields = load_field_edit('article', $id);
        $_SESSION['article'] = array('id' => $id, 'fields' => $fields, 'user' => $article['user'], 'created' => $article['created']);
        foreach ($fields as $key => $value) {
          echo '<div class="lang-article">';
          echo t('Title') . '<input name="title_' . $key . '" type="text" value="' . $value['title']['text'] . '"/><br/>';
          echo t('Lang') . '<input name="lang_' . $key . '" type="text" value="' . $key . '"/><br/>';
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
  $img = empty($article['photo']) ? 'nofoto.jpg' : $article['photo'];
  echo '<div class="avatar"><img id="ava" src="img/avatars/' . $img . '" width="130" height="110" />
       <p align="center"><label for="avatar_label" id="photo">' . t('add photo') . '</label></p>
       <input name="fupload" id="fupload" class="fld" type="file"></div>';
  echo '<input value="' . t('Add lang') . '" name="add_lang" type="submit" />';
  echo '<input value="' . t('Save') . '" name="save" type="submit" /></form>';
  echo '<script>
    function SketchFileSelect(evt) {
      var files = evt.target.files;
      f = files[0];
      if (f.type.match(\'image.*\')) {
        var reader = new FileReader();
        reader.onload = (function (theFile) {
          return function (e) {
            document.getElementById(\'ava\').src = e.target.result;
            document.getElementById(\'photo\').innerHTML = ' . t('edit photo') . '\';
            return false;
          };
        })(f);
        reader.readAsDataURL(f);
      }
    }
    document.getElementById(\'fupload\').addEventListener(\'change\', SketchFileSelect, false);
   </script>';
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
  $output = '<div id="comments"><h3>' . t('Comments') . '</h3>';
  if (is_numeric($aid)) {
    global $dbh;
    $lang = tt();
    $perm_comments = user_access(6);
    $sql = "SELECT * FROM comments WHERE id_article='$aid' and lang='$lang'";
    foreach ($dbh->query($sql) as $row) {
      $output .= comment_render($row, $perm_comments);
    }
  }
  if (isset($_SESSION['user']))
    $output .= '<a href="index.php?comment=create&aid=' . $aid . '">' . t('Add comment') . '</a></div>';
  return $output;
}

function comment_render($row, $permission = false) {
  $row['theme'] = empty($row['theme']) ? text_short($row['body'], 15) : $row['theme'];
  $created = date('d.m.Y G:i', $row['created']);
  $edit = $permission ? "<div class='link'>" . l(t('Edit'), array('comment' => $row['cid'], 'op' => 'edit', 'aid' => $row['id_article'])) . "</div>" : '';
  $output = '<div class="comment-header"><h4>' . $row['theme'] . '</h4>'
      . "<div class='date'>$created</div>"
      . "<div class='autor'><strong>" . $row['user'] . '</strong> ' . t('says') . "</div>"
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
  $theme = isset($com['theme']) ? ' value="' . $com['theme'] . '"' : '';
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
  $access = gen_access_form();
  $_SESSION['access_form'] = $access;
  global $dbh;
  $sql = "SELECT * FROM permissions";
  $perm_name = $dbh->query($sql)->fetchAll();
  $p_name = array();
  foreach ($perm_name as $value)
    $p_name[$value['pid']] = $value['permission'];
  $sql = "SELECT * FROM roles";
  $roles = $dbh->query($sql)->fetchAll();
  $r_name = array();
  foreach ($roles as $value)
    $r_name[$value['rid']] = $value['roles'];
  $sql = "SELECT * FROM roles_perm";
  $roles_perm = $dbh->query($sql)->fetchAll();
  $r_p = array();
  foreach ($roles_perm as $value)
    $r_p[$value['rid']][$value['pid']] = 1;
  $output = '<form name="perm" action="perm.php" method="post">'
      . '<input type="hidden" name="access" value="' . $access . '"/>'
      . '<table id="perms"><tr><th>\</th>';
  foreach ($r_name as $value)
    $output .='<th>' . t($value) . '</th>';
  $output .= '</tr>';
  $r_p1 = array();
  foreach ($p_name as $key => $value) {
    $output .= '<tr><th>' . t($value) . '</th>';
    foreach ($r_name as $key1 => $value1) {
      $ch = isset($r_p[$key1][$key]) ? ' checked' : '';
      $output .='<td><input type="checkbox"' . $ch . ' name="perms[' . $key . '_' . $key1 . ']"/></td>';
    }
    $output .= '</tr>';
  }
  $output .= '</table><input value="' . t('Save') . '" name="save" type="submit" /></form></div>';
  return $output;
}

function user_permission_save($post) {
  global $dbh;
  $sql = "DELETE FROM roles_perm";
  $count = $dbh->exec($sql);
  foreach ($post['perms'] as $key => $value) {
    $var = explode('_', $key);
    $sth = $dbh->prepare('INSERT roles_perm SET rid=?,pid=?');
    $sth->execute(array($var[1], $var[0]));
  }
}

function article_rating($aid, $class = 'rating') {
  global $dbh;
  $sql = "SELECT vot_users, vot_sum FROM article WHERE id='$aid'";
  $rating = $dbh->query($sql)->fetch();
  if (empty($rating['vot_users'])) {
    $output = '<div class="' . $class . '">' . t('No rating') . '</div>';
  }
  else {
    $output = '<div class="' . $class . '">' . t('voice') . ' ' . $rating['vot_users'] . ', ' . t('avegete') . ' ' . round($rating['vot_sum'] / $rating['vot_users'], 1) . '</div>';
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
    $output = "<div class='rating'>" . t('Your grade of article - ') . $rating . "<form name='rating' action='like.php' method='post'>"
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

function static_page_edit($op, $id = null) {
  echo '<h1>' . t('Edit static page') . '</h1>';
  echo '<form name="static-page" action="article.php" method="post">';
  echo '<input type="hidden" name="type" value="static-page-edit"/>';
  echo gen_access_form_add_input();
  echo '<input type="hidden" name="id" value="' . $id . '"/>';
  switch ($op) {
    case 'edit':case 'add_lang':
      if (is_numeric($id)) {
        global $dbh;
        $fields = load_field_edit('static', $id);
        $_SESSION['static'] = array('id' => $id, 'fields' => $fields);
        foreach ($fields as $key => $value) {
          echo '<div class="lang-static">';
          echo t('Lang') . '<input name="lang_' . $key . '" type="text" value="' . $key . '"/><br/>';
          echo '<strong>' . t('Body') . '</strong><br><textarea name="body_' . $key . '" rows="8">' . $value['body']['text'] . '</textarea><hr/></div>';
        }
      }
      if ($op == 'edit' && $fields)
        break;
      echo '<div class="add-lang">';
      echo t('Lang') . '<input name="lang_new" type="text" /><br/>';
      echo '<strong>' . t('Body') . '</strong><br><textarea name="body_new" rows="8"></textarea></div>';
      break;
    default:
      break;
  }
  echo '<input value="' . t('Add lang') . '" name="add_lang" type="submit" />';
  echo '<input value="' . t('Save') . '" name="save" type="submit" /></form>';
}

function static_page_view($id, $lang) {
  $fields = load_field_view('static', $id, $lang);
  $output = $fields['body'];
  return $output;
}

function static_page_save($post) {
  $article = isset($_SESSION['static']) ? $_SESSION['static'] : '';
  unset($_SESSION['static']);
  global $dbh;
  $id = (integer) $article['id'];
  foreach ($article['fields'] as $lang => $value) {
    if (empty($post['lang_' . $lang]) || empty($post['body_' . $lang])) {
      $sql = "DELETE FROM fields WHERE type='static' and id_type = '$id' and lang='$lang'";
      $count = $dbh->exec($sql);
      continue;
    }
    foreach ($value as $name_field => $value1) {
      if ($post[$name_field . '_' . $lang] != $value1['text']) {
        $name = $post[$name_field . '_' . $lang];
        save_field_prepare('static', $id, $lang, $name_field, $name, $value1['id']);
      }
    }
  }
  if (isset($post['lang_new']) && isset($post['body_new'])) {
    $lang = var_user('lang', $post['lang_new'], true);
    $body = $post['body_new'];
    if ($lang && $body) {
      save_field_prepare('static', $id, $lang, 'body', $body);
    }
  }
  return $id;
}

function static_page_list_link($id, $text) {
  $output = '<a href=index.php?st=' . $id . '>' . $text . '</a>'
      . '(<a href=index.php?st=' . $id . '&op=edit>' . t('Edit') . '</a>)';
//   . '(<a href=index.php?st='.$id.'&op=delete>'.t('Delete').'</a>';
  return $output;
}

function static_page_list() {
  $output = static_page_list_link(1, t('Aphorism')) . '<br/>'
      . static_page_list_link(2, t('About')) . '<br/>'
      . static_page_list_link(3, t('Contact')) . '<br/>'
      . '';

  return $output;
}
