<?php
/**
 * Grant Card Unified - Clean & Professional Edition
 * template-parts/grant-card-unified.php
 * 
 * シンプルでスタイリッシュな統一カードテンプレート
 * 機能は完全保持、デザインをクリーンに刷新
 * 
 * @package Grant_Insight_Clean
 * @version 10.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// グローバル変数から必要データを取得
global $post, $user_favorites, $current_view, $display_mode;

$post_id = get_the_ID();
if (!$post_id) return;

// 表示モードの判定
$display_mode = $display_mode ?? (isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'card');
$view_class = 'grant-view-' . $display_mode;

// 既存のヘルパー関数を最大限活用
$grant_data = function_exists('gi_get_complete_grant_data') 
    ? gi_get_complete_grant_data($post_id)
    : gi_get_all_grant_meta($post_id);

// お気に入り情報
$user_favorites = $user_favorites ?? (function_exists('gi_get_user_favorites_safe') 
    ? gi_get_user_favorites_safe() 
    : gi_get_user_favorites());

$is_favorite = in_array($post_id, $user_favorites);

// 基本データ取得
$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
$excerpt = get_the_excerpt($post_id);

// ACFフィールド（安全に取得）
$ai_summary = gi_get_acf_field_safely($post_id, 'ai_summary', '');
$max_amount = gi_get_acf_field_safely($post_id, 'max_amount', '');
$max_amount_numeric = gi_get_acf_field_safely($post_id, 'max_amount_numeric', 0);
$application_status = gi_get_acf_field_safely($post_id, 'application_status', 'open');
$organization = gi_get_acf_field_safely($post_id, 'organization', '');
$grant_target = gi_get_acf_field_safely($post_id, 'grant_target', '');
$subsidy_rate = gi_get_acf_field_safely($post_id, 'subsidy_rate', '');
$grant_difficulty = gi_get_acf_field_safely($post_id, 'grant_difficulty', 'normal');
$grant_success_rate = gi_get_acf_field_safely($post_id, 'grant_success_rate', 0);
$official_url = gi_get_acf_field_safely($post_id, 'official_url', '');
$eligible_expenses = gi_get_acf_field_safely($post_id, 'eligible_expenses', '');
$application_method = gi_get_acf_field_safely($post_id, 'application_method', '');
$required_documents = gi_get_acf_field_safely($post_id, 'required_documents', '');
$contact_info = gi_get_acf_field_safely($post_id, 'contact_info', '');
$is_featured = gi_get_acf_field_safely($post_id, 'is_featured', false);
$priority_order = gi_get_acf_field_safely($post_id, 'priority_order', 100);
$application_period = gi_get_acf_field_safely($post_id, 'application_period', '');

// 締切日の処理
$deadline_raw = gi_get_acf_field_safely($post_id, 'deadline', '');
$deadline_timestamp = 0;
$deadline_formatted = '';

if (!empty($deadline_raw)) {
    // Ymd形式（例：20241231）の場合
    if (is_numeric($deadline_raw) && strlen($deadline_raw) == 8) {
        $year = substr($deadline_raw, 0, 4);
        $month = substr($deadline_raw, 4, 2);
        $day = substr($deadline_raw, 6, 2);
        $deadline_timestamp = mktime(0, 0, 0, $month, $day, $year);
        $deadline_formatted = sprintf('%s年%d月%d日', $year, intval($month), intval($day));
    }
    // UNIXタイムスタンプの場合
    elseif (is_numeric($deadline_raw) && $deadline_raw > 946684800) {
        $deadline_timestamp = intval($deadline_raw);
        $deadline_formatted = date('Y年n月j日', $deadline_timestamp);
    }
    // 文字列形式の日付
    else {
        $deadline_timestamp = strtotime($deadline_raw);
        if ($deadline_timestamp !== false) {
            $deadline_formatted = date('Y年n月j日', $deadline_timestamp);
        }
    }
} else {
    // deadline_dateフィールドをフォールバック
    $deadline_date_numeric = gi_get_acf_field_safely($post_id, 'deadline_date', 0);
    if ($deadline_date_numeric > 0) {
        $deadline_timestamp = intval($deadline_date_numeric);
        $deadline_formatted = date('Y年n月j日', $deadline_timestamp);
    }
}

// 締切日がない場合のデフォルト
if (empty($deadline_formatted)) {
    $deadline_formatted = function_exists('gi_get_formatted_deadline') 
        ? gi_get_formatted_deadline($post_id) : '未定';
}

// タクソノミーデータ
$categories = gi_get_post_categories($post_id, 'grant_category');
$main_category = !empty($categories) ? $categories[0]['name'] : '';

$prefectures = gi_get_post_categories($post_id, 'grant_prefecture');
$prefecture = !empty($prefectures) ? $prefectures[0]['name'] : '全国';

$industries = gi_get_post_categories($post_id, 'grant_industry');
$main_industry = !empty($industries) ? $industries[0]['name'] : '';

// 既存のフォーマッター関数を使用
$amount_display = function_exists('gi_format_amount_unified') 
    ? gi_format_amount_unified($max_amount_numeric, $max_amount)
    : gi_get_grant_amount_display($post_id);

// ステータス表示
$status_display = function_exists('gi_map_application_status_ui') 
    ? gi_map_application_status_ui($application_status)
    : gi_get_status_name($application_status);

// 締切日情報の処理
$deadline_info = array();
if ($deadline_timestamp > 0) {
    $current_timestamp = current_time('timestamp');
    $days_remaining = ceil(($deadline_timestamp - $current_timestamp) / (60 * 60 * 24));
    
    if ($days_remaining <= 0) {
        $deadline_info = array('class' => 'expired', 'text' => '募集終了', 'icon' => 'fa-times-circle');
    } elseif ($days_remaining <= 3) {
        $deadline_info = array('class' => 'critical', 'text' => '残り'.$days_remaining.'日', 'icon' => 'fa-exclamation-triangle');
    } elseif ($days_remaining <= 7) {
        $deadline_info = array('class' => 'urgent', 'text' => '残り'.$days_remaining.'日', 'icon' => 'fa-clock');
    } elseif ($days_remaining <= 30) {
        $deadline_info = array('class' => 'warning', 'text' => '残り'.$days_remaining.'日', 'icon' => 'fa-calendar-alt');
    } else {
        $deadline_info = array('class' => 'normal', 'text' => $deadline_formatted, 'icon' => 'fa-calendar');
    }
}

// 難易度表示の設定
$difficulty_config = array(
    'easy' => array('label' => '易しい', 'color' => '#16a34a', 'icon' => 'fa-smile'),
    'normal' => array('label' => '普通', 'color' => '#525252', 'icon' => 'fa-meh'),
    'hard' => array('label' => '難しい', 'color' => '#d97706', 'icon' => 'fa-frown'),
    'expert' => array('label' => '専門的', 'color' => '#dc2626', 'icon' => 'fa-dizzy')
);
$difficulty_data = $difficulty_config[$grant_difficulty] ?? $difficulty_config['normal'];

// CSS・JSの重複防止
static $assets_loaded = false;
?>

<?php if (!$assets_loaded): $assets_loaded = true; ?>

<style>
/* Clean Grant Card Design System - Full Monochrome Edition */
:root {
    /* Core Colors - Pure Monochrome System */
    --clean-primary: #000000;          /* Pure black for primary actions */
    --clean-primary-light: #262626;    /* Dark gray for light primary */
    --clean-primary-dark: #000000;     /* Pure black for dark primary */
    --clean-secondary: #525252;        /* Medium gray for secondary elements */
    --clean-accent: #171717;           /* Very dark gray for accents */
    
    /* Monochrome Base Colors */
    --clean-white: #ffffff;            /* Pure white */
    --clean-gray-50: #fafafa;          /* Lightest gray */
    --clean-gray-100: #f5f5f5;         /* Very light gray */
    --clean-gray-200: #e5e5e5;         /* Light gray */
    --clean-gray-300: #d4d4d4;         /* Medium light gray */
    --clean-gray-400: #a3a3a3;         /* Medium gray */
    --clean-gray-500: #737373;         /* Gray */
    --clean-gray-600: #525252;         /* Dark gray */
    --clean-gray-700: #404040;         /* Darker gray */
    --clean-gray-800: #262626;         /* Very dark gray */
    --clean-gray-900: #171717;         /* Almost black */
    
    /* Semantic Colors - Minimal Use */
    --clean-success: #22c55e;          /* Green (minimal use) */
    --clean-warning: #f59e0b;          /* Orange (minimal use) */
    --clean-danger: #ef4444;           /* Red (minimal use) */
    --clean-info: #000000;             /* Black for info */
    
    /* Gradients - Pure Monochrome */
    --clean-gradient-primary: linear-gradient(135deg, #000000 0%, #262626 100%);
    --clean-gradient-light: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
    --clean-gradient-secondary: linear-gradient(135deg, #f5f5f5 0%, #e5e5e5 100%);
    --clean-gradient-dark: linear-gradient(135deg, #262626 0%, #171717 100%);
    --clean-gradient-accent: linear-gradient(135deg, #171717 0%, #000000 100%);
    
    /* Monochrome Shadows */
    --clean-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --clean-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.05);
    --clean-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.05);
    --clean-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.15), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    
    /* Border Radius - Clean & Modern */
    --clean-radius-sm: 0.25rem;
    --clean-radius-md: 0.5rem;
    --clean-radius-lg: 0.75rem;
    --clean-radius-xl: 1rem;
    --clean-radius-2xl: 1.5rem;
    
    /* Transitions */
    --clean-transition: all 0.15s ease-in-out;
    --clean-transition-slow: all 0.3s ease-in-out;
    
    /* Typography Colors */
    --clean-text-primary: #171717;     /* Primary text (almost black) */
    --clean-text-secondary: #525252;   /* Secondary text */
    --clean-text-muted: #737373;       /* Muted text */
    --clean-text-light: #a3a3a3;       /* Light text */
    
    /* Border Colors */
    --clean-border-light: #e5e5e5;     /* Light border */
    --clean-border-medium: #d4d4d4;    /* Medium border */
    --clean-border-dark: #a3a3a3;      /* Dark border */
    --clean-border-primary: #000000;   /* Primary border (black) */
}

	

/* ============================================
   カード表示モード
============================================ */
.grant-view-card .grant-card-unified {
    position: relative;
    width: 100%;
    min-height: 480px;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-xl);
    overflow: hidden;
    transition: var(--clean-transition-slow);
    cursor: default;
    display: flex;
    flex-direction: column;
}

.grant-view-card .grant-card-unified:hover {
    transform: translateY(-4px);
    box-shadow: var(--clean-shadow-xl);
    border-color: var(--clean-gray-300);
}

/* ============================================
   リスト表示モード
============================================ */
.grant-view-list .grant-card-unified {
    position: relative;
    width: 100%;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-lg);
    transition: var(--clean-transition);
    cursor: default;
    display: flex;
    flex-direction: row;
    align-items: stretch;
    min-height: 140px;
    margin-bottom: 1rem;
}

.grant-view-list .grant-card-unified:hover {
    box-shadow: var(--clean-shadow-lg);
    transform: translateX(4px);
    border-color: var(--clean-gray-800);
}

.grant-view-list .grant-status-header {
    width: 6px;
    height: auto;
    padding: 0;
    writing-mode: vertical-rl;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clean-gradient-primary);
}

.grant-view-list .grant-card-content {
    flex: 1;
    padding: 1.25rem;
    display: flex;
    flex-direction: row;
    gap: 1.5rem;
}

.grant-view-list .grant-main-info {
    flex: 1;
    min-width: 0;
}

.grant-view-list .grant-title {
    font-size: 1.125rem;
    margin-bottom: 0.75rem;
    -webkit-line-clamp: 2;
}

.grant-view-list .grant-ai-summary {
    display: block;
    max-height: 3.5rem;
}

.grant-view-list .grant-info-grid {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    flex-wrap: wrap;
}

.grant-view-list .grant-info-item {
    background: transparent;
    padding: 0.5rem 0.75rem;
    gap: 0.5rem;
    border-radius: var(--clean-radius-sm);
    background: var(--clean-gray-100);
}

.grant-view-list .grant-info-icon {
    width: 1.25rem;
    height: 1.25rem;
    font-size: 0.875rem;
}

.grant-view-list .grant-card-footer {
    padding: 1.25rem;
    background: transparent;
    border: none;
    border-left: 1px solid var(--clean-gray-200);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    min-width: 12rem;
    justify-content: center;
}

/* ============================================
   コンパクト表示モード
============================================ */
.grant-view-compact .grant-card-unified {
    position: relative;
    width: 100%;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-md);
    transition: var(--clean-transition);
    cursor: default;
    padding: 1rem;
    margin-bottom: 0.75rem;
}

.grant-view-compact .grant-card-unified:hover {
    background: var(--clean-gray-50);
    box-shadow: var(--clean-shadow-md);
    border-color: var(--clean-gray-800);
}

.grant-view-compact .grant-status-header {
    display: none;
}

.grant-view-compact .grant-card-content {
    padding: 0;
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 1rem;
}

.grant-view-compact .grant-title {
    font-size: 1rem;
    margin: 0;
    -webkit-line-clamp: 1;
}

.grant-view-compact .grant-ai-summary,
.grant-view-compact .grant-info-grid,
.grant-view-compact .grant-success-rate {
    display: none;
}

.grant-view-compact .grant-card-footer {
    padding: 0;
    background: transparent;
    border: none;
    flex-direction: row;
    gap: 0.75rem;
    min-width: auto;
}

.grant-view-compact .grant-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* ============================================
   共通スタイル
============================================ */

/* ステータスヘッダー */
.grant-status-header {
    position: relative;
    height: 3rem;
    background: var(--clean-gradient-primary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    overflow: hidden;
}

.grant-status-header.status--closed {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.grant-status-header.status--urgent {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.grant-status-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--clean-white);
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.025em;
}

.grant-deadline-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 1rem;
    color: var(--clean-white);
    font-size: 0.75rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* カードコンテンツ */
.grant-card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    overflow: hidden;
}

/* タイトルセクション */
.grant-title-section {
    margin-bottom: 1.25rem;
}

.grant-category-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    border-radius: var(--clean-radius-2xl);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.75rem;
}

.grant-title {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.4;
    color: var(--clean-gray-900);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 3.5rem;
}

.grant-title a {
    color: inherit;
    text-decoration: none;
    transition: var(--clean-transition);
}

.grant-title a:hover {
    color: var(--clean-gray-800);
}

/* AI要約セクション */
.grant-ai-summary {
    position: relative;
    padding: 1rem;
    background: var(--clean-gradient-secondary);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-lg);
    margin-bottom: 1.25rem;
    min-height: 5rem;
    max-height: 5rem;
    overflow: hidden;
    transition: var(--clean-transition);
}

.grant-ai-summary:hover {
    transform: translateY(-1px);
    box-shadow: var(--clean-shadow-md);
    border-color: var(--clean-gray-800);
}

.grant-ai-summary-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--clean-gray-800);
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.grant-ai-summary-text {
    color: var(--clean-gray-700);
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 情報グリッド */
.grant-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.grant-info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-md);
    transition: var(--clean-transition);
    position: relative;
    overflow: hidden;
}

.grant-info-item:hover {
    transform: translateY(-1px);
    box-shadow: var(--clean-shadow-md);
    border-color: var(--clean-gray-800);
}

.grant-info-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clean-gray-100);
    border-radius: var(--clean-radius-md);
    color: var(--clean-gray-600);
    font-size: 1.125rem;
    flex-shrink: 0;
    transition: var(--clean-transition);
}

.grant-info-item:hover .grant-info-icon {
    transform: scale(1.05);
}

.grant-info-item--amount .grant-info-icon {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
}

.grant-info-item--target .grant-info-icon {
    background: var(--clean-gradient-dark);
    color: var(--clean-white);
}

.grant-info-item--location .grant-info-icon {
    background: var(--clean-gradient-accent);
    color: var(--clean-white);
}

.grant-info-item--rate .grant-info-icon {
    background: var(--clean-gradient-secondary);
    color: var(--clean-gray-800);
}

.grant-info-content {
    flex: 1;
    min-width: 0;
}

.grant-info-value {
    display: block;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--clean-gray-900);
    white-space: normal;        /* nowrap から normal に変更 */
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;          /* 行間を追加 */
    max-height: 2.6em;         /* 2行分の高さ制限 */
    word-break: break-word;    /* 長い文字を改行 */
}


.grant-info-value {
    display: block;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--clean-gray-900);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* アクションフッター */
.grant-card-footer {
    padding: 1.25rem 1.5rem;
    background: var(--clean-gradient-light);
    border-top: 1px solid var(--clean-gray-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    position: relative;
    z-index: 10;
}

.grant-actions {
    display: flex;
    gap: 0.75rem;
    flex: 1;
}

.grant-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: 2px solid transparent;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--clean-transition-slow);
    text-decoration: none;
    white-space: nowrap;
    position: relative;
    overflow: hidden;
    z-index: 20;
}

/* 詳細ボタン */
.grant-btn--primary {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    box-shadow: var(--clean-shadow-md);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.grant-btn--primary:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: var(--clean-shadow-lg);
    background: var(--clean-gradient-dark);
}

.grant-btn--secondary {
    background: var(--clean-gray-100);
    color: var(--clean-gray-900);
    border: 2px solid var(--clean-gray-300);
    box-shadow: var(--clean-shadow-sm);
}

.grant-btn--secondary:hover {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    transform: translateY(-1px) scale(1.01);
    box-shadow: var(--clean-shadow-md);
    border-color: var(--clean-gray-500);
}

/* AI質問ボタン */
.grant-btn--ai {
    background: var(--clean-gradient-accent);
    color: var(--clean-white);
    border: 2px solid var(--clean-gray-800);
    box-shadow: var(--clean-shadow-md);
}

.grant-btn--ai:hover {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    transform: translateY(-2px) scale(1.02);
    box-shadow: var(--clean-shadow-lg);
    border-color: var(--clean-gray-900);
}

.grant-btn--ai:focus {
    outline: 2px solid var(--clean-gray-800);
    outline-offset: 2px;
}

.favorite-btn {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 2px solid var(--clean-gray-200);
    border-radius: 50%;
    color: var(--clean-gray-500);
    cursor: pointer;
    transition: var(--clean-transition-slow);
    flex-shrink: 0;
    position: relative;
    box-shadow: var(--clean-shadow-sm);
    z-index: 20;
    font-size: 1.125rem;
}

.favorite-btn:hover {
    background: var(--clean-gradient-primary);
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
    transform: scale(1.05);
    box-shadow: var(--clean-shadow-md);
}

.favorite-btn.favorited {
    background: var(--clean-gradient-primary);
    border-color: var(--clean-primary);
    color: var(--clean-white);
}

/* ホバー時の詳細表示 */
.grant-hover-details {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(248, 250, 252, 0.98);
    backdrop-filter: blur(20px);
    padding: 0;
    opacity: 0;
    visibility: hidden;
    transition: var(--clean-transition-slow);
    overflow: hidden;
    z-index: 5;
    border-radius: var(--clean-radius-xl);
    display: flex;
    flex-direction: column;
    pointer-events: none;
}

.grant-card-unified:hover .grant-hover-details {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

/* スクロール可能なコンテンツエリア */
.grant-hover-scrollable {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1.75rem;
    height: 100%;
}

/* スクロールバーのカスタマイズ */
.grant-hover-scrollable::-webkit-scrollbar {
    width: 6px;
}

.grant-hover-scrollable::-webkit-scrollbar-track {
    background: var(--clean-gray-200);
    border-radius: 3px;
}

.grant-hover-scrollable::-webkit-scrollbar-thumb {
    background: var(--clean-gray-800);
    border-radius: 3px;
}

.grant-hover-scrollable::-webkit-scrollbar-thumb:hover {
    background: var(--clean-gray-900);
}

.grant-hover-details::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--clean-gradient-primary);
    z-index: 10;
}

.grant-hover-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-top: 0.5rem;
    position: sticky;
    top: 0;
    background: rgba(248, 250, 252, 0.98);
    z-index: 10;
    padding-bottom: 1rem;
}

.grant-hover-title {
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--clean-gray-900);
    line-height: 1.3;
    flex: 1;
    padding-right: 1rem;
}

.grant-hover-close {
    width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clean-gray-200);
    border-radius: 50%;
    color: var(--clean-gray-600);
    cursor: pointer;
    flex-shrink: 0;
    transition: var(--clean-transition);
    border: none;
    pointer-events: auto;
}

.grant-hover-close:hover {
    background: var(--clean-gray-800);
    color: var(--clean-white);
    transform: rotate(90deg);
}

/* クイック情報バー */
.grant-quick-stats {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-lg);
    margin-bottom: 1.25rem;
}

.grant-stat-item {
    flex: 1;
    text-align: center;
    padding: 0.75rem;
    border-right: 1px solid var(--clean-gray-200);
    position: relative;
}

.grant-stat-item:last-child {
    border-right: none;
}

.grant-stat-value {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--clean-gray-900);
    margin-bottom: 0.375rem;
}

.grant-stat-label {
    display: block;
    font-size: 0.6875rem;
    color: var(--clean-gray-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.grant-detail-sections {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    flex: 1;
}

.grant-detail-section {
    padding: 1rem;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-md);
    transition: var(--clean-transition);
}

.grant-detail-section:hover {
    box-shadow: var(--clean-shadow-md);
    transform: translateY(-1px);
    border-color: var(--clean-gray-800);
}

.grant-detail-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--clean-gray-800);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.grant-detail-value {
    font-size: 0.875rem;
    color: var(--clean-gray-700);
    line-height: 1.5;
}

/* ステータスインジケーター */
.grant-status-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background: var(--clean-success);
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
    z-index: 10;
}

.grant-status-indicator.closed {
    background: var(--clean-gray-400);
    box-shadow: none;
}

/* 注目バッジ */
.grant-featured-badge {
    position: absolute;
    top: 4rem;
    right: -2.25rem;
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    padding: 0.375rem 2.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transform: rotate(45deg);
    box-shadow: var(--clean-shadow-md);
    z-index: 10;
}

/* 難易度インジケーター */
.grant-difficulty-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.375rem 0.75rem;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-sm);
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.6875rem;
    font-weight: 600;
    box-shadow: var(--clean-shadow-sm);
    z-index: 10;
}

/* プログレスバー（採択率） */
.grant-success-rate {
    margin-top: auto;
    padding-top: 1rem;
}

.grant-success-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: var(--clean-gray-500);
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.grant-success-bar {
    height: 0.375rem;
    background: var(--clean-gray-200);
    border-radius: 0.1875rem;
    overflow: hidden;
    position: relative;
}

.grant-success-fill {
    height: 100%;
    background: var(--clean-gradient-primary);
    border-radius: 0.1875rem;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

/* タグシステム */
.grant-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.grant-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    background: var(--clean-gray-100);
    color: var(--clean-gray-800);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-2xl);
    font-size: 0.6875rem;
    font-weight: 600;
    transition: var(--clean-transition);
}

.grant-tag:hover {
    background: var(--clean-gray-900);
    color: var(--clean-white);
    transform: scale(1.02);
}

/* アニメーション */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grant-card-unified {
    animation: slideIn 0.5s ease-out;
    animation-fill-mode: both;
}

.grant-card-unified:nth-child(1) { animation-delay: 0.05s; }
.grant-card-unified:nth-child(2) { animation-delay: 0.1s; }
.grant-card-unified:nth-child(3) { animation-delay: 0.15s; }
.grant-card-unified:nth-child(4) { animation-delay: 0.2s; }
.grant-card-unified:nth-child(5) { animation-delay: 0.25s; }
.grant-card-unified:nth-child(6) { animation-delay: 0.3s; }

/* トースト通知 */
.grant-toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    padding: 1.25rem 1.5rem;
    background: var(--clean-gray-900);
    color: var(--clean-white);
    border-radius: var(--clean-radius-lg);
    box-shadow: var(--clean-shadow-xl);
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.9375rem;
    font-weight: 600;
    z-index: 9999;
    opacity: 0;
    transform: translateY(100%);
    transition: var(--clean-transition-slow);
}

.grant-toast.show {
    opacity: 1;
    transform: translateY(0);
}

.grant-toast-icon {
    width: 1.75rem;
    height: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clean-white);
    border-radius: 50%;
    color: var(--clean-gray-900);
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .grants-grid {
        grid-template-columns: 1fr;
        padding: 1.25rem;
        gap: 1.25rem;
    }
    
    .grant-view-card .grant-card-unified {
        height: auto;
        min-height: 28rem;
    }
    
    .grant-info-grid {
        grid-template-columns: 1fr;
    }
    
    .grant-hover-details {
        display: none !important;
    }
    
    .grant-view-list .grant-card-unified {
        flex-direction: column;
    }
    
    .grant-view-list .grant-status-header {
        width: 100%;
        height: 3rem;
        writing-mode: initial;
    }
    
    .grant-view-list .grant-card-footer {
        border-left: none;
        border-top: 1px solid var(--clean-gray-200);
        min-width: auto;
        flex-direction: row;
    }
    
    .grant-card-content {
        padding: 1.25rem;
    }
    
    .grant-title {
        font-size: 1.125rem;
    }
    
    .grant-btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
    }
    
    .favorite-btn {
        width: 2.625rem;
        height: 2.625rem;
    }
    
    /* モバイルでタップで詳細表示 */
    .grant-card-unified {
        cursor: pointer;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    :root {
        --clean-white: #1e293b;
        --clean-gray-50: #0f172a;
        --clean-gray-100: #334155;
        --clean-gray-200: #475569;
        --clean-gray-300: #64748b;
        --clean-gray-400: #94a3b8;
        --clean-gray-500: #cbd5e1;
        --clean-gray-600: #e2e8f0;
        --clean-gray-700: #f1f5f9;
        --clean-gray-800: #f8fafc;
        --clean-gray-900: #ffffff;
    }
}

/* 印刷対応 */
@media print {
    .grant-card-unified {
        break-inside: avoid;
        page-break-inside: avoid;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
    
    .grant-hover-details,
    .favorite-btn,
    .grant-featured-badge {
        display: none !important;
    }
}

/* 高コントラストモード対応 */
@media (prefers-contrast: high) {
    .grant-card-unified {
        border-width: 2px;
        border-color: var(--clean-gray-800);
    }
    
    .grant-btn {
        border-width: 2px;
    }
    
    .grant-info-item {
        border-width: 2px;
    }
}

/* 減らされたモーション設定対応 */
@media (prefers-reduced-motion: reduce) {
    .grant-card-unified,
    .grant-btn,
    .favorite-btn,
    .grant-info-item {
        transition: none;
        animation: none;
    }
    
    .grant-card-unified:hover {
        transform: none;
    }
}

/* フォーカス管理 */
.grant-btn:focus,
.favorite-btn:focus,
.grant-hover-close:focus {
    outline: 2px solid var(--clean-gray-800);
    outline-offset: 2px;
}

/* セレクション色 */
::selection {
    background: rgba(0, 0, 0, 0.1);
    color: var(--clean-gray-900);
}

::-moz-selection {
    background: rgba(0, 0, 0, 0.1);
    color: var(--clean-gray-900);
}

/* スムーススクロール */
.grant-hover-scrollable {
    scroll-behavior: smooth;
}

/* ===== AI質問モーダル ===== */
.grant-ai-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.3s ease;
}

.grant-ai-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.grant-ai-modal-container {
    position: relative;
    width: 90%;
    max-width: 600px;
    height: 80vh;
    max-height: 600px;
    background: var(--clean-white);
    border-radius: var(--clean-radius-xl);
    box-shadow: var(--clean-shadow-xl);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(1);
    transition: transform 0.3s ease;
}

.grant-ai-modal-header {
    padding: var(--clean-radius-lg);
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    position: relative;
}

.grant-ai-modal-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: var(--space-1);
}

.grant-ai-modal-subtitle {
    font-size: 0.875rem;
    opacity: 0.9;
    font-weight: 400;
    max-width: 80%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.grant-ai-modal-close {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    width: 2rem;
    height: 2rem;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: var(--clean-white);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--clean-transition);
}

.grant-ai-modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.grant-ai-modal-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.grant-ai-chat-messages {
    flex: 1;
    padding: var(--space-4);
    overflow-y: auto;
    background: var(--clean-gray-50);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.grant-ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.grant-ai-chat-messages::-webkit-scrollbar-track {
    background: var(--clean-gray-200);
    border-radius: 3px;
}

.grant-ai-chat-messages::-webkit-scrollbar-thumb {
    background: var(--clean-gray-400);
    border-radius: 3px;
}

.grant-ai-chat-messages::-webkit-scrollbar-thumb:hover {
    background: var(--clean-gray-500);
}

.grant-ai-message {
    display: flex;
    gap: var(--space-3);
    max-width: 85%;
    animation: fadeInUp 0.3s ease-out;
}

.grant-ai-message--assistant {
    align-self: flex-start;
}

.grant-ai-message--user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.grant-ai-message--error {
    align-self: flex-start;
}

.grant-ai-message-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}

.grant-ai-message--assistant .grant-ai-message-avatar {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
}

.grant-ai-message--user .grant-ai-message-avatar {
    background: var(--clean-gray-300);
    color: var(--clean-gray-700);
}

.grant-ai-message--error .grant-ai-message-avatar {
    background: var(--clean-danger);
    color: var(--clean-white);
}

.grant-ai-message-content {
    background: var(--clean-white);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--clean-radius-lg);
    border: 1px solid var(--clean-gray-200);
    box-shadow: var(--clean-shadow-sm);
    font-size: 0.9375rem;
    line-height: 1.5;
    position: relative;
}

.grant-ai-message--user .grant-ai-message-content {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    border-color: var(--clean-gray-800);
}

.grant-ai-message--error .grant-ai-message-content {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}

.grant-ai-typing {
    display: flex;
    gap: 4px;
    align-items: center;
    padding: var(--space-2) 0;
}

.grant-ai-typing span {
    width: 8px;
    height: 8px;
    background: var(--clean-gray-400);
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.grant-ai-typing span:nth-child(1) { animation-delay: 0.0s; }
.grant-ai-typing span:nth-child(2) { animation-delay: 0.2s; }
.grant-ai-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

.grant-ai-chat-input-container {
    padding: var(--space-4);
    background: var(--clean-white);
    border-top: 1px solid var(--clean-gray-200);
}

.grant-ai-chat-input-wrapper {
    display: flex;
    gap: var(--space-2);
    align-items: flex-end;
    margin-bottom: var(--space-3);
}

.grant-ai-chat-input {
    flex: 1;
    padding: var(--space-3);
    border: 2px solid var(--clean-gray-300);
    border-radius: var(--clean-radius-lg);
    font-family: inherit;
    font-size: 0.9375rem;
    line-height: 1.5;
    resize: none;
    transition: var(--clean-transition);
    background: var(--clean-white);
    min-height: 2.75rem;
    max-height: 6rem;
}

.grant-ai-chat-input:focus {
    outline: none;
    border-color: var(--clean-primary);
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}

.grant-ai-chat-send {
    width: 2.75rem;
    height: 2.75rem;
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    border: none;
    border-radius: var(--clean-radius-lg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--clean-transition);
    flex-shrink: 0;
}

.grant-ai-chat-send:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: var(--clean-shadow-md);
}

.grant-ai-chat-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.grant-ai-chat-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

.grant-ai-suggestion {
    padding: var(--space-2) var(--space-3);
    background: var(--clean-gray-100);
    border: 1px solid var(--clean-gray-300);
    border-radius: var(--clean-radius-2xl);
    font-size: 0.8125rem;
    color: var(--clean-gray-700);
    cursor: pointer;
    transition: var(--clean-transition);
    white-space: nowrap;
}

.grant-ai-suggestion:hover {
    background: var(--clean-primary);
    color: var(--clean-white);
    border-color: var(--clean-primary);
    transform: translateY(-1px);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(1rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .grant-ai-modal-container {
        width: 95%;
        height: 90vh;
        margin: var(--space-4);
    }
    
    .grant-ai-modal-header {
        padding: var(--space-3);
    }
    
    .grant-ai-modal-title {
        font-size: 1rem;
    }
    
    .grant-ai-modal-subtitle {
        font-size: 0.8125rem;
    }
    
    .grant-ai-chat-messages {
        padding: var(--space-3);
    }
    
    .grant-ai-message {
        max-width: 95%;
    }
    
    .grant-ai-chat-suggestions {
        flex-direction: column;
    }
    
    .grant-ai-suggestion {
        white-space: normal;
        text-align: left;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // お気に入り機能
    document.addEventListener('click', function(e) {
        if (e.target.closest('.favorite-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const btn = e.target.closest('.favorite-btn');
            const postId = btn.dataset.postId;
            
            // 既存のAJAX関数を呼び出し
            if (typeof gi_toggle_favorite === 'function') {
                gi_toggle_favorite(postId, btn);
            } else if (typeof window.toggleFavorite === 'function') {
                window.toggleFavorite(postId, btn);
            } else {
                // フォールバック：ローカルストレージ
                toggleLocalFavorite(postId, btn);
            }
        }
    });
    
    // カードクリック処理（詳細ボタンのみでページ遷移）
    document.addEventListener('click', function(e) {
        // 詳細ボタンがクリックされた場合のみページ遷移
        if (e.target.closest('.grant-btn--primary')) {
            const btn = e.target.closest('.grant-btn--primary');
            const href = btn.getAttribute('href');
            if (href) {
                // クリックエフェクト
                btn.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    window.location.href = href;
                }, 100);
            }
        }
    });
    
    // ホバー詳細の表示・非表示制御（デスクトップのみ）
    function isDesktop() {
        return window.innerWidth > 768 && !('ontouchstart' in window);
    }
    
    // ホバーイベント（デスクトップのみ）
    document.querySelectorAll('.grant-card-unified').forEach(card => {
        let hoverTimeout;
        
        card.addEventListener('mouseenter', function() {
            if (!isDesktop()) return;
            
            clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                const details = this.querySelector('.grant-hover-details');
                if (details) {
                    details.classList.add('show-details');
                    details.style.opacity = '1';
                    details.style.visibility = 'visible';
                }
            }, 200);
        });
        
        card.addEventListener('mouseleave', function() {
            clearTimeout(hoverTimeout);
            const details = this.querySelector('.grant-hover-details');
            if (details) {
                details.classList.remove('show-details');
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
            }
        });
    });
    
    // モバイルでのタップ詳細表示
    let tapCount = 0;
    let tapTimeout;
    
    document.addEventListener('touchend', function(e) {
        if (!e.target.closest('.grant-card-unified')) return;
        if (e.target.closest('.grant-btn, .favorite-btn')) return;
        
        tapCount++;
        
        if (tapCount === 1) {
            tapTimeout = setTimeout(() => {
                tapCount = 0;
            }, 300);
        } else if (tapCount === 2) {
            clearTimeout(tapTimeout);
            tapCount = 0;
            
            // ダブルタップで詳細表示
            const card = e.target.closest('.grant-card-unified');
            const details = card.querySelector('.grant-hover-details');
            if (details) {
                if (details.style.opacity === '1') {
                    details.style.opacity = '0';
                    details.style.visibility = 'hidden';
                    details.classList.remove('show-details');
                } else {
                    details.classList.add('show-details');
                    details.style.opacity = '1';
                    details.style.visibility = 'visible';
                }
            }
        }
    });
    
    // ローカルお気に入り処理
    function toggleLocalFavorite(postId, btn) {
        let favorites = JSON.parse(localStorage.getItem('gi_favorites') || '[]');
        const isFavorited = btn.classList.contains('favorited');
        
        if (isFavorited) {
            favorites = favorites.filter(id => id !== postId);
            btn.classList.remove('favorited');
            btn.innerHTML = '<i class="far fa-heart"></i>';
            btn.setAttribute('aria-pressed', 'false');
            btn.setAttribute('aria-label', 'お気に入りに追加');
            showToast('お気に入りから削除しました', 'remove');
        } else {
            favorites.push(postId);
            btn.classList.add('favorited');
            btn.innerHTML = '<i class="fas fa-heart"></i>';
            btn.setAttribute('aria-pressed', 'true');
            btn.setAttribute('aria-label', 'お気に入りから削除');
            
            // ハートアニメーション
            animateHeart(btn);
            showToast('お気に入りに追加しました', 'add');
        }
        
        localStorage.setItem('gi_favorites', JSON.stringify(favorites));
        updateFavoriteCount(favorites.length);
    }
    
    // ハートアニメーション
    function animateHeart(btn) {
        btn.style.transform = 'scale(1.2)';
        btn.style.filter = 'brightness(1.1)';
        setTimeout(() => {
            btn.style.transform = '';
            btn.style.filter = '';
        }, 200);
        
        // パーティクルエフェクト
        createHeartParticles(btn);
    }
    
    // ハートパーティクル生成
    function createHeartParticles(btn) {
        const rect = btn.getBoundingClientRect();
        const particles = 6;
        
        for (let i = 0; i < particles; i++) {
            const particle = document.createElement('div');
            particle.className = 'heart-particle';
            particle.innerHTML = '♥';
            particle.style.cssText = `
                position: fixed;
                left: ${rect.left + rect.width / 2}px;
                top: ${rect.top + rect.height / 2}px;
                color: #000000;
                font-size: 1rem;
                pointer-events: none;
                z-index: 9999;
                animation: particle-float 1s ease-out forwards;
                transform: rotate(${Math.random() * 360}deg);
            `;
            
            document.body.appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 1000);
        }
    }
    
    // トースト通知
    function showToast(message, type) {
        const existingToast = document.querySelector('.grant-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        const toast = document.createElement('div');
        toast.className = 'grant-toast';
        toast.innerHTML = `
            <div class="grant-toast-icon">
                <i class="fas fa-${type === 'add' ? 'check' : 'times'}"></i>
            </div>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 2500);
    }
    
    // お気に入り数更新
    function updateFavoriteCount(count) {
        const countElement = document.querySelector('.favorite-count');
        if (countElement) {
            countElement.textContent = count;
        }
    }
    
    // ホバー詳細の閉じるボタン
    document.addEventListener('click', function(e) {
        if (e.target.closest('.grant-hover-close')) {
            e.preventDefault();
            e.stopPropagation();
            const details = e.target.closest('.grant-hover-details');
            if (details) {
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
                details.classList.remove('show-details');
            }
        }
    });
    
    // ESCキーで詳細を閉じる
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.grant-hover-details.show-details').forEach(details => {
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
                details.classList.remove('show-details');
            });
        }
    });
    
    // 詳細表示外をクリックで閉じる
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('grant-hover-details')) {
            e.target.style.opacity = '0';
            e.target.style.visibility = 'hidden';
            e.target.classList.remove('show-details');
        }
    });
    
    // 採択率バーのアニメーション
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bar = entry.target.querySelector('.grant-success-fill');
                if (bar && !bar.dataset.animated) {
                    const rate = parseFloat(bar.dataset.rate);
                    bar.dataset.animated = 'true';
                    
                    // アニメーション開始
                    let currentRate = 0;
                    const increment = rate / 40;
                    const timer = setInterval(() => {
                        currentRate += increment;
                        if (currentRate >= rate) {
                            currentRate = rate;
                            clearInterval(timer);
                        }
                        bar.style.width = currentRate + '%';
                    }, 25);
                }
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.grant-success-rate').forEach(el => {
        observer.observe(el);
    });
    
    // 初期お気に入り状態の設定
    function initFavorites() {
        const favorites = JSON.parse(localStorage.getItem('gi_favorites') || '[]');
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            const postId = btn.dataset.postId;
            if (favorites.includes(postId)) {
                btn.classList.add('favorited');
                btn.innerHTML = '<i class="fas fa-heart"></i>';
                btn.setAttribute('aria-pressed', 'true');
                btn.setAttribute('aria-label', 'お気に入りから削除');
            }
        });
        updateFavoriteCount(favorites.length);
    }
    
    // ボタンのフォーカス管理
    document.querySelectorAll('.grant-btn, .favorite-btn, .grant-hover-close').forEach(btn => {
        btn.addEventListener('focus', function() {
            this.style.outline = '2px solid var(--clean-gray-800)';
            this.style.outlineOffset = '2px';
        });
        
        btn.addEventListener('blur', function() {
            this.style.outline = '';
            this.style.outlineOffset = '';
        });
        
        // キーボードでのアクティベート
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // 初期化
    initFavorites();
    
    // ウィンドウリサイズ対応
    window.addEventListener('resize', function() {
        // モバイル・デスクトップ切り替え時に詳細表示をリセット
        document.querySelectorAll('.grant-hover-details').forEach(details => {
            if (!isDesktop()) {
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
                details.classList.remove('show-details');
            }
        });
    });
    
    // AI質問モーダル関数をグローバルに追加
    window.openGrantAIChat = function(button) {
        const postId = button.getAttribute('data-post-id');
        const grantTitle = button.getAttribute('data-grant-title');
        
        if (!postId) {
            console.error('Post ID not found');
            return;
        }
        
        // モーダルを作成または表示
        showAIChatModal(postId, grantTitle);
    };
    
    // AI質問モーダルの表示
    function showAIChatModal(postId, grantTitle) {
        // 既存のモーダルを削除
        const existingModal = document.querySelector('.grant-ai-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // モーダルHTML作成
        const modalHTML = `
            <div class="grant-ai-modal" id="grant-ai-modal">
                <div class="grant-ai-modal-overlay" onclick="closeAIChatModal()"></div>
                <div class="grant-ai-modal-container">
                    <div class="grant-ai-modal-header">
                        <div class="grant-ai-modal-title">
                            <i class="fas fa-robot"></i>
                            <span>AI質問チャット</span>
                        </div>
                        <div class="grant-ai-modal-subtitle">${grantTitle}</div>
                        <button class="grant-ai-modal-close" onclick="closeAIChatModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="grant-ai-modal-body">
                        <div class="grant-ai-chat-messages" id="ai-chat-messages-${postId}">
                            <div class="grant-ai-message grant-ai-message--assistant">
                                <div class="grant-ai-message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="grant-ai-message-content">
                                    こんにちは！この助成金について何でもお聞きください。申請方法、対象要件、必要書類など、詳しくお答えします。
                                </div>
                            </div>
                        </div>
                        <div class="grant-ai-chat-input-container">
                            <div class="grant-ai-chat-input-wrapper">
                                <textarea 
                                    class="grant-ai-chat-input" 
                                    id="ai-chat-input-${postId}"
                                    placeholder="この助成金について質問してください..."
                                    rows="3"></textarea>
                                <button 
                                    class="grant-ai-chat-send" 
                                    id="ai-chat-send-${postId}"
                                    onclick="sendAIQuestion('${postId}')">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div class="grant-ai-chat-suggestions">
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', 'この助成金の申請条件を教えてください')">
                                    申請条件について
                                </button>
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', 'この助成金の申請方法を詳しく教えてください')">
                                    申請方法について  
                                </button>
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', 'どんな費用が対象になりますか？')">
                                    対象経費について
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // モーダルをDOMに追加
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // フォーカス設定
        setTimeout(() => {
            const input = document.getElementById(`ai-chat-input-${postId}`);
            if (input) {
                input.focus();
            }
        }, 100);
        
        // Enterキーでの送信
        const input = document.getElementById(`ai-chat-input-${postId}`);
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendAIQuestion(postId);
                }
            });
        }
    }
    
    // AI質問モーダルを閉じる
    window.closeAIChatModal = function() {
        const modal = document.querySelector('.grant-ai-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    };
    
    // 質問候補の選択
    window.selectSuggestion = function(postId, question) {
        const input = document.getElementById(`ai-chat-input-${postId}`);
        if (input) {
            input.value = question;
            input.focus();
        }
    };
    
    // AI質問送信
    window.sendAIQuestion = function(postId) {
        const input = document.getElementById(`ai-chat-input-${postId}`);
        const sendBtn = document.getElementById(`ai-chat-send-${postId}`);
        const messagesContainer = document.getElementById(`ai-chat-messages-${postId}`);
        
        if (!input || !messagesContainer) {
            console.error('Required elements not found');
            return;
        }
        
        const question = input.value.trim();
        if (!question) {
            return;
        }
        
        // 送信ボタンを無効化
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
        
        // ユーザーメッセージを追加
        const userMessage = document.createElement('div');
        userMessage.className = 'grant-ai-message grant-ai-message--user';
        userMessage.innerHTML = `
            <div class="grant-ai-message-content">${escapeHtml(question)}</div>
            <div class="grant-ai-message-avatar">
                <i class="fas fa-user"></i>
            </div>
        `;
        messagesContainer.appendChild(userMessage);
        
        // 入力をクリア
        input.value = '';
        
        // スクロールダウン
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // AJAX リクエスト
        const formData = new FormData();
        formData.append('action', 'handle_grant_ai_question');
        formData.append('post_id', postId);
        formData.append('question', question);
        formData.append('nonce', '<?php echo wp_create_nonce('gi_ajax_nonce'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // ローディング表示を追加
            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'grant-ai-message grant-ai-message--assistant grant-ai-loading';
            loadingMessage.innerHTML = `
                <div class="grant-ai-message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="grant-ai-message-content">
                    <div class="grant-ai-typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(loadingMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // 2秒後にレスポンスを表示
            setTimeout(() => {
                loadingMessage.remove();
                
                if (data.success) {
                    const assistantMessage = document.createElement('div');
                    assistantMessage.className = 'grant-ai-message grant-ai-message--assistant';
                    assistantMessage.innerHTML = `
                        <div class="grant-ai-message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="grant-ai-message-content">${escapeHtml(data.data.response)}</div>
                    `;
                    messagesContainer.appendChild(assistantMessage);
                } else {
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'grant-ai-message grant-ai-message--error';
                    errorMessage.innerHTML = `
                        <div class="grant-ai-message-avatar">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="grant-ai-message-content">申し訳ございません。エラーが発生しました。しばらく時間をおいて再度お試しください。</div>
                    `;
                    messagesContainer.appendChild(errorMessage);
                }
                
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 2000);
        })
        .catch(error => {
            console.error('Error sending AI question:', error);
            
            // エラーメッセージを表示
            const errorMessage = document.createElement('div');
            errorMessage.className = 'grant-ai-message grant-ai-message--error';
            errorMessage.innerHTML = `
                <div class="grant-ai-message-avatar">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="grant-ai-message-content">通信エラーが発生しました。インターネット接続を確認して再度お試しください。</div>
            `;
            messagesContainer.appendChild(errorMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .finally(() => {
            // 送信ボタンを復活
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
            input.focus();
        });
    };
    
    // HTMLエスケープ関数
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});

// パーティクルアニメーション用CSS追加
const grantCardStyles = document.createElement('style');
grantCardStyles.textContent = `
    @keyframes particle-float {
        0% {
            opacity: 1;
            transform: translateY(0) translateX(0) scale(1);
        }
        100% {
            opacity: 0;
            transform: translateY(-60px) translateX(${Math.random() * 60 - 30}px) scale(0.3);
        }
    }
    
    /* ドラッグ無効化 */
    .grant-card-unified * {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-user-drag: none;
        -khtml-user-drag: none;
        -moz-user-drag: none;
        -o-user-drag: none;
        user-drag: none;
    }
    
    /* テキストのみ選択可能 */
    .grant-title a,
    .grant-ai-summary-text,
    .grant-detail-value {
        -webkit-user-select: text;
        -moz-user-select: text;
        -ms-user-select: text;
        user-select: text;
    }
`;
document.head.appendChild(grantCardStyles);
</script>
<?php endif; ?>

<!-- クリーンカード本体 -->
<article class="grant-card-unified <?php echo esc_attr($view_class); ?>" 
         data-post-id="<?php echo esc_attr($post_id); ?>"
         data-priority="<?php echo esc_attr($priority_order); ?>"
         role="article"
         aria-label="助成金情報カード">
    
    <!-- ステータスヘッダー -->
    <header class="grant-status-header <?php echo $application_status === 'closed' ? 'status--closed' : ''; ?> <?php echo !empty($deadline_info) && $deadline_info['class'] === 'critical' ? 'status--urgent' : ''; ?>">
        <div class="grant-status-badge">
            <i class="fas fa-circle-check" aria-hidden="true"></i>
            <span><?php echo esc_html($status_display); ?></span>
        </div>
        <?php if (!empty($deadline_info)): ?>
        <div class="grant-deadline-indicator">
            <i class="fas <?php echo esc_attr($deadline_info['icon']); ?>" aria-hidden="true"></i>
            <span><?php echo esc_html($deadline_info['text']); ?></span>
        </div>
        <?php endif; ?>
    </header>
    
    <!-- ステータスインジケーター -->
    <div class="grant-status-indicator <?php echo $application_status === 'closed' ? 'closed' : ''; ?>" 
         aria-label="<?php echo $application_status === 'closed' ? '募集終了' : '募集中'; ?>"></div>
    
    <!-- 注目バッジ -->
    <?php if ($is_featured): ?>
    <div class="grant-featured-badge" aria-label="注目の助成金">FEATURED</div>
    <?php endif; ?>
    
    <!-- 難易度バッジ -->
    <?php if ($grant_difficulty && $grant_difficulty !== 'normal'): ?>
    <div class="grant-difficulty-badge" style="color: <?php echo esc_attr($difficulty_data['color']); ?>">
        <i class="fas <?php echo esc_attr($difficulty_data['icon']); ?>" aria-hidden="true"></i>
        <span><?php echo esc_html($difficulty_data['label']); ?></span>
    </div>
    <?php endif; ?>
    
    <!-- カードコンテンツ -->
    <div class="grant-card-content">
        <div class="grant-main-info">
            <!-- タイトルセクション -->
            <div class="grant-title-section">
                <?php if ($main_category): ?>
                <div class="grant-category-tag">
                    <i class="fas fa-tag" aria-hidden="true"></i>
                    <span><?php echo esc_html($main_category); ?></span>
                </div>
                <?php endif; ?>
                <h3 class="grant-title">
                    <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>の詳細ページ" tabindex="-1">
                        <?php echo esc_html($title); ?>
                    </a>
                </h3>
            </div>
            
            <!-- AI要約 -->
            <?php if ($ai_summary || $excerpt): ?>
            <div class="grant-ai-summary">
                <div class="grant-ai-summary-label">
                    <i class="fas fa-robot" aria-hidden="true"></i>
                    <span>AI要約</span>
                </div>
                <p class="grant-ai-summary-text">
                    <?php echo esc_html(wp_trim_words($ai_summary ?: $excerpt, 40, '...')); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <!-- 情報グリッド -->
            <div class="grant-info-grid">
                <!-- 助成金額 -->
                <?php if ($amount_display): ?>
                <div class="grant-info-item grant-info-item--amount">
                    <div class="grant-info-icon" aria-hidden="true">
                        <i class="fas fa-yen-sign"></i>
                    </div>
                    <div class="grant-info-content">
                        <span class="grant-info-label">助成額</span>
                        <span class="grant-info-value"><?php echo esc_html($amount_display); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 対象者 -->
                <?php if ($grant_target): ?>
                <div class="grant-info-item grant-info-item--target">
                    <div class="grant-info-icon" aria-hidden="true">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="grant-info-content">
                        <span class="grant-info-label">対象</span>
                        <span class="grant-info-value"><?php echo esc_html($grant_target); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 地域 -->
                <div class="grant-info-item grant-info-item--location">
                    <div class="grant-info-icon" aria-hidden="true">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="grant-info-content">
                        <span class="grant-info-label">地域</span>
                        <span class="grant-info-value"><?php echo esc_html($prefecture); ?></span>
                    </div>
                </div>
                
                <!-- 補助率 -->
                <?php if ($subsidy_rate): ?>
                <div class="grant-info-item grant-info-item--rate">
                    <div class="grant-info-icon" aria-hidden="true">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="grant-info-content">
                        <span class="grant-info-label">補助率</span>
                        <span class="grant-info-value"><?php echo esc_html($subsidy_rate); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- タグ -->
            <?php if ($main_industry || $application_period): ?>
            <div class="grant-tags">
                <?php if ($main_industry): ?>
                <span class="grant-tag">
                    <i class="fas fa-industry" aria-hidden="true"></i>
                    <?php echo esc_html($main_industry); ?>
                </span>
                <?php endif; ?>
                <?php if ($application_period): ?>
                <span class="grant-tag">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    <?php echo esc_html($application_period); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- 採択率プログレスバー -->
            <?php if ($grant_success_rate > 0): ?>
            <div class="grant-success-rate">
                <div class="grant-success-label">
                    <span>採択率</span>
                    <span><?php echo esc_html($grant_success_rate); ?>%</span>
                </div>
                <div class="grant-success-bar" role="progressbar" aria-valuenow="<?php echo esc_attr($grant_success_rate); ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="grant-success-fill" data-rate="<?php echo esc_attr($grant_success_rate); ?>" style="width: 0;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- アクションフッター -->
    <footer class="grant-card-footer">
        <div class="grant-actions">
            <a href="<?php echo esc_url($permalink); ?>" class="grant-btn grant-btn--primary" role="button">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span>詳細を見る</span>
            </a>
            <button class="grant-btn grant-btn--ai" 
                    data-post-id="<?php echo esc_attr($post_id); ?>" 
                    data-grant-title="<?php echo esc_attr($title); ?>"
                    onclick="openGrantAIChat(this)" 
                    role="button">
                <i class="fas fa-robot" aria-hidden="true"></i>
                <span>AIに質問</span>
            </button>
            <?php if ($official_url): ?>
            <a href="<?php echo esc_url($official_url); ?>" class="grant-btn grant-btn--secondary" target="_blank" rel="noopener noreferrer" role="button">
                <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                <span>公式サイト</span>
            </a>
            <?php endif; ?>
        </div>
        <button class="favorite-btn <?php echo $is_favorite ? 'favorited' : ''; ?>" 
                data-post-id="<?php echo esc_attr($post_id); ?>" 
                title="お気に入り"
                aria-label="<?php echo $is_favorite ? 'お気に入りから削除' : 'お気に入りに追加'; ?>"
                aria-pressed="<?php echo $is_favorite ? 'true' : 'false'; ?>">
            <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart" aria-hidden="true"></i>
        </button>
    </footer>
    
    <!-- ホバー時の詳細表示 -->
    <div class="grant-hover-details" aria-hidden="true">
        <div class="grant-hover-scrollable">
            <div class="grant-hover-header">
                <h3 class="grant-hover-title"><?php echo esc_html($title); ?></h3>
                <button class="grant-hover-close" aria-label="詳細を閉じる">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            
            <!-- クイック統計 -->
            <div class="grant-quick-stats">
                <?php if ($amount_display): ?>
                <div class="grant-stat-item">
                    <span class="grant-stat-value"><?php echo esc_html($amount_display); ?></span>
                    <span class="grant-stat-label">最大助成額</span>
                </div>
                <?php endif; ?>
                <?php if ($grant_success_rate > 0): ?>
                <div class="grant-stat-item">
                    <span class="grant-stat-value"><?php echo esc_html($grant_success_rate); ?>%</span>
                    <span class="grant-stat-label">採択率</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="grant-detail-sections">
                <?php if ($ai_summary): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <i class="fas fa-robot" aria-hidden="true"></i>
                        <span>AI要約（完全版）</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($ai_summary); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($application_period): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        <span>申請期間</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($application_period); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($eligible_expenses): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <i class="fas fa-list-check" aria-hidden="true"></i>
                        <span>対象経費</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($eligible_expenses); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($required_documents): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <i class="fas fa-file-alt" aria-hidden="true"></i>
                        <span>必要書類</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($required_documents); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($application_method): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        <span>申請方法</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($application_method); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($contact_info): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        <span>お問い合わせ</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($contact_info); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>