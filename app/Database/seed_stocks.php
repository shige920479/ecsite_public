<?php
define('BASE_PATH', realpath(__DIR__ . '/../'));
require_once BASE_PATH . './../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH . './../');
$dotenv->load();
date_default_timezone_set('Asia/Tokyo');

require_once './../config/config.php';
require_once './../functions/common.php';

use App\Database\DbConnect;

$items = DbConnect::fetchAll("SELECT id, item_category_id FROM items");

foreach($items as $item) {
  $itemId = $item['id'];
  $stocksData = [rand(10,30), rand(-5, -1), rand(1,15)]; // 最大3件
  $dataCount = rand(2,3); // 2～3件

  for($i=0; $i < $dataCount; $i++) {
    $stockDiff = $stocksData[$i];
    if(! is_numeric($stockDiff) || $stockDiff === 0) {
      continue;
    }
    $createdAt = date('Y-m-d H:i:s', strtotime("+{$i} minutes"));

    $sql = "INSERT INTO stocks (item_id, stock_diff, created_at)
            VALUES
            (:item_id, :stock_diff, :created_at)";
    $param = [
      'item_id' => $itemId,
      'stock_diff' => $stocksData[$i],
      'created_at' => $createdAt
    ];
    DbConnect::execute($sql, $param);
  }
}
echo "stocksテーブルにデータを登録しました。\n";