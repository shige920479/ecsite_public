<?php

use App\Database\DbConnect;

trait TestTransactionHooksTrait
{
  protected function setUpTransactionHooks(): void
  {
    DbConnect::$beginCallback = fn() => file_put_contents(BASE_PATH . '/tests/tmp/test_log.txt', "begin\n", FILE_APPEND);
    DbConnect::$commitCallback = fn() => file_put_contents(BASE_PATH . '/tests/tmp/test_log.txt', "commit\n", FILE_APPEND);
    DbConnect::$rollbackCallback = fn() => file_put_contents(BASE_PATH . '/tests/tmp/test_log.txt', "rollback\n", FILE_APPEND);
  }

  protected function resetTransactionHooks(): void
  {
    DbConnect::$beginCallback = null;
    DbConnect::$commitCallback = null;
    DbConnect::$rollbackCallback = null;
  }
}