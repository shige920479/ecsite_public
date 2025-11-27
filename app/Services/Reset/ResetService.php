<?php 
namespace App\Services\Reset;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Services\Security\PasswordService;
use Exception;
use RuntimeException;

class ResetService
{
  public function run(): ResetResult
  {
    $result = new ResetResult();

    ErrorHandler::log("[RESET] start");
    DbConnect::execute("SET FOREIGN_KEY_CHECKS=0");

    try {
      // テーブル生成（schema.sql）
      ErrorHandler::log("[RESET] apply schema.sql");
      $this->safeCall(fn() => $this->runSqlFile(BASE_PATH . '/app/Database/schema.sql'), $result, 'schema.sql');
  
      // users/admins/owners insert
      ErrorHandler::log("[RESET] insert admin/owner/user");
      $this->safeCall(fn() => $this->insertBaseUsers(), $result, 'insertBaseUsers'); 
  
      // ダミーデータ挿入
      ErrorHandler::log("[RESET] apply seed_base.sql");
      $this->safeCall(fn() => $this->runSqlFile(BASE_PATH . '/app/Database/seed_base.sql'), $result, 'seed_base.sql');

      // shop画像リセット
      ErrorHandler::log("[RESET] regenerate shops");
      $this->safeCall(fn() => $this->resetShopImages($result), $result, 'shopImages');
  
      // 商品画像ファイルリセット
      ErrorHandler::log("[RESET] regenerate item images");
      $this->safeCall(fn() => $this->resetItemImages($result), $result, 'itemImages');
  
      ErrorHandler::log("[RESET] success");

    } finally {
      DbConnect::execute("SET FOREIGN_KEY_CHECKS=1");
      ErrorHandler::log("[RESET] finished");
    }

    return $result;
  }

  private function safeCall(callable $func, ResetResult $result, string $label): void
  {
    try {
      $func();
    } catch (\Throwable $e) {
      $msg = "[{$label}] " . $e->getMessage();
      ErrorHandler::log($msg);
      $result->addError($msg);
    }
  }

  private function insertBaseUsers(): void
  {
    // admin-insert
    $adminPass = PasswordService::hashPassword('admin123');
    $sql = "INSERT INTO admins (name, email, password) VALUES (:name, :email, :password)";
    $param = [
      'name' => 'admin',
      'email' => 'admin@mail.com',
      'password' => $adminPass
    ];
    DbConnect::execute($sql, $param);

    // owner-insert
    for($i=1; $i<6; $i++) {
      $ownerPass = PasswordService::hashPassword('owner123');
      $sql = "INSERT INTO owners (name, email, password) VALUES (:name, :email, :password)";
      $param = [
        'name' => "owner{$i}",
        'email' => "owner{$i}@mail.com",
        'password' => $ownerPass
      ];
      DbConnect::execute($sql, $param);
    }

    // user-insert
    $userPass = PasswordService::hashPassword('user123');
    $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $param = [
      'name' => 'user',
      'email' => 'user@mail.com',
      'password' => $userPass
    ];
    DbConnect::execute($sql, $param);
  }

  private function runSqlFile(string $path): void
  {
    if(! is_file($path)) {
      throw new RuntimeException("sqlファイルが見つかりません: {$path}");
    }
    $sql = file_get_contents($path);
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
    $sql = preg_replace('#/\*.*?\*/#s', '', $sql);
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $sql = preg_replace('/^\s*#.*$/m',  '', $sql);

    $stmts = array_filter(array_map('trim', explode(';', $sql)));

    foreach($stmts as $stmt) {
      if($stmt !== '') {
        DbConnect::execute($stmt);
      }
    }
  }

  private function resetShopImages(ResetResult $result): void
  {
    $this->clearShopImages();
    $this->regenerateShops($result);
  }

  private function clearShopImages(): void
  {
    $dir = BASE_PATH . '/public/uploads/shops/';
    $this->ensureDir($dir);

    $files = scandir($dir);
    foreach ($files as $file) {
      if($file === '.' || $file === '..') {
        continue;
      }

      $filePath = $dir . $file;
      if(is_file($filePath) && ! unlink($filePath))
        throw new Exception("削除失敗: {$filePath}");
      }
   }

  private function regenerateShops(ResetResult $result): void
  {
    $baseDir = BASE_PATH . '/public/uploads/shops/';
    $sourceDir = BASE_PATH . '/public/images/';

    $this->ensureDir($baseDir);

    $imagesFilename = ['shop1.jpg', 'shop2.jpg', 'shop3.jpg', 'shop4.jpg', 'shop5.jpg'];

    foreach($imagesFilename as $filename) {
      $from = $sourceDir . $filename;
      $to = $baseDir . $filename;

      if (!is_file($from)) {
        $msg = "[shopImages] コピー元が見つかりません: {$from}";
        ErrorHandler::log($msg);
        $result->addError($msg);
        continue;
      }

      if(!@copy($from, $to)) {
        $msg = "[shopImages] コピー失敗: {$filename}";
        ErrorHandler::log($msg);
        $result->addError($msg);
        continue;
      }
      
      @chmod($to, 0664);
    }
  }

  private function resetItemImages(ResetResult $result): void
  {
      $this->clearItemImages();
      $this->regenerateItemImages($result);
  }

  private function clearItemImages(): void
  {
    $dir = BASE_PATH . '/public/uploads/item-images/';
    $this->ensureDir($dir);

    $files = scandir($dir);

    foreach ($files as $file) {
      if($file === '.' || $file === '..') {
        continue;
      }

      $filePath = $dir . $file;
      if(is_file($filePath) && ! unlink($filePath)) {
        throw new Exception("削除失敗: {$filePath}");
      }
    }
  }

  private function regenerateItemImages(ResetResult $result): void
  {
    $baseDir = BASE_PATH . '/public/uploads/item-images/';
    $sourceDir = BASE_PATH . '/public/images/';

    $this->ensureDir($baseDir);

    $mugSources = range(1, 20);
    $towelSources = range(1, 20);

    $items = DbConnect::fetchAll("SELECT id, item_category_id FROM items");

    foreach($items as $item) {
      $itemId = $item['id'];
      $categoryId = (int)$item['item_category_id'];

      $isMug = $categoryId <= 9;
      $categoryPrefix = $isMug ? 'mug' : 'towel';
      $sourceSet = $isMug ? $mugSources : $towelSources;
      $imageCount = random_int(3, 4);
      
      for($i = 1; $i <= $imageCount; $i++) {
        $randomImageNumber = $sourceSet[array_rand($sourceSet)];
        $originalFilename = $categoryPrefix . $randomImageNumber . '.jpg';
        $newFilename = "{$categoryPrefix}_{$itemId}_{$i}.jpg";

        $from = $sourceDir . $originalFilename;
        $to = $baseDir . $newFilename;

        if (!is_file($from)) {
          $msg = "[itemImages] コピー元が見つかりません: {$from}";
          ErrorHandler::log($msg);
          $result->addError($msg);
          continue;
        }

        if(!@copy($from, $to)) {
          $msg = "[itemImages] コピ―失敗 : {$originalFilename} -> {$newFilename}";
          ErrorHandler::log($msg);
          $result->addError($msg);
          continue;
        }
        @chmod($to, 0664);

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
  }

  private function ensureDir(string $dir): void
  {
    $dir = rtrim($dir, '/');
    umask(0002);
    if(! is_dir($dir) && ! mkdir($dir, 0775, true)) {
      throw new Exception('mkdirに失敗 :' . $dir);
    }
    @chmod($dir, 02775);
  }
}
