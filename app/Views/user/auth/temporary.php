<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/header.php');?>
  <section class="login-wrapper">
    <div class="login-box">
      <span class="error-msg"><?= SessionService::flash('errors.verification_code') ?></span>
      <h3>新規アカウント作成</h3>
      <small class="small-alert">下記メールアドレスに本登録用の確認コードを送信致します</small>
      <form action="<?= PATH . '/temporary'; ?>" method="post">
        <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
        <ul>
          <div class="input">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="<?= SessionService::flash('old.email'); ?>" />
              <span class="error-msg"><?= SessionService::flash('errors.email') ?></span>
          </div>
          <div class="input">
            <label for="email_confirm">メールアドレス（確認用）</label>
            <input type="email" name="email_confirm" id="email-confirm" value="<?= SessionService::flash('old.email_confirm'); ?>" />
              <span class="error-msg"><?= SessionService::flash('errors.email_confirm') ?></span>
          </div>
          <button type="submit">仮登録</button>
        </ul>
      </form>
    </div>
  </section>
<?php include(APP_PATH . '/Views/_common/footer.php');?>