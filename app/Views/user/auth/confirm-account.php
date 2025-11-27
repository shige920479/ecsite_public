<?php use App\Services\Core\SessionService;?>
<?php include(APP_PATH . '/Views/_common/header.php');?>
  <section class="login-wrapper">
    <p></p>
    <div class="login-box">
      <h3>ご登録内容のご確認</h3>
      <small class="small-alert">下記の内容で宜しければ本登録ボタンをクリックしてください</small>
      <table>
        <tr>
          <th>ユーザーネーム</th>
          <td><?= SessionService::get('register.name') ?></td>
        </tr>
        <tr>
          <th>メールアドレス</th>
          <td><?= SessionService::get('register.email') ?></td>
        </tr>
        <tr>
          <th>パスワード</th>
          <td><?= substr_replace(SessionService::get('register.password'), '*****', -5) ?></td>
        </tr>
      </table>
      <form action="<?= PATH . '/confirmInput' ?>" method="post">
        <input type="hidden" name="token" value="<?= h($csrf_token) ?>">
        <button type="submit">登録する</button>
      </form>
    </div>
  </section>
<?php include(APP_PATH . '/Views/_common/footer.php');?>