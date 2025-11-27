<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main>
  <div class="owner-wrapper">
    <div class="login-box shop-edit">
      <h3>ショップ登録</h3>
      <form action="<?= PATH . '/owner/registerShop'; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
        <ul>
          <li class="input">
            <label for="name">販売する店舗名</label>
            <input type="text" name="name" value="<?= SessionService::flash('old.name'); ?>" id="name" />
            <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
          </li>
          <li class="input">
            <label for="shop-info">店舗情報</label>
            <textarea name="information" id="shop-info"><?= SessionService::flash('old.information'); ?></textarea>
            <span class="error-msg"><?= SessionService::flash('errors.information') ?></span>
          </li>
          <li class="input">
            <label for="">ステータス</label>
            <div>
              <?php $is_selling = SessionService::flash('old.is_selling') ?? 1;?>
              <input type="radio" name="is_selling" value="1" id="available" <?= (int)$is_selling === 1 ? 'checked': '';?>/>
              <label for="available">販売中</label>
              <input type="radio" name="is_selling" value="0" id="stop" <?= (int)$is_selling === 0 ? 'checked': '' ;?>/>
              <label for="stop">停止中</label>
            </div>
            <span class="error-msg"><?= SessionService::flash('errors.is_selling') ?></span>
          </li>
          <li class="input">
            <label for="image">イメージ</label>
            <div id="file-input-flex">
              <div>
                <input type="file" id="image" name="image" value="" />
              </div>
              <div id="file-preview">
                <img id="preview" src="#" alt="プレビュー画像">
              </div>
            </div>
            <div>
              <p><label>現在選択中の画像</label></p>
              <img src="<?= SessionService::get('tmp_image_path'); ?>" alt="" class="shop-img">
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