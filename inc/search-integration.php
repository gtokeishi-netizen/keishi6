<?php
/**
 * Grant Insight - AI機能統合
 * AI関連の機能を統合したファイル
 * 
 * @version 8.0.1 - AI Function Consolidation
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 検索履歴の保存（統合版）
 * 両方の実装を統合してより堅牢な保存機能を提供
 */
function gi_save_search_history($query, $filters = [], $results_count = 0, $session_id = null) {
    // セッションIDが提供された場合はデータベース保存も実行
    if ($session_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'gi_search_history';
        // テーブルが存在する場合のみ保存
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") == $table) {
            $wpdb->insert(
                $table,
                [
                    'session_id' => $session_id,
                    'user_id' => get_current_user_id() ?: null,
                    'search_query' => $query,
                    'search_filter' => is_array($filters) ? json_encode($filters) : $filters,
                    'results_count' => $results_count,
                    'search_time' => current_time('mysql')
                ],
                ['%s', '%d', '%s', '%s', '%d', '%s']
            );
        }
    }
    
    // ユーザーメタデータにも保存（ログインユーザーの場合）
    $user_id = get_current_user_id();
    if ($user_id) {
        $history = get_user_meta($user_id, 'gi_search_history', true) ?: [];
        
        // 新しい検索を追加
        array_unshift($history, [
            'query' => sanitize_text_field($query),
            'filters' => $filters,
            'results_count' => intval($results_count),
            'timestamp' => current_time('timestamp')
        ]);
        
        // 最新の20件のみ保持
        $history = array_slice($history, 0, 20);
        
        update_user_meta($user_id, 'gi_search_history', $history);
    }
    
    return true;
}

/**
 * 検索履歴の取得
 */
function gi_get_search_history($limit = 10) {
    $user_id = get_current_user_id();
    if (!$user_id) return [];
    
    $history = get_user_meta($user_id, 'gi_search_history', true) ?: [];
    
    return array_slice($history, 0, $limit);
}

/**
 * 検索履歴のクリア
 */
function gi_clear_search_history() {
    $user_id = get_current_user_id();
    if (!$user_id) return false;
    
    delete_user_meta($user_id, 'gi_search_history');
    return true;
}

/**
 * 人気検索キーワードの取得
 */
function gi_get_popular_search_terms($limit = 10) {
    // 全ユーザーの検索履歴から人気キーワードを集計
    global $wpdb;
    
    $cache_key = 'gi_popular_search_terms_' . $limit;
    $cached = wp_cache_get($cache_key);
    if ($cached !== false) {
        return $cached;
    }
    
    $table = $wpdb->prefix . 'gi_search_history';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return [];
    }
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT search_query, COUNT(*) as count 
        FROM {$table} 
        WHERE search_query != '' 
        AND search_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY search_query 
        ORDER BY count DESC 
        LIMIT %d
    ", $limit), ARRAY_A);
    
    $popular_terms = [];
    foreach ($results as $result) {
        $popular_terms[] = [
            'term' => $result['search_query'],
            'count' => intval($result['count'])
        ];
    }
    
    // 30分間キャッシュ
    wp_cache_set($cache_key, $popular_terms, '', 1800);
    
    return $popular_terms;
}

/**
 * AI関連設定の取得
 */
function gi_get_ai_settings() {
    return [
        'search_enhancement' => get_option('gi_ai_search_enhancement', false),
        'auto_suggestions' => get_option('gi_ai_auto_suggestions', true),
        'semantic_search' => get_option('gi_ai_semantic_search', false),
        'history_tracking' => get_option('gi_ai_history_tracking', true)
    ];
}

/**
 * AI設定の更新
 */
function gi_update_ai_settings($settings) {
    $valid_settings = ['search_enhancement', 'auto_suggestions', 'semantic_search', 'history_tracking'];
    
    foreach ($settings as $key => $value) {
        if (in_array($key, $valid_settings)) {
            update_option('gi_ai_' . $key, (bool) $value);
        }
    }
    
    return true;
}

/**
 * 検索統計の取得
 */
function gi_get_search_statistics() {
    global $wpdb;
    
    $cache_key = 'gi_search_statistics';
    $cached = wp_cache_get($cache_key);
    if ($cached !== false) {
        return $cached;
    }
    
    $table = $wpdb->prefix . 'gi_search_history';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return [
            'total_searches' => 0,
            'unique_queries' => 0,
            'average_results' => 0,
            'recent_searches' => []
        ];
    }
    
    $total_searches = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    $unique_queries = $wpdb->get_var("SELECT COUNT(DISTINCT search_query) FROM {$table}");
    $average_results = $wpdb->get_var("SELECT AVG(results_count) FROM {$table} WHERE results_count > 0");
    
    $recent_searches = $wpdb->get_results("
        SELECT search_query, results_count, search_time 
        FROM {$table} 
        ORDER BY search_time DESC 
        LIMIT 10
    ", ARRAY_A);
    
    $stats = [
        'total_searches' => intval($total_searches),
        'unique_queries' => intval($unique_queries),
        'average_results' => round(floatval($average_results), 1),
        'recent_searches' => $recent_searches
    ];
    
    // 1時間キャッシュ
    wp_cache_set($cache_key, $stats, '', 3600);
    
    return $stats;
}

/**
 * 検索履歴テーブルの作成
 */
function gi_create_search_history_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'gi_search_history';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        session_id varchar(100) DEFAULT NULL,
        user_id bigint(20) UNSIGNED DEFAULT NULL,
        search_query text NOT NULL,
        search_filter text DEFAULT NULL,
        results_count int(11) DEFAULT 0,
        search_time datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY search_time (search_time)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * チャット履歴テーブルの作成
 */
function gi_create_chat_history_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'gi_chat_history';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_id bigint(20) unsigned DEFAULT NULL,
        message_type varchar(20) NOT NULL,
        message_content text NOT NULL,
        intent_data text DEFAULT NULL,
        related_grants text DEFAULT NULL,
        response_time_ms int(11) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY created_at (created_at),
        KEY message_type (message_type)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * AI機能の初期化
 */
function gi_init_ai_functions() {
    global $wpdb;
    
    // 重複実行を防ぐ
    static $initialized = false;
    if ($initialized) {
        return;
    }
    
    try {
        // 検索履歴テーブルの作成（存在しない場合）
        $table_name = $wpdb->prefix . 'gi_search_history';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            gi_create_search_history_table();
        }
        
        // チャット履歴テーブルの作成（存在しない場合）
        $chat_table = $wpdb->prefix . 'gi_chat_history';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$chat_table}'") != $chat_table) {
            gi_create_chat_history_table();
        }
        
        // 音声履歴テーブルの作成（存在しない場合）
        $voice_table = $wpdb->prefix . 'gi_voice_history';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$voice_table}'") != $voice_table) {
            gi_create_voice_history_table();
        }
        
        $initialized = true;
    } catch (Exception $e) {
        error_log('AI Functions initialization error: ' . $e->getMessage());
    }
    
    // デフォルト設定の作成
    $default_settings = [
        'gi_ai_search_enhancement' => false,
        'gi_ai_auto_suggestions' => true,
        'gi_ai_semantic_search' => false,
        'gi_ai_history_tracking' => true
    ];
    
    foreach ($default_settings as $option => $default_value) {
        if (get_option($option) === false) {
            add_option($option, $default_value);
        }
    }
}

/**
 * 音声履歴テーブル作成
 */
function gi_create_voice_history_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'gi_voice_history';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_id bigint(20) unsigned DEFAULT NULL,
        transcribed_text text NOT NULL,
        confidence_score decimal(3,2) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// フック登録
add_action('init', 'gi_init_ai_functions');

/**
 * 検索履歴のクリーンアップ（古いデータの削除）
 */
function gi_cleanup_old_search_history() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'gi_search_history';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") == $table) {
        // 90日より古いレコードを削除
        $wpdb->query("DELETE FROM {$table} WHERE search_time < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    }
}

// 日次クリーンアップの設定
if (!wp_next_scheduled('gi_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'gi_daily_cleanup');
}
add_action('gi_daily_cleanup', 'gi_cleanup_old_search_history');

/**
 * =============================================================================
 * OpenAI 統合クラス（基本実装）
 * =============================================================================
 */

class GI_OpenAI_Integration {
    private static $instance = null;
    private $api_key;
    private $api_endpoint = 'https://api.openai.com/v1/';
    
    private function __construct() {
        $this->api_key = get_option('gi_openai_api_key', '');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * API キーの設定確認
     */
    public function is_configured() {
        return !empty($this->api_key);
    }
    
    /**
     * 音声認識（Whisper API）
     */
    public function transcribe_audio($audio_data) {
        if (!$this->is_configured()) {
            return 'OpenAI APIキーが設定されていません。設定画面で設定してください。';
        }
        
        try {
            // 実際のWhisper API実装（ファイルアップロードが必要なため、現在はプレースホルダー）
            return 'すみません。音声認識機能は現在開発中です。テキストで入力してください。';
        } catch (Exception $e) {
            error_log('Whisper API Error: ' . $e->getMessage());
            return 'すみません。音声認識でエラーが発生しました。テキストで入力してください。';
        }
    }
    
    /**
     * テキスト生成（GPT API）
     */
    public function generate_response($prompt, $context = []) {
        if (!$this->is_configured()) {
            return $this->generate_fallback_response($prompt, $context);
        }
        
        try {
            return $this->call_gpt_api($prompt, $context);
        } catch (Exception $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            return $this->generate_fallback_response($prompt, $context);
        }
    }
    
    /**
     * GPT API呼び出し
     */
    private function call_gpt_api($prompt, $context = []) {
        // コンテキストを含む完全なプロンプトを作成
        $system_prompt = "あなたは助成金・補助金の専門アドバイザーです。ユーザーからの質問に対して、正確で有用な情報を提供してください。";
        
        if (!empty($context['grants'])) {
            $system_prompt .= "\n\n関連する助成金情報:\n";
            foreach (array_slice($context['grants'], 0, 3) as $grant) {
                $system_prompt .= "- {$grant['title']}: {$grant['excerpt']}\n";
            }
        }
        
        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $prompt]
        ];
        
        $response = $this->make_openai_request('chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        
        throw new Exception('Invalid API response');
    }
    

    
    /**
     * OpenAI API接続テスト
     */
    public function test_connection() {
        if (!$this->is_configured()) {
            return [
                'success' => false,
                'message' => 'APIキーが設定されていません'
            ];
        }
        
        try {
            $response = $this->make_openai_request('models');
            if ($response && isset($response['data'])) {
                return [
                    'success' => true,
                    'message' => 'API接続成功'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'API応答が無効です'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'API接続エラー: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * OpenAI APIリクエストの共通処理
     */
    private function make_openai_request($endpoint, $data = null) {
        $url = $this->api_endpoint . $endpoint;
        
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ];
        
        if ($data) {
            $args['body'] = json_encode($data);
            $args['method'] = 'POST';
            $response = wp_remote_post($url, $args);
        } else {
            $response = wp_remote_get($url, $args);
        }
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);
        
        if ($http_code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) 
                ? $error_data['error']['message'] 
                : 'HTTP Error: ' . $http_code;
            throw new Exception($error_message);
        }
        
        return json_decode($body, true);
    }
    
    /**
     * フォールバック応答生成
     */
    private function generate_fallback_response($prompt, $context = []) {
        // プロンプトベースの基本応答
        if (mb_stripos($prompt, '検索') !== false || mb_stripos($prompt, '補助金') !== false) {
            return 'ご質問ありがとうございます。補助金に関する詳細情報をお調べしております。具体的な業種や目的をお聞かせいただけると、より適切な情報をご提供できます。';
        }
        
        if (mb_stripos($prompt, '申請') !== false) {
            return '申請に関するご質問ですね。補助金の申請には通常、事業計画書、必要書類の準備、申請書の提出が必要です。具体的にどの補助金についてお知りになりたいですか？';
        }
        
        if (mb_stripos($prompt, '締切') !== false || mb_stripos($prompt, '期限') !== false) {
            return '締切に関するお問い合わせですね。各補助金には異なる申請期限が設定されています。お探しの補助金の種類を教えていただけますか？';
        }
        
        return 'ご質問ありがとうございます。より具体的な情報をお聞かせいただけると、詳しい回答をお提供できます。補助金の種類、業種、目的などをお聞かせください。';
    }
}

/**
 * =============================================================================
 * セマンティック検索クラス（基本実装）
 * =============================================================================
 */

class GI_Grant_Semantic_Search {
    private static $instance = null;
    
    private function __construct() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * セマンティック検索実行
     */
    public function search($query, $filters = []) {
        // 現在はWordPress標準の検索にフォールバック
        return $this->fallback_search($query, $filters);
    }
    
    /**
     * フォールバック検索（WordPress標準）
     */
    private function fallback_search($query, $filters = []) {
        $args = [
            'post_type' => 'grant',
            'posts_per_page' => 20,
            'post_status' => 'publish',
            's' => $query
        ];
        
        // フィルター適用
        if (!empty($filters['category'])) {
            $args['tax_query'] = [[
                'taxonomy' => 'grant_category',
                'field' => 'slug',
                'terms' => $filters['category']
            ]];
        }
        
        $wp_query = new WP_Query($args);
        $results = [];
        
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $post_id = get_the_ID();
                
                $results[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'url' => get_permalink(),
                    'amount' => get_post_meta($post_id, 'max_amount', true) ?: '未定',
                    'deadline' => get_post_meta($post_id, 'deadline', true) ?: '随時',
                    'organization' => get_post_meta($post_id, 'organization', true),
                    'categories' => wp_get_post_terms($post_id, 'grant_category', ['fields' => 'names']),
                    'similarity' => 0.7, // デフォルト類似度
                    'relevance' => 0.8   // デフォルト関連性
                ];
            }
            wp_reset_postdata();
        }
        
        return [
            'success' => true,
            'results' => $results,
            'count' => count($results),
            'method' => 'wordpress_fallback'
        ];
    }
}

/**
 * =============================================================================
 * AI設定管理
 * =============================================================================
 */

/**
 * OpenAI APIキーの設定
 */
function gi_set_openai_api_key($api_key) {
    return update_option('gi_openai_api_key', sanitize_text_field($api_key));
}

/**
 * OpenAI APIキーの取得
 */
function gi_get_openai_api_key() {
    return get_option('gi_openai_api_key', '');
}

/**
 * AI機能の有効性チェック
 */
function gi_check_ai_capabilities() {
    $openai = GI_OpenAI_Integration::getInstance();
    $semantic_search = GI_Grant_Semantic_Search::getInstance();
    
    return [
        'openai_configured' => $openai->is_configured(),
        'semantic_search_available' => true, // 常に利用可能（フォールバック付き）
        'voice_recognition_available' => $openai->is_configured(),
        'chat_available' => true // 常に利用可能（フォールバック付き）
    ];
}

