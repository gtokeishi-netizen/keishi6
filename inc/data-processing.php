<?php
/**
 * Grant Insight Perfect - Data Functions
 *
 * データ処理、ヘルパー関数、パフォーマンス最適化を統合管理
 * 
 * @package Grant_Insight_Perfect
 * @version 8.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =============================================================================
 * 1. データ処理・フォーマット関数
 * =============================================================================
 */

/**
 * 統一金額フォーマット関数
 */
function gi_format_amount_unified($amount_numeric, $amount_text = '') {
    $numeric = intval($amount_numeric);
    
    // 数値が0以下で、テキストが存在する場合はパース
    if ($numeric <= 0 && !empty($amount_text)) {
        $numeric = gi_parse_amount_from_text($amount_text);
    }
    
    // フォーマット処理
    if ($numeric >= 100000000) {
        $oku = $numeric / 100000000;
        return $oku == floor($oku) ? number_format($oku) . '億円' : number_format($oku, 1) . '億円';
    } elseif ($numeric >= 10000) {
        $man = $numeric / 10000;
        return $man == floor($man) ? number_format($man) . '万円' : number_format($man, 1) . '万円';
    } elseif ($numeric > 0) {
        return number_format($numeric) . '円';
    }
    
    return !empty($amount_text) ? $amount_text : '金額未設定';
}

/**
 * テキストから金額数値を抽出
 */
function gi_parse_amount_from_text($text) {
    if (empty($text)) return 0;
    
    // 全角数字を半角に変換
    $text = mb_convert_kana($text, 'n');
    
    // パターンマッチング
    $patterns = [
        '/(\d+)億円/' => 100000000,
        '/(\d+\.?\d*)億円/' => 100000000,
        '/(\d+)万円/' => 10000,
        '/(\d+\.?\d*)万円/' => 10000,
        '/(\d+)円/' => 1,
        '/(\d+)/' => 1
    ];
    
    foreach ($patterns as $pattern => $multiplier) {
        if (preg_match($pattern, $text, $matches)) {
            return floatval($matches[1]) * $multiplier;
        }
    }
    
    return 0;
}

/**
 * 締切日のフォーマット表示
 */
function gi_format_deadline_for_display($deadline) {
    if (empty($deadline)) return '未定';
    
    // 既に日本語形式の場合はそのまま返す
    if (preg_match('/\d+年\d+月\d+日/', $deadline)) {
        return $deadline;
    }
    
    $timestamp = is_numeric($deadline) ? intval($deadline) : strtotime($deadline);
    if ($timestamp) {
        return date('Y年n月j日', $timestamp);
    }
    
    return $deadline;
}

/**
 * 残り日数計算
 */
function gi_calculate_days_remaining($deadline) {
    if (empty($deadline)) return null;
    
    $deadline_timestamp = is_numeric($deadline) ? intval($deadline) : strtotime($deadline);
    if (!$deadline_timestamp) return null;
    
    $current_timestamp = current_time('timestamp');
    $diff = $deadline_timestamp - $current_timestamp;
    
    return $diff > 0 ? ceil($diff / (60 * 60 * 24)) : 0;
}

/**
 * ステータス表示のマッピング
 */
function gi_map_application_status_ui($status) {
    $status_map = [
        'open' => '募集中',
        'active' => '募集中',
        'upcoming' => '募集予定',
        'closed' => '募集終了',
        'suspended' => '一時停止',
        'draft' => '下書き'
    ];
    
    return $status_map[$status] ?? $status;
}

/**
 * 安全なメタフィールド取得
 */
function gi_safe_get_meta($post_id, $meta_key, $default = '') {
    $value = get_post_meta($post_id, $meta_key, true);
    
    // 値が存在し、空でない場合
    if ($value !== false && $value !== '' && $value !== null) {
        return $value;
    }
    
    // ACFフィールドとしても試行
    if (function_exists('get_field')) {
        $acf_value = get_field($meta_key, $post_id);
        if ($acf_value !== false && $acf_value !== null) {
            return $acf_value;
        }
    }
    
    return $default;
}

/**
 * 助成金の基本データ取得（高速版）
 */
function gi_get_grant_basic_data($post_id) {
    static $cache = [];
    
    // キャッシュチェック
    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }
    
    $post = get_post($post_id);
    if (!$post) return [];
    
    $data = [
        'id' => $post_id,
        'title' => $post->post_title,
        'permalink' => get_permalink($post_id),
        'excerpt' => $post->post_excerpt,
        'content' => $post->post_content,
        'date' => $post->post_date,
        'modified' => $post->post_modified,
        'status' => $post->post_status
    ];
    
    // よく使用されるメタデータのみ取得
    $essential_meta = [
        'organization',
        'max_amount',
        'max_amount_numeric', 
        'deadline',
        'deadline_date',
        'application_status',
        'grant_difficulty',
        'is_featured'
    ];
    
    foreach ($essential_meta as $key) {
        $data[$key] = gi_safe_get_meta($post_id, $key);
    }
    
    // キャッシュに保存
    $cache[$post_id] = $data;
    
    return $data;
}

/**
 * 複数投稿の基本データを一括取得
 */
function gi_get_multiple_grants_data($post_ids) {
    if (empty($post_ids)) return [];
    
    // パフォーマンス最適化
    gi_prefetch_post_data($post_ids);
    
    $results = [];
    foreach ($post_ids as $post_id) {
        $results[$post_id] = gi_get_grant_basic_data($post_id);
    }
    
    return $results;
}

/**
 * =============================================================================
 * 2. 検索・フィルタリング関数
 * =============================================================================
 */

/**
 * 助成金検索の最適化されたクエリ構築
 */
function gi_build_optimized_grant_query($args = []) {
    $defaults = [
        'post_type' => 'grant',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => [],
        'tax_query' => []
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // パフォーマンス最適化のためのフィールド制限
    if (!isset($args['fields'])) {
        $args['no_found_rows'] = false; // ページネーション用
        $args['update_post_meta_cache'] = true;
        $args['update_post_term_cache'] = true;
    }
    
    return $args;
}

/**
 * 高速な助成金カウント取得
 */
function gi_get_grants_count_by_status($status = 'active') {
    $cache_key = "gi_grants_count_{$status}";
    $count = get_transient($cache_key);
    
    if (false === $count) {
        $args = [
            'post_type' => 'grant',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true
        ];
        
        if ($status !== 'all') {
            $args['meta_query'] = [
                [
                    'key' => 'application_status',
                    'value' => $status,
                    'compare' => '='
                ]
            ];
        }
        
        $query = new WP_Query($args);
        $count = count($query->posts);
        
        // 30分キャッシュ
        set_transient($cache_key, $count, 1800);
    }
    
    return intval($count);
}

/**
 * カテゴリー別統計取得
 */
function gi_get_category_statistics() {
    $cache_key = 'gi_category_stats';
    $stats = get_transient($cache_key);
    
    if (false === $stats) {
        $categories = get_terms([
            'taxonomy' => 'grant_category',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC'
        ]);
        
        $stats = [];
        foreach ($categories as $category) {
            $stats[] = [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count,
                'link' => get_term_link($category)
            ];
        }
        
        // 1時間キャッシュ
        set_transient($cache_key, $stats, 3600);
    }
    
    return $stats;
}

/**
 * =============================================================================
 * 3. パフォーマンス最適化関数
 * =============================================================================
 */

/**
 * 投稿データの一括プリフェッチ（N+1クエリ対策）
 */
function gi_prefetch_post_data($post_ids) {
    if (empty($post_ids) || !is_array($post_ids)) {
        return;
    }
    
    // 投稿メタデータを一括取得
    update_post_caches($post_ids, 'grant');
    
    // タームデータを一括取得
    update_object_term_cache($post_ids, 'grant');
    
    // サムネイルデータもプリフェッチ
    $attachment_ids = [];
    foreach ($post_ids as $post_id) {
        $thumb_id = get_post_thumbnail_id($post_id);
        if ($thumb_id) {
            $attachment_ids[] = $thumb_id;
        }
    }
    
    if (!empty($attachment_ids)) {
        update_post_caches($attachment_ids, 'attachment');
    }
}

/**
 * メタデータキャッシュ管理
 */
class GI_Meta_Cache {
    private static $cache = [];
    
    /**
     * メタデータ取得（キャッシュ付き）
     */
    public static function get_meta($post_id, $meta_key, $default = '') {
        $cache_key = "{$post_id}_{$meta_key}";
        
        if (!isset(self::$cache[$cache_key])) {
            $value = get_post_meta($post_id, $meta_key, true);
            self::$cache[$cache_key] = ($value !== '') ? $value : $default;
        }
        
        return self::$cache[$cache_key];
    }
    
    /**
     * 複数メタデータを一括取得
     */
    public static function get_multiple_meta($post_id, $meta_keys) {
        $results = [];
        foreach ($meta_keys as $key => $default) {
            $results[$key] = self::get_meta($post_id, $key, $default);
        }
        return $results;
    }
    
    /**
     * キャッシュクリア
     */
    public static function clear_cache($post_id = null) {
        if ($post_id) {
            foreach (self::$cache as $key => $value) {
                if (strpos($key, $post_id . '_') === 0) {
                    unset(self::$cache[$key]);
                }
            }
        } else {
            self::$cache = [];
        }
    }
}

/**
 * データベースクエリ最適化
 */
function gi_optimize_database_queries() {
    // 不要なクエリを削減
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    
    // アーカイブページでの不要なメタクエリを削除
    add_action('pre_get_posts', function($query) {
        if (!is_admin() && $query->is_main_query()) {
            if (is_post_type_archive('grant')) {
                // 必要最小限のメタデータのみロード
                $query->set('update_post_meta_cache', false);
                
                // カスタムフィールドのロードを制限
                add_filter('posts_fields', function($fields) {
                    return $fields . ', pm1.meta_value as max_amount, pm2.meta_value as organization';
                });
                
                add_filter('posts_join', function($join) {
                    global $wpdb;
                    $join .= " LEFT JOIN {$wpdb->postmeta} pm1 ON ({$wpdb->posts}.ID = pm1.post_id AND pm1.meta_key = 'max_amount_numeric')";
                    $join .= " LEFT JOIN {$wpdb->postmeta} pm2 ON ({$wpdb->posts}.ID = pm2.post_id AND pm2.meta_key = 'organization')";
                    return $join;
                });
            }
        }
    });
}
add_action('init', 'gi_optimize_database_queries');

/**
 * =============================================================================
 * 4. ユーティリティ関数
 * =============================================================================
 */

/**
 * お気に入り機能（強化版）
 */
function gi_get_user_favorites() {
    $user_id = get_current_user_id();
    
    if ($user_id) {
        return get_user_meta($user_id, 'gi_favorites', true) ?: [];
    }
    
    // Cookie fallback
    $cookie_name = 'gi_favorites';
    if (isset($_COOKIE[$cookie_name])) {
        return array_filter(array_map('intval', explode(',', $_COOKIE[$cookie_name])));
    }
    
    return [];
}

/**
 * お気に入りに追加
 */
function gi_add_to_favorites($post_id) {
    $user_id = get_current_user_id();
    $favorites = gi_get_user_favorites();
    
    if (!in_array($post_id, $favorites)) {
        $favorites[] = $post_id;
        
        if ($user_id) {
            update_user_meta($user_id, 'gi_favorites', $favorites);
        } else {
            // Cookie更新
            setcookie('gi_favorites', implode(',', $favorites), time() + (86400 * 30), '/');
        }
        
        return true;
    }
    
    return false;
}

/**
 * お気に入りから削除
 */
function gi_remove_from_favorites($post_id) {
    $user_id = get_current_user_id();
    $favorites = gi_get_user_favorites();
    $key = array_search($post_id, $favorites);
    
    if ($key !== false) {
        unset($favorites[$key]);
        $favorites = array_values($favorites);
        
        if ($user_id) {
            update_user_meta($user_id, 'gi_favorites', $favorites);
        } else {
            // Cookie更新
            setcookie('gi_favorites', implode(',', $favorites), time() + (86400 * 30), '/');
        }
        
        return true;
    }
    
    return false;
}

// 検索履歴関数は inc/ai-functions.php に移動

/**
 * データの整合性チェック
 */
function gi_validate_grant_data($post_id) {
    $errors = [];
    
    // 必須フィールドチェック
    $required_fields = ['organization', 'max_amount_numeric'];
    foreach ($required_fields as $field) {
        $value = gi_safe_get_meta($post_id, $field);
        if (empty($value)) {
            $errors[] = "必須フィールド '{$field}' が設定されていません";
        }
    }
    
    // 数値フィールドの妥当性チェック
    $amount = gi_safe_get_meta($post_id, 'max_amount_numeric', 0);
    if ($amount < 0) {
        $errors[] = "金額は0以上である必要があります";
    }
    
    // 締切日の妥当性チェック
    $deadline = gi_safe_get_meta($post_id, 'deadline_date');
    if (!empty($deadline) && strtotime($deadline) === false) {
        $errors[] = "締切日の形式が正しくありません";
    }
    
    return $errors;
}

/**
 * =============================================================================
 * 5. 統計・分析関数
 * =============================================================================
 */

/**
 * 助成金統計の取得
 */
function gi_get_grant_statistics() {
    $cache_key = 'gi_grant_statistics';
    $stats = get_transient($cache_key);
    
    if (false === $stats) {
        global $wpdb;
        
        $stats = [
            'total_grants' => wp_count_posts('grant')->publish,
            'active_grants' => gi_get_grants_count_by_status('active'),
            'categories_count' => wp_count_terms('grant_category'),
            'prefectures_count' => wp_count_terms('grant_prefecture')
        ];
        
        // 平均金額計算
        $avg_amount = $wpdb->get_var("
            SELECT AVG(CAST(meta_value AS UNSIGNED)) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = 'max_amount_numeric' 
            AND meta_value > 0
        ");
        
        $stats['average_amount'] = intval($avg_amount);
        $stats['average_amount_formatted'] = gi_format_amount_unified($avg_amount);
        
        // 1時間キャッシュ
        set_transient($cache_key, $stats, 3600);
    }
    
    return $stats;
}

/**
 * 人気の検索キーワード取得
 */
// gi_get_popular_search_terms関数は inc/ai-functions.php に移動

/**
 * 統計情報取得（キャッシュ対応）- エイリアス関数
 */
function gi_get_cached_stats() {
    return gi_get_grant_statistics();
}

/**
 * 締切日の書式化された表示を取得
 */
function gi_get_formatted_deadline($post_id) {
    $deadline = gi_safe_get_meta($post_id, 'deadline_date');
    return gi_format_deadline_for_display($deadline);
}

/**
 * 助成金金額の表示を取得
 */
function gi_get_grant_amount_display($post_id) {
    $amount_numeric = gi_safe_get_meta($post_id, 'max_amount_numeric', 0);
    $amount_text = gi_safe_get_meta($post_id, 'max_amount', '');
    return gi_format_amount_unified($amount_numeric, $amount_text);
}

/**
 * ACFフィールドを安全に取得
 */
function gi_get_acf_field_safely($field_name, $post_id = false, $default = '') {
    if (function_exists('get_field')) {
        $value = get_field($field_name, $post_id);
        return !empty($value) ? $value : $default;
    }
    return gi_safe_get_meta($post_id ?: get_the_ID(), $field_name, $default);
}

/**
 * テーマオプション取得
 */
function gi_get_theme_option($option_name, $default = '') {
    if (function_exists('get_theme_mod')) {
        return get_theme_mod($option_name, $default);
    }
    return get_option($option_name, $default);
}

/**
 * 安全なエスケープ
 */
function gi_safe_escape($value, $type = 'text') {
    if (empty($value)) return '';
    
    switch ($type) {
        case 'html':
            return wp_kses_post($value);
        case 'url':
            return esc_url($value);
        case 'attr':
            return esc_attr($value);
        default:
            return sanitize_text_field($value);
    }
}

/**
 * 投稿のカテゴリ取得
 */
function gi_get_post_categories($post_id) {
    $categories = get_the_terms($post_id, 'grant_category');
    if (is_wp_error($categories) || empty($categories)) {
        return [];
    }
    return array_map(function($cat) {
        return [
            'name' => $cat->name,
            'slug' => $cat->slug,
            'id' => $cat->term_id
        ];
    }, $categories);
}

/**
 * お気に入りトグル
 */
function gi_toggle_favorite($post_id) {
    $favorites = gi_get_user_favorites();
    
    if (in_array($post_id, $favorites)) {
        return gi_remove_from_favorites($post_id);
    } else {
        return gi_add_to_favorites($post_id);
    }
}

/**
 * キャッシュクリア関数
 */
function gi_clear_all_caches() {
    // Transientキャッシュクリア
    $cache_keys = [
        'gi_grant_statistics',
        'gi_category_stats',
        'gi_popular_searches_10'
    ];
    
    foreach ($cache_keys as $key) {
        delete_transient($key);
    }
    
    // ステータス別カウントキャッシュクリア
    $statuses = ['active', 'closed', 'upcoming', 'all'];
    foreach ($statuses as $status) {
        delete_transient("gi_grants_count_{$status}");
    }
    
    // メタキャッシュクリア
    GI_Meta_Cache::clear_cache();
    
    // WordPressオブジェクトキャッシュクリア
    wp_cache_flush();
}

// 投稿更新時にキャッシュクリア
add_action('save_post', function($post_id) {
    if (get_post_type($post_id) === 'grant') {
        gi_clear_all_caches();
    }
});

// タームの更新時にもキャッシュクリア
add_action('edited_term', 'gi_clear_all_caches');
add_action('created_term', 'gi_clear_all_caches');

/**
 * =============================================================================
 * 6. Prefecture Counting Functions (Enhanced)
 * =============================================================================
 */

/**
 * 都道府県の投稿数を取得（高速版）
 */
function gi_get_prefecture_counts($force_refresh = false) {
    $cache_key = 'gi_prefecture_counts_v2';
    
    if ($force_refresh) {
        delete_transient($cache_key);
    }
    
    $prefecture_counts = get_transient($cache_key);
    
    if (false === $prefecture_counts) {
        global $wpdb;
        
        // 直接データベースクエリで高速取得
        $count_results = $wpdb->get_results("
            SELECT t.slug, COUNT(DISTINCT p.ID) as post_count
            FROM {$wpdb->terms} t
            LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
            LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID 
                AND p.post_type = 'grant' 
                AND p.post_status = 'publish'
            WHERE tt.taxonomy = 'grant_prefecture'
            GROUP BY t.term_id, t.slug
            ORDER BY t.slug
        ");
        
        $prefecture_counts = array();
        foreach ($count_results as $result) {
            $prefecture_counts[$result->slug] = intval($result->post_count);
        }
        
        // 全都道府県について0埋め
        if (function_exists('gi_get_all_prefectures')) {
            $all_prefectures = gi_get_all_prefectures();
            foreach ($all_prefectures as $pref) {
                if (!isset($prefecture_counts[$pref['slug']])) {
                    $prefecture_counts[$pref['slug']] = 0;
                }
            }
        }
        
        // 10分キャッシュ
        set_transient($cache_key, $prefecture_counts, 10 * MINUTE_IN_SECONDS);
    }
    
    return $prefecture_counts;
}

/**
 * 都道府県タクソノミーの初期化チェック
 */
function gi_ensure_prefecture_terms() {
    if (!function_exists('gi_get_all_prefectures')) {
        return false;
    }
    
    $all_prefectures = gi_get_all_prefectures();
    $missing_terms = array();
    
    foreach ($all_prefectures as $pref) {
        $term = get_term_by('slug', $pref['slug'], 'grant_prefecture');
        if (!$term || is_wp_error($term)) {
            $missing_terms[] = $pref;
        }
    }
    
    // 欠けているタームを作成
    foreach ($missing_terms as $pref) {
        $result = wp_insert_term(
            $pref['name'],
            'grant_prefecture',
            array('slug' => $pref['slug'])
        );
        
        if (is_wp_error($result)) {
            error_log('Failed to create prefecture term: ' . $pref['name'] . ' - ' . $result->get_error_message());
        }
    }
    
    return count($missing_terms);
}

/**
 * 助成金投稿の都道府県設定チェック
 */
function gi_check_grant_prefecture_assignments() {
    global $wpdb;
    
    // 都道府県が設定されていない助成金投稿数を取得
    $unassigned_count = $wpdb->get_var("
        SELECT COUNT(p.ID)
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            AND tt.taxonomy = 'grant_prefecture'
        WHERE p.post_type = 'grant'
        AND p.post_status = 'publish'
        AND tt.term_taxonomy_id IS NULL
    ");
    
    $total_grants = wp_count_posts('grant')->publish;
    
    return array(
        'total_grants' => intval($total_grants),
        'unassigned_grants' => intval($unassigned_count),
        'assigned_grants' => intval($total_grants) - intval($unassigned_count),
        'assignment_ratio' => $total_grants > 0 ? round((intval($total_grants) - intval($unassigned_count)) / intval($total_grants) * 100, 1) : 0
    );
}

// 都道府県カウントのキャッシュクリアを追加
add_action('save_post', function($post_id) {
    if (get_post_type($post_id) === 'grant') {
        delete_transient('gi_prefecture_counts_v2');
        gi_clear_all_caches();
    }
});