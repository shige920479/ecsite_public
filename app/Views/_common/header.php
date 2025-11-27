<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>esicte</title>
    <base href="<?= BASE_URL;?>">
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script>
      window.BASE_PATH = '<?= PATH;?>';
      window.CSRF_TOKEN = '<?= h($csrf_token); ?>';
    </script>
  </head>
  <body>
    <header id="header">
      <div class="header-wrapper">
        <h1 class="site-title">
          <a href="<?= PATH . '/';?>">
            <img src="./images/logo.png" alt="" />
          </a>
        </h1>
        <div id="search-box">          
          <?php $categoryPath = !empty($categoryPath) ? h($categoryPath) : ''; ?>
          <form action="<?= PATH . "/" . $categoryPath; ?>">
            <input id="search-input" name="item_search" type="text" value="<?= isset($item_search) ? h($item_search) : '' ;?>"/>
            <button id="search-btn"><img src="images/search.png" alt="" /></button>
            <input type="hidden" name="per_page" value="<?= isset($perPage) ? h($perPage) : '';?>">
            <input type="hidden" name="item_select" value="<?= isset($item_select) ? h($item_select) : ''; ?>">
            <input type="hidden" name="page" value="1">
          </form>
        </div>
        <?php if(isset($_SESSION['user'])) :?>
        <div class="login-user-name">
          <span><?= $_SESSION['user']['name'] ?? '' ?> 様</span>
        </div>
        <?php endif;?>
        <nav id="navi">
          <ul class="nav-menu">
            <li>
              <?php if(empty($_SESSION['user'])):?>
                <a href="<?= PATH . '/login'; ?>">
                  <div class="header-icon"><img src="images/logindoor.png" alt="" /></div>
                  <div class="header-icon-text">ログイン</div>
                </a>
              <?php elseif(isset($_SESSION['user'])):?>
                <form action="<?= PATH . '/logout';?>" method="post">
                  <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
                  <div id="logout-box">
                    <div class="header-icon logout"><img src="images/logout.png" alt="" /></div>
                    <div class="header-icon-text">ログアウト</div>
                  </div>
                </form>
              <?php endif;?>
            </li>
            <li>
              <a href="<?= PATH . '/favorite';?>">
                <div class="header-icon"><img src="images/hartmark.png" alt="" /></div>
                <div class="header-icon-text">お気に入り</div>
              </a>
            </li>
            <li>
              <a href="<?= PATH . '/cart';?>">
                <div class="header-icon"><img src="images/cart.png" alt="" /></div>
                <div class="header-icon-text">カート</div>
              </a>
            </li>
            <li>
              <a href="<?= PATH . '/';?>">
                <div class="header-icon"><img src="images/hamburger.png" alt="" /></div>
                <div class="header-icon-text">メニュー</div>
              </a>
            </li>
          </ul>
        </nav>
        </div>
    </header>
    <div id="information"><i>ただいまセール中</i></div>