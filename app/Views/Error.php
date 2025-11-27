<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>エラー発生</title>
  <base href="<?= BASE_URL; ?>">
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
</head>
<body>
  <div class="error-wrapper">
      <h1><?= h($error_mode); ?></h1>
      
      <?php if($error_mode === '400error'): ?>
        <p>400 Bad Request: リクエストが正しくありません</p>
        <div><a href="<?= PATH  . '/' ?>">ホームに戻る</a></div>
        <img src="images/exclamation-triangle-fill.svg" class="e-img">
      
        <?php elseif($error_mode === '403error'): ?>
        <p>403 Forbidden: この操作を実行する権限がありません</p>
        <div><a href="<?= PATH  . '/' ?>">ホームに戻る</a></div>
        <img src="images/exclamation-triangle-fill.svg" class="e-img">

        <?php elseif($error_mode === '404error'): ?>
        <p>404 Not Found: 指定されたページが見つかりません</p>
        <div><a href="<?= PATH  . '/' ?>">ホームに戻る</a></div>
        <img src="images/exclamation-triangle-fill.svg" class="e-img">
      
        <?php elseif($error_mode === '500error'): ?>
        <p>500 Internal Server Error: サーバー内部で問題が発生しました</p>
        <div><a href="<?= PATH  . '/' ?>">ホームに戻る</a></div>
        <img src="images/database-exclamation.svg" class="e-img">
      
        <?php endif; ?>
      <div>
        <p>お問合せ先</p>
        <ul>
          <li>XXXXX株式会社 カスタマセンター</li>
          <li>Tel : XXX-XXXX-XXXX</li>
          <li>email : XXXX@XXXX.com</li>
        </ul>
      </div>
  </div>
</body>
</html>






