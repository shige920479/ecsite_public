<?php
require_once BASE_PATH . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
date_default_timezone_set('Asia/Tokyo');

require_once '/app/config/config.php';
require_once '/app/functions/common.php';

use App\Database\DbConnect;

$baseDir = BASE_PATH . '/public/uploads/item-images/';

$mugSources = range(1, 20);
$towelSources = range(1, 20);

$items = DbConnect::fetchAll("SELECT id, item_category_id FROM items");

foreach($items as $item) {
  $itemId = $item['id'];
  $categoryId = (int)$item['item_category_id'];

  $isMug = $categoryId <= 9;
  $categoryPrefix = $isMug ? 'mug' : 'towel';
  $sourceSet = $isMug ? $mugSources : $towelSources;
  $imageCount = rand(3, 4);
  
  for($i = 1; $i <= $imageCount; $i++) {
    $randomImageNumber = $sourceSet[array_rand($sourceSet)];
    $originalFilename = $categoryPrefix . $randomImageNumber . '.jpg';
    $newFilename = "{$categoryPrefix}_{$itemId}_{$i}.jpg";

    $from = $baseDir . $originalFilename;
    $to = $baseDir . $newFilename;

    if(!copy($from, $to)) {
      echo "コピ―失敗 : {$originalFilename} -> {$newFilename}\n";
      continue;
    }

    $sql = "INSERT INTO item_images
            (item_id, filename, sort_order)
            VALUES
            (:item_id, :filename, :sort_order)";
    $param = [
      'item_id' => $itemId,
      'filename' => $newFilename,
      'sort_order' => $i
    ];

    DbConnect::execute($sql, $param);
  }
}
echo "item_images テーブルに画像データを登録しました。\n";