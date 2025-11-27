<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\ItemCategory;
use Exception;
use PDOException;

class ItemCategoryRepository
{
  /**
   * 新規登録
   */
  public function insert(array $request) : bool
  {
    try {
      $sql = "INSERT INTO item_categories (sub_category_id, name, slug)
              values (:sub_category_id, :name, :slug)";

      return DbConnect::execute($sql, [
        'sub_category_id' => $request['sub_category_id'],
        'name' => $request['name'],
        'slug' => $request['slug'],
      ]);

    } catch(PDOException $e) {
      ErrorHandler::log('アイテムカテゴリーの登録に失敗 : ' . $e->getMessage());
      throw new Exception('アイテムカテゴリーの登録に失敗しました');
    }
  }
  /**
   * 更新
   */
  public function update(array $request, int $id) : bool
  {
    try {
      $sql = "UPDATE item_categories
              SET sub_category_id = :sub_category_id, name = :name, slug = :slug
              WHERE id = :id";
      
      return DbConnect::execute($sql, [
        'sub_category_id' => $request['sub_category_id'],
        'name' => $request['name'],
        'slug' => $request['slug'],
        'id' => $id
      ]);

    } catch(PDOException $e) {
      ErrorHandler::log('アイテムカテゴリーの更新に失敗 : ' . $e->getMessage());
      throw new Exception('アイテムカテゴリーの更新に失敗しました');
    }

  }
  /**
   * 全データ取得
   */
  public function getAll(): ?array
  {
    try {
      $sql = "SELECT id, sub_category_id, name, slug FROM item_categories";

      return Dbconnect::fetchAll($sql);

    } catch(PDOException $e) {
      ErrorHandler::log('アイテムカテゴリーの取得に失敗 : ' . $e->getMessage());
      throw new Exception('アイテムカテゴリーの取得に失敗しました');
    }
  }
  /**
   * idから1件object取得
   */

  public function getById(int $id): ?ItemCategory
  {
    try {
      $sql = "SELECT id, sub_category_id, name, slug FROM item_categories WHERE id = :id";
      $result = Dbconnect::fetch($sql, [':id' => $id]);
      return $result ? new ItemCategory($result) : null;

    } catch(PDOException $e) {
      ErrorHandler::log('アイテムカテゴリーの取得に失敗 : ' . $e->getMessage());
      throw new Exception('アイテムカテゴリーの取得に失敗しました');
    }
  }
  /**
   * 重複確認
   */
  public function isDuplicateName(int $subCategory_id, string $name, ?int $id)
  {
    try {
      $sql = "SELECT count(*) FROM item_categories WHERE sub_category_id = :sub_category_id AND name = :name";
      if($id !== null) {
        $sql = $sql . ' AND NOT id = :id';
        $param = [':sub_category_id' => $subCategory_id, ':name' => $name, ':id' => $id];
      } else {
        $param = [':sub_category_id' => $subCategory_id, ':name' => $name];
      }

      return DbConnect::fetchColumn($sql, $param) > 0;
      
    } catch(PDOException $e) {
      ErrorHandler::log('アイテムカテゴリー数の取得に失敗 : ' . $e->getMessage());
      throw new Exception('アテイムカテゴリー数の取得に失敗しました');
    }
  }
  /**
   * selectbox用のカテゴリーグループデータ取得
   */
  public function getCategoryGroup(): array
  {
    try {
      $sql = "SELECT ca.id AS category_id, ca.name AS category_name, ca.slug AS category_slug,
                sub.id AS sub_category_id, sub.name AS sub_category_name, sub.slug AS sub_category_slug,
                it.id AS item_category_id, it.name AS item_category_name, it.slug AS item_category_slug
              FROM categories AS ca
              RIGHT JOIN sub_categories AS sub
              ON ca.id = sub.category_id
              RIGHT JOIN item_categories AS it
              ON sub.id  = it.sub_category_id;";

      return DbConnect::fetchAll($sql);

    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリーグループの取得に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリーグループの取得に失敗しました');
    }
  }
  /**
   * idからカテゴリー名だけを取得
   */
  public function getNameById(int $id): string|null
  {
    try {
      $sql = "SELECT name FROM item_categories WHERE id = :id";
      $result = DbConnect::fetch($sql, [':id' => $id]);

      return $result ? $result['name'] : null;

    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリー名の取得に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリー名の取得に失敗しました');
    }
  }
  /**
   * idから存在チェック
   */
  public function existById(int $id): bool
  {
    try {
      $sql = "SELECT count(*) FROM item_categories WHERE id = :id";

      return DbConnect::fetchColumn($sql, [':id' => $id]) > 0;
      
    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリーidの取得に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリーidの取得に失敗しました');
    }
  }

  public function getItemNameAndSubNameById(int $id): array|bool
  {
    try {
      $sql = "SELECT it.name as item_name, su.name as sub_name FROM item_categories as it
              LEFT JOIN sub_categories as su
              ON it.sub_category_id = su.id
              WHERE it.id = :id";
      
      return DbConnect::fetch($sql, [':id' => $id]);
      
    } catch(PDOException $e) {
      ErrorHandler::log('商品・サブカテゴリー名の取得に失敗 : ' . $e->getMessage());
      throw new Exception('商品・サブカテゴリー名の取得に失敗しました');
    }
  }

  public function getIdBySlug(string $parent, $sub, $item): int // 注：データがない時は0を戻す
  {
    try {
      $sql = "SELECT it.id FROM item_categories as it
              LEFT JOIN sub_categories as sub ON sub.id = it.sub_category_id
              LEFT JOIN categories AS cat ON cat.id = sub.category_id
              WHERE it.slug = :item
              AND sub.slug = :sub
              AND cat.slug = :parent";
      $param = [
        'item' => $item,
        'sub' => $sub,
        'parent' => $parent
      ];

      return DbConnect::fetchColumn($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('商品カテゴリーidの取得に失敗 : ' . $e->getMessage());
      throw new Exception('商品カテゴリーidの取得に失敗しました');
    }
  }

}