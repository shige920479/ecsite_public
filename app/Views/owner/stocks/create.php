<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main>
  <div class="owner-wrapper">
    <div class="login-box shop-info">
      <h3>在庫登録・更新</h3>
      <span class="success"><?= SessionService::flash('success'); ?></span>
      <div id="stock-flex">
        <div id="img-side">
          <p>商品名 : <?= $item->name; ?></p>
          <p>カテゴリー : <?= $category['sub_name'];?> >> <?= $category['item_name'];?></p>
          <div><img src="<?= 'uploads/item-images/' . $itemImage['filename'] ;?>" alt="" /></div>
        </div>
        <form action="<?= PATH . "/owner/item/{$itemId}/stock";?>" method="post" id="text-side">
        <input type="hidden" name="token" value="<?= h($csrf_token);?>">
          <ul>
              <li class="input">
                <label for="name">現在の在庫数</label>
                <?php if($currentStock === null):?>
                  <p><span id="stock-qty">在庫が登録されていません</span></p>
                <?php else:?>
                  <p>数量 : <span id="stock-qty"><?= $currentStock; ?></span>pcs</p>
                <?php endif;?>
              </li>
              <li class="input">
                <label for="stock_diff">数量</label>
                <div class="quantity-flex">
                  <div>
                    <input type="number" name="stock_diff" id="stock_diff" value="<?= SessionService::flash('old.stock_diff');?>" min="0" />
                  </div>
                  <div>
                    <input type="radio" name="up_down" id="radio-add" value="add"/>
                    <label for="radio-add">増やす</label>
                    <input type="radio" name="up_down" id="radio-reduce" value="reduce" />
                    <label for="radio-reduce">減らす</label>
                  </div>
                </div>
                <span class="error-msg"><?= SessionService::flash('errors.stock_diff') ?></span>
                <span class="error-msg"><?= SessionService::flash('errors.up_down') ?></span>
              </li>
              <li class="input">
                <label for="reason">増減理由/備考</label>
                <input type="text" name="reason" id="reason">
                <span class="error-msg"><?= SessionService::flash('errors.reason') ?></span>
              </li>
            </ul>
            <ul>
              <li class="input">
                <button type="submit">登録する</button>
              </li>
            </ul>
        </form>
      </div>
    </div>
  </div>
</main>
<?php include(APP_PATH . '/Views/_common/footer.php');?>
