
<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main>
  <div class="owner-wrapper">
    <div class="login-box shop-info">
    
    <?php if($shop === null):?>
      <h3 class="unregistered">ショップ情報がありません、新規登録願います</h3>
      <div id="link-shop-register"><a href="<?= PATH . '/owner/registerShop'; ?>">新規登録</a></div>
    <?php else :?>
      <h3>店舗情報</h3>
      <span class="success"><?= SessionService::flash('success'); ?></span>
      <div id="shop-flex">
        <div id="img-side">
          <p><?= $shop->name; ?></p>
          <div><img src="<?= 'uploads/shops/' . $shop->filename ;?>" alt="" /></div>
        </div>
        <div id="text-side">
          <p><?= $shop->information; ?></p>
          <p><?= $shop->is_selling ? "販売中" : "停止中"; ?></p>
          <a href="<?= PATH . "/owner/shop/{$shop->id}/edit"; ?>" id="shop-edit-link-btn">
            ショップ情報編集
          </a>
        </div>
      </div>
    <?php endif;?>
    </div>
  </div>
</main>
<?php include(APP_PATH . '/Views/_common/footer.php');?>