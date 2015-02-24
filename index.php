
<?php
session_start();
?>
<!doctype html>
<html><head>
        <meta charset="utf-8">
        <title>Home</title>
        <link type="text/css" rel="stylesheet" href="style.css">
    </head>
    <body>
        <div id="site">
            <div id="top">
                <img src="logo.jpeg"/>
                <div id="site-name"><a href="index.php">Site of car</a></div>
            </div>
            <div id="left">
                <div id="login">
                    <?php if (!isset($_SESSION['user'])) : ?>
                      <form name="authrization" action="us.php" method="post">
                          Login<input name="login" type="text" />
                          Password<input name="pass" type="password" />
                          <input value="signin" name="submit" type="submit" />
                      </form>
                    <?php else : ?>
                      <div id="login-yes">You are already<br/>logged in</div>
                      <form name="log-out" action="us.php" method="post">
                          <input value="signout" name="submit" type="submit" />
                      </form>
                      <a href='index.php?id=create'>Create content</a>
                    <?php endif; ?>
                </div>
            </div>
            <div id="content">
                <?php
                $dbh = new PDO('mysql:host=localhost;dbname=step1', 'root', '2');
                //print_r($_GET);
                if (isset($_GET['id'])) {
                  if (is_numeric($_GET['id'])) {
                    $id = $_GET['id'];
                    $sql = "SELECT * FROM article WHERE id='$id'";
                    foreach ($dbh->query($sql) as $row) {
                      $created = date('d.m.Y', $row['created']);
                      print("<h1>{$row['title']}</h1>"
                          . "<div class='autor'>{$row['user']}</div>"
                          . "<div class='date'>$created</div>"
                          . "<div class='contetnt-text'>{$row['body']}</div>");
                      if (isset($_SESSION['user']))
                        print("<a href='index.php?edit=$id'>edit</a><br/>"
                            . "<a href='delete.php?id=$id'>delete</a>");
                    }
                  } elseif ($_GET['id'] == 'create' && isset($_SESSION['user'])) {
                    // print_r($_SESSION);
                    include 'create.php';
                  }
                  else {
                    echo 'id error';
                  }
                }
                elseif ($_GET['edit']) {
                  if (is_numeric($_GET['edit'])) {
                    $id = $_GET['edit'];
                    include 'edit.php';
                  }
                  else {
                    echo 'edit_id error';
                  }
                }
                else {
                  $pager_limit = 7;
                  if (isset($_GET['page']) && is_numeric($_GET['page'])):
                    $page = $_GET['page'];
                    $id_max = $page * $pager_limit + 1;
                    $id_min = $id_max - $pager_limit;
                    $page_print[1] = $page == 1 ? 1 : "<a href='index.php?page=$p'>$p</a>";
                  else:
                    $page = 1;
                    $id_max = $pager_limit + 1;
                    $id_min = 0;
                    $page_print[1] = "1";
                  endif;


                  $i = 0;
                  $sql = "SELECT * FROM article";
                  foreach ($dbh->query($sql) as $row) {
                    $i++;
                    if ($i < $id_max && $i >= $id_min) {
                      $created = date('d.m.Y', $row['created']);
                      $body = strlen($row['body']) < 6 ? $row['body'] : substr($row['body'], 0, 6) . '...';
                      print("<div class='block-teaser'><h1><a href='index.php?id={$row['id']}'>{$row['title']}</a></h1>"
                          . "<div class='autor'>{$row['user']}</div>"
                          . "<div class='date'>$created</div>"
                          . "<div class='contetnt-text'>$body</div>"
                          . "<div class='more'><a href='index.php?id={$row['id']}'>Read More</a></div></div>");
                    }
                    else {
                      $p = round($i / $pager_limit) + 1;
                      $page_print[$p] = $p == $page ? $p : "<a href='index.php?page=$p'>$p</a>";
                    }
                  }
                  if (count($page_print) > 1):
                    echo '<br/>' . implode(' ', $page_print);
                  endif;
                }
                $dbh = null;
                ?>  
            </div>
        </div>
    </body>
</html>

