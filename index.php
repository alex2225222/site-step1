<?php
session_start();

include 'user_func.php';
include 'config.php';

$lang = tt();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo t('Test my site about trucks'); ?></title>
        <link href="style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div id="page">
            <div id="wrapper">
                <header id="branding">
                    <h1 id="site-title"> 
                        <a href="index.php" title="Home"><?php echo t('Site about trucks'); ?></a> 
                    </h1>
                    <div class="social">
                        <div class="widget_search">
                            <form method="get" class="search-form">
                                <input type="submit" value="Search" class="search-submit-button" />
                                <input type="text" value="Search" onfocus="this.value = ''" onblur="this.value = 'Search'" name="s" class="search-text-box" />
                            </form> 
                        </div>
                        <ul>
                            <li><a href="#" target="_blank"><img src="img/icon-rss.png" alt="RSS" /></a></li>
                            <li><a href="#" target="_blank"><img src="img/icon-facebook.png" alt="Facebook" /></a></li>
                            <li><a href="#" target="_blank"><img src="img/icon-twitter.png" alt="Twitter" /></a></li>
                            <li><a href="#" target="_blank"><img src="img/icon-dribbble.png" alt="Dribbble" /></a></li>
                            <li><a href="#" target="_blank"><img src="img/icon-linkedin.png" alt="LinkedIn" /></a></li>
                            <li><a href="#" target="_blank"><img src="img/icon-pinterest.png" alt="Pinterest" /></a></li>
                        </ul>
                    </div>
                    <nav id="main-nav" class="main-mav">
                        <div id="menu" class="menu">
                            <ul id="tiny">
                                <li><a href="index.php">Home</a></li>
                                <li><a href="index.php?st=2">About</a></li>
                                <li><a href="index.php?st=3">Contact</a></li>
                            </ul>
                        </div>
                        <div id="user" class="menu">
                            <?php if (isset($_SESSION['user'])) : ?>
                              <div id="hi"><p><?php echo t('Hi user') . ' "' . $_SESSION['user']['login']; ?>"</p></div>
                              <div id="ultiny">
                                  <ul id="tiny">
                                      <li><a href="index.php?user=<?php echo $_SESSION['user']['uid']; ?>"><?php echo t('My profile'); ?></a></li>
                                  </ul>
                              </div>
                              <form name="log-out" action="us.php" method="post">
                                  <input type="image" src="img/logout.png" name="submit" value="<?php echo t('signout'); ?>">
                              </form>
                            <?php endif; ?>
                            <div id="lang">
                                <form name="lang" action="lang.php" method="post">
                                    <input type="image" src="img/ua.png" name="ua" value="ua"/>
                                    <input type="image" src="img/en.png" name="en" value="en"/>
                                </form>    
                            </div>
                        </div>
                        <div class="triangle-l"></div>
                        <div class="triangle-r"></div>
                    </nav>
                    <div id="aphorism-block"><img src="img/avtopark.jpg"  class="avtopark"/>
                        <div class="avtopark-text"><div class="aforizm-text"><?php echo static_page_view(1, $lang); ?></div></div>
                    </div>
                </header>
                <div id="main-nav-line" class="add-aphorism-block">
                    <div class="triangle-l triangle-add-l"></div>
                    <div class="triangle-r triangle-add-r"></div>              
                </div>
                <div id="main-block">
                    <div class="sitebar">
                        <?php echo menu_created(); ?>
                    </div>                
                    <div class="content">
                        <?php echo page_created(); ?>
                    </div>                

                </div>
                <div id="main-nav-line">
                    <div class="triangle-l triangle-add-l"></div>
                    <div class="triangle-r triangle-add-r"></div>
                </div>
                <div id="footer">
                    <?php echo t('My code'); ?>
                </div>
            </div>
        </div>
    </body>
</html>

