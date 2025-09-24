<?php
/**
 * Grant Insight Perfect - Excel Import/Export Functions
 *
 * 助成金投稿のエクセル（Excel）エクスポート・インポート機能
 * CSVベース実装で、幅広いExcelアプリケーションと互換性を持つ
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
 * 1. Excel エクスポート機能
 * =============================================================================
 */

/**
 * 助成金データをExcel形式でエクスポート
 */
function gi_export_grants_to_excel() {
    // 権限チェックなし - 誰でも使用可能

    // nonceチェック
    if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'gi_export_excel')) {
        wp_die('セキュリティチェックに失敗しました');
    }

    // エクスポート対象の取得
    $export_type = sanitize_text_field($_GET['export_type'] ?? 'all');
    $args = array(
        'post_type' => 'grant',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    // フィルター処理
    if ($export_type === 'published') {
        $args['post_status'] = 'publish';
    } elseif ($export_type === 'draft') {
        $args['post_status'] = 'draft';
    }

    $grants = get_posts($args);

    if (empty($grants)) {
        wp_redirect(admin_url('edit.php?post_type=grant&message=no_data'));
        exit;
    }

    // ファイル名生成
    $filename = 'grant_export_' . date('Y-m-d_H-i-s') . '.csv';

    // HTTPヘッダー設定
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // CSV出力開始
    $output = fopen('php://output', 'w');

    // BOM追加（Excel日本語対応）
    fputs($output, "\xEF\xBB\xBF");

    // ヘッダー行
    $headers = gi_get_excel_headers();
    fputcsv($output, $headers);

    // データ行
    foreach ($grants as $grant) {
        $row_data = gi_prepare_grant_row_data($grant);
        fputcsv($output, $row_data);
    }

    fclose($output);
    exit;
}

/**
 * エクセルヘッダー定義
 */
function gi_get_excel_headers() {
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
        '作成者',
    );
}

/**
 * 助成金データを1行分に変換
 */
function gi_prepare_grant_row_data($grant) {
    $post_id = $grant->ID;
    
    // 基本情報
    $title = get_the_title($post_id);
    $status = get_post_status($post_id);
    $author = get_the_author_meta('display_name', $grant->post_author);
    
    // カスタムフィールド（ACF対応）
    $organization = gi_safe_get_meta($post_id, 'organization', '');
    $organization_type = gi_safe_get_meta($post_id, 'organization_type', '');
    $max_amount = gi_safe_get_meta($post_id, 'max_amount', '');
    $min_amount = gi_safe_get_meta($post_id, 'min_amount', '');
    $max_amount_numeric = gi_safe_get_meta($post_id, 'max_amount_numeric', '');
    $subsidy_rate = gi_safe_get_meta($post_id, 'subsidy_rate', '');
    $amount_note = gi_safe_get_meta($post_id, 'amount_note', '');
    $deadline = gi_safe_get_meta($post_id, 'deadline', '');
    $application_start = gi_safe_get_meta($post_id, 'application_start', '');
    $deadline_date = gi_safe_get_meta($post_id, 'deadline_date', '');
    $deadline_note = gi_safe_get_meta($post_id, 'deadline_note', '');
    $application_status = gi_safe_get_meta($post_id, 'application_status', '');
    $target_municipality = gi_safe_get_meta($post_id, 'target_municipality', '');
    $regional_limitation = gi_safe_get_meta($post_id, 'regional_limitation', '');
    $regional_note = gi_safe_get_meta($post_id, 'regional_note', '');
    $grant_target = gi_safe_get_meta($post_id, 'grant_target', '');
    $eligible_expenses = gi_safe_get_meta($post_id, 'eligible_expenses', '');
    $grant_difficulty = gi_safe_get_meta($post_id, 'grant_difficulty', '');
    $grant_success_rate = gi_safe_get_meta($post_id, 'grant_success_rate', '');
    $target_requirements = gi_safe_get_meta($post_id, 'target_requirements', '');
    $application_steps = gi_safe_get_meta($post_id, 'application_steps', '');
    $application_method = gi_safe_get_meta($post_id, 'application_method', '');
    $required_documents = gi_safe_get_meta($post_id, 'required_documents', '');
    $contact_info = gi_safe_get_meta($post_id, 'contact_info', '');
    $official_url = gi_safe_get_meta($post_id, 'official_url', '');
    $summary = gi_safe_get_meta($post_id, 'summary', '');
    $is_featured = gi_safe_get_meta($post_id, 'is_featured', '');;
    
    // タクソノミー
    $prefecture_terms = get_the_terms($post_id, 'grant_prefecture');
    $prefecture = '';
    if ($prefecture_terms && !is_wp_error($prefecture_terms)) {
        $prefecture_names = array();
        foreach ($prefecture_terms as $term) {
            $prefecture_names[] = $term->name;
        }
        $prefecture = implode('、', $prefecture_names);
    }
    
    $municipality_terms = get_the_terms($post_id, 'grant_municipality');
    $municipality = '';
    if ($municipality_terms && !is_wp_error($municipality_terms)) {
        $municipality_names = array();
        foreach ($municipality_terms as $term) {
            $municipality_names[] = $term->name;
        }
        $municipality = implode('、', $municipality_names);
    }
    
    $category_terms = get_the_terms($post_id, 'grant_category');
    $category = '';
    if ($category_terms && !is_wp_error($category_terms)) {
        $category_names = array();
        foreach ($category_terms as $term) {
            $category_names[] = $term->name;
        }
        $category = implode('、', $category_names);
    }
    
    // 標準タグ対応
    $tag_terms = get_the_terms($post_id, 'post_tag');
    $tags = '';
    if ($tag_terms && !is_wp_error($tag_terms)) {
        $tag_names = array();
        foreach ($tag_terms as $term) {
            $tag_names[] = $term->name;
        }
        $tags = implode('、', $tag_names);
    }
    
    // 日付フォーマット
    $created_date = get_post_time('Y-m-d H:i:s', false, $post_id);
    $modified_date = get_post_modified_time('Y-m-d H:i:s', false, $post_id);
    
    // 期限日フォーマット
    if ($deadline) {
        $deadline = date('Y-m-d', strtotime($deadline));
    }
    if ($application_start) {
        $application_start = date('Y-m-d', strtotime($application_start));
    }
    
    // 本文（改行を除去）
    $content = wp_strip_all_tags($grant->post_content);
    $content = str_replace(array("\r\n", "\n", "\r"), ' ', $content);
    
    // 長いテキストフィールドの改行を除去
    $target_requirements = str_replace(array("\r\n", "\n", "\r"), ' ', $target_requirements);
    $application_steps = str_replace(array("\r\n", "\n", "\r"), ' ', $application_steps);
    $required_documents = str_replace(array("\r\n", "\n", "\r"), ' ', $required_documents);
    $summary = str_replace(array("\r\n", "\n", "\r"), ' ', $summary);
    $target_municipality = str_replace(array("\r\n", "\n", "\r"), ' ', $target_municipality);
    $regional_note = str_replace(array("\r\n", "\n", "\r"), ' ', $regional_note);
    
    return array(
        $post_id,
        $title,
        $status,
        $organization,
        $organization_type,
        $max_amount,
        $min_amount,
        $max_amount_numeric,
        $subsidy_rate,
        $amount_note,
        $deadline,
        $application_start,
        $deadline_date,
        $deadline_note,
        $application_status,
        $prefecture,
        $target_municipality,
        $regional_limitation,
        $regional_note,
        $category,
        $tags,
        $grant_target,
        $eligible_expenses,
        $grant_difficulty,
        $grant_success_rate,
        $target_requirements,
        $application_steps,
        $application_method,
        $required_documents,
        $contact_info,
        $official_url,
        $summary,
        $content,
        $is_featured,
        $created_date,
        $modified_date,
        $author,
    );
}

/**
 * =============================================================================
 * 2. Excel インポート機能
 * =============================================================================
 */

/**
 * Excel/CSVファイルから助成金データをインポート
 */
function gi_import_grants_from_excel() {
    // 権限チェックなし - 誰でも使用可能

    // nonceチェック
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'gi_import_excel')) {
        wp_die('セキュリティチェックに失敗しました');
    }

    // ファイルアップロードチェック
    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        wp_redirect(admin_url('edit.php?post_type=grant&message=upload_error'));
        exit;
    }

    $file = $_FILES['import_file'];
    $allowed_types = array('text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel');
    
    if (!in_array($file['type'], $allowed_types)) {
        wp_redirect(admin_url('edit.php?post_type=grant&message=invalid_file_type'));
        exit;
    }

    // ファイル読み込み
    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
        wp_redirect(admin_url('edit.php?post_type=grant&message=file_read_error'));
        exit;
    }

    $import_results = array(
        'success' => 0,
        'errors' => 0,
        'warnings' => array()
    );

    $line_number = 0;
    $headers = array();
    
    while (($data = fgetcsv($handle, 0, ',')) !== false) {
        $line_number++;
        
        // ヘッダー行をスキップ
        if ($line_number === 1) {
            $headers = $data;
            continue;
        }
        
        // データ行処理
        $result = gi_process_import_row($data, $headers, $line_number);
        
        if ($result['success']) {
            $import_results['success']++;
        } else {
            $import_results['errors']++;
            $import_results['warnings'][] = "行 {$line_number}: " . $result['message'];
        }
    }
    
    fclose($handle);

    // インポート結果をセッションに保存
    set_transient('gi_import_results', $import_results, 300);
    
    wp_redirect(admin_url('edit.php?post_type=grant&message=import_complete'));
    exit;
}

/**
 * インポート行処理
 */
function gi_process_import_row($data, $headers, $line_number) {
    try {
        // データの検証
        if (empty($data) || count($data) < 2) {
            return array('success' => false, 'message' => '不正なデータ形式');
        }
        
        // コメント行をスキップ
        if (!empty($data[0]) && strpos($data[0], '※') === 0) {
            return array('success' => false, 'message' => 'コメント行をスキップ');
        }
        
        // ヘッダーとデータの対応
        $row_data = array();
        for ($i = 0; $i < count($headers) && $i < count($data); $i++) {
            $row_data[$headers[$i]] = $data[$i];
        }
        
        // 必須項目チェック
        if (empty($row_data['タイトル'])) {
            return array('success' => false, 'message' => 'タイトルが必要です');
        }
        
        // 投稿ステータスの検証
        $status = sanitize_text_field($row_data['ステータス'] ?? 'draft');
        $valid_statuses = array('publish', 'draft', 'private', 'future');
        if (!in_array($status, $valid_statuses)) {
            $status = 'draft';
        }
        
        // 投稿データ準備
        $post_data = array(
            'post_title' => sanitize_text_field($row_data['タイトル']),
            'post_content' => wp_kses_post($row_data['本文'] ?? ''),
            'post_status' => $status,
            'post_type' => 'grant',
            'post_author' => get_current_user_id()
        );
        
        // 既存投稿のチェック（IDが指定されている場合）
        $post_id = 0;
        if (!empty($row_data['ID']) && is_numeric($row_data['ID'])) {
            $existing_post = get_post($row_data['ID']);
            if ($existing_post && $existing_post->post_type === 'grant') {
                $post_data['ID'] = $row_data['ID'];
                $post_id = wp_update_post($post_data);
            }
        }
        
        // 新規投稿作成
        if ($post_id === 0) {
            $post_id = wp_insert_post($post_data);
        }
        
        if (is_wp_error($post_id)) {
            return array('success' => false, 'message' => $post_id->get_error_message());
        }
        
        // カスタムフィールドの更新
        gi_update_import_custom_fields($post_id, $row_data);
        
        // タクソノミーの更新
        gi_update_import_taxonomies($post_id, $row_data);
        
        return array('success' => true, 'message' => '');
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => 'エラー: ' . $e->getMessage());
    }
}

/**
 * インポート時のカスタムフィールド更新
 */
function gi_update_import_custom_fields($post_id, $row_data) {
    $field_mappings = array(
        'organization' => '実施組織',
        'organization_type' => '組織タイプ',
        'max_amount' => '最大金額（万円）',
        'min_amount' => '最小金額（万円）',
        'max_amount_numeric' => '最大助成額（数値・円単位）',
        'subsidy_rate' => '補助率（%）',
        'amount_note' => '金額備考',
        'deadline' => '申請期限',
        'application_start' => '募集開始日',
        'deadline_date' => '締切日',
        'deadline_note' => '締切に関する備考',
        'application_status' => '申請ステータス',
        'target_municipality' => '対象市町村',
        'regional_limitation' => '地域制限',
        'regional_note' => '地域に関する備考',
        'grant_target' => '助成金対象',
        'eligible_expenses' => '対象経費',
        'grant_difficulty' => '難易度',
        'grant_success_rate' => '成功率（%）',
        'target_requirements' => '対象者・応募要件',
        'application_steps' => '申請手順',
        'application_method' => '申請方法',
        'required_documents' => '必要書類',
        'contact_info' => '連絡先情報',
        'official_url' => '公式URL',
        'summary' => '概要',
        'is_featured' => '注目の助成金'
    );
    
    // 選択項目の有効値定義
    $select_field_values = array(
        'organization_type' => array('national', 'prefecture', 'city', 'public_org', 'private_org', 'other'),
        'application_status' => array('open', 'upcoming', 'closed', 'suspended'),
        'grant_difficulty' => array('easy', 'normal', 'hard', 'expert'),
        'application_method' => array('online', 'mail', 'visit', 'mixed'),
        'regional_limitation' => array('nationwide', 'prefecture_only', 'municipality_only', 'region_group', 'specific_area')
    );
    
    foreach ($field_mappings as $field_key => $excel_header) {
        if (isset($row_data[$excel_header]) && $row_data[$excel_header] !== '') {
            $value = sanitize_text_field($row_data[$excel_header]);
            

            
            // 選択項目のバリデーション
            if (isset($select_field_values[$field_key])) {
                if (!in_array($value, $select_field_values[$field_key])) {
                    // 無効な値の場合はデフォルト値を設定
                    $defaults = array(
                        'organization_type' => 'other',
                        'application_status' => 'open',
                        'grant_difficulty' => 'normal',
                        'application_method' => 'online',
                        'regional_limitation' => 'nationwide'
                    );
                    $value = $defaults[$field_key] ?? '';
                }
            }
            
            // 数値フィールドの処理
            if (in_array($field_key, array('max_amount', 'min_amount', 'max_amount_numeric', 'subsidy_rate', 'grant_success_rate'))) {
                $value = preg_replace('/[^\d.]/', '', $value); // 数字と小数点のみ残す
                if (!is_numeric($value)) {
                    $value = '';
                }
            }
            
            // 日付フィールドの処理
            if (in_array($field_key, array('deadline', 'application_start', 'deadline_date'))) {
                $value = gi_parse_import_date($value);
            }
            
            // ブール値フィールドの処理（注目の助成金）
            if ($field_key === 'is_featured') {
                $value = in_array(strtolower($value), array('1', 'true', 'yes', 'はい', '注目', '特集')) ? '1' : '0';
            }
            
            update_post_meta($post_id, $field_key, $value);
        }
    }
}

/**
 * インポート時のタクソノミー更新
 */
function gi_update_import_taxonomies($post_id, $row_data) {
    // 都道府県
    if (!empty($row_data['対象都道府県'])) {
        $prefectures = explode('、', $row_data['対象都道府県']);
        $prefecture_ids = array();
        
        foreach ($prefectures as $prefecture_name) {
            $prefecture_name = trim($prefecture_name);
            $term = get_term_by('name', $prefecture_name, 'grant_prefecture');
            
            if (!$term) {
                // 新しいタームを作成
                $new_term = wp_insert_term($prefecture_name, 'grant_prefecture');
                if (!is_wp_error($new_term)) {
                    $prefecture_ids[] = $new_term['term_id'];
                }
            } else {
                $prefecture_ids[] = $term->term_id;
            }
        }
        
        if (!empty($prefecture_ids)) {
            wp_set_post_terms($post_id, $prefecture_ids, 'grant_prefecture');
        }
    }
    
    // 市町村
    if (!empty($row_data['対象市町村'])) {
        $municipalities = explode('、', $row_data['対象市町村']);
        $municipality_ids = array();
        
        foreach ($municipalities as $municipality_name) {
            $municipality_name = trim($municipality_name);
            $term = get_term_by('name', $municipality_name, 'grant_municipality');
            
            if (!$term) {
                // 新しいタームを作成
                $new_term = wp_insert_term($municipality_name, 'grant_municipality');
                if (!is_wp_error($new_term)) {
                    $municipality_ids[] = $new_term['term_id'];
                }
            } else {
                $municipality_ids[] = $term->term_id;
            }
        }
        
        if (!empty($municipality_ids)) {
            wp_set_post_terms($post_id, $municipality_ids, 'grant_municipality');
        }
    }
    
    // カテゴリー
    if (!empty($row_data['カテゴリー'])) {
        $categories = explode('、', $row_data['カテゴリー']);
        $category_ids = array();
        
        foreach ($categories as $category_name) {
            $category_name = trim($category_name);
            $term = get_term_by('name', $category_name, 'grant_category');
            
            if (!$term) {
                // 新しいタームを作成
                $new_term = wp_insert_term($category_name, 'grant_category');
                if (!is_wp_error($new_term)) {
                    $category_ids[] = $new_term['term_id'];
                }
            } else {
                $category_ids[] = $term->term_id;
            }
        }
        
        if (!empty($category_ids)) {
            wp_set_post_terms($post_id, $category_ids, 'grant_category');
        }
    }
    
    // タグ
    if (!empty($row_data['タグ'])) {
        $tags = explode('、', $row_data['タグ']);
        $tag_ids = array();
        
        foreach ($tags as $tag_name) {
            $tag_name = trim($tag_name);
            $term = get_term_by('name', $tag_name, 'post_tag');
            
            if (!$term) {
                // 新しいタグを作成
                $new_term = wp_insert_term($tag_name, 'post_tag');
                if (!is_wp_error($new_term)) {
                    $tag_ids[] = $new_term['term_id'];
                }
            } else {
                $tag_ids[] = $term->term_id;
            }
        }
        
        if (!empty($tag_ids)) {
            wp_set_post_terms($post_id, $tag_ids, 'post_tag');
        }
    }
}

/**
 * 日付文字列をパース
 */
function gi_parse_import_date($date_string) {
    if (empty($date_string)) {
        return '';
    }
    
    // 複数の日付フォーマットに対応
    $formats = array(
        'Y-m-d',
        'Y/m/d',
        'm/d/Y',
        'd/m/Y',
        'Y年m月d日'
    );
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $date_string);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }
    
    // strtotime での解析を試行
    $timestamp = strtotime($date_string);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    return $date_string; // パースできない場合はそのまま返す
}

/**
 * =============================================================================
 * 3. AJAX エンドポイント登録
 * =============================================================================
 */

// エクスポート用AJAX
add_action('wp_ajax_gi_export_excel', 'gi_export_grants_to_excel');

// インポート用AJAX
add_action('wp_ajax_gi_import_excel', 'gi_import_grants_from_excel');

/**
 * =============================================================================
 * 4. 管理画面メッセージ処理
 * =============================================================================
 */

/**
 * 管理画面メッセージを追加
 */
add_filter('post_updated_messages', function($messages) {
    $messages['grant']['no_data'] = 'エクスポートするデータがありません。';
    $messages['grant']['upload_error'] = 'ファイルのアップロードに失敗しました。';
    $messages['grant']['invalid_file_type'] = '対応していないファイル形式です。CSVファイルをアップロードしてください。';
    $messages['grant']['file_read_error'] = 'ファイルの読み込みに失敗しました。';
    $messages['grant']['import_complete'] = 'インポートが完了しました。';
    
    return $messages;
});

/**
 * インポート結果を表示
 */
add_action('admin_notices', function() {
    if (isset($_GET['message']) && $_GET['message'] === 'import_complete') {
        $results = get_transient('gi_import_results');
        if ($results) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>インポート完了</strong></p>';
            echo '<p>成功: ' . $results['success'] . '件</p>';
            if ($results['errors'] > 0) {
                echo '<p>エラー: ' . $results['errors'] . '件</p>';
                if (!empty($results['warnings'])) {
                    echo '<ul>';
                    foreach ($results['warnings'] as $warning) {
                        echo '<li>' . esc_html($warning) . '</li>';
                    }
                    echo '</ul>';
                }
            }
            echo '</div>';
            
            delete_transient('gi_import_results');
        }
    }
});

/**
 * =============================================================================
 * 5. ヘルパー関数
 * =============================================================================
 */

/**
 * サンプルCSVファイルのダウンロード
 */
function gi_download_sample_csv() {
    // 権限チェックなし - 誰でも使用可能

    if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'gi_sample_csv')) {
        wp_die('セキュリティチェックに失敗しました');
    }

    $filename = 'grant_import_sample.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // BOM追加
    fputs($output, "\xEF\xBB\xBF");

    // ヘッダー行
    fputcsv($output, gi_get_excel_headers());
    
    // SEO最適化ガイド行
    $seo_guide_row = array(
        '=== SEO最適化ガイド ===',
        '【SEO】地域名+助成金名+年度含む（32文字以内）',
        '【SEO】publishで公開、draftで下書き保存',
        '【SEO】正式名称で信頼性向上',
        '【SEO】組織権威性：national>prefecture>city',
        '【SEO】具体的金額でユーザー関心向上',
        '【SEO】最低金額も記載で幅広いユーザー獲得',
        '【SEO】数字のみ、％記号なしで統一',
        '【SEO】条件明記でミスマッチ防止',
        '【SEO】期限明確化で緊急性訴求',
        '【SEO】開始日記載で計画性サポート',
        '【SEO】ステータス明確化でユーザビリティ向上',
        '【SEO】地域名複数でローカルSEO強化',
        '【SEO】カテゴリー複数で検索網羅性向上',
        '【SEO】タグはロングテールキーワード（5-8個推奨）',
        '【SEO】対象者明確化でターゲティング向上',
        '【SEO】経費項目詳細で検索マッチング強化',
        '【SEO】難易度表示でユーザー選別サポート',
        '【SEO】成功率数値で信頼性・魅力度向上',
        '【SEO】要件箇条書きで可読性向上',
        '【SEO】手順番号付きでユーザビリティ向上',
        '【SEO】申請方法明確でコンバージョン向上',
        '【SEO】書類リスト化で準備サポート',
        '【SEO】連絡先完備で信頼性向上',
        '【SEO】公式URL必須でE-A-T向上',
        '【SEO】概要120-150文字、キーワード自然含有',
        '【SEO】本文1500文字以上、HTML構造化必須',
        '【SEO】作成日は検索エンジンの鮮度評価に影響',
        '【SEO】更新日は継続性評価に重要',
        '【SEO】作成者情報は専門性評価に寄与'
    );
    fputcsv($output, $seo_guide_row);

    // 本文HTML構造ガイド行
    $html_guide_row = array(
        '=== 本文HTML構造ガイド ===',
        '例：「令和6年度 東京都IT導入支援助成金【最大1000万円】」',
        'publish=即座公開、draft=確認後公開推奨',
        '例：「東京都産業労働局」（略称NG、正式名称必須）',
        'prefecture推奨（地域密着性でローカルSEO効果）',
        '例：「1000」（1000万円の場合、単位なし数字のみ）',
        '例：「100」（100万円の場合、範囲設定でより多くの検索にヒット）',
        '例：「50」（50%の場合、％記号なし）',
        '例：「ただし上限1000万円まで、設備投資は補助率2/3」',
        '例：「2024-12-31」（必須、緊急性でクリック率向上）',
        '例：「2024-04-01」（計画的申請サポートでユーザー満足度向上）',
        'open推奨（募集中ステータスで検索上位表示）',
        '例：「東京都、神奈川県、埼玉県」（広域対応でリーチ拡大）',
        '例：「IT・デジタル、設備投資、人材育成」（多カテゴリで検索網羅）',
        '例：「IT導入、デジタル化、生産性向上、中小企業支援、補助金」',
        '例：「中小企業・ベンチャー企業（従業員50名以下、売上高5億円未満）」',
        '例：「ソフトウェア導入費、システム開発費、クラウド利用料」',
        'normal推奨（多くのユーザーが対象と感じる）',
        '例：「65」（65%、具体的数値で信頼性向上）',
        '例：「・東京都内本社\n・従業員50名以下\n・IT導入実績なし」',
        '例：「1.申請書準備\n2.オンライン申請\n3.審査\n4.採択通知」',
        'online推奨（利便性高い、現代的印象）',
        '例：「申請書、事業計画書、見積書、決算書類（2期分）」',
        '例：「○○局 TEL:03-1234-5678 MAIL:info@example.jp」',
        '例：「https://www.example.jp/grant/」（必須、信頼性向上）',
        '【重要】キーワード自然含有、魅力的表現、120-150文字厳守',
        '【重要】HTML構造必須：見出し、表組み、マーカー使用',
        '自動設定（作成時刻、SEOで鮮度アピール）',
        '自動設定（更新時刻、継続的メンテナンス証明）',
        '自動設定（作成者、専門性・権威性向上）'
    );
    fputcsv($output, $html_guide_row);

    // HTML/CSS具体例ガイド行
    $html_example_row = array(
        '=== 本文HTML/CSS具体例 ===',
        '良い例：【最大○万円】を含む、悪い例：曖昧な表現',
        '公開タイミング：重要情報は即座公開推奨',
        '権威性：「○○省」「○○庁」などの公的機関名',
        '信頼性：national > prefecture > city順で権威性高い',
        '具体性：「最大1000万円」「100-500万円」など範囲明記',
        'ユーザビリティ：下限額でハードル低さアピール',
        '明確性：「50」（○）「約50%」（△）「半額程度」（×）',
        '詳細性：「上限額の他に設備投資特例あり」など',
        '緊急性：期限明記でコンバージョン率向上',
        '計画性：開始日で余裕をもった申請サポート',
        '現在性：「現在募集中」で鮮度アピール',
        'ローカルSEO：「東京都、埼玉県」地域網羅',
        'カテゴリSEO：関連分野複数で検索幅拡大',
        'ロングテール：「令和6年度 東京都 中小企業」等',
        '具体性：「従業員○名以下」「売上○億円未満」',
        '網羅性：「ソフト、ハード、サービス」全方位',
        'ユーザー親和性：normal（普通）が最も応募しやすい',
        '信頼性：具体的数値「65%」で根拠あるデータ',
        'リスト形式：「・」で箇条書き、視認性向上',
        '段階表示：「1.2.3...」でプロセス明確化',
        '利便性：online推奨、現代ユーザーのニーズ',
        'チェックリスト：必要書類明確化で準備サポート',
        '完全性：TEL、MAIL、住所すべて記載',
        'E-A-T：公式URL必須、Googleの評価向上',
        'キーワード密度：2-3%程度、自然な文章',
        '構造化：h2,h3,table,ul,ol,mark使用必須',
        'SEO効果：作成日新しいほど検索上位',
        '継続性：定期更新でGoogleの信頼獲得',
        '専門性：作成者情報でE-A-T向上'
    );
    fputcsv($output, $html_example_row);

    // CSS実装例ガイド行  
    $css_example_row = array(
        '=== CSS実装例（コピペ用） ===',
        'タイトル例：地域名+制度名+金額で検索最適化',
        'ステータス例：公開状態の管理とSEO効果',
        '組織例：正式名称で信頼性とE-A-T向上',
        '組織タイプ例：権威性の序列でSEO効果',
        '金額例：数値のみでデータ統一性確保',
        '金額例：範囲設定でより多くの検索にマッチ',
        'パーセント例：統一フォーマットで可読性向上',
        '備考例：付加情報でユーザビリティ向上',
        '期限例：ISO形式(YYYY-MM-DD)でシステム統一',
        '開始例：期間明示でユーザーの計画性サポート',
        'ステータス例：現在の状況でユーザー判断材料',
        '地域例：複数地域対応でリーチ拡大',
        'カテゴリ例：多分野対応で検索網羅性向上',
        'タグ例：ロングテールキーワードでSEO強化',
        '対象例：明確な条件設定でミスマッチ防止',
        '経費例：対象範囲明確化で申請精度向上',
        '難易度例：ユーザーの自己選別サポート',
        '成功率例：データベース信頼性とモチベーション向上',
        '要件例：構造化された情報でユーザビリティ向上',
        '手順例：ステップバイステップでコンバージョン向上',
        '方法例：利便性重視でユーザー体験向上',
        '書類例：準備リストでユーザーサポート強化',
        '連絡先例：多チャネルでアクセシビリティ向上',
        'URL例：公式リンクでE-A-T評価向上',
        '概要例：要約文でユーザーの理解促進',
        '本文例：HTML構造化でSEO効果とUX向上',
        '日時例：システム自動設定で正確性確保',
        '日時例：更新履歴で継続性アピール',
        '作成者例：専門性と責任の所在明確化'
    );
    fputcsv($output, $css_example_row);

    // 空行（区切り）
    fputcsv($output, array(''));

    // 説明行（コメント）
    $comment_row = array(
        '※更新時のみ記入',
        '※32文字以内推奨',
        'publish/draft/private',
        '※実施機関の正式名称',
        'national/prefecture/city/public_org/private_org/other',
        '※万円単位（数字のみ）',
        '※万円単位（数字のみ）',
        '※数字のみ（%記号なし）',
        '※上限・条件など',
        '※YYYY-MM-DD形式',
        '※YYYY-MM-DD形式',
        'open/upcoming/closed/suspended',
        '※複数は全角カンマ区切り',
        '※複数は全角カンマ区切り',
        '※複数は全角カンマ区切り',
        '※対象となる企業・個人',
        '※対象となる経費項目',
        'easy/normal/hard/expert',
        '※数字のみ（%記号なし）',
        '※箇条書き推奨',
        '※ステップ形式推奨',
        'online/mail/visit/mixed',
        '※必要な提出書類',
        '※連絡先・電話・メール',
        '※公式サイトURL',
        '※100-200文字推奨',
        '※詳細な説明文',
        '※自動設定',
        '※自動設定',
        '※自動設定'
    );
    fputcsv($output, $comment_row);

    // サンプルデータ行（SEO最適化版）
    $sample_data = array(
        '', // ID（新規作成時は空）
        '令和6年度 東京都IT導入支援助成金【最大1000万円】',
        'publish',
        '東京都産業労働局',
        'prefecture',
        '1000',
        '100',
        '50',
        'ただし上限1000万円まで、設備投資は補助率2/3適用',
        '2024-12-31',
        '2024-04-01',
        'open',
        '東京都、神奈川県、埼玉県',
        'IT・デジタル、設備投資、人材育成',
        'IT導入、デジタル化、生産性向上、中小企業支援、補助金、東京都、令和6年度',
        '中小企業・ベンチャー企業（従業員50名以下、売上高5億円未満、東京都内本社）',
        'ソフトウェア導入費、システム開発費、クラウドサービス利用料、IT機器購入費',
        'normal',
        '65',
        '・東京都内に本社を有する中小企業
・従業員数50名以下
・直近年度売上高5億円未満
・IT導入実績が少ない企業
・デジタル化推進に意欲的な事業者',
        '1. 申請書類の準備（2週間）
2. オンライン申請システムへの登録
3. 必要書類のアップロード
4. 事前審査（1ヶ月）
5. プレゼンテーション（該当者のみ）
6. 最終審査・採択通知（2週間）',
        'online',
        '申請書、事業計画書、見積書、会社概要、決算書類（直近2期分）、IT導入計画書',
        '東京都産業労働局 助成金担当窓口
TEL: 03-1234-5678（平日9:00-17:00）
MAIL: joseikin@tokyo.lg.jp
窓口: 東京都新宿区西新宿2-8-1',
        'https://www.sangyo-rodo.metro.tokyo.lg.jp/josei/it-support/',
        '東京都内の中小企業向けIT導入支援助成金（最大1000万円）。デジタル化推進により生産性向上を図る事業者を対象に、ソフトウェア導入からシステム開発まで幅広くサポート。',
        '<div class="grant-content">
<h2>📋 助成金概要</h2>
<p>令和6年度東京都IT導入支援助成金は、<mark>最大1000万円</mark>まで支援する都内中小企業向けの助成制度です。デジタル化推進により<mark>生産性向上</mark>を図る事業者をサポートします。</p>

<h3>💰 助成金額・補助率</h3>
<table class="info-table">
<tr><th>項目</th><th>内容</th></tr>
<tr><td>助成上限額</td><td><mark>1,000万円</mark></td></tr>
<tr><td>助成下限額</td><td>100万円</td></tr>
<tr><td>補助率</td><td><mark>50%</mark></td></tr>
<tr><td>設備投資特例</td><td>補助率2/3</td></tr>
</table>

<h3>🎯 対象者・申請要件</h3>
<ul>
<li><mark>東京都内</mark>に本社を有する中小企業</li>
<li>従業員数<mark>50名以下</mark></li>
<li>直近年度売上高<mark>5億円未満</mark></li>
<li>IT導入による生産性向上を目指す事業者</li>
</ul>

<h3>💻 対象となるIT投資</h3>
<table class="info-table">
<tr><th>分野</th><th>対象項目</th></tr>
<tr><td>ソフトウェア</td><td>業務管理システム、CRM、ERP等</td></tr>
<tr><td>クラウドサービス</td><td>SaaS利用料、データ保存サービス</td></tr>
<tr><td>IT機器</td><td>サーバー、PC、タブレット等</td></tr>
<tr><td>システム開発</td><td>オリジナルシステム、Webサイト構築</td></tr>
</table>

<h3>📝 申請の流れ</h3>
<ol>
<li><mark>申請書類の準備</mark>（約2週間）</li>
<li><mark>オンライン申請</mark>システムでの提出</li>
<li>事前審査（約1ヶ月）</li>
<li>必要に応じてプレゼンテーション</li>
<li>最終審査・<mark>採択通知</mark>（約2週間）</li>
</ol>

<h3>⚠️ 重要なポイント</h3>
<p><mark>申請期限は2024年12月31日</mark>です。予算に達し次第受付終了となりますので、早めの申請をお勧めします。</p>

<p>詳しくは<a href="https://www.sangyo-rodo.metro.tokyo.lg.jp/josei/it-support/" target="_blank">公式サイト</a>をご確認ください。</p>
</div>',
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s'),
        get_current_user()->display_name ?? 'admin'
    );

    fputcsv($output, $sample_data);

    // HTML構造サンプル（追加例）
    $html_structure_sample = array(
        '', // ID（新規作成時は空）  
        '令和6年度 神奈川県中小企業DX推進助成金【最大500万円】',
        'draft', // 下書き例
        '神奈川県産業労働局',
        'prefecture',
        '500',
        '50',
        '75',
        'DX関連投資は補助率3/4、一般IT投資は1/2',
        '2024-11-30',
        '2024-06-01',
        'open',
        '神奈川県、東京都（一部地域）',
        'DX・AI、IT・デジタル、人材育成',
        'DX推進、AI導入、デジタル変革、中小企業、神奈川県、補助金、令和6年度',
        '神奈川県内の中小企業（製造業、サービス業優先）、従業員100名以下',
        'AI・IoT導入費、システム開発費、DXコンサルティング費、人材研修費',
        'hard',
        '45',
        '・神奈川県内に本社または主要事業所を有する
・中小企業基本法に定める中小企業
・従業員数100名以下
・DX推進に関する明確なビジョンを有する
・導入後の効果測定に協力できる',
        '1. 事前相談（DX診断）：1-2週間
2. 申請書類作成：2-3週間
3. オンライン申請提出
4. 書類審査：3-4週間
5. 面談審査（該当者）：1週間
6. 現地調査（必要時）：1週間
7. 採択決定・通知：2週間',
        'mixed',
        '申請書、事業計画書、DX推進計画書、見積書、会社概要、決算書（3期分）、DX導入効果測定計画書',
        '神奈川県産業労働局 DX推進課
TEL: 045-210-5661（平日9:00-17:15）
FAX: 045-210-8873
MAIL: dx-support@pref.kanagawa.lg.jp
相談窓口: 横浜市中区日本大通1',
        'https://www.pref.kanagawa.jp/docs/sr4/dx-support.html',
        '神奈川県の中小企業向けDX推進助成金（最大500万円、補助率最大75%）。AI・IoT導入からシステム開発、人材育成まで総合的にサポート。製造業・サービス業を重点支援。',
        '<div class="grant-content">
<h2>🚀 DX推進助成金の特徴</h2>
<p>神奈川県中小企業DX推進助成金は、<mark>最大500万円</mark>（補助率最大75%）でデジタル変革を支援する制度です。<mark>AI・IoT導入</mark>から人材育成まで包括的にサポートします。</p>

<h3>💡 重点支援分野</h3>
<table class="info-table">
<tr><th>分野</th><th>対象技術</th><th>補助率</th></tr>
<tr><td><mark>AI・機械学習</mark></td><td>画像認識、予測分析、チャットボット</td><td>75%</td></tr>
<tr><td><mark>IoT・センサー</mark></td><td>生産管理、在庫管理、品質管理</td><td>75%</td></tr>
<tr><td>システム開発</td><td>基幹システム、業務アプリ</td><td>50%</td></tr>
<tr><td>人材育成</td><td>DX研修、IT教育</td><td>75%</td></tr>
</table>

<h3>🎯 対象企業（優先採択条件）</h3>
<ul>
<li><mark>製造業</mark>・<mark>サービス業</mark>（重点分野）</li>
<li>従業員数<mark>100名以下</mark>の中小企業</li>
<li>神奈川県内に<mark>本社または主要事業所</mark></li>
<li><mark>DX推進計画</mark>を有する事業者</li>
</ul>

<h3>📋 申請プロセス（詳細版）</h3>
<div class="process-flow">
<div class="step">
<h4>Step 1: 事前準備</h4>
<p><mark>DX診断</mark>を受診し、現状分析と改善計画を策定</p>
</div>
<div class="step">
<h4>Step 2: 申請書作成</h4>
<p>専門コンサルタントと連携し、<mark>効果測定計画</mark>を含む申請書を作成</p>
</div>
<div class="step">
<h4>Step 3: 審査</h4>
<p>書類審査→面談審査→現地調査の<mark>3段階審査</mark></p>
</div>
</div>

<h3>💰 助成金額の詳細</h3>
<table class="info-table">
<tr><th>投資内容</th><th>助成上限</th><th>補助率</th><th>備考</th></tr>
<tr><td>AI・IoT導入</td><td><mark>500万円</mark></td><td>75%</td><td>重点分野</td></tr>
<tr><td>システム開発</td><td>300万円</td><td>50%</td><td>一般分野</td></tr>
<tr><td>人材研修</td><td>100万円</td><td>75%</td><td>DX人材育成</td></tr>
<tr><td>コンサルティング</td><td>150万円</td><td>50%</td><td>専門家支援</td></tr>
</table>

<h3>⚠️ 申請時の注意点</h3>
<div class="alert-box">
<p><mark>申請期限：2024年11月30日</mark></p>
<p>予算上限に達し次第受付終了。昨年度は<mark>申請開始から3ヶ月で終了</mark>したため、早期申請を強く推奨します。</p>
</div>

<h3>📞 相談・問い合わせ</h3>
<p>申請前の<mark>無料相談</mark>を実施中。DX専門コンサルタントが計画策定をサポートします。</p>

<p><a href="https://www.pref.kanagawa.jp/docs/sr4/dx-support.html" target="_blank" class="official-link">📄 公式サイトで詳細確認</a></p>
</div>

<style>
.grant-content { background: #fff; color: #000; line-height: 1.8; padding: 20px; }
.info-table { border-collapse: collapse; width: 100%; margin: 15px 0; }
.info-table th, .info-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
.info-table th { background: #f5f5f5; font-weight: bold; }
.info-table tr:nth-child(even) { background: #f9f9f9; }
mark { background: #ffeb3b; color: #000; padding: 2px 4px; font-weight: bold; }
.process-flow { display: flex; gap: 15px; margin: 20px 0; flex-wrap: wrap; }
.step { background: #f8f9fa; padding: 15px; border-radius: 8px; flex: 1; min-width: 200px; }
.alert-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
.official-link { display: inline-block; background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
</style>',
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s'),
        get_current_user()->display_name ?? 'admin'
    );
    
    fputcsv($output, $html_structure_sample);

    fclose($output);
    exit;
}

// サンプルCSVダウンロード用AJAX
add_action('wp_ajax_gi_sample_csv', 'gi_download_sample_csv');

/**
 * =============================================================================
 * 6. AI機能統合
 * =============================================================================
 */

/**
 * AI一括処理用AJAX
 */
function gi_bulk_ai_process() {
    // 権限チェックなし - 誰でも使用可能
    
    // nonceチェック
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gi_ai_bulk_nonce')) {
        wp_send_json_error('セキュリティチェックに失敗しました');
    }
    
    $type = sanitize_text_field($_POST['type'] ?? 'summary');
    $fields = array_map('sanitize_text_field', $_POST['fields'] ?? array());
    
    if (empty($fields)) {
        wp_send_json_error('処理対象フィールドが選択されていません');
    }
    
    // OpenAI API キーの確認
    $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : get_option('gi_openai_api_key', '');
    if (empty($api_key)) {
        wp_send_json_error('OpenAI API キーが設定されていません');
    }
    
    // 助成金投稿を取得
    $grants = get_posts(array(
        'post_type' => 'grant',
        'post_status' => 'any',
        'posts_per_page' => 50, // 一度に処理する件数を制限
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    if (empty($grants)) {
        wp_send_json_error('処理対象の投稿が見つかりません');
    }
    
    $processed = 0;
    $errors = array();
    
    foreach ($grants as $grant) {
        try {
            $result = gi_process_single_post_ai($grant->ID, $type, $fields, $api_key);
            if ($result) {
                $processed++;
            }
        } catch (Exception $e) {
            $errors[] = "投稿ID {$grant->ID}: " . $e->getMessage();
        }
        
        // API制限対応（1秒待機）
        sleep(1);
    }
    
    wp_send_json_success(array(
        'processed' => $processed,
        'total' => count($grants),
        'errors' => $errors,
        'type' => $type,
        'fields' => $fields
    ));
}

/**
 * 個別投稿のAI処理
 */
function gi_process_single_post_ai($post_id, $type, $fields, $api_key) {
    $post = get_post($post_id);
    if (!$post) {
        return false;
    }
    
    $updated = false;
    
    foreach ($fields as $field) {
        try {
            $current_content = '';
            $new_content = '';
            
            // 現在の内容を取得
            if ($field === 'content') {
                $current_content = $post->post_content;
            } elseif ($field === 'summary') {
                $current_content = get_post_meta($post_id, 'summary', true);
            } else {
                $current_content = get_post_meta($post_id, $field, true);
            }
            
            // AI処理を実行
            if ($type === 'summary') {
                $new_content = gi_generate_ai_summary($post, $field, $api_key);
            } elseif ($type === 'improve') {
                $new_content = gi_improve_content_with_ai($current_content, $post, $field, $api_key);
            }
            
            if (!empty($new_content) && $new_content !== $current_content) {
                // 内容を更新
                if ($field === 'content') {
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_content' => $new_content
                    ));
                } else {
                    update_post_meta($post_id, $field, $new_content);
                }
                $updated = true;
            }
            
        } catch (Exception $e) {
            error_log("AI処理エラー (投稿ID: {$post_id}, フィールド: {$field}): " . $e->getMessage());
        }
    }
    
    return $updated;
}

/**
 * AI要約生成（SEO最適化版）
 */
function gi_generate_ai_summary($post, $field, $api_key) {
    // 全ての利用可能なメタデータを取得
    $meta_data = gi_get_comprehensive_post_data($post->ID);
    
    $prompt = "以下の詳細な助成金情報から、SEO最適化された{$field}フィールド用のコンテンツを日本語で生成してください：\n\n";
    
    // 基本情報
    $prompt .= "【基本情報】\n";
    $prompt .= "助成金名: {$meta_data['title']}\n";
    $prompt .= "実施組織: {$meta_data['organization']}\n";
    $prompt .= "組織タイプ: {$meta_data['organization_type']}\n";
    
    // 金額情報
    $prompt .= "\n【金額情報】\n";
    $prompt .= "最大金額: {$meta_data['max_amount']}万円\n";
    $prompt .= "最小金額: {$meta_data['min_amount']}万円\n";
    $prompt .= "補助率: {$meta_data['subsidy_rate']}%\n";
    $prompt .= "金額備考: {$meta_data['amount_note']}\n";
    
    // 期間情報
    $prompt .= "\n【期間情報】\n";
    $prompt .= "申請期限: {$meta_data['application_deadline']}\n";
    $prompt .= "募集開始日: {$meta_data['recruitment_start']}\n";
    $prompt .= "締切日: {$meta_data['deadline']}\n";
    
    // 対象情報
    $prompt .= "\n【対象情報】\n";
    $prompt .= "対象都道府県: {$meta_data['prefectures']}\n";
    $prompt .= "対象市町村: {$meta_data['municipalities']}\n";
    $prompt .= "地域制限: {$meta_data['regional_limitation']}\n";
    $prompt .= "地域備考: {$meta_data['regional_note']}\n";
    $prompt .= "カテゴリー: {$meta_data['categories']}\n";
    $prompt .= "助成金対象: {$meta_data['grant_target']}\n";
    $prompt .= "対象経費: {$meta_data['target_expenses']}\n";
    $prompt .= "難易度: {$meta_data['difficulty']}\n";
    $prompt .= "成功率: {$meta_data['success_rate']}%\n";
    
    $prompt .= "\n【生成要件】\n";
    
    if ($field === 'summary') {
        $prompt .= "- 120-180文字の魅力的で詳細な概要\n";
        $prompt .= "- 検索されやすいキーワードを含める（地域名、助成金、対象者など）\n";
        $prompt .= "- 金額、対象者、申請期限を必ず含める\n";
        $prompt .= "- ユーザーの関心を引く表現と緊急性を表現\n";
        $prompt .= "- SEO効果を高める自然なキーワード配置\n";
    } elseif ($field === 'target_requirements') {
        $prompt .= "- 対象者・応募要件をHTML箇条書き（<ul><li>）で記載\n";
        $prompt .= "- 具体的な条件（従業員数、売上高、地域など）を含める\n";
        $prompt .= "- 除外条件も明記\n";
        $prompt .= "- 検索キーワード「中小企業」「個人事業主」などを自然に含める\n";
        $prompt .= "- 重要な要件は<strong>タグで強調\n";
    } elseif ($field === 'application_steps') {
        $prompt .= "- 申請手順をHTML番号付きリスト（<ol><li>）で記載\n";
        $prompt .= "- 各ステップに具体的な期間や必要時間を含める\n";
        $prompt .= "- 必要書類や注意点を各ステップに含める\n";
        $prompt .= "- 「申請方法」「手続き」などの検索キーワードを含める\n";
        $prompt .= "- 重要なポイントは<span class=\"highlight-yellow\">でハイライト\n";
    } elseif ($field === 'content') {
        $prompt .= "- 2000文字以上の詳細な本文をHTML+CSS形式で生成\n";
        $prompt .= "- 以下のCSS付きHTML構造を使用：\n";
        $prompt .= "  * CSSスタイル定義を<style>タグで含める\n";
        $prompt .= "  * 白黒ベース（#000, #333, #666, #ccc, #f9f9f9）+ 黄色ハイライト（#ffeb3b）\n";
        $prompt .= "  * セクション見出し（h2）: 📋概要、💰助成内容、✅対象者、📅申請手順、📝必要書類、⚠️注意事項、📞連絡先\n";
        $prompt .= "  * 重要部分は<span class=\"highlight-yellow\">で黄色ハイライト\n";
        $prompt .= "  * 表組み（<table class=\"grant-table\">）で詳細情報を整理\n";
        $prompt .= "  * 箇条書き（<ul class=\"grant-list\">）で要件や手順を明記\n";
        $prompt .= "  * スタイリッシュで読みやすいビジネス文書デザイン\n";
        $prompt .= "- SEO効果を高める関連キーワードを自然に含める\n";
        $prompt .= "- 実用的で具体的な情報を提供\n";
    }
    
    $prompt .= "\n生成内容のみを出力してください（説明文は不要）:";
    
    return gi_call_openai_api($prompt, $api_key);
}

/**
 * 投稿の包括的なデータを取得
 */
function gi_get_comprehensive_post_data($post_id) {
    $post = get_post($post_id);
    $data = array(
        'title' => $post->post_title,
        'content' => wp_strip_all_tags($post->post_content),
        'organization' => get_post_meta($post_id, 'organization', true),
        'organization_type' => get_post_meta($post_id, 'organization_type', true),
        'max_amount' => get_post_meta($post_id, 'max_amount', true),
        'min_amount' => get_post_meta($post_id, 'min_amount', true),
        'max_amount_yen' => get_post_meta($post_id, 'max_amount_yen', true),
        'subsidy_rate' => get_post_meta($post_id, 'subsidy_rate', true),
        'amount_note' => get_post_meta($post_id, 'amount_note', true),
        'application_deadline' => get_post_meta($post_id, 'application_deadline', true),
        'recruitment_start' => get_post_meta($post_id, 'recruitment_start', true),
        'deadline' => get_post_meta($post_id, 'deadline', true),
        'deadline_note' => get_post_meta($post_id, 'deadline_note', true),
        'application_status' => get_post_meta($post_id, 'application_status', true),
        'target_municipality' => get_post_meta($post_id, 'target_municipality', true),
        'regional_limitation' => get_post_meta($post_id, 'regional_limitation', true),
        'regional_note' => get_post_meta($post_id, 'regional_note', true),
        'grant_target' => get_post_meta($post_id, 'grant_target', true),
        'target_expenses' => get_post_meta($post_id, 'target_expenses', true),
        'difficulty' => get_post_meta($post_id, 'difficulty', true),
        'success_rate' => get_post_meta($post_id, 'success_rate', true),
        'eligibility_criteria' => get_post_meta($post_id, 'eligibility_criteria', true),
        'application_process' => get_post_meta($post_id, 'application_process', true),
        'application_method' => get_post_meta($post_id, 'application_method', true),
        'required_documents' => get_post_meta($post_id, 'required_documents', true),
        'contact_info' => get_post_meta($post_id, 'contact_info', true),
        'official_url' => get_post_meta($post_id, 'official_url', true),
        'summary' => get_post_meta($post_id, 'summary', true)
    );
    
    // タクソノミー情報を取得
    $prefecture_terms = get_the_terms($post_id, 'grant_prefecture');
    $data['prefectures'] = '';
    if ($prefecture_terms && !is_wp_error($prefecture_terms)) {
        $prefecture_names = wp_list_pluck($prefecture_terms, 'name');
        $data['prefectures'] = implode('、', $prefecture_names);
    }
    
    $municipality_terms = get_the_terms($post_id, 'grant_municipality');
    $data['municipalities'] = '';
    if ($municipality_terms && !is_wp_error($municipality_terms)) {
        $municipality_names = wp_list_pluck($municipality_terms, 'name');
        $data['municipalities'] = implode('、', $municipality_names);
    }
    
    $category_terms = get_the_terms($post_id, 'grant_category');
    $data['categories'] = '';
    if ($category_terms && !is_wp_error($category_terms)) {
        $category_names = wp_list_pluck($category_terms, 'name');
        $data['categories'] = implode('、', $category_names);
    }
    
    $tag_terms = get_the_terms($post_id, 'grant_tag');
    $data['tags'] = '';
    if ($tag_terms && !is_wp_error($tag_terms)) {
        $tag_names = wp_list_pluck($tag_terms, 'name');
        $data['tags'] = implode('、', $tag_names);
    }
    
    return $data;
}

/**
 * AI内容改善
 */
function gi_improve_content_with_ai($content, $post, $field, $api_key) {
    if (empty($content)) {
        return gi_generate_ai_summary($post, $field, $api_key);
    }
    
    $prompt = "以下の助成金の{$field}フィールドの内容を改善してください。より分かりやすく、魅力的で実用的な内容にしてください：\n\n";
    $prompt .= "現在の内容: {$content}\n\n";
    $prompt .= "改善要求: より具体的で分かりやすく、読みやすい日本語に改善してください。";
    
    return gi_call_openai_api($prompt, $api_key);
}

/**
 * OpenAI API呼び出し
 */
function gi_call_openai_api($prompt, $api_key) {
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'timeout' => 60, // タイムアウトを60秒に延長
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-3.5-turbo', // より高品質な応答のため
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'あなたは助成金情報の専門家兼Webデザイナーです。正確で分かりやすく実用的な日本語コンテンツを、HTML/CSSを使用してスタイリッシュに生成してください。白黒ベースのデザインに黄色のハイライト効果を使用し、ビジネス文書として完成度の高い内容を作成してください。'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 3000, // より長いコンテンツ生成のために増量
            'temperature' => 0.7
        ))
    ));
    
    if (is_wp_error($response)) {
        throw new Exception('API呼び出しエラー: ' . $response->get_error_message());
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!isset($data['choices'][0]['message']['content'])) {
        $error_message = 'AI応答の解析に失敗しました';
        if (isset($data['error']['message'])) {
            $error_message .= ': ' . $data['error']['message'];
        }
        throw new Exception($error_message);
    }
    
    return trim($data['choices'][0]['message']['content']);
}

// AI処理用AJAX
add_action('wp_ajax_gi_bulk_ai_process', 'gi_bulk_ai_process');

?>