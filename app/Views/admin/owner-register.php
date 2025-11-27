<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/admin-header.php');?>
<section class="login-wrapper">
  <div class="login-box">
    <h3>ショップオーナー登録</h3>
    <form action="<?= PATH . '/admin/registerOwner';?>" method="post">
      <ul>
        <div class="input account">
          <div>
            <label for="name">名前</label>
          </div>
          <input type="text" name="name" id="name" value="<?= SessionService::flash('old.name');?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
        </div>
        <div class="input account">
          <div>
            <label for="email">メールアドレス</label>
          </div>
          <input type="email" name="email" id="email" value="<?= SessionService::flash('old.email');?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.email') ?></span>
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
        <input type="hidden" name="token" value="<?= h($csrf_token); ?>" />
        <button type="submit">新規オーナー登録</button>
      </ul>
    </form>
  </div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php'); ?>