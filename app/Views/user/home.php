<?php include APP_PATH . '/Views/_common/header.php';?>
<main>
  <div id="top" class="wrapper">
    <div class="item-scope">
      <form action="<?= PATH . '/' . (h($categoryPath) ?? '') ;?>" method="get" id="category-form">
        <div class="breadcrumbs"> <!-- 後でCSSファイルに反映 -->
          <h2>TOP&ensp;/&ensp;</h2>
            <select name="parent" id="parent">
              <option value="">全てのカテゴリー</option>
              <?php foreach ($categoryTree as $parent): ?>
                <option value="<?= h($parent['slug']); ?>">
                  <?= h($parent['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <select name="sub" id="sub" disabled>
              <option value="">全てのサブカテゴリ</option>
            </select>
            <select name="item" id="item" disabled>
              <option value="">全ての商品カテゴリ</option>
            </select>
            <input type="hidden" name="per_page" value="<?= h($perPage);?>">
            <input type="hidden" name="item_select" value="<?= h($item_select) ;?>">
            <input type="hidden" name="item_search" value="<?= h($item_search) ;?>">
            <input type="hidden" name="page" value="1">
            <button type="submit">絞り込む</button>
        </div>
      </form>
      <form action="<?= PATH . '/' . (h($categoryPath) ?? '') ;?>" method="get" id="sort-form">
        <div class="item-sort-box">
          <label for="item-select">表示順</label>
          <select name="item_select" id="item-select">
            <option value="">並べ替えなし</option>
            <option value="price_asc" <?= $item_select === 'price_asc' ? 'selected' : '' ;?>>価格の安い順</option>
            <option value="price_desc" <?= $item_select === 'price_desc' ? 'selected' : '' ;?>>価格の高い順</option>
            <option value="date_desc" <?= $item_select === 'date_desc' ? 'selected' : '' ;?>>新着順</option>
            <option value="shop_asc" <?= $item_select === 'shop_asc' ? 'selected' : '' ;?>>ショップ順</option>
          </select>
          <input type="hidden" name="per_page" value="<?= h($perPage);?>">
          <input type="hidden" name="item_search" value="<?= h($item_search) ;?>">
          <input type="hidden" name="page" value="1">
        </div>
      </form>
    </div>
    <ul class="product-list">
      <?php foreach($items as $item) :?>
      <li>
        <a href="<?= PATH . "/items/{$item['item_id']}" . '?backUrl=' . urlencode($backUrl);?>">
          <img src="<?= 'uploads/item-images/' . $item['filename'] ;?>" alt="" />
          <p><?= $item['item_name'];?></p>
          <p><?= $item['shop_name'];?></p>
          <p>&yen;<?= number_format(priceWithTax((int)$item['price']));?> <small>(税込)</small></p>
        </a>
      </li>
      <?php endforeach;?>
    </ul>
    <ul class="pagination">
      <li>全<?= $total?>件</li>
      <?php for($i = 1; $i <= $totalPages; $i++) :?>
        <?php if($i === $currentPage): ?>
          <li class="current-page"><?= $i;?></li>
        <?php else:?>
          <li class="link-page">
            <a href="<?= PATH . '/' . (h($categoryPath) ?? '') . "?page={$i}&per_page={$perPage}&item_search={$item_search}&item_select={$item_select}";?>">
              <?= $i;?>
            </a>
          </li>
        <?php endif;?>
      <?php endfor;?>
      <li>
        <form action="<?= PATH . "/" . (h($categoryPath) ?? '') ;?>" method="get" id="per-page-form">
          <label for="per-page">表示件数</label>
          <select name="per_page" id="per-page">
            <?php foreach(PER_PAGE_OPTION as $num): ?>
              <option value="<?= h($num);?>" <?= $perPage === $num ? 'selected': ''; ?>><?= $num;?></option>
            <?php endforeach;?>
          </select>
          <input type="hidden" name="page" value="<?= h($currentPage);?>">
          <input type="hidden" name="item_select" value="<?= h($item_select) ;?>">
          <input type="hidden" name="item_search" value="<?= h($item_search) ;?>">
        </form>
      </li>
    </ul>

  </div>
</main>
<script>
  const categoryMap = <?= json_encode($categoryTree, JSON_UNESCAPED_UNICODE);?>
</script>
<?php include APP_PATH . '/Views/_common/footer.php';?>