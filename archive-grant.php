<?php
/**
 * Grant Archive Template - Clean & Professional Edition v9.0
 * File: archive-grant.php
 * 
 * シンプルでスタイリッシュな信頼感のあるデザイン
 * 機能はそのまま、デザインをクリーンに刷新
 * 
 * @package Grant_Insight_Clean
 * @version 9.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// 必要な関数の存在確認
$required_functions = [
    'gi_safe_get_meta',
    'gi_get_formatted_deadline',
    'gi_map_application_status_ui',
    'gi_get_user_favorites',
    'gi_get_grant_amount_display'
];

// URLパラメータから検索条件を取得（両方のパラメータ名に対応）
$search_params = [
    'search' => sanitize_text_field($_GET['s'] ?? ''),
    'category' => sanitize_text_field($_GET['category'] ?? $_GET['grant_category'] ?? ''),
    'prefecture' => sanitize_text_field($_GET['prefecture'] ?? $_GET['grant_prefecture'] ?? ''),
    'amount' => sanitize_text_field($_GET['amount'] ?? ''),
    'status' => sanitize_text_field($_GET['status'] ?? ''),
    'difficulty' => sanitize_text_field($_GET['difficulty'] ?? ''),
    'success_rate' => sanitize_text_field($_GET['success_rate'] ?? ''),
    'application_method' => sanitize_text_field($_GET['method'] ?? ''),
    'is_featured' => sanitize_text_field($_GET['featured'] ?? ''),
    'sort' => sanitize_text_field($_GET['sort'] ?? 'date_desc'),
    'view' => sanitize_text_field($_GET['view'] ?? 'grid'),
    'page' => max(1, intval($_GET['paged'] ?? 1))
];

// 統計データ取得
$stats = function_exists('gi_get_cached_stats') ? gi_get_cached_stats() : [
    'total_grants' => wp_count_posts('grant')->publish ?? 0,
    'active_grants' => 0,
    'prefecture_count' => 47,
    'avg_success_rate' => 65
];

// お気に入りリスト取得
$user_favorites = function_exists('gi_get_user_favorites_cached') ? 
    gi_get_user_favorites_cached() : 
    (function_exists('gi_get_user_favorites') ? gi_get_user_favorites() : []);

// 初期表示用クエリの構築
$initial_args = [
    'post_type' => 'grant',
    'posts_per_page' => 12,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => false
];

// 検索条件の適用
if (!empty($search_params['search'])) {
    $initial_args['s'] = $search_params['search'];
}

// タクソノミークエリ
$tax_query = ['relation' => 'AND'];
if (!empty($search_params['category'])) {
    $tax_query[] = [
        'taxonomy' => 'grant_category',
        'field' => 'slug',
        'terms' => explode(',', $search_params['category'])
    ];
}
if (!empty($search_params['prefecture'])) {
    $tax_query[] = [
        'taxonomy' => 'grant_prefecture',
        'field' => 'slug',
        'terms' => explode(',', $search_params['prefecture'])
    ];
}
if (count($tax_query) > 1) {
    $initial_args['tax_query'] = $tax_query;
}

// メタクエリ
$meta_query = ['relation' => 'AND'];

if (!empty($search_params['status'])) {
    $statuses = explode(',', $search_params['status']);
    $db_statuses = array_map(function($s) {
        return $s === 'active' ? 'open' : ($s === 'upcoming' ? 'upcoming' : $s);
    }, $statuses);
    
    $meta_query[] = [
        'key' => 'application_status',
        'value' => $db_statuses,
        'compare' => 'IN'
    ];
}

if (!empty($search_params['is_featured']) && $search_params['is_featured'] === '1') {
    $meta_query[] = [
        'key' => 'is_featured',
        'value' => '1',
        'compare' => '='
    ];
}

if (count($meta_query) > 1) {
    $initial_args['meta_query'] = $meta_query;
}

// ソート処理
switch($search_params['sort']) {
    case 'amount_desc':
        $initial_args['orderby'] = 'meta_value_num';
        $initial_args['meta_key'] = 'max_amount_numeric';
        $initial_args['order'] = 'DESC';
        break;
    case 'featured_first':
        $initial_args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];
        $initial_args['meta_key'] = 'is_featured';
        break;
    default:
        $initial_args['orderby'] = 'date';
        $initial_args['order'] = 'DESC';
}

// クエリ実行
$grants_query = new WP_Query($initial_args);

// タクソノミー取得
$all_categories = get_terms([
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
    'orderby' => 'count',
    'order' => 'DESC'
]);

$all_prefectures = get_terms([
    'taxonomy' => 'grant_prefecture',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
]);
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700;800&display=swap" as="style">
    
    <!-- Critical CSS -->
    <style>
    /* ===== Clean & Professional Design System - Monochrome Edition ===== */
    :root {
        /* Core Colors - Stylish Monochrome */
        --primary: #000000;
        --primary-light: #262626;
        --primary-dark: #000000;
        --secondary: #525252;
        --accent: #171717;
        
        /* Neutral Colors - Monochrome Palette */
        --white: #ffffff;
        --gray-50: #fafafa;
        --gray-100: #f5f5f5;
        --gray-200: #e5e5e5;
        --gray-300: #d4d4d4;
        --gray-400: #a3a3a3;
        --gray-500: #737373;
        --gray-600: #525252;
        --gray-700: #404040;
        --gray-800: #262626;
        --gray-900: #171717;
        
        /* Semantic Colors */
        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #000000;
        
        /* Typography */
        --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
        --font-japanese: 'Noto Sans JP', 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', 'Yu Gothic Medium', 'Meiryo', sans-serif;
        
        /* Spacing */
        --space-1: 0.25rem;
        --space-2: 0.5rem;
        --space-3: 0.75rem;
        --space-4: 1rem;
        --space-5: 1.25rem;
        --space-6: 1.5rem;
        --space-8: 2rem;
        --space-10: 2.5rem;
        --space-12: 3rem;
        --space-16: 4rem;
        --space-20: 5rem;
        
        /* Border Radius */
        --radius-sm: 0.25rem;
        --radius-md: 0.375rem;
        --radius-lg: 0.5rem;
        --radius-xl: 0.75rem;
        --radius-2xl: 1rem;
        
        /* Shadows */
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        
        /* Transitions */
        --transition: all 0.15s ease-in-out;
        --transition-slow: all 0.3s ease-in-out;
    }
    
    /* Reset & Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    html {
        font-size: 16px;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    body {
        font-family: var(--font-primary);
        color: var(--gray-900);
        background-color: var(--gray-50);
        font-weight: 400;
    }
    
    /* Container */
    .clean-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 var(--space-4);
    }
    
    @media (min-width: 768px) {
        .clean-container {
            padding: 0 var(--space-6);
        }
    }
    
    /* ===== HEADER SECTION ===== */
    .clean-header {
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding: var(--space-8) 0;
    }
    
    .clean-header-content {
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .clean-title {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: var(--space-4);
        letter-spacing: -0.025em;
    }
    
    .clean-subtitle {
        font-size: 1.125rem;
        color: var(--gray-600);
        font-weight: 400;
        max-width: 600px;
        margin: 0 auto;
    }
    
    /* ===== SEARCH SECTION ===== */
    .clean-search-section {
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding: var(--space-6) 0;
    }
    
    .clean-search-wrapper {
        max-width: 600px;
        margin: 0 auto var(--space-6);
    }
    
    .clean-search-box {
        position: relative;
    }
    
    .clean-search-input {
        width: 100%;
        padding: var(--space-4) var(--space-4) var(--space-4) 3rem;
        border: 2px solid var(--gray-300);
        border-radius: var(--radius-lg);
        font-size: 1rem;
        font-weight: 400;
        background: var(--white);
        transition: var(--transition);
    }
    
    .clean-search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
    }
    
    .clean-search-input::placeholder {
        color: var(--gray-500);
    }
    
    .clean-search-icon {
        position: absolute;
        left: var(--space-4);
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 1rem;
    }
    
    .clean-search-clear {
        position: absolute;
        right: var(--space-4);
        top: 50%;
        transform: translateY(-50%);
        width: 1.75rem;
        height: 1.75rem;
        border: none;
        background: var(--gray-300);
        color: var(--gray-600);
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        font-size: 0.75rem;
    }
    
    .clean-search-clear:hover {
        background: var(--danger);
        color: var(--white);
    }
    
    /* Quick Filters */
    .clean-filters {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-2);
        justify-content: center;
    }
    
    .clean-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-4);
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-2xl);
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
        position: relative;
        overflow: hidden;
    }
    
    .clean-filter-pill::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .clean-filter-pill:hover {
        background: var(--gray-200);
        border-color: var(--gray-300);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }
    
    .clean-filter-pill:hover::before {
        left: 100%;
    }
    
    .clean-filter-pill.active {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
    }
    
    .clean-filter-pill:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    
    .clean-filter-pill i {
        font-size: 0.75rem;
    }
    
    .clean-filter-count {
        background: rgba(255, 255, 255, 0.2);
        color: inherit;
        padding: 0 var(--space-2);
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 1.25rem;
        text-align: center;
    }
    
    .clean-filter-pill:not(.active) .clean-filter-count {
        background: var(--primary);
        color: var(--white);
    }
    
    /* ===== CONTROLS SECTION ===== */
    .clean-controls {
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding: var(--space-4) 0;
    }
    
    .clean-controls-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--space-4);
        flex-wrap: wrap;
    }
    
    .clean-controls-left,
    .clean-controls-right {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }
    
    .clean-select {
        padding: var(--space-2) var(--space-4);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        background: var(--white);
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        cursor: pointer;
        transition: var(--transition);
        min-width: 150px;
    }
    
    .clean-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
    }
    
    .clean-filter-button {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-4);
        background: var(--white);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        color: var(--gray-700);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .clean-filter-button:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .clean-filter-button.has-filters {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }
    
    .clean-view-toggle {
        display: flex;
        background: var(--gray-100);
        border-radius: var(--radius-md);
        padding: 2px;
    }
    
    .clean-view-btn {
        padding: var(--space-2) var(--space-3);
        background: transparent;
        border: none;
        color: var(--gray-600);
        cursor: pointer;
        border-radius: calc(var(--radius-md) - 2px);
        transition: var(--transition);
        font-size: 0.875rem;
    }
    
    .clean-view-btn:hover {
        color: var(--gray-800);
    }
    
    .clean-view-btn.active {
        background: var(--white);
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }
    
    /* ===== MAIN LAYOUT ===== */
    .clean-main {
        padding: var(--space-8) 0;
        min-height: 50vh;
    }
    
    .clean-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: var(--space-8);
        align-items: start;
    }
    
    /* ===== SIDEBAR ===== */
    .clean-sidebar {
        position: sticky;
        top: 120px;
    }
    
    .clean-filter-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-xl);
        overflow: hidden;
    }
    
    .clean-filter-header {
        background: var(--gray-50);
        padding: var(--space-4);
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .clean-filter-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }
    
    .clean-filter-close {
        display: none;
        width: 2rem;
        height: 2rem;
        border: none;
        background: var(--gray-200);
        color: var(--gray-600);
        cursor: pointer;
        border-radius: 50%;
        transition: var(--transition);
    }
    
    .clean-filter-close:hover {
        background: var(--gray-300);
    }
    
    .clean-filter-body {
        padding: var(--space-4);
    }
    
    .clean-filter-group {
        margin-bottom: var(--space-6);
        padding-bottom: var(--space-4);
        border-bottom: 1px solid var(--gray-200);
    }
    
    .clean-filter-group:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .clean-filter-group-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: var(--space-3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .clean-filter-option {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2);
        cursor: pointer;
        border-radius: var(--radius-md);
        transition: var(--transition);
    }
    
    .clean-filter-option:hover {
        background: var(--gray-50);
    }
    
    .clean-filter-checkbox,
    .clean-filter-radio {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
        cursor: pointer;
    }
    
    .clean-filter-label {
        flex: 1;
        font-size: 0.875rem;
        color: var(--gray-700);
        font-weight: 400;
        cursor: pointer;
    }
    
    .clean-filter-count {
        background: var(--gray-100);
        color: var(--gray-600);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0 var(--space-2);
        border-radius: var(--radius-sm);
        min-width: 1.25rem;
        text-align: center;
    }
    
    /* ===== RESULTS HEADER ===== */
    .clean-results-header {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        margin-bottom: var(--space-6);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .clean-results-info {
        display: flex;
        align-items: baseline;
        gap: var(--space-2);
    }
    
    .clean-results-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
    }
    
    .clean-results-text {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    .clean-loading-indicator {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        color: var(--gray-600);
        font-size: 0.875rem;
    }
    
    .clean-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid var(--gray-300);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* ===== GRANTS GRID ===== */
    .clean-grants-container {
        position: relative;
        min-height: 400px;
    }
    
    .clean-grants-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }
    
    .clean-grants-list {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
        margin-bottom: var(--space-8);
    }
    
    /* Grant Card */
    .clean-grant-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: var(--transition-slow);
        cursor: pointer;
    }
    
    .clean-grant-card:hover {
        border-color: var(--gray-300);
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }
    
    .clean-grant-card-header {
        padding: var(--space-4);
        border-bottom: 1px solid var(--gray-200);
    }
    
    .clean-grant-card-body {
        padding: var(--space-4);
    }
    
    .clean-grant-card-footer {
        padding: var(--space-3) var(--space-4);
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* ===== PAGINATION ===== */
    .clean-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: var(--space-1);
        margin-top: var(--space-8);
    }
    
    .clean-page-btn {
        min-width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--white);
        border: 1px solid var(--gray-300);
        color: var(--gray-700);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        border-radius: var(--radius-md);
        transition: var(--transition);
        text-decoration: none;
    }
    
    .clean-page-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .clean-page-btn.current {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }
    
    /* ===== NO RESULTS ===== */
    .clean-no-results {
        text-align: center;
        padding: var(--space-12);
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
    }
    
    .clean-no-results-icon {
        font-size: 3rem;
        color: var(--gray-400);
        margin-bottom: var(--space-4);
    }
    
    .clean-no-results-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: var(--space-2);
    }
    
    .clean-no-results-text {
        color: var(--gray-600);
        margin-bottom: var(--space-6);
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .clean-reset-button {
        padding: var(--space-3) var(--space-6);
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: var(--radius-md);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .clean-reset-button:hover {
        background: var(--primary-dark);
    }
    
    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 1024px) {
        .clean-layout {
            grid-template-columns: 1fr;
        }
        
        .clean-sidebar {
            position: static;
            margin-bottom: var(--space-6);
        }
        
        .clean-grants-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .clean-container {
            padding: 0 var(--space-4);
        }
        
        .clean-title {
            font-size: 1.875rem;
        }
        
        .clean-subtitle {
            font-size: 1rem;
        }
        
        .clean-search-section {
            padding: var(--space-4) 0;
        }
        
        .clean-filters {
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: var(--space-2);
        }
        
        .clean-controls-inner {
            flex-direction: column;
            align-items: stretch;
        }
        
        .clean-controls-left,
        .clean-controls-right {
            width: 100%;
            justify-content: space-between;
        }
        
        .clean-view-toggle {
            display: none;
        }
        
        .clean-grants-grid {
            grid-template-columns: 1fr;
        }
        
        /* Mobile Sidebar */
        .clean-sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--white);
            z-index: 1000;
            transition: left 0.3s ease;
            overflow-y: auto;
        }
        
        .clean-sidebar.active {
            left: 0;
        }
        
        .clean-filter-close {
            display: flex;
        }
    }
    
    @media (max-width: 480px) {
        .clean-header {
            padding: var(--space-6) 0;
        }
        
        .clean-title {
            font-size: 1.5rem;
        }
        
        .clean-subtitle {
            font-size: 0.9375rem;
        }
        
        .clean-search-input {
            padding: var(--space-3) var(--space-3) var(--space-3) 2.5rem;
            font-size: 0.9375rem;
        }
        
        .clean-search-icon {
            left: var(--space-3);
        }
        
        .clean-search-clear {
            right: var(--space-3);
        }
        
        .clean-filter-pill {
            font-size: 0.8125rem;
            padding: var(--space-2) var(--space-3);
        }
    }
    
    /* ===== LOADING OVERLAY ===== */
    .clean-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: var(--radius-lg);
    }
    
    .clean-loading-overlay .clean-spinner {
        width: 2rem;
        height: 2rem;
    }
    
    /* ===== UTILITY CLASSES ===== */
    .clean-hidden {
        display: none !important;
    }
    
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    
    .clean-filter-count-active {
        animation: pulse 0.5s ease-in-out;
    }
    
    /* ===== FILTER MORE BUTTON ===== */
    .clean-filter-more-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
        transition: var(--transition);
        padding: var(--space-2);
        border-radius: var(--radius-md);
        width: 100%;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: var(--primary);
        font-weight: 500;
    }
    
    .clean-filter-more-btn:hover {
        background: var(--gray-50);
        color: var(--primary-dark);
    }
    
    .clean-filter-more-item {
        transition: opacity 0.3s ease, max-height 0.3s ease;
    }
    
    .clean-filter-more-item.hidden {
        display: none;
    }
    
    /* ===== FOCUS STYLES ===== */
    button:focus-visible,
    input:focus-visible,
    select:focus-visible,
    a:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    
    /* ===== FILTER ACTIONS ===== */
    .clean-filter-actions {
        padding: var(--space-4) 0;
        border-top: 1px solid var(--gray-200);
        margin-top: var(--space-4);
    }
    
    .clean-reset-button {
        width: 100%;
        padding: var(--space-3) var(--space-4);
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
    }
    
    .clean-reset-button:hover {
        background: var(--gray-200);
        border-color: var(--gray-400);
    }
    
    .clean-reset-button:active {
        transform: translateY(1px);
    }
    
    /* ===== LOADING STATES ===== */
    .clean-filter-loading {
        opacity: 0.6;
        pointer-events: none;
        position: relative;
    }
    
    .clean-filter-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        border: 2px solid var(--gray-300);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translate(-50%, -50%);
    }
    
    /* ===== ENHANCED ANIMATIONS ===== */
    .clean-filter-pill {
        animation: fadeInUp 0.3s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .clean-grant-card {
        animation: slideIn 0.4s ease-out;
    }
    
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
    
    /* ===== ENHANCED RESPONSIVE DESIGN ===== */
    @media (max-width: 640px) {
        .clean-filters {
            gap: var(--space-2);
            padding: var(--space-2) 0;
        }
        
        .clean-filter-pill {
            font-size: 0.8125rem;
            padding: var(--space-2) var(--space-3);
        }
        
        .clean-filter-pill i {
            font-size: 0.6875rem;
        }
        
        .clean-controls-inner {
            gap: var(--space-3);
        }
    }
    
    /* ===== ACCESSIBILITY ENHANCEMENTS ===== */
    @media (prefers-reduced-motion: reduce) {
        .clean-filter-pill,
        .clean-grant-card,
        .clean-loading-overlay .clean-spinner {
            animation: none;
        }
        
        .clean-filter-pill::before {
            display: none;
        }
        
        * {
            transition: none !important;
        }
    }
    
    @media (prefers-color-scheme: dark) {
        :root {
            --gray-50: #0f0f0f;
            --gray-100: #1a1a1a;
            --gray-200: #2a2a2a;
            --gray-300: #3a3a3a;
            --white: #1f1f1f;
        }
    }
    
    /* ===== BOTTOM NAVIGATION HIDE ===== */
    .gi-bottom-nav,
    .floating-nav {
        display: none !important;
    }
    
    /* ===== FILTER COUNT INDICATOR ===== */
    .clean-filter-count {
        background: var(--primary);
        color: var(--white);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0 var(--space-2);
        border-radius: var(--radius-sm);
        min-width: 1.25rem;
        text-align: center;
        animation: pulse 0.5s ease-in-out;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .clean-filter-pill.active .clean-filter-count {
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* ===== FILTER LIST CONTAINER WITH SCROLL ===== */
    .clean-filter-list-container {
        max-height: 300px;
        overflow-y: auto;
        margin-bottom: var(--space-3);
        padding-right: var(--space-1);
    }
    
    .clean-filter-list-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .clean-filter-list-container::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: var(--radius-sm);
    }
    
    .clean-filter-list-container::-webkit-scrollbar-thumb {
        background: var(--gray-400);
        border-radius: var(--radius-sm);
        transition: var(--transition);
    }
    
    .clean-filter-list-container::-webkit-scrollbar-thumb:hover {
        background: var(--gray-600);
    }
    
    /* Firefox scrollbar */
    .clean-filter-list-container {
        scrollbar-width: thin;
        scrollbar-color: var(--gray-400) var(--gray-100);
    }
    
    /* Scroll fade indicators */
    .clean-filter-group {
        position: relative;
    }
    
    .clean-filter-group::after {
        content: '';
        position: absolute;
        bottom: 60px;
        left: 0;
        right: 20px;
        height: 20px;
        background: linear-gradient(transparent, var(--white));
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .clean-filter-group.has-scroll::after {
        opacity: 1;
    }
    
    /* Enhanced filter options for better scrolling */
    .clean-filter-option {
        padding: var(--space-3) var(--space-2);
        margin-bottom: 2px;
        border-radius: var(--radius-md);
    }
    
    .clean-filter-option:hover {
        background: var(--gray-50);
    }
    </style>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body <?php body_class('clean-archive-page'); ?>>

<!-- Header Section -->
<section class="clean-header">
    <div class="clean-container">
        <div class="clean-header-content">
            <h1 class="clean-title">助成金・補助金検索</h1>
            <p class="clean-subtitle">
                <?php 
                if (!empty($search_params['search']) || !empty($search_params['category']) || !empty($search_params['prefecture'])) {
                    echo '検索条件に該当する助成金・補助金を表示しています。最適な制度を見つけてビジネスを成長させましょう。';
                } else {
                    echo '全国の助成金・補助金を簡単検索。あなたにピッタリの制度を見つけてビジネスの成長を支援します。';
                }
                ?>
            </p>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="clean-search-section">
    <div class="clean-container">
        <!-- Search Box -->
        <div class="clean-search-wrapper">
            <div class="clean-search-box">
                <input type="text" 
                       id="clean-search-input" 
                       name="search" 
                       placeholder="助成金名、実施組織名、対象事業者などで検索..." 
                       class="clean-search-input"
                       value="<?php echo esc_attr($search_params['search']); ?>"
                       autocomplete="off">
                <i class="fas fa-search clean-search-icon"></i>
                <button type="button" id="clean-search-clear" class="clean-search-clear" <?php echo empty($search_params['search']) ? 'style="display:none"' : ''; ?>>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="clean-filters" role="group" aria-label="クイックフィルター">
            <button class="clean-filter-pill <?php echo empty($search_params['status']) && empty($search_params['is_featured']) && empty($search_params['amount']) ? 'active' : ''; ?>" data-filter="all" aria-pressed="<?php echo empty($search_params['status']) && empty($search_params['is_featured']) && empty($search_params['amount']) ? 'true' : 'false'; ?>">
                <i class="fas fa-list" aria-hidden="true"></i>
                すべて
            </button>
            <button class="clean-filter-pill <?php echo $search_params['is_featured'] === '1' ? 'active' : ''; ?>" data-filter="featured" aria-pressed="<?php echo $search_params['is_featured'] === '1' ? 'true' : 'false'; ?>">
                <i class="fas fa-star" aria-hidden="true"></i>
                おすすめ
            </button>
            <button class="clean-filter-pill <?php echo $search_params['status'] === 'active' ? 'active' : ''; ?>" data-filter="active" aria-pressed="<?php echo $search_params['status'] === 'active' ? 'true' : 'false'; ?>">
                <i class="fas fa-circle-dot" aria-hidden="true"></i>
                募集中
                <?php if ($stats['active_grants'] > 0): ?>
                <span class="clean-filter-count" aria-label="<?php echo $stats['active_grants']; ?>件"><?php echo $stats['active_grants']; ?></span>
                <?php endif; ?>
            </button>
            <button class="clean-filter-pill <?php echo $search_params['amount'] === '1000-3000' || $search_params['amount'] === '3000+' ? 'active' : ''; ?>" data-filter="large-amount" aria-pressed="<?php echo $search_params['amount'] === '1000-3000' || $search_params['amount'] === '3000+' ? 'true' : 'false'; ?>">
                <i class="fas fa-coins" aria-hidden="true"></i>
                高額助成金
            </button>
            <button class="clean-filter-pill <?php echo $search_params['amount'] === '0-100' ? 'active' : ''; ?>" data-filter="small-medium" aria-pressed="<?php echo $search_params['amount'] === '0-100' ? 'true' : 'false'; ?>">
                <i class="fas fa-piggy-bank" aria-hidden="true"></i>
                中小規模
            </button>
            <button class="clean-filter-pill" data-filter="upcoming" aria-pressed="false">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                募集予定
            </button>
            <button class="clean-filter-pill" data-filter="deadline-soon" aria-pressed="false">
                <i class="fas fa-clock" aria-hidden="true"></i>
                締切間近
            </button>
        </div>
    </div>
</section>

<!-- Controls Section -->
<section class="clean-controls">
    <div class="clean-container">
        <div class="clean-controls-inner">
            <div class="clean-controls-left">
                <select id="clean-sort-select" class="clean-select">
                    <option value="date_desc" <?php selected($search_params['sort'], 'date_desc'); ?>>新着順</option>
                    <option value="featured_first" <?php selected($search_params['sort'], 'featured_first'); ?>>おすすめ順</option>
                    <option value="amount_desc" <?php selected($search_params['sort'], 'amount_desc'); ?>>金額が高い順</option>
                    <option value="deadline_asc" <?php selected($search_params['sort'], 'deadline_asc'); ?>>締切が近い順</option>
                    <option value="success_rate_desc" <?php selected($search_params['sort'], 'success_rate_desc'); ?>>採択率順</option>
                </select>

                <button id="clean-filter-toggle" class="clean-filter-button" aria-expanded="false" aria-controls="clean-filter-sidebar">
                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                    詳細フィルター
                    <span id="clean-filter-count" class="clean-filter-count" style="display:none" aria-label="フィルター適用数">0</span>
                </button>
                
                <button id="clean-clear-all-filters" class="clean-filter-button" style="display:none" aria-label="すべてのフィルターをクリア">
                    <i class="fas fa-times" aria-hidden="true"></i>
                    クリア
                </button>
            </div>

            <div class="clean-controls-right">
                <div class="clean-view-toggle">
                    <button id="clean-grid-view" 
                            class="clean-view-btn <?php echo $search_params['view'] === 'grid' ? 'active' : ''; ?>" 
                            data-view="grid" 
                            title="グリッド表示">
                        <i class="fas fa-th"></i>
                    </button>
                    <button id="clean-list-view" 
                            class="clean-view-btn <?php echo $search_params['view'] === 'list' ? 'active' : ''; ?>" 
                            data-view="list" 
                            title="リスト表示">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="clean-main">
    <div class="clean-container">
        <div class="clean-layout">
            
            <!-- Sidebar Filters -->
            <aside id="clean-filter-sidebar" class="clean-sidebar" role="complementary" aria-labelledby="filter-title">
                <div class="clean-filter-card">
                    <div class="clean-filter-header">
                        <h3 id="filter-title" class="clean-filter-title">詳細フィルター</h3>
                        <button id="clean-filter-close" class="clean-filter-close" aria-label="フィルターパネルを閉じる">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="clean-filter-body">
                        
                        <!-- Special Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">特別条件</h4>
                            <label class="clean-filter-option">
                                <input type="checkbox" 
                                       name="is_featured" 
                                       value="1" 
                                       class="clean-filter-checkbox featured-checkbox"
                                       <?php checked($search_params['is_featured'], '1'); ?>>
                                <span class="clean-filter-label">おすすめの助成金のみ</span>
                            </label>
                        </div>
                        
                        <!-- Prefecture Filters -->
                        <?php if (!empty($all_prefectures) && !is_wp_error($all_prefectures)): ?>
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">対象地域</h4>
                            <div class="clean-filter-list-container">
                                <?php 
                                $prefecture_limit = 10;
                                $selected_prefectures = explode(',', $search_params['prefecture']);
                                $prefecture_count = count($all_prefectures);
                                
                                $has_selected = !empty(array_filter($selected_prefectures));
                                $show_all_initially = $has_selected;
                                
                                foreach ($all_prefectures as $index => $prefecture): 
                                    $is_selected = in_array($prefecture->slug, $selected_prefectures);
                                    $is_hidden = !$show_all_initially && $index >= $prefecture_limit;
                                ?>
                                <label class="clean-filter-option <?php echo $is_hidden ? 'clean-filter-more-item hidden' : ''; ?>">
                                    <input type="checkbox" 
                                           name="prefectures[]" 
                                           value="<?php echo esc_attr($prefecture->slug); ?>" 
                                           class="clean-filter-checkbox prefecture-checkbox"
                                           <?php checked($is_selected); ?>>
                                    <span class="clean-filter-label"><?php echo esc_html($prefecture->name); ?></span>
                                    <?php if ($prefecture->count > 0): ?>
                                    <span class="clean-filter-count"><?php echo esc_html($prefecture->count); ?></span>
                                    <?php endif; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($prefecture_count > $prefecture_limit): ?>
                            <button type="button" class="clean-filter-more-btn" data-target="prefecture">
                                <span class="show-more-text <?php echo $show_all_initially ? 'hidden' : ''; ?>">さらに表示 (+<?php echo $prefecture_count - $prefecture_limit; ?>)</span>
                                <span class="show-less-text <?php echo !$show_all_initially ? 'hidden' : ''; ?>">表示を減らす</span>
                                <i class="fas fa-chevron-down show-more-icon <?php echo $show_all_initially ? 'hidden' : ''; ?>"></i>
                                <i class="fas fa-chevron-up show-less-icon <?php echo !$show_all_initially ? 'hidden' : ''; ?>"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Category Filters -->
                        <?php if (!empty($all_categories) && !is_wp_error($all_categories)): ?>
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">カテゴリ</h4>
                            <div class="clean-filter-list-container">
                                <?php 
                                $category_limit = 8;
                                $selected_categories = explode(',', $search_params['category']);
                                $category_count = count($all_categories);
                                
                                $has_selected_cat = !empty(array_filter($selected_categories));
                                $show_all_cat_initially = $has_selected_cat;
                                
                                foreach ($all_categories as $index => $category): 
                                    $is_selected_cat = in_array($category->slug, $selected_categories);
                                    $is_hidden = !$show_all_cat_initially && $index >= $category_limit;
                                ?>
                                <label class="clean-filter-option <?php echo $is_hidden ? 'clean-filter-more-item hidden' : ''; ?>">
                                    <input type="checkbox" 
                                           name="categories[]" 
                                           value="<?php echo esc_attr($category->slug); ?>" 
                                           class="clean-filter-checkbox category-checkbox"
                                           <?php checked($is_selected_cat); ?>>
                                    <span class="clean-filter-label"><?php echo esc_html($category->name); ?></span>
                                    <?php if ($category->count > 0): ?>
                                    <span class="clean-filter-count"><?php echo esc_html($category->count); ?></span>
                                    <?php endif; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($category_count > $category_limit): ?>
                            <button type="button" class="clean-filter-more-btn" data-target="category">
                                <span class="show-more-text <?php echo $show_all_cat_initially ? 'hidden' : ''; ?>">さらに表示 (+<?php echo $category_count - $category_limit; ?>)</span>
                                <span class="show-less-text <?php echo !$show_all_cat_initially ? 'hidden' : ''; ?>">表示を減らす</span>
                                <i class="fas fa-chevron-down show-more-icon <?php echo $show_all_cat_initially ? 'hidden' : ''; ?>"></i>
                                <i class="fas fa-chevron-up show-less-icon <?php echo !$show_all_cat_initially ? 'hidden' : ''; ?>"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Amount Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">助成金額</h4>
                            <?php
                            $amount_ranges = [
                                '' => 'すべての金額',
                                '0-100' => '〜100万円',
                                '100-500' => '100〜500万円',
                                '500-1000' => '500〜1000万円',
                                '1000-3000' => '1000〜3000万円',
                                '3000+' => '3000万円以上'
                            ];
                            foreach ($amount_ranges as $value => $label):
                            ?>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="radio" 
                                       name="amount" 
                                       value="<?php echo esc_attr($value); ?>" 
                                       class="clean-filter-radio amount-radio"
                                       <?php checked($search_params['amount'], $value); ?>
                                       aria-describedby="amount-<?php echo esc_attr($value ?: 'all'); ?>-desc">
                                <span class="clean-filter-label" id="amount-<?php echo esc_attr($value ?: 'all'); ?>-desc"><?php echo esc_html($label); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Status Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">募集状況</h4>
                            <?php
                            $status_options = [
                                'active' => '募集中',
                                'upcoming' => '募集予定',
                                'closed' => '募集終了'
                            ];
                            foreach ($status_options as $value => $label):
                                $selected_statuses = explode(',', $search_params['status']);
                                $is_selected = in_array($value, $selected_statuses);
                            ?>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="status[]" 
                                       value="<?php echo esc_attr($value); ?>" 
                                       class="clean-filter-checkbox status-checkbox"
                                       <?php checked($is_selected); ?>
                                       aria-describedby="status-<?php echo esc_attr($value); ?>-desc">
                                <span class="clean-filter-label" id="status-<?php echo esc_attr($value); ?>-desc"><?php echo esc_html($label); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Application Method Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">申請方法</h4>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="application_method[]" 
                                       value="online" 
                                       class="clean-filter-checkbox method-checkbox"
                                       aria-describedby="method-online-desc">
                                <span class="clean-filter-label" id="method-online-desc">
                                    <i class="fas fa-laptop" aria-hidden="true"></i>
                                    オンライン申請
                                </span>
                            </label>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="application_method[]" 
                                       value="mail" 
                                       class="clean-filter-checkbox method-checkbox"
                                       aria-describedby="method-mail-desc">
                                <span class="clean-filter-label" id="method-mail-desc">
                                    <i class="fas fa-envelope" aria-hidden="true"></i>
                                    郵送申請
                                </span>
                            </label>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="application_method[]" 
                                       value="direct" 
                                       class="clean-filter-checkbox method-checkbox"
                                       aria-describedby="method-direct-desc">
                                <span class="clean-filter-label" id="method-direct-desc">
                                    <i class="fas fa-building" aria-hidden="true"></i>
                                    持参申請
                                </span>
                            </label>
                        </div>

                        <!-- Reset Filters -->
                        <div class="clean-filter-actions">
                            <button type="button" id="clean-reset-filters" class="clean-reset-button">
                                <i class="fas fa-undo" aria-hidden="true"></i>
                                フィルターをリセット
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="clean-content">
                <!-- Results Header -->
                <div class="clean-results-header">
                    <div class="clean-results-info">
                        <span id="clean-results-count" class="clean-results-number"><?php echo number_format($grants_query->found_posts); ?></span>
                        <span class="clean-results-text">件の助成金</span>
                    </div>
                    <div id="clean-loading" class="clean-loading-indicator clean-hidden">
                        <div class="clean-spinner"></div>
                        <span>更新中...</span>
                    </div>
                </div>

                <!-- Grants Container -->
                <div id="clean-grants-container" class="clean-grants-container">
                    <div id="clean-grants-display">
                        <?php if ($grants_query->have_posts()): ?>
                            <div class="<?php echo $search_params['view'] === 'grid' ? 'clean-grants-grid' : 'clean-grants-list'; ?>">
                                <?php
                                while ($grants_query->have_posts()):
                                    $grants_query->the_post();
                                    
                                    $GLOBALS['current_view'] = $search_params['view'];
                                    $GLOBALS['user_favorites'] = $user_favorites;
                                    
                                    get_template_part('template-parts/grant-card-unified');
                                endwhile;
                                ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($grants_query->max_num_pages > 1): ?>
                            <div class="clean-pagination">
                                <?php
                                $pagination_args = [
                                    'total' => $grants_query->max_num_pages,
                                    'current' => max(1, $search_params['page']),
                                    'format' => '?paged=%#%',
                                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                    'next_text' => '<i class="fas fa-chevron-right"></i>',
                                    'type' => 'array',
                                    'end_size' => 2,
                                    'mid_size' => 2
                                ];
                                
                                $pagination_links = paginate_links($pagination_args);
                                if ($pagination_links) {
                                    foreach ($pagination_links as $link) {
                                        $link = str_replace('class="page-numbers', 'class="clean-page-btn page-numbers', $link);
                                        $link = str_replace('class="clean-page-btn page-numbers current', 'class="clean-page-btn page-numbers current', $link);
                                        echo $link;
                                    }
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="clean-no-results">
                                <div class="clean-no-results-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3 class="clean-no-results-title">該当する助成金が見つかりませんでした</h3>
                                <p class="clean-no-results-text">検索条件を変更して再度お試しください。</p>
                                <button id="clean-reset-search" class="clean-reset-button">
                                    検索をリセット
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<!-- JavaScript -->
<script>
/**
 * Clean Grant Archive JavaScript
 * シンプルでクリーンなデザイン用の機能
 */
(function() {
    'use strict';
    
    // Configuration
    const config = {
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('gi_ajax_nonce'); ?>',
        debounceDelay: 300,
        searchDelay: 500
    };
    
    // State Management
    const state = {
        currentView: '<?php echo $search_params['view']; ?>',
        currentPage: <?php echo $search_params['page']; ?>,
        isLoading: false,
        filters: {
            search: '<?php echo esc_js($search_params['search']); ?>',
            categories: <?php echo json_encode(array_filter(explode(',', $search_params['category']))); ?>,
            prefectures: <?php echo json_encode(array_filter(explode(',', $search_params['prefecture']))); ?>,
            amount: '<?php echo esc_js($search_params['amount']); ?>',
            status: <?php echo json_encode(array_filter(explode(',', $search_params['status']))); ?>,
            is_featured: '<?php echo esc_js($search_params['is_featured']); ?>',
            sort: '<?php echo esc_js($search_params['sort']); ?>'
        }
    };
    
    // DOM Elements
    const elements = {};
    
    // Timers
    let debounceTimer = null;
    let searchTimer = null;
    
    /**
     * Initialize
     */
    function init() {
        cacheElements();
        bindEvents();
        updateFilterCount();
        initializeCardInteractions();
    }
    
    /**
     * Cache DOM elements
     */
    function cacheElements() {
        const ids = [
            'clean-search-input', 'clean-search-clear',
            'clean-sort-select', 'clean-filter-toggle', 'clean-filter-sidebar',
            'clean-filter-close', 'clean-grid-view', 'clean-list-view',
            'clean-reset-search', 'clean-results-count', 'clean-loading',
            'clean-grants-container', 'clean-grants-display', 'clean-filter-count'
        ];
        
        ids.forEach(id => {
            elements[id.replace(/-/g, '_')] = document.getElementById(id);
        });
        
        elements.quickFilters = document.querySelectorAll('.clean-filter-pill');
        elements.filterCheckboxes = document.querySelectorAll('.clean-filter-checkbox');
        elements.filterRadios = document.querySelectorAll('.clean-filter-radio');
    }
    
    /**
     * Bind events
     */
    function bindEvents() {
        // Search
        if (elements.clean_search_input) {
            elements.clean_search_input.addEventListener('input', handleSearchInput);
            elements.clean_search_input.addEventListener('keypress', handleSearchKeypress);
        }
        
        if (elements.clean_search_clear) {
            elements.clean_search_clear.addEventListener('click', handleSearchClear);
        }
        
        // Sort
        if (elements.clean_sort_select) {
            elements.clean_sort_select.addEventListener('change', handleSortChange);
        }
        
        // Filter toggle
        if (elements.clean_filter_toggle) {
            elements.clean_filter_toggle.addEventListener('click', toggleFilterSidebar);
        }
        
        if (elements.clean_filter_close) {
            elements.clean_filter_close.addEventListener('click', closeFilterSidebar);
        }
        
        // View switcher
        if (elements.clean_grid_view) {
            elements.clean_grid_view.addEventListener('click', () => switchView('grid'));
        }
        
        if (elements.clean_list_view) {
            elements.clean_list_view.addEventListener('click', () => switchView('list'));
        }
        
        // Quick filters
        elements.quickFilters.forEach(filter => {
            filter.addEventListener('click', handleQuickFilter);
        });
        
        // Filter inputs
        [...elements.filterCheckboxes, ...elements.filterRadios].forEach(input => {
            input.addEventListener('change', handleFilterChange);
        });
        
        // Reset
        if (elements.clean_reset_search) {
            elements.clean_reset_search.addEventListener('click', resetAllFilters);
        }
        
        // Clear all filters button
        const clearAllBtn = document.getElementById('clean-clear-all-filters');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', resetAllFilters);
        }
        
        // Reset filters button in sidebar
        const resetFiltersBtn = document.getElementById('clean-reset-filters');
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', resetAllFilters);
        }
        
        // Pagination
        document.addEventListener('click', handlePaginationClick);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', handleKeyboardShortcuts);
        
        // More filters toggle
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.clean-filter-more-btn');
            if (btn) {
                handleMoreFiltersToggle(e);
            }
        });
        
        // Initialize scroll indicators
        initScrollIndicators();
    }
    
    /**
     * Handle search input
     */
    function handleSearchInput(e) {
        state.filters.search = e.target.value;
        elements.clean_search_clear.style.display = e.target.value ? 'block' : 'none';
        
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            loadGrants();
        }, config.searchDelay);
    }
    
    /**
     * Handle search keypress
     */
    function handleSearchKeypress(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadGrants();
        }
    }
    
    /**
     * Handle search clear
     */
    function handleSearchClear() {
        elements.clean_search_input.value = '';
        elements.clean_search_clear.style.display = 'none';
        state.filters.search = '';
        loadGrants();
    }
    
    /**
     * Handle sort change
     */
    function handleSortChange(e) {
        state.filters.sort = e.target.value;
        state.currentPage = 1;
        loadGrants();
    }
    
    /**
     * Handle quick filter
     */
    function handleQuickFilter(e) {
        const filter = e.currentTarget.dataset.filter;
        
        elements.quickFilters.forEach(f => f.classList.remove('active'));
        e.currentTarget.classList.add('active');
        
        resetFiltersState();
        
        switch(filter) {
            case 'featured':
                state.filters.is_featured = '1';
                break;
            case 'active':
                state.filters.status = ['active'];
                break;
            case 'large-amount':
                state.filters.amount = '1000-3000';
                break;
            case 'small-medium':
                state.filters.amount = '0-100';
                break;
            case 'upcoming':
                state.filters.status = ['upcoming'];
                break;
            case 'deadline-soon':
                // 締切間近のロジックを追加（例：1週間以内）
                state.filters.deadline_range = '1week';
                break;
            default:
                break;
        }
        
        state.currentPage = 1;
        updateFilterCount();
        loadGrants();
    }
    
    /**
     * Handle filter change
     */
    function handleFilterChange() {
        updateFiltersFromForm();
        updateFilterCount();
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.currentPage = 1;
            loadGrants();
        }, config.debounceDelay);
    }
    
    /**
     * Update filters from form
     */
    function updateFiltersFromForm() {
        state.filters.categories = Array.from(
            document.querySelectorAll('.category-checkbox:checked')
        ).map(cb => cb.value);
        
        state.filters.prefectures = Array.from(
            document.querySelectorAll('.prefecture-checkbox:checked')
        ).map(cb => cb.value);
        
        const featuredCheckbox = document.querySelector('.featured-checkbox:checked');
        state.filters.is_featured = featuredCheckbox ? '1' : '';
        
        const amountRadio = document.querySelector('.amount-radio:checked');
        state.filters.amount = amountRadio ? amountRadio.value : '';
    }
    
    /**
     * Update filter count
     */
    function updateFilterCount() {
        const count = 
            state.filters.categories.length +
            state.filters.prefectures.length +
            (state.filters.amount ? 1 : 0) +
            state.filters.status.length +
            (state.filters.is_featured ? 1 : 0);
        
        if (elements.clean_filter_count) {
            elements.clean_filter_count.textContent = count;
            elements.clean_filter_count.style.display = count > 0 ? 'inline-block' : 'none';
            elements.clean_filter_count.setAttribute('aria-label', `${count}個のフィルターが適用中`);
        }
        
        if (elements.clean_filter_toggle) {
            elements.clean_filter_toggle.classList.toggle('has-filters', count > 0);
            elements.clean_filter_toggle.setAttribute('aria-expanded', 
                elements.clean_filter_sidebar?.classList.contains('active') ? 'true' : 'false');
        }
        
        // Clear all button visibility
        const clearAllBtn = document.getElementById('clean-clear-all-filters');
        if (clearAllBtn) {
            clearAllBtn.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        
        // Add visual feedback
        if (count > 0) {
            elements.clean_filter_count?.classList.add('clean-filter-count-active');
        } else {
            elements.clean_filter_count?.classList.remove('clean-filter-count-active');
        }
    }
    
    /**
     * Switch view
     */
    function switchView(view) {
        if (state.currentView === view) return;
        
        state.currentView = view;
        
        elements.clean_grid_view.classList.toggle('active', view === 'grid');
        elements.clean_list_view.classList.toggle('active', view === 'list');
        
        loadGrants();
    }
    
    /**
     * Toggle filter sidebar
     */
    function toggleFilterSidebar() {
        const isActive = elements.clean_filter_sidebar.classList.contains('active');
        
        if (isActive) {
            closeFilterSidebar();
        } else {
            elements.clean_filter_sidebar.classList.add('active');
            elements.clean_filter_toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            
            // フォーカス管理
            const firstInput = elements.clean_filter_sidebar.querySelector('input, button');
            if (firstInput) {
                firstInput.focus();
            }
        }
    }
    
    /**
     * Close filter sidebar
     */
    function closeFilterSidebar() {
        elements.clean_filter_sidebar.classList.remove('active');
        elements.clean_filter_toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        
        // フォーカスを戻す
        elements.clean_filter_toggle.focus();
    }
    
    /**
     * Reset all filters
     */
    function resetAllFilters() {
        resetFiltersState();
        
        elements.filterCheckboxes.forEach(cb => cb.checked = false);
        elements.filterRadios.forEach(rb => rb.checked = rb.value === '');
        
        elements.quickFilters.forEach(f => f.classList.remove('active'));
        document.querySelector('.clean-filter-pill[data-filter="all"]')?.classList.add('active');
        
        if (elements.clean_search_input) {
            elements.clean_search_input.value = '';
        }
        if (elements.clean_search_clear) {
            elements.clean_search_clear.style.display = 'none';
        }
        
        state.currentPage = 1;
        updateFilterCount();
        loadGrants();
    }
    
    /**
     * Reset filters state
     */
    function resetFiltersState() {
        state.filters = {
            search: '',
            categories: [],
            prefectures: [],
            amount: '',
            status: [],
            is_featured: '',
            sort: state.filters.sort
        };
    }
    
    /**
     * Handle pagination
     */
    function handlePaginationClick(e) {
        if (e.target.classList.contains('clean-page-btn') || e.target.closest('.clean-page-btn')) {
            e.preventDefault();
            
            const btn = e.target.classList.contains('clean-page-btn') ? e.target : e.target.closest('.clean-page-btn');
            const href = btn.getAttribute('href');
            
            if (href) {
                const url = new URL(href, window.location.origin);
                const page = parseInt(url.searchParams.get('paged')) || 1;
                
                if (page !== state.currentPage) {
                    state.currentPage = page;
                    loadGrants();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }
    }
    
    /**
     * Handle keyboard shortcuts
     */
    function handleKeyboardShortcuts(e) {
        if (e.key === 'Escape') {
            closeFilterSidebar();
        }
    }
    
    /**
     * Load grants via AJAX
     */
    async function loadGrants() {
        if (state.isLoading) return;
        
        state.isLoading = true;
        showLoading();
        
        try {
            const response = await fetch(config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'gi_load_grants',
                    nonce: config.nonce,
                    search: state.filters.search,
                    categories: JSON.stringify(state.filters.categories),
                    prefectures: JSON.stringify(state.filters.prefectures),
                    amount: state.filters.amount,
                    status: JSON.stringify(state.filters.status),
                    only_featured: state.filters.is_featured,
                    sort: state.filters.sort,
                    view: state.currentView,
                    page: state.currentPage
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                renderGrants(data.data);
                updateURL();
            } else {
                showNoResults();
            }
        } catch (error) {
            console.error('Error loading grants:', error);
            showError();
        } finally {
            state.isLoading = false;
            hideLoading();
        }
    }
    
    /**
     * Render grants
     */
    function renderGrants(data) {
        const { grants, pagination, stats } = data;
        
        if (elements.clean_results_count) {
            elements.clean_results_count.textContent = stats?.total_found ? number_format(stats.total_found) : '0';
        }
        
        if (grants && grants.length > 0) {
            const containerClass = state.currentView === 'grid' ? 'clean-grants-grid' : 'clean-grants-list';
            elements.clean_grants_display.innerHTML = `
                <div class="${containerClass}">
                    ${grants.map(grant => grant.html).join('')}
                </div>
            `;
            
            initializeCardInteractions();
        } else {
            showNoResults();
        }
    }
    
    /**
     * Initialize card interactions
     */
    function initializeCardInteractions() {
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', handleFavoriteClick);
        });
    }
    
    /**
     * Handle favorite click
     */
    async function handleFavoriteClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = e.currentTarget;
        const postId = btn.dataset.postId;
        
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';
        
        try {
            const response = await fetch(config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'gi_toggle_favorite',
                    nonce: config.nonce,
                    post_id: postId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                btn.textContent = data.data.is_favorite ? '♥' : '♡';
                btn.style.color = data.data.is_favorite ? '#dc2626' : '#6b7280';
                
                showNotification(data.data.message, 'success');
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
            showNotification('エラーが発生しました', 'error');
        } finally {
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
    }
    
    /**
     * Show loading
     */
    function showLoading() {
        if (elements.clean_loading) {
            elements.clean_loading.classList.remove('clean-hidden');
        }
        
        const container = elements.clean_grants_container;
        if (container && !container.querySelector('.clean-loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'clean-loading-overlay';
            overlay.innerHTML = `
                <div class="clean-spinner" role="status" aria-label="検索中"></div>
                <span class="sr-only">助成金を検索しています...</span>
            `;
            container.appendChild(overlay);
        }
        
        // フィルターボタンにローディング状態を追加
        elements.quickFilters.forEach(btn => {
            btn.classList.add('clean-filter-loading');
        });
    }
    
    /**
     * Hide loading
     */
    function hideLoading() {
        if (elements.clean_loading) {
            elements.clean_loading.classList.add('clean-hidden');
        }
        
        const overlay = document.querySelector('.clean-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
        
        // フィルターボタンからローディング状態を削除
        elements.quickFilters.forEach(btn => {
            btn.classList.remove('clean-filter-loading');
        });
    }
    
    /**
     * Show no results
     */
    function showNoResults() {
        elements.clean_grants_display.innerHTML = `
            <div class="clean-no-results">
                <div class="clean-no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="clean-no-results-title">該当する助成金が見つかりませんでした</h3>
                <p class="clean-no-results-text">検索条件を変更して再度お試しください。</p>
                <button class="clean-reset-button" onclick="CleanGrants.resetAllFilters()">
                    検索をリセット
                </button>
            </div>
        `;
    }
    
    /**
     * Show error
     */
    function showError() {
        elements.clean_grants_display.innerHTML = `
            <div class="clean-no-results">
                <div class="clean-no-results-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="clean-no-results-title">エラーが発生しました</h3>
                <p class="clean-no-results-text">しばらく時間をおいて再度お試しください</p>
                <button class="clean-reset-button" onclick="window.location.reload()">
                    ページを再読み込み
                </button>
            </div>
        `;
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: ${type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#000000'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05);
            z-index: 10000;
            font-weight: 500;
            max-width: 300px;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    /**
     * Update URL
     */
    function updateURL() {
        const params = new URLSearchParams();
        
        if (state.filters.search) params.set('s', state.filters.search);
        if (state.filters.categories.length) params.set('category', state.filters.categories.join(','));
        if (state.filters.prefectures.length) params.set('prefecture', state.filters.prefectures.join(','));
        if (state.filters.amount) params.set('amount', state.filters.amount);
        if (state.filters.status.length) params.set('status', state.filters.status.join(','));
        if (state.filters.is_featured) params.set('featured', '1');
        if (state.filters.sort !== 'date_desc') params.set('sort', state.filters.sort);
        if (state.currentView !== 'grid') params.set('view', state.currentView);
        if (state.currentPage > 1) params.set('paged', state.currentPage);
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newURL);
    }
    
    /**
     * Handle more filters toggle
     */
    function handleMoreFiltersToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = e.target.closest('.clean-filter-more-btn');
        if (!btn) return;
        
        const filterGroup = btn.closest('.clean-filter-group');
        if (!filterGroup) return;
        
        const moreItems = filterGroup.querySelectorAll('.clean-filter-more-item');
        const showMoreText = btn.querySelector('.show-more-text');
        const showLessText = btn.querySelector('.show-less-text');
        const showMoreIcon = btn.querySelector('.show-more-icon');
        const showLessIcon = btn.querySelector('.show-less-icon');
        
        const isCollapsed = showMoreText && !showMoreText.classList.contains('hidden');
        
        if (isCollapsed) {
            moreItems.forEach(item => item.classList.remove('hidden'));
            if (showMoreText) showMoreText.classList.add('hidden');
            if (showLessText) showLessText.classList.remove('hidden');
            if (showMoreIcon) showMoreIcon.classList.add('hidden');
            if (showLessIcon) showLessIcon.classList.remove('hidden');
        } else {
            moreItems.forEach(item => item.classList.add('hidden'));
            if (showMoreText) showMoreText.classList.remove('hidden');
            if (showLessText) showLessText.classList.add('hidden');
            if (showMoreIcon) showMoreIcon.classList.remove('hidden');
            if (showLessIcon) showLessIcon.classList.add('hidden');
        }
    }
    
    /**
     * Initialize scroll indicators for filter lists
     */
    function initScrollIndicators() {
        document.querySelectorAll('.clean-filter-list-container').forEach(container => {
            const filterGroup = container.closest('.clean-filter-group');
            
            function updateScrollIndicator() {
                const hasScroll = container.scrollHeight > container.clientHeight;
                const isScrolledToBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 5;
                
                if (filterGroup) {
                    filterGroup.classList.toggle('has-scroll', hasScroll && !isScrolledToBottom);
                }
            }
            
            // Initial check
            updateScrollIndicator();
            
            // Update on scroll
            container.addEventListener('scroll', updateScrollIndicator);
            
            // Update on resize
            window.addEventListener('resize', updateScrollIndicator);
            
            // Update when filter items are shown/hidden
            const observer = new MutationObserver(updateScrollIndicator);
            observer.observe(container, { 
                childList: true, 
                subtree: true, 
                attributes: true, 
                attributeFilter: ['class', 'style'] 
            });
        });
    }
    
    /**
     * Format number
     */
    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    // Public API
    window.CleanGrants = {
        resetAllFilters,
        loadGrants,
        switchView
    };
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

<?php get_footer(); ?>