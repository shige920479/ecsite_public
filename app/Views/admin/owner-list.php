<?php
use App\Services\Core\SessionService;
use Carbon\Carbon;
?>
<?php include(APP_PATH . '/Views/_common/admin-header.php');?>
<div class="admin-wrapper">
  <div class="admin-home">
    <h3>オーナー情報</h3>
    <span class="success"><?= SessionService::flash('success'); ?></span>
    <div id="owner-info">
      <table>
        <thead>
          <tr>
            <th id="th-1">オーナー名</th>
            <th id="th-2">メールアドレス</th>
            <th id="th-3">登録日</th>
            <th id="th-4">変更</th>
            <th id="th-5">停止</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($owners)):?>
            <tr><td colspan="5">オーナー情報は未登録です</td></tr>
          <?php else:?>
            <?php foreach($owners as $owner): ?>
              <tr>
                <td class="td-1"><?= $owner['name']; ?></td>
                <td class="td-2"><?= $owner['email']; ?></td>
                <td class="td-3"><?=  Carbon::parse($owner['updated_at'])->format('Y年m月d日') ; ?></td>
                <td class="td-4"><a href='<?= PATH . "/admin/owner/{$owner['id']}/edit" ?>'>編集</a></td>
                <td class="td-5">
                  <form action='<?= PATH . "/admin/owner/{$owner['id']}/delete"; ?>' method="post">
                    <input type="hidden" name="token" value="<?= h($csrf_token); ?>">
                    <button type="button" class="del-btn" data-id="<?= $owner['id'];?>">削除</button>
                  </form>
                </td>
              </tr>
            <?php endforeach;?>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include(APP_PATH . '/Views/_common/footer.php'); ?>