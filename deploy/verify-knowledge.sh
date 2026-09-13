#!/bin/bash
set -Eeuo pipefail

WP_ROOT="/home/dangduon6a72/public_html"
WP_BIN="$(command -v wp || true)"
PHP_BIN="$(command -v php || true)"

CODE='if (!class_exists("Bizrise_DDG_Knowledge_Seed_2026")) { fwrite(STDERR, "Knowledge seeder class missing\n"); exit(11); }
Bizrise_DDG_Knowledge_Seed_2026::seed();
$r=get_option("bizrise_ddg_knowledge_seed_2026_report", []);
$v=(string)get_option("bizrise_ddg_knowledge_seed_2026_version", "");
$q=new WP_Query([
  "post_type"=>"post",
  "post_status"=>"publish",
  "posts_per_page"=>1,
  "fields"=>"ids",
  "no_found_rows"=>false,
  "meta_key"=>"_bizrise_ddg_content_version",
  "meta_value"=>"2.0.0",
]);
$count=(int)$q->found_posts;
$out=["version"=>$v,"published_v2"=>$count,"report"=>$r];
echo wp_json_encode($out, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
if ($v!=="2.0.0" || (int)($r["total"]??0)!==40 || (int)($r["failed"]??0)!==0 || $count!==40) { exit(12); }'

if [ -n "$WP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
  OUTPUT="$(WP_CLI_PHP_ARGS='-d max_execution_time=0 -d memory_limit=512M' "$WP_BIN" --path="$WP_ROOT" eval "$CODE")" || {
    echo "[DDG VERIFY] Knowledge baseline FAILED" >&2
    exit 1
  }
  echo "[DDG VERIFY] $OUTPUT"
  exit 0
fi

if [ -n "$PHP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
  PHP_CODE="require '$WP_ROOT/wp-load.php'; $CODE"
  OUTPUT="$("$PHP_BIN" -d max_execution_time=0 -d memory_limit=512M -r "$PHP_CODE")" || {
    echo "[DDG VERIFY] Knowledge baseline FAILED" >&2
    exit 1
  }
  echo "[DDG VERIFY] $OUTPUT"
  exit 0
fi

echo "[DDG VERIFY] Cannot verify knowledge baseline: wp/php unavailable" >&2
exit 1
