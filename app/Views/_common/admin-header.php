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
  </head>
  <body>
    <header id="header">
      <div class="header-wrapper">
        <h1 class="site-title">
          <a href="<?= PATH . '/admin/home'; ?>">
            <img src="./images/logo.png" alt="" />
          </a>
        </h1>
        <nav id="navi">
          <ul id="owner-nav-menu">
            <li class="nav-items"><a href="<?= PATH . '/admin/showOwner'; ?>">オーナー一覧</a></li>
            <li class="nav-items"><a href="<?= PATH . '/admin/registerOwner'; ?>">オーナー登録</a></li>
            <li class="nav-items"><a href="<?= PATH . '/admin/itemCategoryList'; ?>">カテゴリー一覧</a></li>
            <li class="nav-items"><a href="<?= PATH . '/admin/category'; ?>">カテゴリー登録</a></li>
          </ul>
        </nav>
        <?php $isAdminLogined = isset($_SESSION['admin']['id']) && isset($_SESSION['admin']['name']) ? true : false; ?>
        <?php if($isAdminLogined):?>
          <form action="<?= PATH . '/admin/logout';?>" method="post">
            <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
            <div id="logout-box">
              <div class="header-icon logout"><img src="images/logout.png" alt="" /></div>
              <div class="header-icon-text">ログアウト</div>
            </div>
          </form>
        <? endif;?>
      </div>
    </header>
    <div id="information">
      <?= $isAdminLogined ? '<i>管理者ページにログインしました</i>' : '<i>管理者ページです ログインしてください</i>';?>
    </div>
    <main>