<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
    <main class="owner-main">
      <div class="owner-wrapper">
        <div class="login-box item-edit">
          <h3>商品編集</h3>
          <span class="error-msg"><?= SessionService::flash('errors.general') ?></span>
          <form action="<?= PATH . "/owner/item/{$item->id}/edit";?>" method="post" id="item-edit-flex">
            <input type="hidden" name="token" value="<?= h($csrf_token);?>">
            <input type="hidden" name="mode" value="edit">
            <input type="hidden" name="item_id" value="<?= $item->id;?>">
            <ul class="item-left">
              <li class="input">
                <label for="">販売する店舗</label>
                <input type="text" value="<?= $shop->name; ?>" disabled>
                <input type="hidden" name="shop_id" value="<?= SessionService::flash('old.shop_id') ?? $shop->id; ?>">
                <span class="error-msg"><?= SessionService::flash('errors.shop_id') ?></span>
              </li>
              <li class="input">
                <label for="item-category">商品カテゴリー</label>
                <select name="item_category_id" id="item-category">
                  <option value="">選択してください</option>
                  <?php 
                    $session_id = SessionService::flash('old.item_category_id') ?? $item->item_category_id ?? null;
                  ?>
                  <?php foreach($categoryGroup as $category => $subCategories):?>
                    <optgroup label="<?= $category ?>"></optgroup>
                    <?php foreach($subCategories as $subCategory => $itemCategories):?>
                      <optgroup label="<?= $subCategory; ?>"></optgroup>
                      <?php foreach($itemCategories as $itemCategory):?>
                        <option value="<?= $itemCategory['id'];?>" <?= $itemCategory['id'] === (int)$session_id ? 'selected' : '';?>>
                          <?= h($itemCategory['name']);?>
                        </option>
                      <?php endforeach;?>
                    <?php endforeach;?>
                  <?php endforeach; ?>
                </select>
                <span class="error-msg"><?= SessionService::flash('errors.item_category_id') ?></span>
              </li>
              <li class="input">
                <label for="name">商品名</label>
                <?php $itemName = SessionService::flash('old.name') ?? $item->name ?? '';?>
                <input type="text" name="name" id="name" value="<?= h($itemName) ?>" />
                <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
              </li>
              <li class="input">
                <label for="price">価格</label>
                <?php $price = SessionService::flash('old.price') ?? $item->price ?? '';?>
                <input type="number" name="price" id="price" value="<?= h((int)$price);?>" />
                <span class="error-msg"><?= SessionService::flash('errors.price') ?></span>
              </li>
              <li class="input">
                <label for="sort_order">表示順</label>
                <?php $sortOrder = SessionService::flash('old.sort_order') ?? $item->sort_order ?? '';?>
                <input type="number" name="sort_order" id="sort_order" value="<?= h($sortOrder); ?>" />
                <span class="error-msg"><?= SessionService::flash('errors.sort_order') ?></span>
              </li>
            </ul>
            <ul class="item-right">
              <li class="input">
                <label for="item-information">商品情報</label>
                <?php $information = SessionService::flash('old.information') ?? $item->information ?? '';?>
                <textarea type="text" name="information" id="item-information" rows="10"><?= h($information);?></textarea>
                <span class="error-msg"><?= SessionService::flash('errors.information') ?></span>
              </li>
              <li class="input">
                <label>ステータス</label>
                <div>
                <?php $is_selling = SessionService::flash('old.is_selling') ?? $item->is_selling ?? 1;?>
                  <input type="radio" name="is_selling" value="1" id="available" <?= (int)$is_selling === 1 ? 'checked': '';?>/>
                  <label for="available">販売中</label>
                  <input type="radio" name="is_selling" value="0" id="stop" <?= (int)$is_selling === 0 ? 'checked': '' ;?>/>
                  <label for="stop">停止中</label>
                  <span class="error-msg"><?= SessionService::flash('errors.is_selling') ?></span>
                </div>
              </li>
              <li>
                <button type="submit">商品情報の更新</button>
              </li>
            </ul>
          </form>
        </div>
      </div>
    </main>
<?php include(APP_PATH . '/Views/_common/footer.php');?>