<?php
/**
 * Grant Insight Perfect - Google Sheets Integration
 *
 * Google スプレッドシートと助成金投稿の双方向同期機能
 * リアルタイム連携によるデータ管理の効率化
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * =============================================================================
 * 1. Google Sheets API クライアント
 * =============================================================================
 */

class GI_Google_Sheets_Integration {
    
    private $service_account_key;
    private $spreadsheet_id;
    private $sheet_name;
    private $access_token;
    private $token_expires;
    
    public function __construct() {
        $this->service_account_key = get_option('gi_google_service_account_key', '');
        $this->spreadsheet_id = get_option('gi_google_spreadsheet_id', '');
        $this->sheet_name = get_option('gi_google_sheet_name', 'Sheet1');
        
        // AJAX ハンドラーの登録
        add_action('wp_ajax_gi_test_sheets_connection', array($this, 'test_connection'));
        add_action('wp_ajax_gi_sync_from_sheets', array($this, 'sync_from_sheets'));
        add_action('wp_ajax_gi_sync_to_sheets', array($this, 'sync_to_sheets'));
        add_action('wp_ajax_gi_setup_sheet_headers', array($this, 'setup_sheet_headers'));
        
        // 定期同期用のフック
        add_action('gi_sheets_sync_cron', array($this, 'cron_sync_from_sheets'));
        
        // 投稿保存時の同期
        add_action('save_post', array($this, 'on_post_save'), 10, 2);
    }
    
    /**
     * Google Sheets API アクセストークンを取得
     */
    private function get_access_token() {
        // キャッシュされたトークンをチェック
        if ($this->access_token && $this->token_expires > time()) {
            return $this->access_token;
        }
        
        if (empty($this->service_account_key)) {
            throw new Exception('Google サービスアカウントキーが設定されていません。');
        }
        
        $key_data = json_decode($this->service_account_key, true);
        if (!$key_data) {
            throw new Exception('サービスアカウントキーの形式が正しくありません。');
        }
        
        // JWT トークンを生成
        $jwt_token = $this->create_jwt_token($key_data);
        
        // アクセストークンをリクエスト
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt_token
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Google API への接続に失敗しました: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            throw new Exception('Google API エラー: ' . $data['error_description']);
        }
        
        if (!isset($data['access_token'])) {
            throw new Exception('アクセストークンの取得に失敗しました。');
        }
        
        $this->access_token = $data['access_token'];
        $this->token_expires = time() + ($data['expires_in'] - 60); // 60秒の余裕
        
        return $this->access_token;
    }
    
    /**
     * JWT トークンを作成
     */
    private function create_jwt_token($key_data) {
        $header = json_encode(array(
            'alg' => 'RS256',
            'typ' => 'JWT'
        ));
        
        $now = time();
        $payload = json_encode(array(
            'iss' => $key_data['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ));
        
        $base64_header = $this->base64url_encode($header);
        $base64_payload = $this->base64url_encode($payload);
        
        $signature_input = $base64_header . '.' . $base64_payload;
        
        // RSA-SHA256 で署名
        $private_key = openssl_pkey_get_private($key_data['private_key']);
        if (!$private_key) {
            throw new Exception('秘密鍵の読み込みに失敗しました。');
        }
        
        openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
        $base64_signature = $this->base64url_encode($signature);
        
        return $signature_input . '.' . $base64_signature;
    }
    
    /**
     * Base64 URL エンコーディング
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * スプレッドシートからデータを取得
     */
    public function get_sheet_data($range = null) {
        $access_token = $this->get_access_token();
        
        if (!$range) {
            $range = $this->sheet_name . '!A:AZ'; // A列からAZ列まで
        }
        
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheet_id}/values/{$range}";
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('スプレッドシートへの接続に失敗しました: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            throw new Exception('スプレッドシート API エラー: ' . $data['error']['message']);
        }
        
        return $data['values'] ?? array();
    }
    
    /**
     * スプレッドシートにデータを更新
     */
    public function update_sheet_data($range, $values) {
        $access_token = $this->get_access_token();
        
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheet_id}/values/{$range}?valueInputOption=RAW";
        
        $response = wp_remote_request($url, array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'values' => $values
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('スプレッドシートの更新に失敗しました: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            throw new Exception('スプレッドシート更新エラー: ' . $data['error']['message']);
        }
        
        return $data;
    }
    
    /**
     * スプレッドシートに行を追加
     */
    public function append_sheet_data($values) {
        $access_token = $this->get_access_token();
        
        $range = $this->sheet_name . '!A:AZ';
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheet_id}/values/{$range}:append?valueInputOption=RAW&insertDataOption=INSERT_ROWS";
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'values' => array($values)
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('スプレッドシートへの追加に失敗しました: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            throw new Exception('スプレッドシート追加エラー: ' . $data['error']['message']);
        }
        
        return $data;
    }
    
    /**
     * =============================================================================
     * 2. データ同期機能
     * =============================================================================
     */
    
    /**
     * スプレッドシートから WordPress に同期
     */
    public function sync_from_sheets() {
        try {
            check_ajax_referer('gi_sheets_nonce', 'nonce');
            
            if (!current_user_can('edit_posts')) {
                wp_die('権限がありません');
            }
            
            $data = $this->get_sheet_data();
            
            if (empty($data)) {
                wp_send_json_error('スプレッドシートにデータがありません。');
                return;
            }
            
            $headers = array_shift($data); // 1行目をヘッダーとして取得
            $results = $this->process_sheet_import($headers, $data);
            
            wp_send_json_success(array(
                'message' => 'スプレッドシートからの同期が完了しました。',
                'processed' => $results['processed'],
                'updated' => $results['updated'],
                'created' => $results['created'],
                'errors' => $results['errors']
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('同期エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * WordPress からスプレッドシートに同期
     */
    public function sync_to_sheets() {
        try {
            check_ajax_referer('gi_sheets_nonce', 'nonce');
            
            if (!current_user_can('edit_posts')) {
                wp_die('権限がありません');
            }
            
            // 助成金投稿を取得
            $grants = get_posts(array(
                'post_type' => 'grant',
                'post_status' => array('publish', 'draft'),
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            
            if (empty($grants)) {
                wp_send_json_error('同期する助成金投稿がありません。');
                return;
            }
            
            $headers = $this->get_sheet_headers();
            $sheet_data = array($headers);
            
            foreach ($grants as $grant) {
                $row_data = $this->prepare_grant_for_sheets($grant);
                $sheet_data[] = $row_data;
            }
            
            // スプレッドシート全体を更新
            $range = $this->sheet_name . '!A1:AZ' . (count($sheet_data));
            $this->update_sheet_data($range, $sheet_data);
            
            wp_send_json_success(array(
                'message' => 'スプレッドシートへの同期が完了しました。',
                'exported' => count($grants)
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('同期エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * スプレッドシートのヘッダー行を設定
     */
    public function setup_sheet_headers() {
        try {
            check_ajax_referer('gi_sheets_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('権限がありません');
            }
            
            $headers = $this->get_sheet_headers();
            $this->update_sheet_data($this->sheet_name . '!1:1', array($headers));
            
            wp_send_json_success(array(
                'message' => 'スプレッドシートのヘッダーが設定されました。',
                'headers' => $headers
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('ヘッダー設定エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * 接続テスト
     */
    public function test_connection() {
        try {
            check_ajax_referer('gi_sheets_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die('権限がありません');
            }
            
            $access_token = $this->get_access_token();
            
            // スプレッドシートの基本情報を取得してテスト
            $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheet_id}";
            
            $response = wp_remote_get($url, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                throw new Exception('接続テスト失敗: ' . $response->get_error_message());
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['error'])) {
                throw new Exception('スプレッドシートアクセスエラー: ' . $data['error']['message']);
            }
            
            wp_send_json_success(array(
                'message' => 'Google スプレッドシートへの接続に成功しました。',
                'spreadsheet_title' => $data['properties']['title'] ?? '不明',
                'sheet_count' => count($data['sheets'] ?? array())
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('接続エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * =============================================================================
     * 3. データ処理機能
     * =============================================================================
     */
    
    /**
     * スプレッドシートのヘッダー定義を取得
     */
    private function get_sheet_headers() {
        return array(
            'ID',
            'タイトル',
            'ステータス',
            '実施組織',
            '組織タイプ',
            '最大金額（万円）',
            '最小金額（万円）',
            '最大助成額（数値・円単位）',
            '補助率（%）',
            '金額備考',
            '申請期限',
            '募集開始日',
            '締切日',
            '締切に関する備考',
            '申請ステータス',
            '対象都道府県',
            '対象市町村',
            '地域制限',
            '地域に関する備考',
            'カテゴリー',
            'タグ',
            '助成金対象',
            '対象経費',
            '難易度',
            '成功率（%）',
            '対象者・応募要件',
            '申請手順',
            '申請方法',
            '必要書類',
            '連絡先情報',
            '公式URL',
            '概要',
            '本文',
            '注目の助成金',
            '作成日',
            '更新日',
            '最終更新者'
        );
    }
    
    /**
     * 助成金投稿をスプレッドシート用データに変換
     */
    private function prepare_grant_for_sheets($grant) {
        $post_id = $grant->ID;
        
        // Excel エクスポート機能を再利用
        if (function_exists('gi_prepare_grant_row_data')) {
            $row_data = gi_prepare_grant_row_data($grant);
            // 最後に更新者情報を追加
            $row_data[] = get_the_author_meta('display_name', $grant->post_author);
            return $row_data;
        }
        
        // フォールバック: 基本データのみ
        return array(
            $post_id,
            get_the_title($post_id),
            get_post_status($post_id),
            get_post_meta($post_id, 'organization', true),
            get_post_meta($post_id, 'organization_type', true),
            // ... 他のフィールドも同様に取得
        );
    }
    
    /**
     * スプレッドシートデータを WordPress に取り込み
     */
    private function process_sheet_import($headers, $data) {
        $results = array(
            'processed' => 0,
            'updated' => 0,
            'created' => 0,
            'errors' => array()
        );
        
        foreach ($data as $row_index => $row) {
            $line_number = $row_index + 2; // ヘッダー行を考慮
            
            try {
                // 行データを連想配列に変換
                $row_data = array();
                foreach ($headers as $col_index => $header) {
                    $row_data[$header] = isset($row[$col_index]) ? $row[$col_index] : '';
                }
                
                // Excel インポート機能を再利用
                if (function_exists('gi_process_import_row')) {
                    $result = gi_process_import_row($row, $headers, $line_number);
                    
                    if ($result['success']) {
                        $results['processed']++;
                        // 新規作成か更新かを判定（IDの有無で）
                        if (empty($row_data['ID'])) {
                            $results['created']++;
                        } else {
                            $results['updated']++;
                        }
                    } else {
                        $results['errors'][] = "行 {$line_number}: " . $result['message'];
                    }
                } else {
                    // 基本的な処理
                    $this->process_single_row($row_data);
                    $results['processed']++;
                }
                
            } catch (Exception $e) {
                $results['errors'][] = "行 {$line_number}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * 単一行の処理（フォールバック）
     */
    private function process_single_row($row_data) {
        $post_id = !empty($row_data['ID']) ? intval($row_data['ID']) : 0;
        
        // 投稿データの準備
        $post_data = array(
            'post_type' => 'grant',
            'post_title' => sanitize_text_field($row_data['タイトル'] ?? ''),
            'post_content' => wp_kses_post($row_data['本文'] ?? ''),
            'post_status' => sanitize_text_field($row_data['ステータス'] ?? 'draft'),
            'post_excerpt' => sanitize_text_field($row_data['概要'] ?? '')
        );
        
        if ($post_id > 0) {
            // 更新
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        } else {
            // 新規作成
            $post_id = wp_insert_post($post_data);
            if (is_wp_error($post_id)) {
                throw new Exception('投稿の作成に失敗しました: ' . $post_id->get_error_message());
            }
        }
        
        // カスタムフィールドの更新
        $this->update_post_custom_fields($post_id, $row_data);
    }
    
    /**
     * カスタムフィールドの更新
     */
    private function update_post_custom_fields($post_id, $row_data) {
        $field_mappings = array(
            'organization' => '実施組織',
            'organization_type' => '組織タイプ',
            'target_municipality' => '対象市町村',
            'regional_limitation' => '地域制限',
            // ... 他のフィールドマッピング
        );
        
        foreach ($field_mappings as $meta_key => $excel_header) {
            if (isset($row_data[$excel_header]) && $row_data[$excel_header] !== '') {
                update_post_meta($post_id, $meta_key, sanitize_text_field($row_data[$excel_header]));
            }
        }
    }
    
    /**
     * =============================================================================
     * 4. 自動同期機能
     * =============================================================================
     */
    
    /**
     * 投稿保存時のスプレッドシート同期
     */
    public function on_post_save($post_id, $post) {
        // 助成金投稿以外は処理しない
        if ($post->post_type !== 'grant') {
            return;
        }
        
        // 自動保存や リビジョンは処理しない
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // 自動同期が有効でない場合は処理しない
        if (!get_option('gi_sheets_auto_sync', false)) {
            return;
        }
        
        // バックグラウンドで同期処理
        wp_schedule_single_event(time() + 10, 'gi_sheets_sync_single_post', array($post_id));
    }
    
    /**
     * 定期同期処理
     */
    public function cron_sync_from_sheets() {
        try {
            $this->log_sync_activity('定期同期開始');
            
            $data = $this->get_sheet_data();
            if (empty($data)) {
                $this->log_sync_activity('同期対象データなし');
                return;
            }
            
            $headers = array_shift($data);
            $results = $this->process_sheet_import($headers, $data);
            
            $this->log_sync_activity("定期同期完了: 処理 {$results['processed']} 件");
            
        } catch (Exception $e) {
            $this->log_sync_activity('定期同期エラー: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * 単一投稿の同期
     */
    public function sync_single_post_to_sheets($post_id) {
        try {
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'grant') {
                return;
            }
            
            // スプレッドシート内の該当行を更新
            $row_data = $this->prepare_grant_for_sheets($post);
            
            // TODO: 特定行の更新ロジックを実装
            // 現在はフルシンクのみ対応
            
        } catch (Exception $e) {
            $this->log_sync_activity("単一投稿同期エラー (ID: {$post_id}): " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * =============================================================================
     * 5. ログ・ユーティリティ機能
     * =============================================================================
     */
    
    /**
     * 同期活動のログ記録
     */
    private function log_sync_activity($message, $level = 'info') {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message
        );
        
        $log = get_option('gi_sheets_sync_log', array());
        array_unshift($log, $log_entry);
        
        // 最新100件のみ保持
        $log = array_slice($log, 0, 100);
        update_option('gi_sheets_sync_log', $log);
    }
    
    /**
     * 設定の検証
     */
    public function validate_settings() {
        $errors = array();
        
        if (empty($this->service_account_key)) {
            $errors[] = 'Google サービスアカウントキーが設定されていません。';
        }
        
        if (empty($this->spreadsheet_id)) {
            $errors[] = 'スプレッドシートIDが設定されていません。';
        }
        
        return $errors;
    }
}

/**
 * =============================================================================
 * 6. 初期化とフック登録
 * =============================================================================
 */

// クラスのインスタンス化
function gi_init_google_sheets_integration() {
    if (is_admin()) {
        new GI_Google_Sheets_Integration();
    }
}
add_action('init', 'gi_init_google_sheets_integration');

// クーロン間隔の追加
add_filter('cron_schedules', function($schedules) {
    $schedules['gi_sheets_sync_interval'] = array(
        'interval' => get_option('gi_sheets_sync_interval', 3600), // デフォルト1時間
        'display' => 'Grant Insights Sheets Sync'
    );
    return $schedules;
});

// 単一投稿同期のフック
add_action('gi_sheets_sync_single_post', function($post_id) {
    $integration = new GI_Google_Sheets_Integration();
    $integration->sync_single_post_to_sheets($post_id);
});