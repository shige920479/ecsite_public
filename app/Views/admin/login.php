<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/admin-header.php');?>
<section class="login-wrapper">
  <div class="login-box">
    <h3>サイト管理者ログイン</h3>
    <form action="<?= PATH . '/admin/login';?>" method="post">
      <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
      <ul>
        <div class="input">
          <label for="email">メールアドレス</label>
          <input type="email" name="email" id="email" value="<?= SessionService::flash('old.email') ?>" />
          <span class="error-msg"><?= SessionService::flash('errors.email') ?></span>
        </div>
        <div class="input">
          <label for="password">パスワード</label>
          <input type="password" name="password" id="password" />
          <span class="error-msg"><?= SessionService::flash('errors.password') ?></span>
        </div>
        
        <button type="submit">Login</button>
      </ul>
    </form>
  </div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php');?>