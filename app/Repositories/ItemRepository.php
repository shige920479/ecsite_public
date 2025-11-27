<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\Item;
use App\Services\Core\SessionService;
use Exception;
use PDOException;

class ItemRepository
{
  public function getAllItems(): array
  {
    try {
      $sql = "SELECT it.id AS item_id , it.name AS item_name, sh.name
              AS shop_name, img.filename as filename, it.price AS price
              FROM items as it
              LEFT JOIN shops as sh ON it.shop_id = sh.id
              LEFT JOIN (SELECT * FROM item_images WHERE sort_order = 1) AS img ON img.item_id = it.id
              WHERE it.deleted_at IS NULL";
      
      return DbConnect::fetchAll($sql);

    } catch(PDOException $e) {
      ErrorHandler::log('全商品情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('全商品情報の取得に失敗しました');
    }
  }

  public function insert(array $previewData): int
  {
    try {
      $sql = "INSERT INTO items
              (shop_id, item_category_id, name, information, price, sort_order, is_selling)
              VALUES
              (:shop_id, :item_category_id, :name, :information, :price, :sort_order, :is_selling)
              ";
      $param = [
        'shop_id' => $previewData['shop_id'],
        'item_category_id' => $previewData['item_category_id'],
        'name' => $previewData['name'],
        'information' => $previewData['information'],
        'price' => $previewData['price'],
        'sort_order' => $previewData['sort_order'],
        'is_selling' => $previewData['is_selling']
      ];

      DbConnect::execute($sql, $param);
      return DbConnect::lastInsertID();

    } catch(PDOException $e) {
      ErrorHandler::log('新規商品登録に失敗 : ' . $e->getMessage());
      throw new Exception('新規商品登録に失敗しました');
    }
  }

  public function update(array $previewData, int $ownerId): int
  {
    try {
      $sql = "UPDATE items as it
              LEFT JOIN shops as sh ON sh.id = it.shop_id
              SET it.shop_id = :shop_id, it.item_category_id = :item_category_id, it.name = :name, it.price = :price,
              it.sort_order = :sort_order, it.information = :information, it.is_selling = :is_selling
              WHERE it.id = :item_id
              AND sh.owner_id = :owner_id";

      $param = [
        'shop_id' => $previewData['shop_id'],
        'item_category_id' => $previewData['item_category_id'],
        'name' => $previewData['name'],
        'information' => $previewData['information'],
        'price' => $previewData['price'],
        'sort_order' => $previewData['sort_order'],
        'is_selling' => $previewData['is_selling'],
        'item_id' => $previewData['item_id'],
        'owner_id' => $ownerId
      ];

      return DbConnect::executeAndRowCount($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('商品登録変更に失敗 : ' . $e->getMessage());
      throw new Exception('新規商品変更に失敗しました');
    }
  }

  public function softDelete(int $itemId): bool
  {
    try {
      $sql = "UPDATE items SET deleted_at = NOW() WHERE id = :id";

      return DbConnect::execute($sql, ['id' => $itemId]);

    } catch(PDOException $e) {
      ErrorHandler::log('商品の削除に失敗 : ' . $e->getMessage());
      throw new Exception('商品の削除に失敗しました');
    }
  }

  public function findItemById(int $itemId): ?Item
  {
    try {
      $sql = "SELECT * FROM items WHERE id = :id";
      $result = DbConnect::fetch($sql, ['id' => $itemId]);
      return $result ? new Item($result) : null;

    } catch(PDOException $e) {
      ErrorHandler::log('商品情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('商品情報の取得に失敗しました');
    }
  }

  public function getOwnerIdByItemId(int $itemId): ?int
  {
    try {
      $sql = "SELECT sh.owner_id as owner_id FROM shops as sh
              LEFT JOIN items as it
              ON sh.id = it.shop_id
              WHERE it.id = :id";
      return DbConnect::fetchColumn($sql, ['id' => $itemId]);

    } catch(PDOException $e) {
      ErrorHandler::log('オーナーidの取得に失敗 : ' . $e->getMessage());
      throw new Exception('オーナーidの取得に失敗しました');
    }
  }

  public function existById(int $itemId): bool
  {
    try {
      $sql = "SELECT count(*) FROM items WHERE id = :id";

      return DbConnect::fetchColumn($sql, ['id' => $itemId]) > 0; 

    } catch(PDOException $e) {
      ErrorHandler::log('商品情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('商品情報の取得に失敗しました');
    }
  }

  public function getAllItemByOwner(int $ownerId): array
  {
    try {
      $sql = "SELECT it.id, it.name, itc.name AS category, suc.name AS sub_category, it.price AS price,
              img.filename AS filename, stk.stock_qty AS stock_qty, it.is_selling AS is_selling
              FROM items AS it
              LEFT JOIN item_categories AS itc
              ON itc.id = it.item_category_id
              LEFT JOIN sub_categories AS suc
              ON suc.id = itc.sub_category_id
              LEFT JOIN item_images AS img ON it.id = img.item_id AND img.sort_order = 1
              LEFT JOIN (SELECT item_id, COALESCE(SUM(stocks.stock_diff), 0) AS stock_qty FROM stocks GROUP BY item_id) AS stk
              ON stk.item_id = it.id
              LEFT JOIN shops ON shops.id = it.shop_id
              WHERE shops.owner_id = :owner_id
              AND it.deleted_at IS NULL";

      return DbConnect::fetchAll($sql, ['owner_id' => $ownerId]);

    } catch(PDOException $e) {
      ErrorHandler::log("オーナーID:{$ownerId}の商品情報取得に失敗 : " . $e->getMessage());
      throw new Exception('商品情報取得に失敗しました');
    }
  }

  public function isDuplicateName(int $shopId, string $name, ?int $itemId): bool
  {
    try {
      $sql = "SELECT count(*) FROM items WHERE shop_id = :shop_id AND name = :name AND deleted_at IS NULL";
      $param = ['shop_id' => $shopId, 'name' => $name];
      if($itemId !== null) {
        $sql = $sql . " AND id != :id";
        $param['id'] = $itemId;
      } 
      
      return DbConnect::fetchColumn($sql, $param) > 0;

    } catch(PDOException $e) {
      ErrorHandler::log('商品名の重複確認に失敗 : ' . $e->getMessage());
      throw new Exception('商品名の重複確認に失敗しました');
    }
  }

  /**
   * ページネート
   * @param int $page 現在のページ
   * @param int $perPage 1ページあたりの表示数
   * @return array [$items 表示データ、$total 全件数、$page 現在のページ、$total_pages 頁数 
   */
  public function getPagenateData(int $page, string $querySearch, string $keyword, string $queryCategory, int|array|null $categoryParam, string $querySelect, ?int $perPage = 8): array
  {
    try {
      $total = $this->countAll($querySearch, $keyword, $queryCategory, $categoryParam);

      $offset = ($page - 1) * $perPage;
      $totalPages = (int)ceil($total / $perPage);
      if($totalPages > 0 && $page > $totalPages) {
          $page = $totalPages;
          $offset = ($totalPages - 1) * $perPage;
      } elseif($totalPages === 0) {
          $page = 1;
          $offset = 0;
      }

      $baseSql = "SELECT it.id AS item_id , it.name AS item_name, ict.name AS category,
                  sh.name AS shop_name, img.filename as filename, it.price AS price
                  FROM items as it
                  LEFT JOIN shops as sh ON it.shop_id = sh.id
                  LEFT JOIN item_categories as ict ON it.item_category_id = ict.id
                  LEFT JOIN (SELECT * FROM item_images WHERE sort_order = 1) AS img ON img.item_id = it.id
                  WHERE it.deleted_at IS NULL";
      $addSql = " LIMIT :limit OFFSET :offset";

      $sql = $baseSql . $querySearch . $queryCategory . $querySelect . $addSql;

      $param = $this->createParam($querySearch, $keyword, $queryCategory, $categoryParam);
      $param['limit'] = $perPage;
      $param['offset'] = $offset;

      $items = DbConnect::fetchAll($sql, $param);

      return [
        'items' => $items,
        'total' => $total,
        'current_page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
      ];

    } catch(PDOException $e) {
      ErrorHandler::log('ページデータの取得に失敗 : ' . $e->getMessage());
      throw new Exception('ページデータの取得に失敗しました');
    }
  }
  /**
   * 個別商品情報/ショップ名/在庫数を取得
   */
  public function getItemAndShopAndCategoryAndStockById(int $id): array
  {
    try {
      $sql = "SELECT it.id AS item_id, it.name AS item_name, it.information AS information, it.price AS price, it.is_selling AS is_selling,
              sh.name AS shop_name, stc.stock_qty AS stock_qty, itc.name AS item_category, sub.name AS sub_category, ca.name AS category
              FROM items AS it
              JOIN shops AS sh
              ON sh.id = it.shop_id
              LEFT JOIN (SELECT item_id, sum(stock_diff) AS stock_qty FROM stocks GROUP BY item_id) AS stc
              ON stc.item_id = it.id
              JOIN item_categories AS itc
              ON itc.id = it.item_category_id
              JOIN sub_categories AS sub
              ON sub.id = itc.sub_category_id
              JOIN categories AS ca
              ON ca.id = sub.category_id
              WHERE it.id = :id";
      
      return DbConnect::fetch($sql, ['id' => $id]);

    } catch(PDOException $e) {
      ErrorHandler::log('個別商品/ショップ名/在庫情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('個別商品/ショップ名/在庫情報の取得に失敗しました');
    }

  }


  /**
   * items 件数取得
   */
  private function countAll(string $querySearch, $keyword, $queryCategory, int|array|null $categoryParam): int
  {
    try {
      $baseSql = "SELECT count(id) FROM items as it WHERE deleted_at IS NULL";
      $sql = $baseSql . $querySearch . $queryCategory;
      
      $param = $this->createParam($querySearch, $keyword, $queryCategory, $categoryParam);
      
      if($param !== null) {
        return DbConnect::fetchColumn($sql, $param);
      } else {
        return DbConnect::fetchColumn($sql);
      }

    } catch(PDOException $e) {
      ErrorHandler::log('商品件数の取得に失敗 : ' . $e->getMessage());
      throw new Exception('商品件数の取得に失敗しました');
    }
  }

  private function createParam(string $querySearch, $keyword, $queryCategory, int|array|null $categoryIds)
  {
    if(! empty($querySearch) && ! empty($queryCategory)) {
      if(is_array($categoryIds)) {
        $param = array_merge($categoryIds, ['keyword' => $keyword]);
      } else {
        $param = [
          'item_category_id' => $categoryIds,
          'keyword' => $keyword
        ];
      }
    } elseif(! empty($queryCategory)) {
      if(is_array($categoryIds)) {
        $param = $categoryIds;
      } else {
        $param = ['item_category_id' => $categoryIds];
      }
    } elseif(! empty($querySearch)) {
      $param = ['keyword' => $keyword];
    } else {
      $param = null;
    }

    return $param;
  }
}