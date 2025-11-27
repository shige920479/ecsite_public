<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/admin-header.php');?>
<section class="login-wrapper">
  <div class="login-box">
    <h3>商品カテゴリー登録</h3>
    <span class="error-msg"><?= SessionService::flash('errors.general'); ?></span>
    <span class="success"><?= SessionService::flash('success'); ?></span>
    <form action='<?= PATH . "/admin/itemCategory";?>' method="post">
      <ul>
        <div class="input account">
          <div>
            <label for="name">サブカテゴリー選択</label>
          </div>
          <select name="sub_category_id" id="sub-categories">
            <?php if(empty($categoryGroup)):?>
              <option value="">先にカテゴリーを登録して下さい</option>
            <?php else:?>
              <option value="">選択してください</option>
            <?php $oldSubCategoryId = SessionService::flash('old.sub_category_id')?>
            <?php foreach($categoryGroup as $categoryName => $subCategories):?>
              <optgroup label="<?= $categoryName;?>"></optgroup>
                <?php foreach($subCategories as $subCategory):?>
                  <option value="<?= h($subCategory['id']); ?>"
                    <?= (int)$oldSubCategoryId === $subCategory['id'] ? 'selected': ''; ?>>
                      <?= $subCategory['name'];?>
                  </option>
                <?php endforeach;?>
            <?php endforeach;?>
            <?php endif;?>
          </select>
          <span class="error-msg"><?= SessionService::flash('errors.sub_category_id') ?></span>
        </div>
        <div class="input account">
          <div>
            <label for="name">商品カテゴリー名</label>
          </div>
          <input type="text" name="name" id="name" value="<?= SessionService::flash('old.name');?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.name') ?></span>
        </div>
        <div class="input account">
          <div>
            <label for="slug">商品スラグ名</label>
          </div>
          <input type="text" name="slug" id="slug" value="<?= SessionService::flash('old.slug');?>"/>
          <span class="error-msg"><?= SessionService::flash('errors.slug') ?></span>
        </div>
        <input type="hidden" name="token" value="<?= h($csrf_token) ?>" />
        <button type="submit">登録する</button>
      </ul>
    </form>
  </div>
  <div id="other-category-link">
    <a href="<?= PATH . '/admin/category' ?>">カテゴリー登録</a>
    <a href="<?= PATH . '/admin/subCategory' ?>">サブカテゴリー登録</a>
  </div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php'); ?>