<?php use App\Services\Core\SessionService;?>
<?php include APP_PATH . '/Views/_common/header.php';?>
<main>
  <div class="content container">
    <div id="item">
      <div class="item-img">
        <div class="swiper main-swiper">
          <div class="swiper-wrapper">
            <?php foreach($images as $image):?>
              <div class="swiper-slide">
                <div class="swiper-zoom-container">
                  <img src="<?= 'uploads/item-images/' . $image['filename'] ;?>" alt="商品画像">
                </div>
              </div>
            <?php endforeach;?>
          </div>
        </div>
        <div class="swiper thumb-swiper">
          <div class="swiper-wrapper">
            <?php foreach($images as $image):?>
              <div class="swiper-slide">
                <img src="<?= 'uploads/item-images/' . $image['filename'] ;?>" alt="商品画像">
              </div>
            <?php endforeach;?>
          </div>
        </div>
      </div>
      <div class="item-text">
        <div class="item-detail-info">
          <div class="title-favorite-flex">
            <div>
              <?php if($item['is_selling']) :?>
                <span>販売中</span>
              <?php else:?>
                <span class="not-selling">販売停止(現在お取り扱いしておりません)</span>
              <?php endif;?>
              <h2 class="page-title"><?= $item['item_name'] ;?></h2>
            </div>
            <div>
              <button id="favorite-button" class="favorite-icon" data-item-id="<?= $item['item_id'];?>"
                data-is-favorite="<?= $isFavorite ?>" data-is-logged-in="<?= $isLoggedIn ?>" aria-label="お気に入り">❤
              </button>
            </div>
          </div>
          
          <div>
            <div><?= $item['shop_name'];?></div>
            <div><?= $item['category'] . ' / ' . $item['sub_category'] . ' / ' . $item['item_category'];?></div>
          </div>
          <p><?= $item['information']; ?></p>
        </div>
        <hr class="hr" />
        <span class="error-msg"><?= SessionService::flash('errors.cart_in'); ?></span>
        <div>
          <?php
            if($item['is_selling'] && $isLoggedIn === "true") {
              $action = PATH . '/cart';
              $method = 'post';
            } elseif($item['is_selling']) {
              $action = PATH . '/login';
              $method = 'get';
            } else {
              $action = "";
              $method = "";
            }
          ?>
          <form action="<?= h($action);?>" method="<?= h($method);?>">
            <div class="price-quantity">
              <span class="price">&yen;<?= number_format(priceWithTax((int)$item['price']));?><small>(税込)</small></span>
              <div>
                <span>在庫</span>
                <div>残り<?= $item['stock_qty'] ?>個</div>
              </div>
              <div>
                <span>数量を選択</span>
                <input type="number" name="quantity" value="<?= SessionService::flash('old.quantity'); ?>" min="0">
              </div>
            </div>
            <?php if($item['is_selling'] && $isLoggedIn === "true") :?>
              <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
              <input type="hidden" name="item_id" value="<?= h($item['item_id']);?>">
              <button id="cart-in">カートに入れる</button>
            <?php elseif($item['is_selling']):?>
              <button id="cart-in">ログインしてカートに入れる</button>
              <input type="hidden" name="login_backUrl" value="<?= urlencode($loginForBackUrl); ?>">
            <?php else:?>
              <button id="cart-in" disabled>現在、販売を停止しております</button>
            <?php endif;?>
          </form>
        </div>
      </div>
    </div>
    <a class="link-text" href="<?= $backUrl ?? PATH . '/';?>">一覧ページへ戻る</a>
  </div>
</main>
<?php include APP_PATH . '/Views/_common/footer.php';?>