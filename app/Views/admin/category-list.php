<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/admin-header.php');?>
<section class="item-list-wrapper">
<span class="success ca"><?= SessionService::flash('success'); ?></span>
<div class="category-list">
  <?php foreach ($categoryGroup as $categoryName => $subCategories): ?>
    <div class="category-block">
      <h2><?= h($categoryName); ?></h2>
      <ul class="sub-category-list">
        <?php foreach ($subCategories as $subCategoryName => $itemCategories): ?>
          <li>
            <strong><?= h($subCategoryName); ?></strong>
            <ul class="item-category-list">
              <?php foreach ($itemCategories as $itemCategory): ?>
                <li>
                  <a href="<?= PATH . "/admin/itemCategory/{$itemCategory['id']}/edit";?>">
                    <?= h($itemCategory['name']); ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</div>
</section>
<?php include(APP_PATH . '/Views/_common/footer.php'); ?>