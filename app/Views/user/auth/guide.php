<?php

use App\Services\Core\SessionService;

 include(APP_PATH . '/Views/_common/header.php');?>

<div id="guide-wrapper">
  <p>認証用コードを下記に入力願います</p>
  <form action="<?= PATH . '/guide';?>" method="post">
    <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
      <div  class="error-msg mt-10"><?= SessionService::flash('errors.verification_code'); ?></div>
    <input type="text" name="verification_code" id="input-code"/>
    <?php if(! empty(SessionService::get('register.email'))): ?>
      <input type="hidden" name="email" value="<?= h(SessionService::get('register.email')) ?>">
      <div id="guide">
        <button type="submit" class="btn">送信する</button>
      </div>
    <?php else:?>
      <div id="guide">
        <a href="<?= PATH . '/temporary';?>">仮登録画面へ戻る</a>
      </div>
    <?php endif;?>
    <div id="verification-code">
        <span>認証コード: <small><?= SessionService::get('register.verification_code'); ?></small></span>
    </div>
  </form>
</div>

<?php include(APP_PATH . '/Views/_common/footer.php');?>