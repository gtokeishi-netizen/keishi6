<?php
/**
 * Grant Insight Jグランツ・インポーター
 * 助成金自動取得・生成システム
 * 
 * Version: 1.2.2
 * Author: Manus AI
 */

// セキュリティ: 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

// 定数定義
if (!defined('GIJI_VERSION')) {
    define('GIJI_VERSION', '1.2.2');
}

// =============================================
// JグランツAPIクライアントクラス
// =============================================

if (!class_exists('JGrantsAPIClient')) {
    class JGrantsAPIClient {
        
        private $base_url = 'https://api.jgrants-portal.go.jp/exp/v1/public';
        private $timeout = 30;
        
        /**
         * 助成金一覧を取得
         */
        public function get_subsidies($params = []) {
            $validated_params = $this->validate_search_params($params);
            if (is_wp_error($validated_params)) {
                return $validated_params;
            }
            
            $endpoint = $this->base_url . '/subsidies';
            $url = add_query_arg($validated_params, $endpoint);
            
            giji_log('JグランツAPI一覧取得開始: キーワード「' . $validated_params['keyword'] . '」');
            
            $response = $this->make_request($url);
            
            if (is_wp_error($response)) {
                giji_log('JグランツAPI一覧取得エラー: ' . $response->get_error_message(), 'error');
                return $response;
            }
            
            $count = isset($response['result']) ? count($response['result']) : 0;
            giji_log("JグランツAPI一覧取得成功: {$count}件");
            
            return $response;
        }
        
        /**
         * 助成金詳細を取得
         */
        public function get_subsidy_detail($id) {
            if (empty($id)) {
                return new WP_Error('invalid_id', '助成金IDが指定されていません');
            }
            
            $sanitized_id = sanitize_text_field($id);
            $endpoint = $this->base_url . '/subsidies/id/' . $sanitized_id;
            
            giji_log('JグランツAPI詳細取得開始: ID ' . $sanitized_id);
            
            $response = $this->make_request($endpoint);
            
            if (is_wp_error($response)) {
                giji_log('JグランツAPI詳細取得エラー: ' . $response->get_error_message(), 'error');
                return $response;
            }
            
            giji_log('JグランツAPI詳細取得成功: ID ' . $sanitized_id);
            sleep(1); // API負荷軽減
            
            return $response;
        }
        
        /**
         * 検索パラメータのバリデーション
         */
        private function validate_search_params($params) {
            $default_params = [
                'keyword' => '補助金',
                'sort' => 'created_date',
                'order' => 'DESC',
                'acceptance' => '1',
                'per_page' => 10
            ];
            
            $params = wp_parse_args($params, $default_params);
            
            if (isset($params['keyword'])) {
                $keyword = sanitize_text_field($params['keyword']);
                if (mb_strlen($keyword) < 2) {
                    return new WP_Error('invalid_keyword', 'キーワードは2文字以上で入力してください');
                }
                $params['keyword'] = $keyword;
            }
            
            if (isset($params['per_page'])) {
                $per_page = intval($params['per_page']);
                if ($per_page < 1 || $per_page > 50) {
                    $per_page = 10;
                }
                $params['per_page'] = $per_page;
            }
            
            return $params;
        }
        
        /**
         * APIリクエストを実行
         */
        private function make_request($url) {
            $args = [
                'timeout' => $this->timeout,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Grant Insight Jグランツ・インポーター/' . GIJI_VERSION,
                    'Accept' => 'application/json'
                ],
                'sslverify' => true
            ];
            
            $response = wp_remote_get($url, $args);
            
            if (is_wp_error($response)) {
                return $response;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            if ($response_code !== 200) {
                return new WP_Error('http_error', "HTTPエラー: {$response_code}");
            }
            
            if (empty($response_body)) {
                return new WP_Error('empty_response', 'APIレスポンスが空です');
            }
            
            $data = json_decode($response_body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new WP_Error('json_error', 'JSONデコードエラー: ' . json_last_error_msg());
            }
            
            return $data;
        }
        
        /**
         * API接続テスト
         */
        public function test_connection() {
            $test_params = [
                'keyword' => 'テスト',
                'per_page' => 1,
                'acceptance' => '0'
            ];
            
            $response = $this->get_subsidies($test_params);
            
            if (is_wp_error($response)) {
                return false;
            }
            
            return isset($response['result']);
        }
    }
}

// =============================================
// 統合AIクライアントクラス
// =============================================

if (!class_exists('UnifiedAIClient')) {
    class UnifiedAIClient {
        
        private $provider;
        private $api_key;
        private $model;
        private $timeout = 60;
        
        private $endpoints = [
            'openai' => 'https://api.openai.com/v1',
            'claude' => 'https://api.anthropic.com/v1',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta'
        ];
        
        public function __construct() {
            $this->load_config();
        }
        
        /**
         * 設定の読み込み
         */
        private function load_config() {
            $this->provider = get_option('giji_ai_provider', 'openai');
            
            switch($this->provider) {
                case 'openai':
                    $this->api_key = get_option('giji_openai_api_key', '');
                    $this->model = get_option('giji_openai_model', 'gpt-4o-mini');
                    break;
                case 'claude':
                    $this->api_key = get_option('giji_claude_api_key', '');
                    $this->model = get_option('giji_claude_model', 'claude-3-haiku-20240307');
                    break;
                case 'gemini':
                default:
                    $this->api_key = get_option('giji_gemini_api_key', '');
                    $this->model = get_option('giji_gemini_model', 'gemini-1.5-flash');
                    break;
            }
        }
        
        /**
         * テキスト生成
         */
        public function generate_text($prompt, $config = []) {
            if (empty($prompt) || empty($this->api_key)) {
                return new WP_Error('invalid_input', 'プロンプトまたはAPIキーが不正です');
            }
            
            switch($this->provider) {
                case 'openai':
                    return $this->generate_text_openai($prompt, $config);
                case 'claude':
                    return $this->generate_text_claude($prompt, $config);
                case 'gemini':
                default:
                    return $this->generate_text_gemini($prompt, $config);
            }
        }
        
        /**
         * OpenAI APIでテキスト生成
         */
        private function generate_text_openai($prompt, $config = []) {
            $default_config = [
                'temperature' => 0.7,
                'max_tokens' => 3000
            ];
            
            $config = wp_parse_args($config, $default_config);
            
            $request_body = [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => floatval($config['temperature']),
                'max_tokens' => intval($config['max_tokens'])
            ];
            
            $url = $this->endpoints['openai'] . '/chat/completions';
            
            $args = [
                'method' => 'POST',
                'timeout' => $this->timeout,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_key
                ],
                'body' => wp_json_encode($request_body)
            ];
            
            $response = wp_remote_post($url, $args);
            
            if (is_wp_error($response)) {
                return $response;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            if ($response_code !== 200) {
                return new WP_Error('openai_api_error', "OpenAI APIエラー: {$response_code}");
            }
            
            $data = json_decode($response_body, true);
            
            if (!isset($data['choices'][0]['message']['content'])) {
                return new WP_Error('invalid_response', 'APIレスポンスの形式が不正です');
            }
            
            return trim($data['choices'][0]['message']['content']);
        }
        
        /**
         * Claude APIでテキスト生成
         */
        private function generate_text_claude($prompt, $config = []) {
            $default_config = [
                'temperature' => 0.7,
                'max_tokens' => 3000
            ];
            
            $config = wp_parse_args($config, $default_config);
            
            $request_body = [
                'model' => $this->model,
                'max_tokens' => intval($config['max_tokens']),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => floatval($config['temperature'])
            ];
            
            $url = $this->endpoints['claude'] . '/messages';
            
            $args = [
                'method' => 'POST',
                'timeout' => $this->timeout,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->api_key,
                    'anthropic-version' => '2023-06-01'
                ],
                'body' => wp_json_encode($request_body)
            ];
            
            $response = wp_remote_post($url, $args);
            
            if (is_wp_error($response)) {
                return $response;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            if ($response_code !== 200) {
                return new WP_Error('claude_api_error', "Claude APIエラー: {$response_code}");
            }
            
            $data = json_decode($response_body, true);
            
            if (!isset($data['content'][0]['text'])) {
                return new WP_Error('invalid_response', 'APIレスポンスの形式が不正です');
            }
            
            return trim($data['content'][0]['text']);
        }
        
        /**
         * Gemini APIでテキスト生成
         */
        private function generate_text_gemini($prompt, $config = []) {
            $default_config = [
                'temperature' => 0.7,
                'max_output_tokens' => 3000
            ];
            
            $config = wp_parse_args($config, $default_config);
            
            $request_body = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => floatval($config['temperature']),
                    'maxOutputTokens' => intval($config['max_output_tokens'])
                ]
            ];
            
            $endpoint = $this->endpoints['gemini'] . '/models/' . $this->model . ':generateContent';
            $url = add_query_arg('key', $this->api_key, $endpoint);
            
            $args = [
                'method' => 'POST',
                'timeout' => $this->timeout,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => wp_json_encode($request_body)
            ];
            
            $response = wp_remote_post($url, $args);
            
            if (is_wp_error($response)) {
                return $response;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            if ($response_code !== 200) {
                return new WP_Error('gemini_api_error', "Gemini APIエラー: {$response_code}");
            }
            
            $data = json_decode($response_body, true);
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return new WP_Error('invalid_response', 'APIレスポンスの形式が不正です');
            }
            
            return trim($data['candidates'][0]['content']['parts'][0]['text']);
        }
        
        /**
         * コンテンツ生成メソッド
         */
        public function generate_content($grant_data) {
            $prompt_template = get_option('giji_content_prompt', '');
            if (empty($prompt_template)) {
                return new WP_Error('no_prompt', '本文生成用プロンプトが設定されていません');
            }
            
            $prompt = $this->replace_variables($prompt_template, $grant_data);
            return $this->generate_text($prompt, ['max_tokens' => 3000, 'max_output_tokens' => 3000]);
        }
        
        public function generate_excerpt($grant_data) {
            $prompt_template = get_option('giji_excerpt_prompt', '');
            if (empty($prompt_template)) {
                $prompt_template = '以下の助成金情報を基に、100文字程度の魅力的な要約を作成してください。

助成金名: [補助金名]
概要: [概要]

読者が興味を持ち、申請を検討したくなるような簡潔な文章にしてください。';
            }
            
            $prompt = $this->replace_variables($prompt_template, $grant_data);
            return $this->generate_text($prompt, ['max_tokens' => 150, 'max_output_tokens' => 150]);
        }
        
        /**
         * プロンプト内の変数を置換
         */
        private function replace_variables($template, $grant_data) {
            // 補助額の万円表示を計算
            $max_amount_man = '';
            if (isset($grant_data['subsidy_max_limit']) && is_numeric($grant_data['subsidy_max_limit'])) {
                $amount_numeric = intval($grant_data['subsidy_max_limit']);
                $amount_man = $amount_numeric / 10000;
                $max_amount_man = number_format($amount_man) . '万円';
            }
            
            // 締切日の日本語表記
            $deadline_japanese = '';
            if (isset($grant_data['acceptance_end_datetime'])) {
                $timestamp = strtotime($grant_data['acceptance_end_datetime']);
                if ($timestamp) {
                    $deadline_japanese = date('Y年n月j日', $timestamp);
                }
            }
            
            $variables = [
                '[補助金名]' => $grant_data['title'] ?? '',
                '[概要]' => $grant_data['overview'] ?? '',
                '[Jグランツ詳細URL]' => $grant_data['front_subsidy_detail_page_url'] ?? '',
                '[補助額上限]' => $grant_data['subsidy_max_limit'] ?? '',
                '[補助額上限万円]' => $max_amount_man,
                '[補助率]' => $grant_data['subsidy_rate'] ?? '',
                '[募集終了日時]' => $grant_data['acceptance_end_datetime'] ?? '',
                '[募集終了日時日本語]' => $deadline_japanese,
                '[利用目的]' => $grant_data['use_purpose'] ?? '',
                '[補助対象地域]' => $grant_data['target_area_search'] ?? ''
            ];
            
            return str_replace(array_keys($variables), array_values($variables), $template);
        }
        
        /**
         * APIキー検証
         */
        public function validate_api_key() {
            if (empty($this->api_key)) {
                return false;
            }
            
            $test_response = $this->generate_text('テスト', ['max_tokens' => 10, 'max_output_tokens' => 10]);
            return !is_wp_error($test_response);
        }
        
        public function get_provider() {
            return $this->provider;
        }
    }
}

// =============================================
// 助成金データ処理クラス
// =============================================

if (!class_exists('GrantDataProcessor')) {
    class GrantDataProcessor {
        
        private $jgrants_client;
        private $ai_client;
        
        public function __construct($jgrants_client, $ai_client) {
            $this->jgrants_client = $jgrants_client;
            $this->ai_client = $ai_client;
        }
        
        /**
         * 助成金データを処理してWordPressに保存
         */
        public function process_and_save_grant($subsidy_data) {
            // データ検証
            if (!is_array($subsidy_data) || empty($subsidy_data['id']) || empty($subsidy_data['title'])) {
                return new WP_Error('invalid_data', 'データが不正です');
            }
            
            // 重複チェック
            if ($this->is_duplicate($subsidy_data['id'])) {
                return new WP_Error('duplicate_grant', '既に登録済みの助成金です');
            }
            
            // データマッピング
            $mapped_data = $this->map_jgrants_data($subsidy_data);
            
            // AI生成コンテンツの作成
            $ai_enabled = get_option('giji_ai_generation_enabled', []);
            
            if (isset($ai_enabled['content']) && $ai_enabled['content']) {
                $content = $this->ai_client->generate_content($mapped_data);
                if (!is_wp_error($content)) {
                    $mapped_data['post_content'] = $content;
                }
            }
            
            if (isset($ai_enabled['excerpt']) && $ai_enabled['excerpt']) {
                $excerpt = $this->ai_client->generate_excerpt($mapped_data);
                if (!is_wp_error($excerpt)) {
                    $mapped_data['ai_excerpt'] = $excerpt;
                }
            }
            
            // 投稿として保存
            $post_id = $this->save_as_wordpress_post($mapped_data);
            
            if (is_wp_error($post_id)) {
                return $post_id;
            }
            
            giji_log('助成金データ保存成功: 投稿ID ' . $post_id);
            return $post_id;
        }
        
        /**
         * 重複チェック
         */
        private function is_duplicate($jgrants_id) {
            $existing_posts = get_posts([
                'post_type' => 'grant',
                'meta_query' => [
                    [
                        'key' => 'jgrants_id',
                        'value' => $jgrants_id,
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => 1,
                'post_status' => ['publish', 'draft', 'private']
            ]);
            
            return !empty($existing_posts);
        }
        
        /**
         * JグランツAPIデータをWordPress形式にマッピング
         */
        private function map_jgrants_data($subsidy_data) {
            $mapped_data = [
                'jgrants_id' => $subsidy_data['id'],
                'title' => $subsidy_data['title'] ?? '',
                'overview' => $subsidy_data['detail'] ?? ''
            ];
            
            // 募集終了日時の処理
            if (isset($subsidy_data['acceptance_end_datetime'])) {
                $datetime = $subsidy_data['acceptance_end_datetime'];
                $timestamp = strtotime($datetime);
                if ($timestamp) {
                    $mapped_data['deadline_date'] = date('Ymd', $timestamp);
                    $mapped_data['deadline_text'] = date('Y年n月j日', $timestamp);
                    $mapped_data['acceptance_end_datetime'] = $datetime;
                }
            }
            
            // 補助額の処理
            if (isset($subsidy_data['subsidy_max_limit']) && is_numeric($subsidy_data['subsidy_max_limit'])) {
                $amount_numeric = intval($subsidy_data['subsidy_max_limit']);
                $mapped_data['max_amount_numeric'] = $amount_numeric;
                $mapped_data['subsidy_max_limit'] = $amount_numeric;
                $amount_man = $amount_numeric / 10000;
                $mapped_data['max_amount'] = number_format($amount_man);
            }
            
            $mapped_data['subsidy_rate'] = $subsidy_data['subsidy_rate'] ?? '';
            $mapped_data['official_url'] = $subsidy_data['front_subsidy_detail_page_url'] ?? '';
            $mapped_data['front_subsidy_detail_page_url'] = $subsidy_data['front_subsidy_detail_page_url'] ?? '';
            $mapped_data['use_purpose'] = $subsidy_data['use_purpose'] ?? '';
            $mapped_data['target_area_search'] = $subsidy_data['target_area_search'] ?? '';
            
            return $mapped_data;
        }
        
        /**
         * WordPressの投稿として保存
         */
        private function save_as_wordpress_post($grant_data) {
            $post_content = $grant_data['post_content'] ?? $grant_data['overview'];
            $post_excerpt = $grant_data['ai_excerpt'] ?? '';
            
            $post_data = [
                'post_title' => $grant_data['title'],
                'post_content' => $post_content,
                'post_excerpt' => $post_excerpt,
                'post_status' => 'draft',
                'post_type' => 'grant',
                'post_author' => 1
            ];
            
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                return $post_id;
            }
            
            // カスタムフィールドの保存
            $custom_fields = [
                'jgrants_id', 'ai_excerpt', 'deadline_date', 'deadline_text',
                'max_amount_numeric', 'max_amount', 'official_url', 'subsidy_rate'
            ];
            
            foreach ($custom_fields as $field) {
                if (isset($grant_data[$field])) {
                    giji_update_field($field, $grant_data[$field], $post_id);
                }
            }
            
            // タクソノミーの設定
            $this->set_taxonomies($post_id, $grant_data);
            
            return $post_id;
        }
        
        /**
         * タクソノミーの設定
         */
        private function set_taxonomies($post_id, $grant_data) {
            // 対象地域の設定
            if (isset($grant_data['target_area_search'])) {
                $areas = explode(' / ', $grant_data['target_area_search']);
                $area_terms = [];
                
                foreach ($areas as $area) {
                    $area = trim($area);
                    if (!empty($area)) {
                        $term = get_term_by('name', $area, 'grant_prefecture');
                        if (!$term) {
                            $term = wp_insert_term($area, 'grant_prefecture');
                            if (!is_wp_error($term)) {
                                $area_terms[] = $term['term_id'];
                            }
                        } else {
                            $area_terms[] = $term->term_id;
                        }
                    }
                }
                
                if (!empty($area_terms)) {
                    wp_set_object_terms($post_id, $area_terms, 'grant_prefecture');
                }
            }
            
            // 利用目的の設定
            if (isset($grant_data['use_purpose'])) {
                $purposes = explode(' / ', $grant_data['use_purpose']);
                $purpose_terms = [];
                
                foreach ($purposes as $purpose) {
                    $purpose = trim($purpose);
                    if (!empty($purpose)) {
                        $term = get_term_by('name', $purpose, 'grant_category');
                        if (!$term) {
                            $term = wp_insert_term($purpose, 'grant_category');
                            if (!is_wp_error($term)) {
                                $purpose_terms[] = $term['term_id'];
                            }
                        } else {
                            $purpose_terms[] = $term->term_id;
                        }
                    }
                }
                
                if (!empty($purpose_terms)) {
                    wp_set_object_terms($post_id, $purpose_terms, 'grant_category');
                }
            }
        }
    }
}

// =============================================
// 自動化コントローラークラス
// =============================================

if (!class_exists('AutomationController')) {
    class AutomationController {
        
        private $data_processor;
        const CRON_HOOK = 'giji_auto_import_hook';
        
        public function __construct($data_processor) {
            $this->data_processor = $data_processor;
            add_action(self::CRON_HOOK, [$this, 'execute_auto_import']);
        }
        
        /**
         * 自動インポートの実行
         */
        public function execute_auto_import() {
            giji_log('自動インポート開始');
            
            $jgrants_client = new JGrantsAPIClient();
            
            if (!$jgrants_client->test_connection()) {
                giji_log('JグランツAPIに接続できませんでした', 'error');
                return;
            }
            
            $search_params = [
                'keyword' => get_option('giji_import_keyword', '補助金'),
                'sort' => 'created_date',
                'order' => 'DESC',
                'acceptance' => '1'
            ];
            
            $response = $jgrants_client->get_subsidies($search_params);
            
            if (is_wp_error($response) || !isset($response['result'])) {
                giji_log('助成金一覧取得に失敗しました', 'error');
                return;
            }
            
            $subsidies = $response['result'];
            $processed_count = 0;
            $max_count = intval(get_option('giji_max_process_count', 10));
            
            foreach ($subsidies as $index => $subsidy) {
                if ($processed_count >= $max_count) {
                    break;
                }
                
                $detail_response = $jgrants_client->get_subsidy_detail($subsidy['id']);
                
                if (is_wp_error($detail_response) || !isset($detail_response['result'][0])) {
                    continue;
                }
                
                $detail_data = $detail_response['result'][0];
                $result = $this->data_processor->process_and_save_grant($detail_data);
                
                if (!is_wp_error($result)) {
                    $processed_count++;
                }
            }
            
            giji_log('自動インポート完了: ' . $processed_count . '件処理');
            
            // 結果を保存
            update_option('giji_last_import_result', [
                'timestamp' => current_time('mysql'),
                'processed_count' => $processed_count,
                'total_count' => count($subsidies)
            ]);
        }
        
        /**
         * 手動インポートの実行
         */
        public function execute_manual_import($search_params = [], $max_count = 5) {
            $jgrants_client = new JGrantsAPIClient();
            
            if (!$jgrants_client->test_connection()) {
                return [
                    'success' => false,
                    'message' => 'JグランツAPIに接続できませんでした。'
                ];
            }
            
            $default_params = [
                'keyword' => '補助金',
                'sort' => 'created_date',
                'order' => 'DESC',
                'acceptance' => '1',
                'per_page' => $max_count
            ];
            
            $search_params = wp_parse_args($search_params, $default_params);
            $response = $jgrants_client->get_subsidies($search_params);
            
            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => '助成金一覧取得エラー: ' . $response->get_error_message()
                ];
            }
            
            if (!isset($response['result']) || empty($response['result'])) {
                return [
                    'success' => true,
                    'results' => ['success' => 0, 'error' => 0, 'duplicate' => 0, 'details' => []]
                ];
            }
            
            $subsidies = $response['result'];
            $results = ['success' => 0, 'error' => 0, 'duplicate' => 0, 'details' => []];
            
            foreach ($subsidies as $subsidy) {
                $detail_response = $jgrants_client->get_subsidy_detail($subsidy['id']);
                
                if (is_wp_error($detail_response) || !isset($detail_response['result'][0])) {
                    $results['error']++;
                    $results['details'][] = [
                        'title' => $subsidy['title'] ?? 'ID:' . $subsidy['id'],
                        'status' => 'error',
                        'message' => '詳細取得失敗'
                    ];
                    continue;
                }
                
                $detail_data = $detail_response['result'][0];
                $result = $this->data_processor->process_and_save_grant($detail_data);
                
                if (is_wp_error($result)) {
                    if ($result->get_error_code() === 'duplicate_grant') {
                        $results['duplicate']++;
                        $results['details'][] = [
                            'title' => $detail_data['title'],
                            'status' => 'duplicate',
                            'message' => '重複'
                        ];
                    } else {
                        $results['error']++;
                        $results['details'][] = [
                            'title' => $detail_data['title'],
                            'status' => 'error',
                            'message' => $result->get_error_message()
                        ];
                    }
                } else {
                    $results['success']++;
                    $results['details'][] = [
                        'title' => $detail_data['title'],
                        'status' => 'success',
                        'message' => '投稿ID: ' . $result
                    ];
                }
            }
            
            return ['success' => true, 'results' => $results];
        }
        
        /**
         * 手動公開の実行
         */
        public function execute_manual_publish($count) {
            $draft_posts = get_posts([
                'post_type' => 'grant',
                'post_status' => 'draft',
                'posts_per_page' => $count,
                'orderby' => 'date',
                'order' => 'ASC'
            ]);
            
            $results = ['success' => 0, 'error' => 0, 'details' => []];
            
            foreach ($draft_posts as $post) {
                $update_result = wp_update_post([
                    'ID' => $post->ID,
                    'post_status' => 'publish'
                ]);
                
                if (is_wp_error($update_result)) {
                    $results['error']++;
                    $results['details'][] = [
                        'title' => $post->post_title,
                        'status' => 'error',
                        'message' => '公開失敗'
                    ];
                } else {
                    $results['success']++;
                    $results['details'][] = [
                        'title' => $post->post_title,
                        'status' => 'success',
                        'message' => '公開完了'
                    ];
                }
            }
            
            return $results;
        }
        
        /**
         * 下書き一括削除の実行
         */
        public function execute_bulk_delete_drafts() {
            $results = ['success' => 0, 'error' => 0];
            
            $draft_posts = get_posts([
                'post_type' => 'grant',
                'post_status' => 'draft',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            
            foreach ($draft_posts as $post_id) {
                $delete_result = wp_delete_post($post_id, true);
                
                if ($delete_result !== false) {
                    $results['success']++;
                } else {
                    $results['error']++;
                }
            }
            
            return $results;
        }
        
        /**
         * Cronスケジュールの設定
         */
        public function set_cron_schedule($schedule) {
            wp_clear_scheduled_hook(self::CRON_HOOK);
            
            if ($schedule !== 'disabled') {
                wp_schedule_event(time(), $schedule, self::CRON_HOOK);
            }
            
            update_option('giji_cron_schedule', $schedule);
        }
        
        /**
         * 統計情報の取得
         */
        public function get_draft_count() {
            $count = wp_count_posts('grant');
            return isset($count->draft) ? intval($count->draft) : 0;
        }
        
        public function get_published_count() {
            $count = wp_count_posts('grant');
            return isset($count->publish) ? intval($count->publish) : 0;
        }
        
        public function get_next_scheduled_time() {
            $timestamp = wp_next_scheduled(self::CRON_HOOK);
            return $timestamp ? date('Y-m-d H:i:s', $timestamp) : false;
        }
        
        public function get_last_import_result() {
            return get_option('giji_last_import_result', false);
        }
    }
}

// =============================================
// 管理画面マネージャークラス
// =============================================

if (!class_exists('AdminPageManager')) {
    class AdminPageManager {
        
        private $automation_controller;
        
        public function __construct($automation_controller) {
            $this->automation_controller = $automation_controller;
            
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_init', [$this, 'register_settings']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
            
            // AJAX処理
            add_action('wp_ajax_giji_manual_import', [$this, 'handle_manual_import']);
            add_action('wp_ajax_giji_manual_publish', [$this, 'handle_manual_publish']);
            add_action('wp_ajax_giji_bulk_delete_drafts', [$this, 'handle_bulk_delete_drafts']);
            add_action('wp_ajax_giji_test_api_keys', [$this, 'handle_test_api_keys']);
        }
        
        /**
         * 管理画面メニューの追加
         */
        public function add_admin_menu() {
            add_menu_page(
                'Grant Insight Jグランツ・インポーター',
                'Jグランツ・インポーター',
                'manage_options',
                'grant-insight-jgrants-importer',
                [$this, 'display_main_page'],
                'dashicons-money-alt',
                30
            );
            
            add_submenu_page(
                'grant-insight-jgrants-importer',
                '設定',
                '設定',
                'manage_options',
                'giji-settings',
                [$this, 'display_settings_page']
            );
        }
        
        /**
         * 設定の登録
         */
        public function register_settings() {
            // API設定
            register_setting('giji_api_settings', 'giji_ai_provider');
            register_setting('giji_api_settings', 'giji_gemini_api_key');
            register_setting('giji_api_settings', 'giji_openai_api_key');
            register_setting('giji_api_settings', 'giji_claude_api_key');
            register_setting('giji_api_settings', 'giji_gemini_model');
            register_setting('giji_api_settings', 'giji_openai_model');
            register_setting('giji_api_settings', 'giji_claude_model');
            
            // 自動化設定
            register_setting('giji_automation_settings', 'giji_cron_schedule');
            register_setting('giji_automation_settings', 'giji_max_process_count');
            register_setting('giji_automation_settings', 'giji_import_keyword');
            register_setting('giji_automation_settings', 'giji_import_count');
            
            // AI生成設定
            register_setting('giji_ai_settings', 'giji_ai_generation_enabled');
            
            // プロンプト設定
            register_setting('giji_prompt_settings', 'giji_content_prompt');
            register_setting('giji_prompt_settings', 'giji_excerpt_prompt');
        }
        
        /**
         * スクリプトの読み込み
         */
        public function enqueue_admin_scripts($hook) {
            if (strpos($hook, 'grant-insight-jgrants-importer') === false && strpos($hook, 'giji-') === false) {
                return;
            }
            
            // CSSスタイルを追加
            wp_add_inline_style('wp-admin', '
                .giji-dashboard { max-width: 1200px; }
                .giji-stats { display: flex; gap: 20px; margin-bottom: 30px; }
                .giji-stat-box { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center; min-width: 150px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
                .giji-stat-box h3 { margin: 0 0 10px 0; font-size: 14px; color: #666; font-weight: normal; }
                .giji-stat-number { font-size: 32px; font-weight: bold; color: #0073aa; line-height: 1; }
                .giji-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
                .giji-action-section { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
                .giji-action-section h3 { margin: 0 0 10px 0; font-size: 16px; color: #23282d; }
                .giji-action-section p { margin: 0 0 15px 0; color: #666; font-size: 13px; }
                .giji-result { margin-top: 15px; }
                .giji-result .notice { margin: 10px 0; }
                .giji-result ul { margin: 10px 0; padding-left: 20px; }
                .giji-result li.success { color: #46b450; }
                .giji-result li.error { color: #dc3232; }
                .giji-result .success { color: #46b450; font-weight: bold; }
                .giji-result .error { color: #dc3232; font-weight: bold; }
                .giji-status { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
                .giji-status h3 { margin: 0 0 15px 0; font-size: 16px; color: #23282d; }
                .giji-status p { margin: 5px 0; color: #666; }
                .api-key-row { transition: opacity 0.3s ease; }
                .api-key-row.hidden { display: none; }
                .giji-variables-list { background: #e7f3ff; padding: 10px; margin: 10px 0; border-radius: 4px; }
                .giji-variables-list code { background: #fff; padding: 2px 4px; margin: 2px; border-radius: 2px; }
            ');
            
            // JavaScriptを追加
            wp_add_inline_script('jquery', '
                jQuery(document).ready(function($) {
                    var giji_ajax = {
                        ajax_url: "' . admin_url('admin-ajax.php') . '",
                        nonce: "' . wp_create_nonce('giji_ajax_nonce') . '"
                    };
                    
                    // AIプロバイダー切り替え処理
                    $("#giji_ai_provider").on("change", function() {
                        var provider = $(this).val();
                        $(".api-key-row").hide();
                        $("." + provider + "-row").show();
                    });
                    
                    // 手動インポート
                    $("#giji-manual-import").on("click", function() {
                        var button = $(this);
                        var resultDiv = $("#giji-import-result");
                        var keyword = $("#giji-import-keyword").val();
                        var count = $("#giji-import-count").val();
                        
                        if (keyword.length < 2) {
                            alert("キーワードは2文字以上で入力してください。");
                            return;
                        }
                        if (!count || count < 1) {
                            alert("取得件数は1以上で入力してください。");
                            return;
                        }
                        
                        button.prop("disabled", true).text("実行中...");
                        resultDiv.html("<p>インポートを実行しています...</p>");
                        
                        $.ajax({
                            url: giji_ajax.ajax_url,
                            type: "POST",
                            data: {
                                action: "giji_manual_import",
                                nonce: giji_ajax.nonce,
                                keyword: keyword,
                                count: count
                            },
                            success: function(response) {
                                if (response.success) {
                                    var html = "<div class=\"notice notice-success\"><p>インポートが完了しました。</p></div>";
                                    html += "<p><strong>検索キーワード:</strong> " + keyword + " | <strong>取得件数:</strong> " + count + "件</p>";
                                    html += "<p>成功: " + response.results.success + "件、エラー: " + response.results.error + "件、重複: " + response.results.duplicate + "件</p>";
                                    
                                    if (response.results.details.length > 0) {
                                        html += "<h4>詳細:</h4><ul>";
                                        response.results.details.forEach(function(detail) {
                                            var statusClass = detail.status === "success" ? "success" : "error";
                                            html += "<li class=\"" + statusClass + "\">" + detail.title + " (" + detail.status + ")";
                                            if (detail.message) {
                                                html += " - " + detail.message;
                                            }
                                            html += "</li>";
                                        });
                                        html += "</ul>";
                                    }
                                    
                                    resultDiv.html(html);
                                    
                                    setTimeout(function() {
                                        location.reload();
                                    }, 3000);
                                } else {
                                    resultDiv.html("<div class=\"notice notice-error\"><p>エラー: " + response.message + "</p></div>");
                                }
                            },
                            error: function() {
                                resultDiv.html("<div class=\"notice notice-error\"><p>通信エラーが発生しました。</p></div>");
                            },
                            complete: function() {
                                button.prop("disabled", false).text("手動インポート実行");
                            }
                        });
                    });
                    
                    // 手動公開
                    $("#giji-manual-publish").on("click", function() {
                        var button = $(this);
                        var resultDiv = $("#giji-publish-result");
                        var count = $("#giji-publish-count").val();
                        
                        if (!count || count < 1) {
                            alert("公開件数を正しく入力してください。");
                            return;
                        }
                        
                        button.prop("disabled", true).text("実行中...");
                        resultDiv.html("<p>公開処理を実行しています...</p>");
                        
                        $.ajax({
                            url: giji_ajax.ajax_url,
                            type: "POST",
                            data: {
                                action: "giji_manual_publish",
                                nonce: giji_ajax.nonce,
                                count: count
                            },
                            success: function(response) {
                                if (response.success) {
                                    var html = "<div class=\"notice notice-success\"><p>公開処理が完了しました。</p></div>";
                                    html += "<p>成功: " + response.results.success + "件、エラー: " + response.results.error + "件</p>";
                                    
                                    if (response.results.details && response.results.details.length > 0) {
                                        html += "<h4>詳細:</h4><ul>";
                                        response.results.details.forEach(function(detail) {
                                            var statusClass = detail.status === "success" ? "success" : "error";
                                            html += "<li class=\"" + statusClass + "\">" + detail.title + " (" + detail.status + ")";
                                            if (detail.message) {
                                                html += " - " + detail.message;
                                            }
                                            html += "</li>";
                                        });
                                        html += "</ul>";
                                    }
                                    
                                    resultDiv.html(html);
                                    
                                    setTimeout(function() {
                                        location.reload();
                                    }, 3000);
                                } else {
                                    resultDiv.html("<div class=\"notice notice-error\"><p>エラーが発生しました。</p></div>");
                                }
                            },
                            error: function() {
                                resultDiv.html("<div class=\"notice notice-error\"><p>通信エラーが発生しました。</p></div>");
                            },
                            complete: function() {
                                button.prop("disabled", false).text("公開実行");
                            }
                        });
                    });
                    
                    // 下書き一括削除
                    $("#giji-bulk-delete").on("click", function() {
                        if (!confirm("本当に下書きをすべて削除しますか？")) {
                            return false;
                        }
                        
                        var button = $(this);
                        var resultDiv = $("#giji-delete-result");
                        
                        button.prop("disabled", true).text("実行中...");
                        resultDiv.html("<p>削除処理を実行しています...</p>");
                        
                        $.ajax({
                            url: giji_ajax.ajax_url,
                            type: "POST",
                            data: {
                                action: "giji_bulk_delete_drafts",
                                nonce: giji_ajax.nonce
                            },
                            success: function(response) {
                                if (response.success) {
                                    var html = "<div class=\"notice notice-success\"><p>削除処理が完了しました。</p></div>";
                                    html += "<p>成功: " + response.results.success + "件、エラー: " + response.results.error + "件</p>";
                                    
                                    resultDiv.html(html);
                                    
                                    setTimeout(function() {
                                        location.reload();
                                    }, 3000);
                                } else {
                                    resultDiv.html("<div class=\"notice notice-error\"><p>エラーが発生しました。</p></div>");
                                }
                            },
                            error: function() {
                                resultDiv.html("<div class=\"notice notice-error\"><p>通信エラーが発生しました。</p></div>");
                            },
                            complete: function() {
                                button.prop("disabled", false).text("一括削除実行");
                            }
                        });
                    });
                    
                    // APIキーテスト
                    $("#giji-test-api-keys").on("click", function() {
                        var button = $(this);
                        var resultDiv = $("#giji-api-test-result");
                        
                        button.prop("disabled", true).text("テスト中...");
                        resultDiv.html("<p>APIキーをテストしています...</p>");
                        
                        $.ajax({
                            url: giji_ajax.ajax_url,
                            type: "POST",
                            data: {
                                action: "giji_test_api_keys",
                                nonce: giji_ajax.nonce
                            },
                            success: function(response) {
                                if (response.success) {
                                    var html = "<div class=\"notice notice-info\"><p>APIキーテスト結果:</p></div>";
                                    html += "<ul>";
                                    html += "<li>JグランツAPI: " + (response.jgrants ? "<span class=\"success\">有効</span>" : "<span class=\"error\">無効</span>") + "</li>";
                                    html += "<li>選択されたAI API (" + response.provider + "): " + (response.ai_api ? "<span class=\"success\">有効</span>" : "<span class=\"error\">無効</span>") + "</li>";
                                    html += "</ul>";
                                    
                                    resultDiv.html(html);
                                } else {
                                    resultDiv.html("<div class=\"notice notice-error\"><p>テストに失敗しました。</p></div>");
                                }
                            },
                            error: function() {
                                resultDiv.html("<div class=\"notice notice-error\"><p>通信エラーが発生しました。</p></div>");
                            },
                            complete: function() {
                                button.prop("disabled", false).text("APIキーをテスト");
                            }
                        });
                    });
                });
            ');
        }
        
        /**
         * メインページの表示
         */
        public function display_main_page() {
            $draft_count = $this->automation_controller->get_draft_count();
            $published_count = $this->automation_controller->get_published_count();
            $last_import = $this->automation_controller->get_last_import_result();
            $next_scheduled = $this->automation_controller->get_next_scheduled_time();
            
            ?>
            <div class="wrap">
                <h1>Grant Insight Jグランツ・インポーター</h1>
                
                <div class="giji-dashboard">
                    <div class="giji-stats">
                        <div class="giji-stat-box">
                            <h3>下書き投稿数</h3>
                            <div class="giji-stat-number"><?php echo $draft_count; ?></div>
                        </div>
                        <div class="giji-stat-box">
                            <h3>公開投稿数</h3>
                            <div class="giji-stat-number"><?php echo $published_count; ?></div>
                        </div>
                    </div>
                    
                    <div class="giji-actions">
                        <div class="giji-action-section">
                            <h3>手動インポート</h3>
                            <p>JグランツAPIから最新の助成金情報を手動で取得します。</p>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="giji-import-keyword">キーワード</label></th>
                                    <td>
                                        <input type="text" id="giji-import-keyword" value="<?php echo esc_attr(get_option('giji_import_keyword', '補助金')); ?>" class="regular-text">
                                        <p class="description">Jグランツで検索するキーワード（必須、2文字以上）</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="giji-import-count">取得件数</label></th>
                                    <td>
                                        <input type="number" id="giji-import-count" value="<?php echo esc_attr(get_option('giji_import_count', 5)); ?>" min="1" class="small-text">
                                        <p class="description">一度に取得する最大件数</p>
                                    </td>
                                </tr>
                            </table>
                            <button type="button" class="button button-primary" id="giji-manual-import">手動インポート実行</button>
                            <div id="giji-import-result" class="giji-result"></div>
                        </div>
                        
                        <div class="giji-action-section">
                            <h3>手動公開</h3>
                            <p>下書き状態の助成金投稿を公開します。</p>
                            <label for="giji-publish-count">公開件数:</label>
                            <input type="number" id="giji-publish-count" value="5" min="1" max="<?php echo $draft_count; ?>">
                            <button type="button" class="button button-primary" id="giji-manual-publish">公開実行</button>
                            <div id="giji-publish-result" class="giji-result"></div>
                        </div>
                        
                        <div class="giji-action-section">
                            <h3>下書き一括削除</h3>
                            <p>下書き状態の助成金投稿をすべて削除します。</p>
                            <button type="button" class="button button-secondary" id="giji-bulk-delete">一括削除実行</button>
                            <div id="giji-delete-result" class="giji-result"></div>
                        </div>
                    </div>
                    
                    <div class="giji-status">
                        <h3>自動インポート状況</h3>
                        <?php if ($next_scheduled): ?>
                            <p><strong>次回実行予定:</strong> <?php echo $next_scheduled; ?></p>
                        <?php else: ?>
                            <p><strong>自動インポート:</strong> 無効</p>
                        <?php endif; ?>
                        
                        <?php if ($last_import): ?>
                            <p><strong>最後の実行:</strong> <?php echo $last_import['timestamp']; ?></p>
                            <p><strong>処理件数:</strong> <?php echo $last_import['processed_count']; ?>件 / <?php echo $last_import['total_count']; ?>件</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
        
        /**
         * 設定ページの表示
         */
        public function display_settings_page() {
            if (isset($_POST['submit'])) {
                // 設定保存処理
                $this->save_settings();
                echo '<div class="notice notice-success is-dismissible"><p>設定を保存しました。</p></div>';
            }
            
            $ai_provider = get_option('giji_ai_provider', 'openai');
            $cron_schedule = get_option('giji_cron_schedule', 'daily');
            $ai_enabled = get_option('giji_ai_generation_enabled', []);
            
            ?>
            <div class="wrap">
                <h1>設定</h1>
                
                <form method="post" action="">
                    <?php wp_nonce_field('giji_settings_action', 'giji_settings_nonce'); ?>
                    
                    <h2>AI API設定</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">AIプロバイダー</th>
                            <td>
                                <select name="giji_ai_provider" id="giji_ai_provider">
                                    <option value="gemini" <?php selected($ai_provider, 'gemini'); ?>>Google Gemini</option>
                                    <option value="openai" <?php selected($ai_provider, 'openai'); ?>>OpenAI (ChatGPT)</option>
                                    <option value="claude" <?php selected($ai_provider, 'claude'); ?>>Anthropic (Claude)</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="api-key-row gemini-row" <?php echo $ai_provider !== 'gemini' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">Gemini APIキー</th>
                            <td>
                                <input type="text" name="giji_gemini_api_key" value="<?php echo esc_attr(get_option('giji_gemini_api_key', '')); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr class="api-key-row openai-row" <?php echo $ai_provider !== 'openai' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">OpenAI APIキー</th>
                            <td>
                                <input type="text" name="giji_openai_api_key" value="<?php echo esc_attr(get_option('giji_openai_api_key', '')); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr class="api-key-row claude-row" <?php echo $ai_provider !== 'claude' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">Claude APIキー</th>
                            <td>
                                <input type="text" name="giji_claude_api_key" value="<?php echo esc_attr(get_option('giji_claude_api_key', '')); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">自動インポートスケジュール</th>
                            <td>
                                <select name="giji_cron_schedule">
                                    <option value="disabled" <?php selected($cron_schedule, 'disabled'); ?>>無効</option>
                                    <option value="hourly" <?php selected($cron_schedule, 'hourly'); ?>>1時間ごと</option>
                                    <option value="twicedaily" <?php selected($cron_schedule, 'twicedaily'); ?>>1日2回</option>
                                    <option value="daily" <?php selected($cron_schedule, 'daily'); ?>>1日1回</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">最大処理件数</th>
                            <td>
                                <input type="number" name="giji_max_process_count" value="<?php echo esc_attr(get_option('giji_max_process_count', 10)); ?>" min="1" max="100">
                            </td>
                        </tr>
                    </table>
                    
                    <h2>インポート設定</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">デフォルト検索キーワード</th>
                            <td>
                                <input type="text" name="giji_import_keyword" value="<?php echo esc_attr(get_option('giji_import_keyword', '補助金')); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">デフォルト取得件数</th>
                            <td>
                                <input type="number" name="giji_import_count" value="<?php echo esc_attr(get_option('giji_import_count', 5)); ?>" min="1" max="50">
                            </td>
                        </tr>
                    </table>
                    
                    <h2>AI生成機能</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">有効な機能</th>
                            <td>
                                <label><input type="checkbox" name="giji_ai_generation_enabled[content]" value="1" <?php checked(isset($ai_enabled['content']) && $ai_enabled['content']); ?>> 本文生成</label><br>
                                <label><input type="checkbox" name="giji_ai_generation_enabled[excerpt]" value="1" <?php checked(isset($ai_enabled['excerpt']) && $ai_enabled['excerpt']); ?>> 要約生成（抜粋）</label><br>
                            </td>
                        </tr>
                    </table>
                    
                    <h2>プロンプト設定</h2>
                    
                    <div class="giji-variables-list">
                        <h4>使用可能な変数</h4>
                        <p>プロンプト内で以下の変数を使用できます：</p>
                        <code>[補助金名]</code> <code>[概要]</code> <code>[補助額上限]</code> <code>[補助額上限万円]</code> <code>[補助率]</code> <code>[募集終了日時]</code> <code>[募集終了日時日本語]</code> <code>[Jグランツ詳細URL]</code> <code>[利用目的]</code> <code>[補助対象地域]</code>
                    </div>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">本文生成用プロンプト</th>
                            <td>
                                <textarea name="giji_content_prompt" rows="12" class="large-text" style="font-family: monospace;"><?php echo esc_textarea(get_option('giji_content_prompt', '')); ?></textarea>
                                <p class="description">HTMLとCSSを使用したスタイリッシュな記事デザインで生成されます</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">要約生成用プロンプト</th>
                            <td>
                                <textarea name="giji_excerpt_prompt" rows="6" class="large-text"><?php echo esc_textarea(get_option('giji_excerpt_prompt', '')); ?></textarea>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('設定を保存'); ?>
                    
                    <button type="button" class="button" id="giji-test-api-keys">APIキーをテスト</button>
                    <div id="giji-api-test-result" class="giji-result"></div>
                </form>
            </div>
            <?php
        }
        
        /**
         * 設定保存処理
         */
        private function save_settings() {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['giji_settings_nonce'], 'giji_settings_action')) {
                return;
            }
            
            // API設定の保存
            if (isset($_POST['giji_ai_provider'])) {
                update_option('giji_ai_provider', sanitize_text_field($_POST['giji_ai_provider']));
            }
            
            $api_keys = ['giji_gemini_api_key', 'giji_openai_api_key', 'giji_claude_api_key'];
            foreach ($api_keys as $key) {
                if (isset($_POST[$key])) {
                    update_option($key, sanitize_text_field($_POST[$key]));
                }
            }
            
            // その他の設定保存
            $other_settings = [
                'giji_cron_schedule' => 'sanitize_text_field',
                'giji_max_process_count' => 'absint',
                'giji_import_keyword' => 'sanitize_text_field',
                'giji_import_count' => 'absint',
                'giji_content_prompt' => 'wp_kses_post',
                'giji_excerpt_prompt' => 'wp_kses_post'
            ];
            
            foreach ($other_settings as $option => $sanitize_callback) {
                if (isset($_POST[$option])) {
                    update_option($option, call_user_func($sanitize_callback, $_POST[$option]));
                }
            }
            
            // AI生成機能の設定
            $ai_enabled = isset($_POST['giji_ai_generation_enabled']) && is_array($_POST['giji_ai_generation_enabled'])
                        ? array_map('sanitize_text_field', $_POST['giji_ai_generation_enabled'])
                        : [];
            update_option('giji_ai_generation_enabled', $ai_enabled);
            
            // Cronスケジュールの更新
            if (isset($_POST['giji_cron_schedule'])) {
                $this->automation_controller->set_cron_schedule(sanitize_text_field($_POST['giji_cron_schedule']));
            }
        }
        
        /**
         * AJAX処理
         */
        public function handle_manual_import() {
            check_ajax_referer('giji_ajax_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('権限がありません。');
            }
            
            $keyword = sanitize_text_field($_POST['keyword'] ?? '補助金');
            $count = absint($_POST['count'] ?? 5);
            
            $search_params = [
                'keyword' => $keyword,
                'sort' => 'created_date',
                'order' => 'DESC',
                'acceptance' => '1'
            ];
            
            $result = $this->automation_controller->execute_manual_import($search_params, $count);
            wp_send_json($result);
        }
        
        public function handle_manual_publish() {
            check_ajax_referer('giji_ajax_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('権限がありません。');
            }
            
            $count = absint($_POST['count']);
            $result = $this->automation_controller->execute_manual_publish($count);
            
            wp_send_json(['success' => true, 'results' => $result]);
        }
        
        public function handle_bulk_delete_drafts() {
            check_ajax_referer('giji_ajax_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('権限がありません。');
            }
            
            $result = $this->automation_controller->execute_bulk_delete_drafts();
            wp_send_json(['success' => true, 'results' => $result]);
        }
        
        public function handle_test_api_keys() {
            check_ajax_referer('giji_ajax_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('権限がありません。');
            }
            
            $jgrants_client = new JGrantsAPIClient();
            $unified_ai_client = new UnifiedAIClient();
            
            $jgrants_valid = $jgrants_client->test_connection();
            $ai_api_valid = $unified_ai_client->validate_api_key();
            
            wp_send_json([
                'success' => true,
                'jgrants' => $jgrants_valid,
                'ai_api' => $ai_api_valid,
                'provider' => $unified_ai_client->get_provider()
            ]);
        }
    }
}

// =============================================
// メインシステムクラス
// =============================================

if (!class_exists('JGrantsImporter')) {
    class JGrantsImporter {
        
        private static $instance = null;
        
        public static function get_instance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        private function __construct() {
            add_action('init', [$this, 'init']);
        }
        
        public function init() {
            // 各クラスのインスタンス化
            $jgrants_client = new JGrantsAPIClient();
            $ai_client = new UnifiedAIClient();
            $data_processor = new GrantDataProcessor($jgrants_client, $ai_client);
            $automation_controller = new AutomationController($data_processor);
            
            // 管理画面の初期化
            if (is_admin()) {
                new AdminPageManager($automation_controller);
            }
            
            // デフォルト設定の初期化
            add_action('admin_init', [$this, 'init_default_settings'], 1);
        }
        
        /**
         * デフォルト設定の初期化
         */
        public function init_default_settings() {
            $default_settings = [
                'giji_ai_provider' => 'gemini',
                'giji_openai_model' => 'gpt-4o-mini',
                'giji_gemini_model' => 'gemini-1.5-flash',
                'giji_claude_model' => 'claude-3-haiku-20240307',
                'giji_import_keyword' => '補助金',
                'giji_import_count' => 5,
                'giji_max_process_count' => 10,
                'giji_cron_schedule' => 'daily',
                'giji_ai_generation_enabled' => [
                    'content' => true,
                    'excerpt' => true
                ]
            ];
            
            // スタイリッシュなHTMLデザイン用プロンプト
            $default_prompts = [
                'giji_content_prompt' => 'あなたは助成金情報をわかりやすく伝えるプロのWebデザイナーです。以下の助成金情報を基に、HTML+CSSでスタイリッシュで読みやすい記事を作成してください。

【助成金情報】
助成金名: [補助金名]
概要: [概要]
補助額上限: [補助額上限万円]
補助率: [補助率]
募集終了日: [募集終了日時日本語]
詳細URL: [Jグランツ詳細URL]
対象地域: [補助対象地域]
利用目的: [利用目的]

【デザイン要件】
- 白黒ベースのモダンなデザイン
- 蛍光黄色（#FFFF00）をハイライト・マーカーとして使用
- 表やボックス、アイコンを効果的に配置
- レスポンシブ対応
- 読みやすいタイポグラフィ

【記事構成】
1. 助成金の概要とポイント
2. 基本情報表
3. 対象者・条件
4. 申請方法・スケジュール
5. 重要ポイント・注意事項

必ずHTML+CSSで完全なコードを出力してください。外部CSSファイルは使用せず、<style>タグ内にすべてのCSSを記述してください。',

                'giji_excerpt_prompt' => '以下の助成金情報を基に、100文字程度の魅力的な要約を作成してください。

助成金名: [補助金名]
概要: [概要]

読者が興味を持ち、申請を検討したくなるような簡潔な文章にしてください。'
            ];
            
            // 設定が存在しない場合のみデフォルト値を設定
            foreach ($default_settings as $key => $value) {
                if (false === get_option($key)) {
                    update_option($key, $value);
                }
            }
            
            foreach ($default_prompts as $key => $value) {
                if (false === get_option($key)) {
                    update_option($key, $value);
                }
            }
        }
    }
}

// =============================================
// ヘルパー関数
// =============================================

/**
 * ログ記録用の関数
 */
if (!function_exists('giji_log')) {
    function giji_log($message, $level = 'info') {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $log_message = sprintf('[%s] [%s] %s', $timestamp, strtoupper($level), $message);
        
        error_log('[Grant Insight Jグランツ・インポーター] ' . $log_message);
    }
}

/**
 * ACFフィールド更新用のヘルパー関数
 */
if (!function_exists('giji_update_field')) {
    function giji_update_field($key, $value, $post_id) {
        if (function_exists('update_field')) {
            return update_field($key, $value, $post_id);
        } else {
            return update_post_meta($post_id, $key, $value);
        }
    }
}

if (!function_exists('giji_get_field')) {
    function giji_get_field($key, $post_id = false) {
        if (function_exists('get_field')) {
            return get_field($key, $post_id);
        } else {
            return get_post_meta($post_id, $key, true);
        }
    }
}

// =============================================
// システム初期化
// =============================================

// システムの初期化
JGrantsImporter::get_instance();

// ログ出力
giji_log('Grant Insight Jグランツ・インポーター読み込み完了 - Version: ' . GIJI_VERSION);