<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main class="owner-main">
  <div class="owner-wrapper">
    <div class="login-box item-edit">
      <?php if($mode === 'create'):?>
        <h3>商品登録内容のご確認</h3>
        <form action="<?= PATH . '/owner/item/confirm'?>" method="post">
      <?php elseif($mode === 'edit'):?>
        <h3> 商品変更内容のご確認</h3>
      <form action=<?= PATH . "/owner/item/{$item_id}/update"?> method="post">
      <?php endif;?>
          <input type="hidden" name="token" value="<?= h($csrf_token);?>">
          <div id="item-edit-flex">
            <ul class="item-left">
              <li class="input">
                <label for="">販売する店舗</label>
                <p class="item-preview"><?= $viewData['shop_name']; ?></p>
              </li>
              <li class="input">
                <label for="item-category">商品カテゴリー</label>
                <p class="item-preview"><?= $viewData['item_category_name']; ?></p>
              </li>
              <li class="input">
                <label for="name">商品名</label>
                <p class="item-preview"><?= $viewData['name']; ?></p>
              </li>
              <li class="input">
                <label for="price">価格</label>
                <p class="item-preview"><?= number_format($viewData['price']) . '円'; ?></p>
              </li>
              <li class="input">
                <label for="sort_order">表示順</label>
                <p class="item-preview"><?= ! empty($viewData['sort_order']) ?: '指定なし' ;?></p>
              </li>
            </ul>
            <ul class="item-right">
              <li class="input">
                <label for="item-information">商品情報</label>
                <p class="item-preview"><?= $viewData['information']; ?></p>
              </li>
              <li class="input">
                <label>ステータス</label>
                <p class="item-preview"><?= $viewData['is_selling']; ?></p>
              </li>
            </ul>
          </div>
          <div id="item-preview-btn-flex">
            <?php if($mode === 'create'):?>
              <a href="<?= PATH . '/owner/item/create' ?>">入力画面へ戻る</a>
              <button type="submit">商品登録</button>
            <?php elseif($mode === 'edit'):?>
              <a href=<?= PATH . "/owner/item/{$item_id}/edit" ?>>入力画面へ戻る</a>
              <button type="submit">商品変更登録</button>
            <?php endif;?>
          </div>
        </form>
    </div>
  </div>
</main>
<?php include(APP_PATH . '/Views/_common/footer.php');?>


