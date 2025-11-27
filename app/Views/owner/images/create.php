<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main class="owner-main">
  <div class="owner-wrapper">
    <div class="login-box item-edit">
      <h3>画像登録</h3>
      <span class="success"><?= SessionService::flash('success') ?></span>
      <form action="<?= PATH . "/owner/item/{$item->id}/image";?>" method="post" id="item-edit-flex" enctype="multipart/form-data" >
        <input type="hidden" name="token" value="<?= h($csrf_token) ;?>">
      <ul id="item-info">
          <li class="input">
            <label for="">商品名</label>
            <p><?= $item->name ;?></p>
          </li>
          <li class="input">
            <label for="">カテゴリー</label>
            <p><?= $categoryName ;?></p>
          </li>
          <li class="input">
            <label for="">商品情報</label>
            <p><?= $item->information ;?></p>
          </li>
          <li class="input">
            <label for="">価格</label>
            <p><?= number_format($item->price) . '円';?></p>
          </li>
        </ul>
        <ul class="store-images">
          <li class="input">
            <label>イメージ</label>
            <span class="error-msg grid"><?= SessionService::flash('errors.image');?></span>
            <div class="image-grid">
              <div class="grid-img">
                <label for="iamge1">画像1
                  <span class="error-msg grid"><?= SessionService::flash('errors.image[0]');?></span>
                </label>
                <input type="file" id="iamge1" name="image[]" class="input-image" data-preview="preview1"/>
                <input type="hidden" name="def_sort[]" value="0"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    <img id="preview1" class="preview-img"></div>
                  <div class="selected-div">
                    <p><label>現在選択中の画像</label></p>
                    <img src="<?= SessionService::get('tmp_image_path.0') ?? 'images/dummy.png'; ?>" alt="" class="selected-img">
                  </div>
                </div>
                
              </div>
              <div class="grid-img">
                <label for="iamge2">画像2
                  <span class="error-msg grid"><?= SessionService::flash('errors.image[1]');?></span>
                </label>
                <input type="file" id="iamge2" name="image[]" class="input-image" data-preview="preview2"/>
                <input type="hidden" name="def_sort[]" value="1"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    <img id="preview2" class="preview-img"></div>
                  <div class="selected-div">
                    <p><label>現在選択中の画像</label></p>
                    <img src="<?= SessionService::get('tmp_image_path.1') ?? 'images/dummy.png'; ?>" alt="" class="selected-img">
                  </div>
                </div>
              </div>
              <div class="grid-img">
                <label for="iamge3">画像3
                  <span class="error-msg grid"><?= SessionService::flash('errors.image[2]');?></span>
                </label>
                <input type="file" id="iamge3" name="image[]" class="input-image" data-preview="preview3"/>
                <input type="hidden" name="def_sort[]" value="2"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    <img id="preview3" class="preview-img"></div>
                  <div class="selected-div">
                    <p><label>現在選択中の画像</label></p>
                    <img src="<?= SessionService::get('tmp_image_path.2') ?? 'images/dummy.png'; ?>" alt="" class="selected-img">
                  </div>
                </div>
              </div>
              <div class="grid-img">
                <label for="iamge4">画像4
                  <span class="error-msg grid"><?= SessionService::flash('errors.image[3]');?></span>
                </label>
                <input type="file" id="iamge4" name="image[]" class="input-image" data-preview="preview4"/>
                <input type="hidden" name="def_sort[]" value="3"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    <img id="preview4" class="preview-img"></div>
                  <div class="selected-div">
                    <p><label>現在選択中の画像</label></p>
                    <img src="<?= SessionService::get('tmp_image_path.3') ?? 'images/dummy.png'; ?>" alt="" class="selected-img">
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li>
            <button type="submit" id="images-store-btn">登録する</button>
          </li>
        </ul>
      </form>
    </div>
  </div>
</main>
<?php include(APP_PATH . '/Views/_common/footer.php');?>