<?php
namespace App\Database;
use PDO;

class DbConnect
{
  private static PDO $pdo;

  // テスト用
  public static $beginCallback = null;
  public static $commitCallback = null;
  public static $rollbackCallback = null;
  
  public function __construct()
  {
  }

  private static function getInstance(): PDO
  {
    if(! isset(self::$pdo)) {
      list($host, $db, $user, $pass) = [DB_HOST, DB_NAME, DB_USER, DB_PASS];
      self::$pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8", $user, $pass);
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return self::$pdo;
  }

  /**
   * トランザクション開始
   * 
   * @param void
   * @return void
   */
  public static function beginTransaction(): void
  {
    if(is_callable(self::$beginCallback)) {
      call_user_func(self::$beginCallback);
      return;
    }

    if(self::getInstance()->inTransaction()) {
      return;
    }
    self::getInstance()->beginTransaction();
  }

  /**
   * トランザクションコミット
   * 
   * @param void
   * @return void
   */
  public static function commitTransaction(): void 
  {
    if(is_callable(self::$commitCallback)) {
      call_user_func(self::$commitCallback);
      return;
    }

    if(! self::getInstance()->inTransaction()) {
      return;
    }
    self::getInstance()->commit();
  }

  /**
   * トランザクションロールバック
   * 
   * @param void
   * @return void
   */
  public static function rollbackTransaction(): void
  {
    if(is_callable(self::$rollbackCallback)) {
      call_user_func(self::$rollbackCallback);
      return;
    }
    
    if(! self::getInstance()->inTransaction()) {
      return;
    }
    self::getInstance()->rollBack();
  }
  /**
   * sqlを実行し結果を取得（1行用）
   * 
   * @param string $sql sql
   * @param array $param sqlに渡すパラメーター
   * @return array|bool  クエリの実行結果を取得
   */
  public static function fetch(string $sql, array $param = []): array | bool
  {
    $stmt = self::getInstance()->prepare($sql);
    self::bindParams($stmt, $param);
    $stmt->execute();
    return $stmt->fetch();
  }

  /**
   * sqlを実行し結果を取得（複数行用）
   * 
   * @param string $sql sql
   * @param array $param sqlに渡すパラメーター
   * @return array クエリの実行結果を取得
   */
  public static function fetchAll(string $sql, array $param = []): array
  {
    $stmt = self::getInstance()->prepare($sql);
    self::bindParams($stmt, $param);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /**
   * sql実行
   * @param string $sql sql
   * @param array $param sqlに渡すパラメーター
   * @return bool 実行結果
   */
  public static function execute(string $sql, array $param = []): bool
  {
    $stmt = self::getInstance()->prepare($sql);
    self::bindParams($stmt, $param);
    return $stmt->execute();
  }
  
  public static function fetchColumn(string $sql, array $param = []): int
  {
    $stmt = self::getInstance()->prepare($sql);
    self::bindParams($stmt, $param);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  public static function fetchAllColumn(string $sql, array $param = []): array
  {
    $stmt = self::getInstance()->prepare($sql);
    self::bindParams($stmt, $param);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public static function lastInsertID(): int
  {
    return self::getInstance()->lastInsertID();
  }
  public static function executeAndRowCount(string $sql, array $param = []): int
  {
    $stmt = self::getInstance()->prepare($sql);
    self::bindParams($stmt, $param);
    $stmt->execute();
    return $stmt->rowCount();
  }

  private static function bindParams(\PDOStatement $stmt, array $param): void
  {
    $position = 1;

    foreach($param as $key => $value) {

      if(is_int($key)) {
        $type = is_int($value) ? \PDO::PARAM_INT :
                (is_bool($value) ? \PDO::PARAM_BOOL :
                (is_null($value) ? \PDO::PARAM_NULL : \PDO::PARAM_STR));
        $stmt->bindValue($position++, $value, $type);
      } else {
        $paramKey = ':' . ltrim($key, ':');

        if (in_array($key, ['limit', 'offset'])) {
          $stmt->bindValue($paramKey, (int)$value, \PDO::PARAM_INT);
        } elseif (is_int($value)) {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_INT);
        } elseif (is_bool($value)) {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_BOOL);
        } elseif (is_null($value)) {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_NULL);
        } else {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_STR);
        }
      }
    }
  }
}