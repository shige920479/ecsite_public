<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/admin-header.php');?>
<section class="login-wrapper">
  <div class="login-box">
    <h3>カテゴリー登録</h3>
    <span class="success"><?= SessionService::flash('success'); ?></span>
    <span class="error-msg"><?= SessionService::flash('errors.general'); ?></span>
    <form action='<?= PATH . "/admin/category";?>' method="post">
      <div>
        <div class="input account">
          <div><label for="name">カテゴリー名</label></div>
          <input type="text" name="name" id="name" value="<?= SessionService::flash('old.name');?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
        </div>
        <div class="input account">
          <div><label for="slug">スラグ（英数字2～50文字以内）</label></div>
          <input type="text" name="slug" id="slug" value="<?= SessionService::flash('old.slug');?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.slug') ?></span>
        </div>
        <input type="hidden" name="token" value="<?= h($csrf_token) ?>" />
        <button type="submit">登録する</button>
      </div>
    </form>
  </div>
  <div id="other-category-link">
    <a href="<?= PATH . '/admin/subCategory' ?>">サブカテゴリー登録</a>
    <a href="<?= PATH . '/admin/itemCategory' ?>">商品カテゴリー登録</a>
  </div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php'); ?>