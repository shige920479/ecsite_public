<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class CategoryRepository
{
  public function insert(array $request) : bool
  {
    try {
      $sql = "INSERT INTO categories (name, slug) values (:name, :slug)";

      return DbConnect::execute($sql, [
        'name' => $request['name'],
        'slug' => $request['slug']
      ]);

    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリーの登録に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリーの登録に失敗しました');
    }
  }

  public function getAll(): ?array
  {
    try {
      $sql = "SELECT id, name, slug FROM categories";

      return Dbconnect::fetchAll($sql);

    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリーの取得に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリーの取得に失敗しました');
    }
  }

  public function existById(int $id)
  {
    try {
      $sql = "SELECT count(*) FROM categories WHERE id = :id";

      return DbConnect::fetchColumn($sql, [':id' => $id]) > 0;
      
    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリーidの取得に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリーidの取得に失敗しました');
    }
  }

  public function isDuplicateName(string $name): bool
  {
    try {
      $sql = "SELECT count(*) FROM categories WHERE name = :name";

      return DbConnect::fetchColumn($sql, ['name' => $name]) > 0;
      
    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリー数の重複確認に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリー数の重複確認に失敗しました');
    }
  }
  public function isDuplicateSlug(string $slug): bool
  {
    try {
      $sql = "SELECT count(*) FROM categories WHERE slug = :slug";

      return DbConnect::fetchColumn($sql, ['slug' => $slug]) > 0;
      
    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリースラグの重複確認に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリースラグの重複確認に失敗しました');
    }
  }

  public function getItemIdBySlug(string $slug): array // 注：データがない時は0を戻す
  {
    try {
      $sql = "SELECT it.id FROM item_categories AS it
              LEFT JOIN sub_categories AS sub ON sub.id = it.sub_category_id
              LEFT JOIN categories AS ca ON ca.id = sub.category_id
              WHERE ca.slug = :slug";

      return DbConnect::fetchAllColumn($sql, ['slug' => $slug]);

    } catch(PDOException $e) {
      ErrorHandler::log('カテゴリーidの取得に失敗 : ' . $e->getMessage());
      throw new Exception('カテゴリーidの取得に失敗しました');
    }
  }
}