<?php use App\Services\Core\SessionService;?>
<?php include APP_PATH . '/Views/_common/header.php';?>
<main>
  <div class="content container favorite">
    <span class="success"><?= SessionService::flash('success'); ?></span>
    <span class="error-msg"><?= SessionService::flash('errors.favorite'); ?></span>
    <h1>お気に入り</h1>
    <div id="favorite-flex">
      <div id="favorite-list-side">
      <?php foreach($favorites as $item):?>
        <div class="favorite">
          <hr class="hr" />
          <div class="favorite-box">
            <div class="favorite-img"><img src="<?= 'uploads/item-images/' . $item['filename']; ?>" .  alt="" /></div>
            <div class="favorite-info">
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
                      <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
                      <input type="hidden" name="favorite_id" value="<?= h($item['favorite_id']);?>">
                      <button type="submit" class="favorite-del-btn">削除</button>
                  </div>
                  <form action="<?= PATH . '/favorite';?>" method="post">
                    <?php if($item['is_selling']) :?>
                      <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
                      <input type="hidden" name="favorite_id" value="<?= h($item['favorite_id']);?>">
                      <button id="cart-in" class="favorite-btn"><img src="images/cart-white.png" class="cart-white">カートに入れる</button>
                    <?php else:?>
                      <button id="cart-in" class="disabled-btn" disabled>販売停止中です</button>
                    <?php endif;?>
                  </form>
                </div>
            </div>
          </div>
        </div>
        <?php endforeach;?>
        <hr class="hr" />
      </div>
    </div>

  </div>
  <a class="link-text" href="<?= PATH . '/';?>">一覧ページへ戻る</a>
</main>
<?php include APP_PATH . '/Views/_common/footer.php';?>