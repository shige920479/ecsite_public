#!/bin/bash
set -e

echo "=========================================="
echo " Laravel_my_golf_range クリーンアップツール "
echo "=========================================="

# 削除対象一覧（dry-run 表示用）
TARGETS=(
    "app/Controller/SuperUserController.php"
    "app/Dev"
    "app/Views/sessionView.php"
    "app/Views/super-user.php"
    "routes/dev.php"
    "./.phpunit.cache"
    "./.phpunit.result.cache"
    "phpstan.neon"
    "progress.txt"
    "vendor"
    "public/uploads/item-images/*"
    "public/uploads/shops/*"
)

echo ""
echo "🔍 以下のファイル・ディレクトリを削除します："
echo ""

for TARGET in "${TARGETS[@]}"; do
    echo " - $TARGET"
done

echo ""
read -p "本当に削除しますか？ (y/N): " CONFIRM

if [[ "$CONFIRM" != "y" && "$CONFIRM" != "Y" ]]; then
    echo "キャンセルしました。"
    exit 1
fi

echo ""
echo "🧹 削除処理を開始します..."
echo ""

# 削除処理
for TARGET in "${TARGETS[@]}"; do
    if ls $TARGET >/dev/null 2>&1; then
        rm -rf $TARGET
        echo "✔ 削除: $TARGET"
    else
        echo "⚠ なし: $TARGET"
    fi
done

echo ""
echo "=========================================="
echo "  Clean up 完了しました！"
echo "=========================================="