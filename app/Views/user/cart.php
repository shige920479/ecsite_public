<?php use App\Services\Core\SessionService;?>
<?php include APP_PATH . '/Views/_common/header.php';?>
<main>
  <div class="content container cart">
    <span class="success"><?= SessionService::flash('success'); ?></span>
    <span class="error-msg"><?= SessionService::flash('errors.order');?></span>
    <span class="error-msg"><?= SessionService::flash('errors.payment');?></span>
    <span class="error-msg"><?= SessionService::flash('errors.cart');?></span>
    <span class="error-msg"><?= SessionService::flash('errors.cart-clear');?></span>
    <h1>カート</h1>
    <div id="cart-flex">
      <div id="cart-list-side">
      <?php foreach($cartItems as $item):?>
        <div class="cart">
          <hr class="hr" />
          <div class="cart-box">
            <div class="cart-img"><img src="<?= 'uploads/item-images/' . $item['filename']; ?>" .  alt="" /></div>
            <div class="cart-info">
              <ul>
                <li><?= $item['item_name'];?></li>
                <li><small>商品番号:</small><?= $item['item_id'];?></li>
                <li><small>ショップ:</small><?= $item['shop_name'];?></li>
              </ul>
                <div class="price-quantity">
                  <div>
                    &yen;<span class="unit-price text-12"><?= number_format(priceWithTax((int)$item['price']));?></span> (税込)
                  </div>
                  <div>
                    <form action="<?= PATH . "/cart/item/{$item['item_id']}/delete"; ?>" method="post">
                      <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
                      <input type="hidden" name="cart_id" value="<?= h($item['cart_id']);?>">
                      <button type="submit" class="cartitem-del-btn">削除</button>
                    </form>
                  </div>
                  <div>
                    
                      <span>数量を選択</span>
                      <input type="number" class="quantity-input" data-cart-id="<?= $item['cart_id']; ?>"
                              name="quantity" value="<?= $item['quantity']; ?>" min="1">
                    
                  </div>
                </div>
              <hr class="hr-subtotal">
              <div class="item-subtotal">
                <p>商品小計</p>
                <div><span class="subtotal-calc"></span>円 (税込)</div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach;?>
      <hr class="hr" />
      </div>
      <div id="cart-total">
        <div id="cart-total-flex">
          <div>
            <span class="error-msg"><?= SessionService::flash('order_error');?></span>
            <p>カート内合計金額</p>
            <div><span id="total-price"></span>円</div>
          </div>
          <form action="<?= PATH . '/checkout/show'; ?>" method="get">
            <button class="btn">注文に進む</button>
            <?php foreach($cartItems as $item):?>
              <input type="hidden" name="cart_id[]" value="<?= h($item['cart_id']);?>">
            <?php endforeach;?>
          </form>
        </div>
      </div>
    </div>

  </div>
  <a class="link-text" href="<?= PATH . '/';?>">一覧ページへ戻る</a>
</main>
<?php include APP_PATH . '/Views/_common/footer.php';?>
