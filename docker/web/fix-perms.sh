#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="/var/www/html"
DIRS=(
  "$APP_ROOT/app/log"
  "$APP_ROOT/public/uploads"
  "$APP_ROOT/public/tmp"
)

# 1) ディレクトリ作成 & グループ=www-data へ
for d in "${DIRS[@]}"; do
  mkdir -p "$d"
  chgrp -hR www-data "$d" || true
  # 2) ディレクトリは 2775（rwxrwsr-x）= setgid 継承
  find "$d" -type d -exec chmod 2775 {} +
  # 3) ファイルは 664（rw-rw-r--）
  find "$d" -type f -exec chmod 664 {} +
done

# 4) error.log を作ってモード付与
touch "$APP_ROOT/app/log/error.log"
chgrp www-data "$APP_ROOT/app/log/error.log" || true
chmod 664 "$APP_ROOT/app/log/error.log"

# 5) 新規作成物が 664/775 系になるように
umask 0002

# 6) 最後に Apache をこのプロセスに置き換え
exec apache2-foreground