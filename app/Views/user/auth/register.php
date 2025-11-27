<?php 
use App\Services\Core\SessionService;
?>
<?php include(APP_PATH . '/Views/_common/header.php');?>
<section class="login-wrapper">
  <div class="login-box">
    <h3>アカウント登録</h3>
    <form action="<?= PATH . '/userRegister';?>" method="post">
      <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
      <ul>
        <div class="input account">
          <div>
            <label for="name">ユーザーネーム</label>
          </div>
          <input type="text" name="name" id="name" value="<?= SessionService::flash('old.name');?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
        </div>
        <div class="input account">
          <div>
            <label for="email">メールアドレス</label>
          </div>
          <div id="account-email"><?= SessionService::get('register.email') ?></div>
        </div>
        <div class="input account">
          <div>
            <label for="password">パスワード</label>
          </div>
          <input type="password" name="password" id="password" />
          <span class="error-msg"><?= SessionService::flash('errors.password') ?></span>
        </div>
        <div class="input">
          <label for="confirm_password">パスワード（確認用）</label>
          <input type="password" name="confirm_password" id="confirm_password" />
          <span class="error-msg"><?= SessionService::flash('errors.confirm_password') ?></span>
        </div>
        <button type="submit">アカウント登録</button>
      </ul>
    </form>
  </div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php');?>