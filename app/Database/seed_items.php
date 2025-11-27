<?php
define('BASE_PATH', realpath(__DIR__ . '/../'));
require_once BASE_PATH . './../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH . './../');
$dotenv->load();
date_default_timezone_set('Asia/Tokyo');

require_once './../config/config.php';
require_once './../functions/common.php';

use App\Database\DbConnect;

for ($i = 1; $i <= 100; $i++) {
  $shopId = rand(1, 5);
  $itemCategoryId = rand(1, 18);
  $name = "ダミー商品{$i}";
  $information = "これはダミー商品{$i}の商品情報です。これはダミー商品{$i}の商品情報です。\nこれはダミー商品{$i}の商品情報です。";
  $price = mt_rand(80, 300) * 10;

  $sql = "INSERT INTO items
          (shop_id, item_category_id, name, information, price)
          VALUES (:shop_id, :item_category_id, :name, :information, :price)";
  $param = [
    "shop_id" => $shopId,
    "item_category_id" => $itemCategoryId,
    "name" => $name,
    "information" => $information,
    "price" => $price
  ];

  DbConnect::execute($sql, $param);

}

echo "ダミー商品を100件登録しました\n";