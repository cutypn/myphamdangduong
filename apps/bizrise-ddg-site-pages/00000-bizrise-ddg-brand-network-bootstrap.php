<?php
/**
 * Plugin Name: Bizrise DDG Brand Network Bootstrap
 * Description: Idempotently provisions DDG brand subsites and exposes a safe production health probe.
 * Version: 2.0.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Brand_Network_Bootstrap {
    private const VERSION = '2.0.0';
    private const NETWORK_DOMAIN = 'dangduonggroup.com';
    private const STATE_OPTION = 'bizrise_ddg_brand_network_bootstrap_v2';

    private static function brands(): array {
        return [
            'one-today'      => ['title' => 'One Today',      'theme' => 'ddg-one-today',      'agent' => 'DDG-CONTENT-ONE-TODAY'],
            'she-one'        => ['title' => 'She One',        'theme' => 'ddg-she-one',        'agent' => 'DDG-CONTENT-SHE-ONE'],
            'x2'             => ['title' => 'Cream X2',       'theme' => 'ddg-x2',             'agent' => 'DDG-CONTENT-X2'],
            'hatagold'       => ['title' => 'Hatagold',       'theme' => 'ddg-hatagold',       'agent' => 'DDG-CONTENT-HATAGOLD'],
            'ever-today'     => ['title' => 'Ever Today',     'theme' => 'ddg-ever-today',     'agent' => 'DDG-CONTENT-EVER-TODAY'],
            'one-today-gold' => ['title' => 'One Today Gold', 'theme' => 'ddg-one-today-gold', 'agent' => 'DDG-CONTENT-ONE-TODAY-GOLD'],
        ];
    }

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_provision'], 3);
        add_action('template_redirect', [__CLASS__, 'health_probe'], -1000);
    }

    public static function maybe_provision(): void {
        if (get_site_option(self::STATE_OPTION) === self::VERSION && self::is_ready()) { return; }
        self::provision(false);
    }

    public static function provision(bool $force = false): array {
        $report = ['ok' => false, 'created' => 0, 'updated' => 0, 'errors' => []];
        if (!is_multisite()) { $report['errors'][] = 'not_multisite'; return $report; }
        if (!is_subdomain_install()) { $report['errors'][] = 'not_subdomain_install'; return $report; }

        $network = get_network();
        if (!$network || strtolower((string)$network->domain) !== self::NETWORK_DOMAIN) {
            $report['errors'][] = 'unexpected_network_domain';
            return $report;
        }

        if (!$force && get_site_option(self::STATE_OPTION) === self::VERSION && self::is_ready()) {
            $report['ok'] = true;
            return $report;
        }

        $owner_id = self::network_owner_id();
        if ($owner_id < 1) { $report['errors'][] = 'network_owner_missing'; return $report; }

        $allowed = get_site_option('allowedthemes', []);
        if (!is_array($allowed)) { $allowed = []; }
        foreach (self::brands() as $brand) {
            $theme = wp_get_theme($brand['theme']);
            if (!$theme->exists()) {
                $report['errors'][] = 'theme_missing:' . $brand['theme'];
                continue;
            }
            $allowed[$brand['theme']] = true;
        }
        update_site_option('allowedthemes', $allowed);

        foreach (self::brands() as $slug => $brand) {
            $domain = $slug . '.' . self::NETWORK_DOMAIN;
            $site = get_site_by_path($domain, '/');
            $blog_id = $site ? (int)$site->blog_id : 0;

            if ($blog_id < 1) {
                $created = wpmu_create_blog(
                    $domain,
                    '/',
                    $brand['title'],
                    $owner_id,
                    ['public' => 1],
                    (int)$network->id
                );
                if (is_wp_error($created)) {
                    $report['errors'][] = 'create_failed:' . $slug . ':' . $created->get_error_code();
                    continue;
                }
                $blog_id = (int)$created;
                $report['created']++;
            }

            switch_to_blog($blog_id);
            $theme = wp_get_theme($brand['theme']);
            if (!$theme->exists()) {
                $report['errors'][] = 'theme_missing_after_switch:' . $brand['theme'];
                restore_current_blog();
                continue;
            }
            if (get_stylesheet() !== $brand['theme']) {
                switch_theme($brand['theme']);
            }
            update_option('blogname', $brand['title']);
            update_option('bizrise_brand_key', $slug);
            update_option('bizrise_brand_content_agent', $brand['agent']);
            update_option('bizrise_brand_theme', $brand['theme']);
            restore_current_blog();
            $report['updated']++;
        }

        $report['ok'] = empty($report['errors']) && self::is_ready();
        if ($report['ok']) {
            update_site_option(self::STATE_OPTION, self::VERSION);
            clean_blog_cache(get_main_site_id());
        }
        return $report;
    }

    public static function status(): array {
        $sites = [];
        foreach (self::brands() as $slug => $brand) {
            $domain = $slug . '.' . self::NETWORK_DOMAIN;
            $site = is_multisite() ? get_site_by_path($domain, '/') : null;
            $blog_id = $site ? (int)$site->blog_id : 0;
            $theme = '';
            if ($blog_id > 0) {
                switch_to_blog($blog_id);
                $theme = (string)get_stylesheet();
                restore_current_blog();
            }
            $sites[$slug] = [
                'domain' => $domain,
                'blog_id' => $blog_id,
                'theme' => $theme,
                'expected_theme' => $brand['theme'],
                'ready' => $blog_id > 0 && $theme === $brand['theme'],
            ];
        }
        return [
            'status' => self::is_ready() ? 'PASS' : 'FAIL',
            'version' => self::VERSION,
            'code_hash' => hash_file('sha256', __FILE__),
            'multisite' => is_multisite(),
            'subdomain_install' => is_multisite() ? is_subdomain_install() : false,
            'network_domain' => is_multisite() && get_network() ? (string)get_network()->domain : '',
            'sites' => $sites,
        ];
    }

    public static function health_probe(): void {
        if (!isset($_GET['ddg_network_health']) || (string)$_GET['ddg_network_health'] !== '1') { return; }
        if (!is_main_site()) { return; }
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        echo wp_json_encode(self::status(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private static function is_ready(): bool {
        if (!is_multisite() || !is_subdomain_install()) { return false; }
        $network = get_network();
        if (!$network || strtolower((string)$network->domain) !== self::NETWORK_DOMAIN) { return false; }
        foreach (self::brands() as $slug => $brand) {
            $site = get_site_by_path($slug . '.' . self::NETWORK_DOMAIN, '/');
            if (!$site) { return false; }
            switch_to_blog((int)$site->blog_id);
            $active = (string)get_stylesheet();
            restore_current_blog();
            if ($active !== $brand['theme']) { return false; }
        }
        return true;
    }

    private static function network_owner_id(): int {
        foreach (get_super_admins() as $login) {
            $user = get_user_by('login', $login);
            if ($user) { return (int)$user->ID; }
        }
        $email = (string)get_site_option('admin_email');
        if ($email !== '') {
            $user = get_user_by('email', $email);
            if ($user) { return (int)$user->ID; }
        }
        return 0;
    }
}

Bizrise_DDG_Brand_Network_Bootstrap::boot();
