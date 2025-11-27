<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main>
  <div class="owner-wrapper">
    <div class="login-box shop-edit">
      <h3>ショップ登録内容の変更</h3>
      <form action="<?= PATH . "/owner/shop/{$shop->id}/edit"; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
        <ul>
          <li class="input">
            <label for="name">販売する店舗名</label>
            <input type="text" name="name" value="<?= SessionService::flash('old.name') ?? $shop->name; ?>" id="name" />
            <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
          </li>
          <li class="input">
            <label for="shop-info">店舗情報</label>
            <textarea name="information" id="shop-info"><?= SessionService::flash('old.information') ?? $shop->information; ?></textarea>
            <span class="error-msg"><?= SessionService::flash('errors.information') ?></span>
          </li>
          <li class="input">
            <label for="">ステータス</label>
            <div>
              <?php
                $is_selling = SessionService::flash('old.is_selling');
                if($is_selling === null) {
                  $is_selling = $shop->is_selling;
                }
              ?>
              <input type="radio" name="is_selling" value="1" id="available" <?= (int)$is_selling === 1 ? 'checked': '';?> >
              <label for="available">販売中</label>
              <input type="radio" name="is_selling" value="0" id="stop" <?= (int)$is_selling === 0 ? 'checked': '';?>/>
              <label for="stop">停止中</label>
            </div>
            <span class="error-msg"><?= SessionService::flash('errors.is_selling') ?></span>
          </li>
          <li class="input">
            <div id="shop-edit">
              <?php if(! empty($shop->filename)):?>
                <div>
                  <p><label>現在の登録画像</label></p>
                  <img src=<?= "/uploads/shops/" . h($shop->filename);?> alt="" class="shop-img">
                  <input type="hidden" name="current_filename" value="<?= h($shop->filename);?>">
                </div>
              <?php endif;?>
              <label for="image">画像を変更する</label>
              <div id="file-input-flex">
                <div id="file-select">
                  <input type="file" id="image" name="image"/>
                </div>
                <div id="file-preview">
                  <img id="preview" src="#" alt="プレビュー画像">
                </div>
              </div>
              <p><label>現在選択中の画像</label></p>
              <div id="tmp-img-wrapper">
                <?php if($tempImage = SessionService::get('tmp_image_path')): ?>
                  <div class="img-preview" data-filename="<?= str_replace('/tmp/', '', $tempImage);?>">
                    <div><img src="<?= $tempImage; ?>" alt="" class="shop-img"></div>
                    <button type="button" class="tmp-delete-btn">画像を削除</button>
                  </div>
                <?php endif;?>
              </div>
            </div>
            <span class="error-msg"><?= SessionService::flash('errors.image') ?></span>
          </li>
        </ul>
        <button type="submit">店舗登録</button>
      </form>
    </div>
  </div>
</main>
<?php include(APP_PATH . '/Views/_common/footer.php');?>