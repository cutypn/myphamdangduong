<?php
/**
 * Plugin Name: Bizrise DDG Public Content 2026
 * Description: Renders cleaned public-facing DDG website copy from the public content master.
 * Version: 1.0.1
 */
if (!defined('ABSPATH')) exit;

final class Bizrise_DDG_Public_Content_2026 {
    private const FILE = 'DDG_PUBLIC_CONTENT_MASTER_2026.md';

    private static array $routes = [
        '/', '/ve-dang-duong/', '/cau-chuyen-dang-duong/', '/tam-nhin-su-menh/', '/gia-tri-thuong-hieu/', '/media-su-kien/',
        '/nang-luc/', '/gia-cong-my-pham/', '/oem-odm-my-pham/', '/nha-may-san-xuat-my-pham/', '/nghien-cuu-phat-trien/', '/phat-trien-cong-thuc/', '/quy-trinh-chat-luong/', '/quy-trinh-gia-cong-my-pham/',
        '/thuong-hieu/', '/thuong-hieu/one-today/', '/thuong-hieu/hatagold/', '/thuong-hieu/she-one/',
        '/san-pham/', '/san-pham/duong-sang-deu-mau/', '/san-pham/cham-soc-da-mun/', '/san-pham/chong-nang/', '/san-pham/dau-hieu-lao-hoa/', '/san-pham/cham-soc-body/',
        '/routine-buoi-sang/', '/routine-buoi-toi/', '/starter-routine/', '/complete-routine/',
        '/kien-thuc/', '/hieu-lan-da/', '/thanh-phan-my-pham/', '/routine-cach-dung/', '/cau-chuyen-san-pham/',
        '/doi-tac/', '/he-thong-phan-phoi/', '/tim-diem-ban/', '/tro-thanh-dai-ly/', '/affiliate/', '/hop-tac-oem-odm/', '/lien-he/'
    ];

    private static array $seo = [];

    public static function boot(): void {
        add_action('template_redirect', [__CLASS__, 'render'], -200);
        add_filter('pre_get_document_title', [__CLASS__, 'title'], 120);
        add_action('wp_head', [__CLASS__, 'meta'], 1);
    }

    private static function path(): string {
        $path = wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($path !== '/') $path = '/' . trim($path, '/') . '/';
        return $path;
    }

    private static function source(): string {
        $paths = [
            WPMU_PLUGIN_DIR . '/data/ddg-content/' . self::FILE,
            dirname(rtrim(ABSPATH, '/')) . '/repositories/myphamdangduong/docs/content/' . self::FILE,
        ];
        foreach ($paths as $path) {
            if (is_readable($path)) return (string) file_get_contents($path);
        }
        return '';
    }

    private static function section(string $markdown, string $path): string {
        $lines = preg_split('/\r?\n/u', $markdown);
        $start = -1;
        foreach ($lines as $i => $line) {
            if (preg_match('/^#\s+\d+\.\s+`([^`]+)`\s*$/u', trim($line), $m) && $m[1] === $path) {
                $start = $i;
                break;
            }
        }
        if ($start < 0) return '';
        $out = [];
        for ($i = $start + 1, $n = count($lines); $i < $n; $i++) {
            if (preg_match('/^#\s+\d+\.\s+`/u', trim($lines[$i]))) break;
            $out[] = $lines[$i];
        }
        return trim(implode("\n", $out));
    }

    private static function field(string $section, string $name): string {
        $name = preg_quote($name, '/');
        if (preg_match('/^(?:-\s*)?\*\*' . $name . ':\*\*\s*(.+)$/miu', $section, $m)) return trim($m[1]);
        return '';
    }

    private static function sub(string $section, string $name): string {
        $lines = preg_split('/\r?\n/u', $section);
        $start = -1; $level = 0;
        foreach ($lines as $i => $line) {
            if (preg_match('/^(#{2,5})\s+(.+)$/u', trim($line), $m) && strtolower(trim($m[2])) === strtolower($name)) {
                $start = $i; $level = strlen($m[1]); break;
            }
        }
        if ($start < 0) return '';
        $out = [];
        for ($i = $start + 1, $n = count($lines); $i < $n; $i++) {
            if (preg_match('/^(#{2,5})\s+/u', trim($lines[$i]), $m) && strlen($m[1]) <= $level) break;
            $out[] = $lines[$i];
        }
        return trim(implode("\n", $out));
    }

    private static function first_paragraph(string $text): string {
        foreach (preg_split('/\n\s*\n/u', trim($text)) as $p) {
            $p = trim(wp_strip_all_tags($p));
            if ($p !== '') return $p;
        }
        return '';
    }

    private static function public_body(string $section): string {
        $lines = preg_split('/\r?\n/u', $section);
        $out = [];
        $skip = false; $skip_level = 0;
        foreach ($lines as $line) {
            $trim = trim($line);
            if (preg_match('/^-\s+\*\*(Primary Keyword|SEO Title|Meta Description|H1):\*\*/iu', $trim)) continue;
            if (preg_match('/^(#{2,5})\s+(.+)$/u', $trim, $m)) {
                $level = strlen($m[1]);
                $title = trim($m[2]);
                if ($skip && $level <= $skip_level) $skip = false;
                if (in_array(strtolower($title), ['direct answer', 'hero'], true)) {
                    $skip = true; $skip_level = $level; continue;
                }
            }
            if (!$skip) $out[] = $line;
        }
        return trim(implode("\n", $out));
    }

    private static function heading(string $text): string {
        return trim((string) preg_replace('/^H[2-6]\s*[—-]\s*/u', '', $text));
    }

    private static function md(string $markdown): string {
        $lines = preg_split('/\r?\n/u', trim($markdown));
        $html = ''; $list = null;
        $flush = function() use (&$html, &$list) {
            if ($list) { $html .= '</' . $list . '>'; $list = null; }
        };
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') { $flush(); continue; }
            if (preg_match('/^##\s+(.+)$/u', $t, $m)) { $flush(); $html .= '<h2>' . esc_html(self::heading($m[1])) . '</h2>'; continue; }
            if (preg_match('/^###\s+(.+)$/u', $t, $m)) { $flush(); $html .= '<h3>' . esc_html(self::heading($m[1])) . '</h3>'; continue; }
            if (preg_match('/^\d+\.\s+(.+)$/u', $t, $m)) {
                if ($list !== 'ol') { $flush(); $list = 'ol'; $html .= '<ol>'; }
                $html .= '<li>' . esc_html($m[1]) . '</li>'; continue;
            }
            if (preg_match('/^-\s+(.+)$/u', $t, $m)) {
                if ($list !== 'ul') { $flush(); $list = 'ul'; $html .= '<ul>'; }
                $html .= '<li>' . esc_html($m[1]) . '</li>'; continue;
            }
            $flush();
            if (preg_match('/^(.+?)\s+→\s+`(\/[^`]+)`$/u', $t, $m)) {
                $html .= '<p><a href="' . esc_url(home_url($m[2])) . '">' . esc_html($m[1]) . '</a></p>';
            } else {
                $clean = preg_replace('/\*\*(.+?)\*\*/u', '$1', $t);
                $html .= '<p>' . esc_html($clean) . '</p>';
            }
        }
        $flush();
        return $html;
    }

    public static function render(): void {
        if (is_admin() || is_feed() || wp_doing_ajax()) return;
        $path = self::path();
        if (!in_array($path, self::$routes, true)) return;
        $section = self::section(self::source(), $path);
        if ($section === '') return;

        $h1 = self::field($section, 'H1') ?: 'Đăng Dương Group';
        $title = self::field($section, 'SEO Title') ?: $h1 . ' | Đăng Dương Group';
        $meta = self::field($section, 'Meta Description') ?: self::first_paragraph(self::sub($section, 'Direct Answer'));
        $answer = self::first_paragraph(self::sub($section, 'Direct Answer'));
        $hero = self::sub($section, 'Hero');
        $eyebrow = self::field($hero, 'Eyebrow') ?: 'ĐĂNG DƯƠNG GROUP';
        $support = self::field($hero, 'Support') ?: $answer;

        self::$seo = [$title, $meta];
        status_header(200);
        get_header();
        echo '<main class="ddgcf">';
        echo '<section class="ddgcf-hero"><div><p class="ey">' . esc_html($eyebrow) . '</p><h1>' . esc_html($h1) . '</h1><p class="lead">' . esc_html($support ?: $answer) . '</p></div></section>';
        echo '<article><div class="ddgcf-in">' . wp_kses_post(self::md(self::public_body($section))) . '</div></article>';
        echo '</main>';
        get_footer();
        exit;
    }

    public static function title(string $title): string {
        if (self::$seo) return self::$seo[0];
        $path = self::path();
        if (!in_array($path, self::$routes, true)) return $title;
        $section = self::section(self::source(), $path);
        return self::field($section, 'SEO Title') ?: $title;
    }

    public static function meta(): void {
        $path = self::path();
        if (!in_array($path, self::$routes, true)) return;
        $section = self::section(self::source(), $path);
        if ($section === '') return;
        $meta = self::field($section, 'Meta Description') ?: self::first_paragraph(self::sub($section, 'Direct Answer'));
        if ($meta !== '') echo '<meta name="description" content="' . esc_attr($meta) . '">' . "\n";
    }
}
Bizrise_DDG_Public_Content_2026::boot();
