<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main class="owner-main">
  <div class="owner-wrapper">
    <div class="login-box item-edit">
      <h3>登録済み画像の編集</h3>
      <div id="item-edit-flex" class="image-edit-wrapper">
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
        <form class="store-images edit" action="<?= PATH . "/owner/item/{$item->id}/image/update";?>" method="post" enctype="multipart/form-data" >
          <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
          <div class="input image-edit">
            <label>イメージ</label>
            <span class="error-msg grid"><?= SessionService::flash('errors.image');?></span>
            <span class="success"><?= SessionService::flash('success'); ?></span>
            <ul id="sortable-list" class="image-grid">
              <?php for($i = 0; $i < 4; $i++):?>
                <?php
                  $image = $images[$i] ?? null;
                  $imageId = $image['id'] ?? '';
                  $tmpImage = SessionService::get("tmp_image_path.{$i}");
                  $dbImage = isset($image['filename']) ? "uploads/item-images/{$image['filename']}" : null;
                  $imageSrc = $tmpImage ?? $dbImage ?? "images/dummy.png";
                  $filename = $image['filename'] ?? '';
                  $hasImage = $tmpImage || $imageId;
                ?>

                <li class="sortable-item grid-img" <?= $hasImage ? "data-id=\"{$imageId}\"" : '';?>>
                  <label for="<?="image{$i}"?>">画像<?= $i+1;?>
                    <span class="error-msg grid"><?= SessionService::flash("errors.image[$i]");?></span>
                  </label>
                  <input type="file" id="<?="image{$i}"?>" name="image[]" class="input-image" data-preview='<?= "preview{$i}";?>'/>
                  <input type="hidden" name="image_id[]" value="<?= h($imageId);?>">
                  <input type="hidden" name="sort_order[]" value="">
                  <input type="hidden" name="tmp_image[]" value="<?= h($tmpImage);?>">
                  <input type="hidden" name="def_sort[]" value="<?= h($i);?>">
                  <input type="hidden" name="def_filename[]" value="<?= h($filename);?>">
                  <div class="preview-selected-flex">
                    <div class="preview-div">
                      <p><label>画像プレビュー</label></p>
                      <img id="<?= "preview{$i}";?>" class="preview-img">
                    </div>
                    <div class="current-img-wrapper" data-filename="<?= h($imageSrc);?>">
                      <?php if($imageId && !$tmpImage):?>
                        <p><label>登録済みの画像</label></p>
                        <img src="<?= h($imageSrc);?>" alt="" class="selected-img">
                        <button type="button" class="delete-img-btn">✖</button>
                      <?php elseif($tmpImage): ?>
                        <p><label>選択中の画像</label></p>
                        <img src="<?= h($imageSrc);?>" alt="" class="selected-img">
                      <?php else :?>
                        <p><label>登録・選択画像なし</label></p>
                        <img src="<?= h($imageSrc);?>" alt="" class="selected-img">
                      <?php endif;?>
                    </div>
                  </div>
                </li>
              <?php endfor;?>
            </ul>
          </div>
          <div>
            <button type="submit" id="images-store-btn">登録する</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>
<?php include(APP_PATH . '/Views/_common/owner-footer.php');?>