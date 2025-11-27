<?php

use App\Services\Core\SessionService;

 include APP_PATH . '/Views/_common/header.php'; ?>

<div class="checkout-message cancel">
  <h2>決済がキャンセルされました</h2>
  <p>お支払いは完了しておりません。</p>
  <p>再度お試しいただくか、カート内容をご確認ください。</p>
  <a href="<?= PATH . '/cart';?>" class="btn">カートに戻る</a>
</div>
<div class="cancel-error-div">
  <span class="error-msg cancel"><?= SessionService::flash('errors.cancel') ?></span>
</div>

<?php include APP_PATH . '/Views/_common/footer.php'; ?>