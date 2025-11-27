<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/admin-header.php');?>
<section class="login-wrapper">
  <div class="login-box">
    <h3>ショップオーナー編集</h3>
    <span class="error-msg"><?= SessionService::flash('errors.general'); ?></span>
    <form action='<?= PATH . "/admin/owner/{$owner['id']}/edit";?>' method="post">
      <ul>
        <div class="input account">
          <div>
            <label for="name">名前</label>
          </div>
          <input type="text" name="name" id="name" value="<?= SessionService::flash('old.name') ?? $owner['name'];?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
        </div>
        <div class="input account">
          <div>
            <label for="email">メールアドレス</label>
          </div>
          <input type="email" name="email" id="email" value="<?= SessionService::flash('old.email') ?? $owner['email'];?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.email') ?></span>
        </div>
        <input type="hidden" name="token" value="<?= h($csrf_token); ?>" />
        <button type="submit">変更する</button>
      </ul>
    </form>
  </div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php'); ?>