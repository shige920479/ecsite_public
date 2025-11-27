<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/owner-header.php');?>
<main class="owner-main item-list-view">
  <div class="owner-wrapper">
    <h3>商品一覧</h3>
    <span class="success"><?= SessionService::flash('success'); ?></span>
    <span class="error-msg"><?= SessionService::flash('errors.item') ?></span>
    <table class="item-table">
      <thead>
        <tr>
          <th>画像</th>
          <th>商品名</th>
          <th>カテゴリ</th>
          <th>価格</th>
          <th>在庫</th>
          <th>ステータス</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($items as $item):?>
          <tr>
            <td><img src="uploads/item-images/<?= $item['filename'];?>" class="item-image"></td>
            <td><?= $item['name'] ;?></td>
            <td><?= $item['sub_category'];?><span> > > </span><?= $item['category'];?></td>
            <td class="text-right">¥<?= number_format($item['price']);?></td>
            <td class="text-right"><?= $item['stock_qty'] ?>pcs</td>
            <td class="text-center"><?= $item['is_selling'] ? '販売中' : '停止中'?></td>
            <td class="text-center">
              <a href=<?= PATH . "/owner/item/{$item['id']}/edit" ;?>>商品編集</a> |
              <a href=<?= PATH . "/owner/item/{$item['id']}/image/edit" ;?>>画像</a> |
              <a href=<?= PATH . "/owner/item/{$item['id']}/stock" ;?>>在庫</a> |
              <form action=<?= PATH . "/owner/item/{$item['id']}/delete" ;?> method="POST" class="delete-form">
                <input type="hidden" name="token" value="<?= h($csrf_token);?>">
                <button type="button" class="del-btn">削除</button>
              </form>
            </td>
          </tr>
        <? endforeach;?>
        <!-- 繰り返し行 -->
      </tbody>
    </table>
  </div>
</main>
<?php include(APP_PATH . '/Views/_common/footer.php');?>