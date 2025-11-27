<?php use App\Services\Core\SessionService;?>
<?php include APP_PATH . '/Views/_common/header.php';?>
<main>
  <div class="content container confirm">
    <h1>注文内容の確認</h1>
    <span class="error-msg"><?= SessionService::flash('errors.orders'); ?></span>
    <div class="confirm-list">
      <div class="confirm-item-header">
        <span class="w30">商品名</span>
        <span>数量</span>
        <span>小計</span>
        <span class="w30">リストから削除</span>
      </div>

      <!-- 繰り返し部分 -->
      <?php foreach($tmpOrderItems as $item):?>
        <div class="confirm-item" id="<?= "data" . "_" . $item['cart_id'];?>">
          <span class="w30"><?= $item['item_name']; ?>
            <span class="error-msg order"><?= SessionService::flash("errors.item_{$item['item_id']}");?></span>
          </span>
          <span class="quantity"><?= $item['quantity']; ?>個</span>
          <span>&yen;<span class="amount"><?= number_format($item['amount']);?></span> <small>(税込)</small></span>
          <div class="confirm-actions w30">
            <div class="inline-form">
              <button type="submit" class="order-btn btn-secondary cart-back-btn" data-id="<?= $item['cart_id'];?>">カートへ戻す</button>
            </div>
            <div class="inline-form">
            <!-- <form action="/favorite" method="post" class="inline-form">
              <input type="hidden" name="item_id" value="6"> -->
              <button type="submit" class="order-btn btn-secondary move-favorite-btn" data-cart-id="<?= $item['cart_id']?>">
                お気に入りへ
              </button>
            <!-- </form> -->
            </div>
          </div>
        </div>
      <?endforeach;?>
    </div>
    <div class="confirm-total-box">
      <p>ご購入金額</p>
      <div class="confirm-total">&yen;<span id="order-total-amount"><?= number_format($total) ;?></span></div>
    </div>

    <div class="confirm-buttons">
      <a href="<?= PATH . '/cart'; ?>" class="order-btn btn-back">カートに戻る</a>
      <form action="<?= PATH . '/checkout/confirm'; ?>" method="post">
        <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
        <?php foreach($tmpOrderItems as $item):?>
          <input type="hidden" name="cart_ids[]" value="<?= h($item['cart_id']);?>" id="<?= "order-cart_" . h($item['cart_id']); ?>">

        <?php endforeach;?>
        <input type="hidden" name="redirect_path" value="<?= urlencode($redirectPath); ?>">
        <button type="submit" class="order-btn btn-primary">注文を確定する</button>
      </form>
    </div>
  </div>
</main>
<?php include APP_PATH . '/Views/_common/footer.php';?>
