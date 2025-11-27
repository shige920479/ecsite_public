<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class SubCategoryRepository
{
  public function insert(array $request) : bool
  {
    try {
      $sql = "INSERT INTO sub_categories (category_id, name, slug) values (:category_id, :name, :slug)";

      return DbConnect::execute($sql, [
        'category_id' => $request['category_id'],
        'name' => $request['name'],
        'slug' => $request['slug']
      ]);

    } catch(PDOException $e) {
      ErrorHandler::log('サブカテゴリーの登録に失敗 : ' . $e->getMessage());
      throw new Exception('サブカテゴリーの登録に失敗しました');
    }
  }

  public function getAll(): ?array
  {
    try {
      $sql = "SELECT id, category_id, name, slug FROM sub_categories";

      return Dbconnect::fetchAll($sql);

    } catch(PDOException $e) {
      ErrorHandler::log('サブカテゴリーの取得に失敗 : ' . $e->getMessage());
      throw new Exception('サブカテゴリーの取得に失敗しました');
    }
  }

  public function existById(int $id): bool
  {
    try {
      $sql = "SELECT count(*) FROM sub_categories WHERE id = :id";

      return DbConnect::fetchColumn($sql, [':id' => $id]) > 0;
      
    } catch(PDOException $e) {
      ErrorHandler::log('サブカテゴリーの存在判定に失敗 : ' . $e->getMessage());
      throw new Exception('サブカテゴリーの存在判定に失敗しました');
    }
  }

  public function isDuplicateName(int $category_id, string $name)
  {
    try {
      $sql = "SELECT count(*) FROM sub_categories WHERE category_id = :category_id AND name = :name";

      return DbConnect::fetchColumn($sql, [
        ':category_id' => $category_id,
        ':name' => $name,
      ]) > 0;
    } catch(PDOException $e) {
      ErrorHandler::log('サブカテゴリー数の取得に失敗 : ' . $e->getMessage());
      throw new Exception('サブカテゴリー数の取得に失敗しました');
    }
  }

  public function getCategoryGroup()
  {
    try {
      $sql = "SELECT ca.name as category, sub.id, sub.name FROM categories as ca
              RIGHT JOIN sub_categories as sub
              ON ca.id = sub.category_id";

      return DbConnect::fetchAll($sql);

    } catch(PDOException $e) {
      ErrorHandler::log('サブカテゴリー・カテゴリーの取得に失敗 : ' . $e->getMessage());
      throw new Exception('サブカテゴリー・カテゴリーの取得に失敗しました');
    }
  }

  public function getItemIdBySlug(string $parent, $sub): array
  {
    try {
      $sql = "SELECT it.id FROM item_categories as it
              LEFT JOIN sub_categories as sub ON sub.id = it.sub_category_id
              LEFT JOIN categories AS cat ON cat.id = sub.category_id
              WHERE sub.slug = :sub
              AND cat.slug = :parent";
      $param = [
        'sub' => $sub,
        'parent' => $parent
      ];

      return DbConnect::fetchAllColumn($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('サブカテゴリーidの取得に失敗 : ' . $e->getMessage());
      throw new Exception('サブカテゴリーidの取得に失敗しました');
    }
  }
}