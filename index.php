<?php
session_start();
include 'user_func.php';
?>
<!doctype html>
<html><head>
        <meta charset="utf-8">
        <title><?php echo t('Home'); ?></title>
        <link type="text/css" rel="stylesheet" href="style.css">
    </head>
    <body>
        <div id="site">
            <div id="top">
                <img src="logo.jpeg"/>
                <div id="site-name"><a href="index.php"><?php echo t('Site of car'); ?></a></div>
            </div>
            <div id="left">
                <div id="login">
                    <?php if (!isset($_SESSION['user'])) : ?>
                      <form name="authrization" action="us.php" method="post">
                          <?php echo t('Login'); ?><input name="login" type="text" />
                          <?php echo t('Password'); ?><input name="pass" type="password" />
                          <input value="signin" name="submit" type="submit" />
                          <a href='index.php?user=0'><?php echo t('Registration'); ?></a>
                      </form>
                    <?php else : ?>
                      <div id="login-yes"><?php echo t('Hi user'); ?> "<?php echo $_SESSION['user']['login']; ?>"</div>
                      <form name="log-out" action="us.php" method="post">
                          <input value="signout" name="submit" type="submit" />
                      </form>
                      <a href='index.php?user=<?php echo $_SESSION['user']['uid']; ?>'><?php echo t('My profile'); ?></a>
                      <?php
                      if (in_array(4, $_SESSION['user']['rid'])) {
                        echo "<h1>" . t('You profile is blocked') . "</h1>";
                      }
                      else {
                        if (in_array(2, $_SESSION['user']['rid']) || in_array(3, $_SESSION['user']['rid'])) {
                          echo "<br/><a href='index.php?id=create'>" . t('Create content') . "</a>";
                          echo "<br/><a href='index.php?tr=edit'>" . t('Edit translate') . "</a>";
                        }
                        if (in_array(3, $_SESSION['user']['rid'])) {
                          echo "<br/><a href='index.php?user=0'>" . t('Add of new user') . "</a>";
                          echo "<br/><a href='index.php?user=all'>" . t('List of users') . "</a>";
                        }
                      }
                    endif;
                    ?>
                </div>
                <div id="lang">
                    <form name="lang" action="lang.php" method="post">
                        <input type="image" src="img/ua.png" name="ua" value="ua">
                        <input type="image" src="img/en.png" name="en" value="en">
                    </form>    

                </div>
            </div>
            <div id="content">
                <?php
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
                else {
                  $pager_limit = 5;
                  if (isset($_GET['page']) && is_numeric($_GET['page'])):
                    $page = $_GET['page'];
                  else:
                    $page = 1;
                  endif;
                  $id_min = ($page - 1) * $pager_limit;
                  $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM article WHERE id > $id_min LIMIT $pager_limit";
                  $lang = tt();
                  foreach ($dbh->query($sql) as $row) {
                    echo article_view($row['id'], $lang, true);
                  }
                  $sql = "SELECT FOUND_ROWS()";
                  $count_article = $dbh->query($sql)->fetchColumn();
                  $page_all = round(($count_article + $id_min) / $pager_limit);

                  //echo "page_all - $page_all, count_article - $count_article,  id_min - $id_min <br/>";
                  if ($page_all > 1) {
                    echo '<br/>';
                    for ($x = 0; $x++ < $page_all;) {
                      if ($x == $page) : echo $x . ' ';
                      else: echo "<a href='index.php?page=$x'>$x</a> ";
                      endif;
                    }
                  }
                }
                $dbh = null;
                ?>  
            </div>
        </div>
    </body>
</html>

