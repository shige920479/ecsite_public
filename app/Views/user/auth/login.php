<?php
  use App\Services\Security\TokenManager;
  use App\Services\Core\SessionService;
?>
<?php include(APP_PATH . '/Views/_common/header.php');?>
<section class="login-wrapper">
  <div class="login-box">
    <h3>ログイン</h3>
    <small>登録済みのお客様はこちらからログインしてください。</small>
    <form action="<?= PATH . '/login';?>" method="post">
      <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
      <ul>
        <div class="input">
          <label for="email">メールアドレス</label>
          <input type="email" name="email" id="email" value="" />
          <span class="error-msg"><?= SessionService::flash('errors.email') ?></span>
        </div>
        <div class="input">
          <label for="password">パスワード</label>
          <input type="password" name="password" id="password" />
          <span class="error-msg"><?= SessionService::flash('errors.password') ?></span>
        </div>
        <input type="hidden" name="backUrl" value="<?= h($backUrl);?>">
        <button type="submit">Login</button>
      </ul>
    </form>
    <div id="to-register">
      <span>アカントが未登録ですか？</span>
      <a href="<?= PATH . '/temporary'; ?>" class="auth-link">→ アカウントを作成</a>
    </div>
  </div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php');?>