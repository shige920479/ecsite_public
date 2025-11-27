<?php include APP_PATH . '/Views/_common/header.php'; ?>

<div class="checkout-message success">
  <h2>ご購入ありがとうございました！</h2>
  <p class="order-id">注文番号：<?= $orderId !== null ? h($orderId) : 'ご注文情報を取得できませんでした'; ?></p>
  <p>ご注文内容を確認するメールをお送りしております。</p>
  <p>商品は準備が整い次第、発送させていただきます。</p>
  <a href="<?= PATH . '/';?>" class="btn">トップページに戻る</a>
</div>

<?php include APP_PATH . '/Views/_common/footer.php'; ?>