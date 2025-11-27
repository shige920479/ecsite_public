<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>esicte</title>
    <base href="<?= BASE_URL?>">
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <script>
      window.BASE_PATH = '<?= PATH;?>';
      window.CSRF_TOKEN = '<?= h($csrf_token); ?>';
    </script>
  </head>
  <body>
    <header id="header" >
      <div class="header-wrapper">
        <h1 class="site-title">
          <?php $confirmAtrr = ! empty($is_confirm) ? 'data-confirm="leave-confirm"' : '';?>
            <a href="<?= PATH . '/owner/home';?>" class="with-confirm" <?= $confirmAtrr;?>>
              <img src="images/logo.png" alt="" />
            </a>
        </h1>
        <nav id="navi">
          <ul id="owner-nav-menu">
            <li class="nav-items">
              <a href="<?= PATH . "/owner/home";?>" class="with-confirm" <?= $confirmAtrr;?>>店舗情報</a>
            </li>
            <li class="nav-items">
              <a href="<?= PATH . '/owner/items';?>" class="with-confirm" <?= $confirmAtrr;?>>商品管理</a>
            </li>
            <li class="nav-items">
              <a href="<?= PATH . '/owner/item/create';?>" class="with-confirm" <?= $confirmAtrr;?>>商品登録</a>
            </li>
          </ul>
        </nav>
        <form action="<?= PATH . '/session/clearRedirect' ?>" id="leave-confirm-form" method="POST" style="display:none;">
          <input type="hidden" name="redirect" value="">
          <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
        </form>
        <?php $isOwnerLoggined = isset($_SESSION['owner']['id']) && isset($_SESSION['owner']['name']) ? true : false; ?>
        <?php if($isOwnerLoggined):?>
          <form action="<?= PATH . '/owner/logout';?>" method="post">
            <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
            <div id="logout-box">
              <div class="header-icon logout"><img src="images/logout.png" alt="" /></div>
              <div class="header-icon-text">ログアウト</div>
            </div>
          </form>
        <?php endif;?>
      </div>
    </header>
    <div id="information">
      <?= $isOwnerLoggined ? '<i>オーナーページにログインしました</i>' : '<i>オーナーページです ログインしてください</i>';?>
    </div>
    