<?php
/**
 * Ultra Modern Categories Section - Monochrome Professional Edition
 * カテゴリー別助成金検索セクション - モノクローム・プロフェッショナル版
 *
 * @package Grant_Insight_Perfect
 * @version 22.0-monochrome
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// functions.phpとの連携確認
if (!function_exists('gi_get_acf_field_safely')) {
    require_once get_template_directory() . '/inc/4-helper-functions.php';
}

// データベースから実際のカテゴリと件数を取得
$main_categories = get_terms(array(
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 6
));

$all_categories = get_terms(array(
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));

$prefectures = get_terms(array(
    'taxonomy' => 'grant_prefecture',
    'hide_empty' => false,
    'orderby' => 'count',
    'order' => 'DESC'
));

// カテゴリアイコンとカラー設定（モノクローム版）
$category_configs = array(
    0 => array(
        'icon' => 'fas fa-laptop-code',
        'gradient' => 'from-gray-900 to-black',
        'description' => 'IT導入・DX推進・デジタル化支援'
    ),
    1 => array(
        'icon' => 'fas fa-industry',
        'gradient' => 'from-black to-gray-900',
        'description' => 'ものづくり・製造業支援'
    ),
    2 => array(
        'icon' => 'fas fa-rocket',
        'gradient' => 'from-gray-800 to-black',
        'description' => '創業・スタートアップ支援'
    ),
    3 => array(
        'icon' => 'fas fa-store',
        'gradient' => 'from-black to-gray-800',
        'description' => '小規模事業者・商業支援'
    ),
    4 => array(
        'icon' => 'fas fa-leaf',
        'gradient' => 'from-gray-900 to-gray-700',
        'description' => '環境・省エネ・SDGs支援'
    ),
    5 => array(
        'icon' => 'fas fa-users',
        'gradient' => 'from-gray-700 to-black',
        'description' => '人材育成・雇用支援'
    )
);

$archive_base_url = get_post_type_archive_link('grant');

// 統計情報を取得（functions.phpから）
if (function_exists('gi_get_cached_stats')) {
    $stats = gi_get_cached_stats();
} else {
    $stats = array(
        'total_grants' => wp_count_posts('grant')->publish ?? 0,
        'active_grants' => 0,
        'prefecture_count' => count($prefectures)
    );
}
?>

<!-- フォント・アイコン読み込み -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+JP:wght@300;400;500;700;900&display=swap" rel="stylesheet">

<!-- モノクローム・カテゴリーセクション -->
<section class="monochrome-categories" id="grant-categories">
    <!-- 背景エフェクト -->
    <div class="background-effects">
        <div class="grid-pattern"></div>
        <div class="gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <div class="section-container">
        <!-- セクションヘッダー -->
        <div class="section-header" data-aos="fade-up">
            <div class="header-accent"></div>
            
            <h2 class="section-title">
                <span class="title-en">CATEGORY SEARCH</span>
                <span class="title-ja">カテゴリーから探す</span>
                <div class="yellow-marker"></div>
            </h2>
            
            <p class="section-description">
                業種・目的別に最適な助成金を簡単検索
            </p>
        </div>

        <!-- メインカテゴリーグリッド -->
        <div class="main-categories-grid">
            <?php
            if (!empty($main_categories)) :
                foreach ($main_categories as $index => $category) :
                    if ($index >= 6) break;
                    $config = $category_configs[$index] ?? array(
                        'icon' => 'fas fa-folder',
                        'gradient' => 'from-gray-800 to-black',
                        'description' => ''
                    );
                    $category_url = add_query_arg('category', $category->slug, $archive_base_url);
                    
                    // カテゴリーの最新投稿を取得
                    $recent_grants = get_posts(array(
                        'post_type' => 'grant',
                        'posts_per_page' => 3,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'grant_category',
                                'field' => 'term_id',
                                'terms' => $category->term_id
                            )
                        )
                    ));
            ?>
            <div class="category-card" 
                 data-aos="fade-up" 
                 data-aos-delay="<?php echo $index * 50; ?>"
                 data-category="<?php echo esc_attr($category->slug); ?>">
                
                <div class="card-inner">
                    <!-- グラデーションボーダー -->
                    <div class="card-border"></div>
                    
                    <!-- カードコンテンツ -->
                    <div class="card-content">
                        <!-- アイコンとタイトル -->
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="<?php echo esc_attr($config['icon']); ?>"></i>
                            </div>
                            <div class="card-badge">
                                <span class="badge-count"><?php echo number_format($category->count); ?></span>
                                <span class="badge-label">件</span>
                            </div>
                        </div>
                        
                        <h3 class="card-title"><?php echo esc_html($category->name); ?></h3>
                        
                        <?php if ($config['description']): ?>
                        <p class="card-description"><?php echo esc_html($config['description']); ?></p>
                        <?php endif; ?>
                        
                        <!-- 最新の助成金プレビュー -->
                        <?php if (!empty($recent_grants)): ?>
                        <div class="recent-grants">
                            <div class="recent-grants-label">最新の助成金</div>
                            <?php foreach ($recent_grants as $grant): 
                                $amount = gi_safe_get_meta($grant->ID, 'max_amount', '');
                            ?>
                            <a href="<?php echo esc_url(get_permalink($grant->ID)); ?>" class="recent-grant-item" target="_blank">
                                <span class="grant-title"><?php echo esc_html(mb_substr($grant->post_title, 0, 20)); ?>...</span>
                                <?php if ($amount): ?>
                                <span class="grant-amount"><?php echo esc_html($amount); ?></span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- アクションボタン -->
                        <a href="<?php echo esc_url($category_url); ?>" class="card-link">
                            <span class="link-text">詳細を見る</span>
                            <span class="link-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </a>
                    </div>
                    
                    <!-- ホバーエフェクト -->
                    <div class="hover-effect"></div>
                </div>
            </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <!-- その他のカテゴリー -->
        <?php if (!empty($all_categories) && count($all_categories) > 6) :
            $other_categories = array_slice($all_categories, 6);
        ?>
        <div class="other-categories-section" data-aos="fade-up">
            <button type="button" id="toggle-categories" class="toggle-button">
                <span class="toggle-icon">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="toggle-text">その他のカテゴリーを表示</span>
                <span class="count-badge"><?php echo count($other_categories); ?></span>
            </button>

            <div id="other-categories" class="other-categories-container">
                <div class="categories-grid">
                    <?php foreach ($other_categories as $category) :
                        $category_url = add_query_arg('category', $category->slug, $archive_base_url);
                    ?>
                    <a href="<?php echo esc_url($category_url); ?>" class="mini-category-card">
                        <div class="mini-card-inner">
                            <i class="fas fa-folder mini-icon"></i>
                            <span class="mini-title"><?php echo esc_html($category->name); ?></span>
                            <span class="mini-count"><?php echo $category->count; ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 地域別検索 -->
        <div class="region-section" data-aos="fade-up">
            <div class="region-header">
                <h3 class="region-title">
                    <span class="title-en">REGIONAL SEARCH</span>
                    <span class="title-ja">地域から探す</span>
                    <div class="yellow-marker"></div>
                </h3>
                <p class="region-description">
                    47都道府県から助成金を検索
                </p>
            </div>

            <!-- デバッグ情報パネル（管理者のみ） -->
            <?php if (current_user_can('manage_options') && (isset($_GET['debug_counts']) || defined('WP_DEBUG') && WP_DEBUG)) : ?>
            <div class="debug-panel" style="background:#f0f0f0;border:1px solid #ccc;padding:15px;margin:20px 0;border-radius:8px;">
                <h4 style="margin-top:0;">🔧 Prefecture Counts Debug Info (管理者のみ)</h4>
                <?php
                $total_grants = wp_count_posts('grant')->publish;
                $cache_status = get_transient('gi_prefecture_counts_v2') !== false ? 'キャッシュ有り' : 'キャッシュ無し';
                $prefectures_with_posts = count(array_filter($prefecture_counts));
                ?>
                <p><strong>総助成金投稿数:</strong> <?php echo $total_grants; ?></p>
                <p><strong>キャッシュ状態:</strong> <?php echo $cache_status; ?></p>
                <p><strong>投稿のある都道府県数:</strong> <?php echo $prefectures_with_posts; ?> / <?php echo count($all_prefectures); ?></p>
                <p>
                    <a href="<?php echo add_query_arg('refresh_counts', '1'); ?>" style="background:#007cba;color:white;padding:5px 10px;text-decoration:none;border-radius:3px;">
                        🔄 カウンターを強制更新
                    </a>
                    <a href="<?php echo remove_query_arg(array('debug_counts', 'refresh_counts')); ?>" style="background:#666;color:white;padding:5px 10px;text-decoration:none;border-radius:3px;margin-left:10px;">
                        ❌ デバッグを閉じる
                    </a>
                </p>
                <?php if ($prefectures_with_posts > 0) : ?>
                <details style="margin-top:10px;">
                    <summary style="cursor:pointer;font-weight:bold;">投稿のある都道府県一覧</summary>
                    <div style="margin-top:10px;max-height:200px;overflow-y:auto;">
                        <?php foreach ($prefecture_counts as $slug => $count) : if ($count > 0) : ?>
                            <span style="display:inline-block;background:#e1f5fe;padding:3px 8px;margin:2px;border-radius:3px;font-size:12px;">
                                <?php
                                $pref_data = array_filter($all_prefectures, function($p) use ($slug) { return $p['slug'] === $slug; });
                                $pref_name = !empty($pref_data) ? array_values($pref_data)[0]['name'] : $slug;
                                echo $pref_name . ': ' . $count;
                                ?>
                            </span>
                        <?php endif; endforeach; ?>
                    </div>
                </details>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="regions-container">
                <!-- 左側：47都道府県リスト -->
                <div class="all-prefectures-container">
                    <h4 class="prefecture-list-title">都道府県一覧</h4>
                    <div class="prefecture-list">
                        <?php
                        // 47都道府県の完全なリストを関数から取得
                        if (function_exists('gi_get_all_prefectures')) {
                            $all_prefectures = gi_get_all_prefectures();
                        } else {
                            // フォールバック
                            $all_prefectures = array(
                                array('name' => '北海道', 'slug' => 'hokkaido', 'region' => 'hokkaido'),
                            array('name' => '青森県', 'slug' => 'aomori', 'region' => 'tohoku'),
                            array('name' => '岩手県', 'slug' => 'iwate', 'region' => 'tohoku'),
                            array('name' => '宮城県', 'slug' => 'miyagi', 'region' => 'tohoku'),
                            array('name' => '秋田県', 'slug' => 'akita', 'region' => 'tohoku'),
                            array('name' => '山形県', 'slug' => 'yamagata', 'region' => 'tohoku'),
                            array('name' => '福島県', 'slug' => 'fukushima', 'region' => 'tohoku'),
                            // 関東
                            array('name' => '茨城県', 'slug' => 'ibaraki', 'region' => 'kanto'),
                            array('name' => '栃木県', 'slug' => 'tochigi', 'region' => 'kanto'),
                            array('name' => '群馬県', 'slug' => 'gunma', 'region' => 'kanto'),
                            array('name' => '埼玉県', 'slug' => 'saitama', 'region' => 'kanto'),
                            array('name' => '千葉県', 'slug' => 'chiba', 'region' => 'kanto'),
                            array('name' => '東京都', 'slug' => 'tokyo', 'region' => 'kanto'),
                            array('name' => '神奈川県', 'slug' => 'kanagawa', 'region' => 'kanto'),
                            // 中部
                            array('name' => '新潟県', 'slug' => 'niigata', 'region' => 'chubu'),
                            array('name' => '富山県', 'slug' => 'toyama', 'region' => 'chubu'),
                            array('name' => '石川県', 'slug' => 'ishikawa', 'region' => 'chubu'),
                            array('name' => '福井県', 'slug' => 'fukui', 'region' => 'chubu'),
                            array('name' => '山梨県', 'slug' => 'yamanashi', 'region' => 'chubu'),
                            array('name' => '長野県', 'slug' => 'nagano', 'region' => 'chubu'),
                            array('name' => '岐阜県', 'slug' => 'gifu', 'region' => 'chubu'),
                            array('name' => '静岡県', 'slug' => 'shizuoka', 'region' => 'chubu'),
                            array('name' => '愛知県', 'slug' => 'aichi', 'region' => 'chubu'),
                            // 近畿
                            array('name' => '三重県', 'slug' => 'mie', 'region' => 'kinki'),
                            array('name' => '滋賀県', 'slug' => 'shiga', 'region' => 'kinki'),
                            array('name' => '京都府', 'slug' => 'kyoto', 'region' => 'kinki'),
                            array('name' => '大阪府', 'slug' => 'osaka', 'region' => 'kinki'),
                            array('name' => '兵庫県', 'slug' => 'hyogo', 'region' => 'kinki'),
                            array('name' => '奈良県', 'slug' => 'nara', 'region' => 'kinki'),
                            array('name' => '和歌山県', 'slug' => 'wakayama', 'region' => 'kinki'),
                            // 中国
                            array('name' => '鳥取県', 'slug' => 'tottori', 'region' => 'chugoku'),
                            array('name' => '島根県', 'slug' => 'shimane', 'region' => 'chugoku'),
                            array('name' => '岡山県', 'slug' => 'okayama', 'region' => 'chugoku'),
                            array('name' => '広島県', 'slug' => 'hiroshima', 'region' => 'chugoku'),
                            array('name' => '山口県', 'slug' => 'yamaguchi', 'region' => 'chugoku'),
                            // 四国
                            array('name' => '徳島県', 'slug' => 'tokushima', 'region' => 'shikoku'),
                            array('name' => '香川県', 'slug' => 'kagawa', 'region' => 'shikoku'),
                            array('name' => '愛媛県', 'slug' => 'ehime', 'region' => 'shikoku'),
                            array('name' => '高知県', 'slug' => 'kochi', 'region' => 'shikoku'),
                            // 九州・沖縄
                            array('name' => '福岡県', 'slug' => 'fukuoka', 'region' => 'kyushu'),
                            array('name' => '佐賀県', 'slug' => 'saga', 'region' => 'kyushu'),
                            array('name' => '長崎県', 'slug' => 'nagasaki', 'region' => 'kyushu'),
                            array('name' => '熊本県', 'slug' => 'kumamoto', 'region' => 'kyushu'),
                            array('name' => '大分県', 'slug' => 'oita', 'region' => 'kyushu'),
                            array('name' => '宮崎県', 'slug' => 'miyazaki', 'region' => 'kyushu'),
                            array('name' => '鹿児島県', 'slug' => 'kagoshima', 'region' => 'kyushu'),
                                array('name' => '沖縄県', 'slug' => 'okinawa', 'region' => 'kyushu')
                            );
                        }
                        
                        // 都道府県別の投稿数を取得（改善版 - デバッグ機能付き）
                        $prefecture_counts = get_transient('gi_prefecture_counts_v2');
                        $debug_mode = defined('WP_DEBUG') && WP_DEBUG;
                        
                        if (false === $prefecture_counts || (isset($_GET['refresh_counts']) && current_user_can('manage_options'))) {
                            $prefecture_counts = array();
                            
                            // デバッグ情報
                            if ($debug_mode) {
                                error_log('Prefecture Counts: Starting calculation...');
                            }
                            
                            // 方法1: 直接データベースクエリで一括取得（最高速）
                            global $wpdb;
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
                            
                            // 結果をマップに変換
                            $db_counts = array();
                            foreach ($count_results as $result) {
                                $db_counts[$result->slug] = intval($result->post_count);
                            }
                            
                            // 方法2: すべての都道府県について結果を確保（存在しない場合は0）
                            foreach ($all_prefectures as $pref) {
                                if (isset($db_counts[$pref['slug']])) {
                                    $prefecture_counts[$pref['slug']] = $db_counts[$pref['slug']];
                                } else {
                                    // タームが存在しない場合は0
                                    $prefecture_counts[$pref['slug']] = 0;
                                    
                                    // デバッグ: タームの存在確認
                                    if ($debug_mode) {
                                        $term = get_term_by('slug', $pref['slug'], 'grant_prefecture');
                                        if (!$term || is_wp_error($term)) {
                                            error_log("Prefecture Counts: Term not found for slug '{$pref['slug']}' - {$pref['name']}");
                                        }
                                    }
                                }
                            }
                            
                            // デバッグ情報
                            if ($debug_mode) {
                                $total_prefectures = count($all_prefectures);
                                $prefectures_with_posts = count(array_filter($prefecture_counts));
                                error_log("Prefecture Counts: {$total_prefectures} prefectures processed, {$prefectures_with_posts} have posts");
                            }
                            
                            // 方法3: フォールバック - WP_Queryで個別カウント（デバッグ用）
                            if ($debug_mode && empty(array_filter($prefecture_counts))) {
                                error_log('Prefecture Counts: No counts found via DB query, trying WP_Query fallback...');
                                
                                foreach ($all_prefectures as $pref) {
                                    $term = get_term_by('slug', $pref['slug'], 'grant_prefecture');
                                    if ($term && !is_wp_error($term)) {
                                        // より詳細なクエリパラメータ
                                        $args = array(
                                            'post_type' => 'grant',
                                            'post_status' => 'publish',
                                            'posts_per_page' => -1,
                                            'fields' => 'ids',
                                            'meta_query' => array(),
                                            'tax_query' => array(
                                                array(
                                                    'taxonomy' => 'grant_prefecture',
                                                    'field' => 'slug',
                                                    'terms' => $pref['slug'],
                                                    'operator' => 'IN'
                                                )
                                            )
                                        );
                                        
                                        $query = new WP_Query($args);
                                        $fallback_count = $query->found_posts;
                                        wp_reset_postdata();
                                        
                                        if ($fallback_count > 0) {
                                            $prefecture_counts[$pref['slug']] = $fallback_count;
                                            error_log("Prefecture Counts: Found {$fallback_count} posts for {$pref['name']} via WP_Query");
                                        }
                                    }
                                }
                            }
                            
                            // 10分間キャッシュ（短縮してテスト用）
                            set_transient('gi_prefecture_counts_v2', $prefecture_counts, 10 * MINUTE_IN_SECONDS);
                            
                            // デバッグ: 最終結果
                            if ($debug_mode) {
                                $sample_results = array_slice($prefecture_counts, 0, 5, true);
                                error_log('Prefecture Counts: Sample results - ' . print_r($sample_results, true));
                            }
                        }
                        
                        foreach ($all_prefectures as $pref) :
                            $count = isset($prefecture_counts[$pref['slug']]) ? $prefecture_counts[$pref['slug']] : 0;
                            
                            // デバッグ表示 (管理者のみ)
                            if ($debug_mode && current_user_can('manage_options') && $count > 0) {
                                error_log("Prefecture Display: {$pref['name']} ({$pref['slug']}) - {$count} posts");
                            }
                            
                            // フィルター付きURLを生成（パラメータ名を修正）
                            $prefecture_url = add_query_arg(
                                array(
                                    'prefecture' => $pref['slug'],  // archive-grant.phpが期待するパラメータ名
                                ), 
                                $archive_base_url
                            );
                        ?>
                        <a href="<?php echo esc_url($prefecture_url); ?>" 
                           class="prefecture-item" 
                           data-region="<?php echo esc_attr($pref['region']); ?>"
                           data-count="<?php echo esc_attr($count); ?>">
                            <span class="prefecture-name"><?php echo esc_html($pref['name']); ?></span>
                            <span class="prefecture-count"><?php echo $count; ?>件</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- 右側：主要地域選択と日本地図 -->
                <div class="main-regions-container">
                    <h4 class="regions-title">主要地域から選択</h4>
                    
                    <!-- 日本地図表示エリア -->
                    <div class="japan-map-container">
                        <h5 class="map-title">🗾 地域を選択して絞り込み</h5>
                        
                        <!-- インタラクティブSVG日本地図 -->
                        <svg viewBox="0 0 600 750" class="japan-map-svg" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#f8f9fa" />
                                    <stop offset="100%" style="stop-color:#e9ecef" />
                                </linearGradient>
                                <filter id="shadow">
                                    <feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity="0.15"/>
                                </filter>
                            </defs>
                            
                            <rect width="600" height="750" fill="url(#bgGrad)" rx="16"/>
                            
                            <g class="map-regions">
                                <!-- 北海道 -->
                                <g class="region-group" data-region="hokkaido">
                                    <path class="map-region" data-region="hokkaido"
                                          d="M 380 60 Q 420 45, 460 50 L 500 80 Q 510 120, 490 150 L 440 160 Q 390 155, 360 140 L 340 100 Q 345 75, 380 60 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="420" y="105" text-anchor="middle" class="region-label">北海道</text>
                                    <text x="420" y="125" text-anchor="middle" class="region-count">0件</text>
                                </g>
                                
                                <!-- 東北 -->
                                <g class="region-group" data-region="tohoku">
                                    <path class="map-region" data-region="tohoku"
                                          d="M 400 180 Q 430 170, 460 175 L 475 210 Q 470 250, 450 280 L 410 290 Q 380 285, 355 270 L 340 230 Q 345 200, 400 180 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="407" y="230" text-anchor="middle" class="region-label">東北</text>
                                    <text x="407" y="250" text-anchor="middle" class="region-count">0件</text>
                                </g>
                                
                                <!-- 関東 -->
                                <g class="region-group" data-region="kanto">
                                    <path class="map-region" data-region="kanto"
                                          d="M 410 300 Q 440 290, 470 295 L 485 330 Q 480 370, 460 400 L 420 410 Q 390 405, 365 390 L 350 350 Q 355 320, 410 300 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="417" y="350" text-anchor="middle" class="region-label">関東</text>
                                    <text x="417" y="370" text-anchor="middle" class="region-count">0件</text>
                                </g>
                                
                                <!-- 中部 -->
                                <g class="region-group" data-region="chubu">
                                    <path class="map-region" data-region="chubu"
                                          d="M 310 310 Q 340 300, 370 305 L 385 340 Q 380 380, 360 410 L 320 420 Q 290 415, 265 400 L 250 360 Q 255 330, 310 310 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="317" y="360" text-anchor="middle" class="region-label">中部</text>
                                    <text x="317" y="380" text-anchor="middle" class="region-count">0件</text>
                                </g>
                                
                                <!-- 近畿 -->
                                <g class="region-group" data-region="kinki">
                                    <path class="map-region" data-region="kinki"
                                          d="M 230 380 Q 260 370, 290 375 L 305 410 Q 300 450, 280 480 L 240 490 Q 210 485, 185 470 L 170 430 Q 175 400, 230 380 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="237" y="430" text-anchor="middle" class="region-label">近畿</text>
                                    <text x="237" y="450" text-anchor="middle" class="region-count">0件</text>
                                </g>
                                
                                <!-- 中国 -->
                                <g class="region-group" data-region="chugoku">
                                    <path class="map-region" data-region="chugoku"
                                          d="M 120 390 Q 150 380, 180 385 L 195 420 Q 190 460, 170 490 L 130 500 Q 100 495, 75 480 L 60 440 Q 65 410, 120 390 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="127" y="440" text-anchor="middle" class="region-label">中国</text>
                                    <text x="127" y="460" text-anchor="middle" class="region-count">0件</text>
                                </g>
                                
                                <!-- 四国 -->
                                <g class="region-group" data-region="shikoku">
                                    <path class="map-region" data-region="shikoku"
                                          d="M 190 480 Q 220 470, 250 475 L 265 510 Q 260 540, 240 565 L 200 575 Q 170 570, 145 555 L 130 520 Q 135 495, 190 480 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="197" y="525" text-anchor="middle" class="region-label">四国</text>
                                    <text x="197" y="545" text-anchor="middle" class="region-count">0件</text>
                                </g>
                                
                                <!-- 九州 -->
                                <g class="region-group" data-region="kyushu">
                                    <path class="map-region" data-region="kyushu"
                                          d="M 70 510 Q 100 500, 130 505 L 145 540 Q 140 580, 120 610 L 80 620 Q 50 615, 25 600 L 10 560 Q 15 530, 70 510 Z"
                                          fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="77" y="560" text-anchor="middle" class="region-label">九州</text>
                                    <text x="77" y="580" text-anchor="middle" class="region-count">0件</text>
                                    
                                    <!-- 沖縄 -->
                                    <circle cx="60" cy="680" r="25" class="map-region" data-region="kyushu"
                                            fill="#ffffff" stroke="#333" stroke-width="2" filter="url(#shadow)"/>
                                    <text x="60" y="686" text-anchor="middle" class="region-label" font-size="12">沖縄</text>
                                </g>
                            </g>
                        </svg>
                        
                        <!-- 主要地域ボタン -->
                        <div class="region-buttons">
                            <?php
                            $main_regions = array(
                                array('id' => 'hokkaido', 'name' => '北海道', 'prefectures' => ['北海道']),
                                array('id' => 'tohoku', 'name' => '東北', 'prefectures' => ['青森県','岩手県','宮城県','秋田県','山形県','福島県']),
                                array('id' => 'kanto', 'name' => '関東', 'prefectures' => ['茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県']),
                                array('id' => 'chubu', 'name' => '中部', 'prefectures' => ['新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県','静岡県','愛知県']),
                                array('id' => 'kinki', 'name' => '近畿', 'prefectures' => ['三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県']),
                                array('id' => 'chugoku', 'name' => '中国', 'prefectures' => ['鳥取県','島根県','岡山県','広島県','山口県']),
                                array('id' => 'shikoku', 'name' => '四国', 'prefectures' => ['徳島県','香川県','愛媛県','高知県']),
                                array('id' => 'kyushu', 'name' => '九州・沖縄', 'prefectures' => ['福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県'])
                            );
                            
                            foreach ($main_regions as $region) :
                                // 地域内の助成金数を計算（キャッシュデータを使用）
                                $region_count = 0;
                                foreach ($region['prefectures'] as $pref_name) {
                                    // slugを取得
                                    $pref_data = array_filter($all_prefectures, function($p) use ($pref_name) {
                                        return $p['name'] === $pref_name;
                                    });
                                    if (!empty($pref_data)) {
                                        $pref_data = array_values($pref_data)[0];
                                        // キャッシュされた都道府県カウントを使用
                                        $count = isset($prefecture_counts[$pref_data['slug']]) ? $prefecture_counts[$pref_data['slug']] : 0;
                                        $region_count += $count;
                                    }
                                }
                            ?>
                            <button class="region-button" 
                                    data-region="<?php echo esc_attr($region['id']); ?>"
                                    data-count="<?php echo esc_attr($region_count); ?>">
                                <span class="region-name"><?php echo esc_html($region['name']); ?></span>
                                <span class="region-count"><?php echo $region_count; ?>件</span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 人気の都道府県 -->
                    <div class="popular-prefectures">
                        <h5 class="popular-title">人気の都道府県</h5>
                        <div class="popular-list">
                            <?php
                            $popular = array('東京都', '大阪府', '愛知県', '神奈川県', '福岡県');
                            foreach ($popular as $pref_name) :
                            ?>
                            <span class="popular-item"><?php echo esc_html($pref_name); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="cta-section" data-aos="fade-up">
            <div class="cta-content">
                <h3 class="cta-title">すべての助成金を探す</h3>
                <p class="cta-description">条件を絞り込んで、あなたに最適な助成金を見つけましょう</p>
                <a href="<?php echo esc_url($archive_base_url); ?>" class="cta-button">
                    <span class="button-text">助成金を検索</span>
                    <span class="button-icon">
                        <i class="fas fa-search"></i>
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- モノクローム・スタイル -->
<style>
/* ベース設定 */
.monochrome-categories {
    position: relative;
    padding: 100px 0;
    background: #ffffff;
    overflow: hidden;
    font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* 背景エフェクト */
.background-effects {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.grid-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        linear-gradient(rgba(0, 0, 0, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
    background-size: 50px 50px;
}

.gradient-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 50%, transparent 0%, rgba(255, 255, 255, 0.8) 100%);
}

.floating-shapes {
    position: absolute;
    inset: 0;
}

.shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.05;
}

.shape-1 {
    width: 600px;
    height: 600px;
    background: #000000;
    top: -300px;
    right: -200px;
    animation: float 20s ease-in-out infinite;
}

.shape-2 {
    width: 400px;
    height: 400px;
    background: #333333;
    bottom: -200px;
    left: -100px;
    animation: float 25s ease-in-out infinite reverse;
}

.shape-3 {
    width: 300px;
    height: 300px;
    background: #666666;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: pulse 15s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(180deg); }
}

@keyframes pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.05; }
    50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.1; }
}

/* コンテナ */
.section-container {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* セクションヘッダー */
.section-header {
    text-align: center;
    margin-bottom: 80px;
    position: relative;
}

.header-accent {
    width: 60px;
    height: 4px;
    background: #000000;
    margin: 0 auto 40px;
    position: relative;
    overflow: hidden;
}

.header-accent::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    animation: shine 3s ease-in-out infinite;
}

@keyframes shine {
    0% { left: -100%; }
    100% { left: 200%; }
}

.section-title {
    margin-bottom: 20px;
}

.title-en {
    display: block;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: #999999;
    margin-bottom: 12px;
}

.title-ja {
    display: block;
    font-size: clamp(36px, 5vw, 48px);
    font-weight: 900;
    color: #000000;
    line-height: 1.2;
    letter-spacing: 0.02em;
}

.section-description {
    font-size: 18px;
    color: #666666;
    margin-bottom: 40px;
    font-weight: 400;
}

/* Yellow Markers */
.yellow-marker {
    width: 60px;
    height: 4px;
    background: #ffeb3b;
    margin: 10px auto 0;
    border-radius: 2px;
    position: relative;
    box-shadow: 0 2px 8px rgba(255, 235, 59, 0.3);
}

.yellow-marker::after {
    content: '';
    position: absolute;
    top: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    background: #ffeb3b;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(255, 235, 59, 0.4);
}

/* 統計情報は完全削除済み */

/* メインカテゴリーグリッド */
.main-categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

/* カテゴリーカード */
.category-card {
    position: relative;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
}

.card-inner {
    position: relative;
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    height: 100%;
}

.card-border {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #000000, #333333, #000000);
    padding: 2px;
    border-radius: 20px;
    -webkit-mask: 
        linear-gradient(#fff 0 0) content-box, 
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
}

.card-content {
    position: relative;
    padding: 35px;
    background: #ffffff;
    border-radius: 18px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
}

.card-icon {
    width: 56px;
    height: 56px;
    background: #000000;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 24px;
    transition: all 0.3s ease;
}

.category-card:hover .card-icon {
    background: #333333;
    transform: rotate(5deg);
}

.card-badge {
    text-align: right;
    background: linear-gradient(135deg, #000000, #333333);
    padding: 10px 15px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    display: inline-block;
    border: 2px solid #ffeb3b;
    position: relative;
}

.card-badge::before {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    background: #ffeb3b;
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(255, 235, 59, 0.6);
}

.badge-count {
    font-size: 28px;
    font-weight: 900;
    color: #ffffff;
    display: inline-block;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.badge-label {
    font-size: 12px;
    color: #ffffff;
    font-weight: 600;
    margin-left: 4px;
}

.card-title {
    font-size: 22px;
    font-weight: 800;
    color: #000000;
    margin-bottom: 12px;
    line-height: 1.3;
}

.card-description {
    font-size: 14px;
    color: #666666;
    line-height: 1.6;
    margin-bottom: 25px;
}

/* 最新の助成金 */
.recent-grants {
    margin: 20px 0;
    padding: 20px;
    background: #fafafa;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
}

.recent-grants-label {
    font-size: 11px;
    font-weight: 700;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 12px;
}

.recent-grant-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e0e0e0;
    text-decoration: none;
    transition: all 0.2s ease;
}

a.recent-grant-item:hover {
    background: rgba(0, 0, 0, 0.02);
    padding-left: 8px;
    margin-left: -8px;
    padding-right: 8px;
    margin-right: -8px;
}

.recent-grant-item:last-child {
    border-bottom: none;
}

.grant-title {
    font-size: 13px;
    color: #333333;
    flex: 1;
}

.grant-amount {
    font-size: 13px;
    font-weight: 700;
    color: #000000;
}

.card-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: #000000;
    color: #ffffff;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    margin-top: auto;
}

.card-link:hover {
    background: #ffffff;
    color: #000000;
    box-shadow: inset 0 0 0 2px #000000;
}

.link-arrow {
    transition: transform 0.3s ease;
}

.card-link:hover .link-arrow {
    transform: translateX(5px);
}

/* ホバーエフェクト */
.hover-effect {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, transparent, rgba(0, 0, 0, 0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.category-card:hover .hover-effect {
    opacity: 1;
}

/* その他のカテゴリー */
.other-categories-section {
    margin-bottom: 80px;
}

.toggle-button {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 0 auto 40px;
    padding: 18px 32px;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 999px;
    font-size: 15px;
    font-weight: 700;
    color: #000000;
    cursor: pointer;
    transition: all 0.3s ease;
}

.toggle-button:hover {
    background: #000000;
    color: #ffffff;
}

.toggle-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.toggle-button.active .toggle-icon {
    transform: rotate(45deg);
}

.count-badge {
    padding: 4px 12px;
    background: #000000;
    color: #ffffff;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.toggle-button:hover .count-badge {
    background: #ffffff;
    color: #000000;
}

.other-categories-container {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease;
}

.other-categories-container.show {
    max-height: 2000px;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    padding: 40px;
    background: #fafafa;
    border-radius: 20px;
    border: 2px solid #000000;
}

/* ミニカテゴリーカード */
.mini-category-card {
    display: block;
    text-decoration: none;
    transition: all 0.3s ease;
}

.mini-card-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.mini-category-card:hover .mini-card-inner {
    background: #000000;
    border-color: #000000;
}

.mini-icon {
    font-size: 18px;
    color: #666666;
    transition: color 0.3s ease;
}

.mini-category-card:hover .mini-icon {
    color: #ffffff;
}

.mini-title {
    flex: 1;
    font-size: 14px;
    font-weight: 600;
    color: #000000;
    transition: color 0.3s ease;
}

.mini-category-card:hover .mini-title {
    color: #ffffff;
}

.mini-count {
    padding: 4px 8px;
    background: #f0f0f0;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    color: #666666;
    transition: all 0.3s ease;
}

.mini-category-card:hover .mini-count {
    background: #ffffff;
    color: #000000;
}

/* 地域セクション */
.region-section {
    margin-bottom: 80px;
}

.region-header {
    text-align: center;
    margin-bottom: 50px;
}

.region-title {
    margin-bottom: 20px;
}

.region-description {
    font-size: 16px;
    color: #666666;
    margin-bottom: 40px;
}

.regions-container {
    display: grid;
    grid-template-columns: 2fr 3fr;
    gap: 40px;
    align-items: start;
}

/* 47都道府県リストコンテナ */
.all-prefectures-container {
    background: #fafafa;
    border-radius: 20px;
    padding: 30px;
    border: 2px solid #000000;
    max-height: 700px;
    overflow-y: auto;
}

.all-prefectures-container::-webkit-scrollbar {
    width: 8px;
}

.all-prefectures-container::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.all-prefectures-container::-webkit-scrollbar-thumb {
    background: #000000;
    border-radius: 10px;
}

.prefecture-list-title {
    font-size: 18px;
    font-weight: 700;
    color: #000000;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #000000;
}

/* 都道府県リスト */
.prefecture-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
}

.prefecture-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 8px;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    text-align: center;
}

.prefecture-item:hover {
    background: #000000;
    border-color: #000000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.prefecture-item[data-region]:hover {
    border-color: #10b981;
}

.prefecture-name {
    font-size: 13px;
    font-weight: 600;
    color: #000000;
    transition: color 0.3s ease;
    margin-bottom: 4px;
}

.prefecture-item:hover .prefecture-name {
    color: #ffffff;
}

.prefecture-count {
    font-size: 11px;
    font-weight: 700;
    color: #666666;
    transition: color 0.3s ease;
}

.prefecture-item:hover .prefecture-count {
    color: #cccccc;
}

/* 主要地域コンテナ */
.main-regions-container {
    background: #ffffff;
    border-radius: 20px;
    padding: 30px;
    border: 2px solid #000000;
}

.regions-title {
    font-size: 18px;
    font-weight: 700;
    color: #000000;
    margin-bottom: 25px;
    text-align: center;
}

/* 日本地図コンテナ */
.japan-map-container {
    position: relative;
    margin-bottom: 30px;
}

.map-title {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

.japan-map-svg {
    width: 100%;
    height: auto;
    max-height: 500px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    aspect-ratio: 600 / 750;
}

.map-region {
    cursor: pointer;
    transition: all 0.3s ease;
}

.map-region:hover {
    fill: #86efac !important;
    stroke: #10b981;
    stroke-width: 3;
}

.map-region.active {
    fill: #10b981 !important;
    stroke: #059669;
    stroke-width: 3;
}

.region-label {
    font-size: 13px;
    font-weight: 700;
    fill: #333;
    pointer-events: none;
}

.region-count {
    font-size: 11px;
    font-weight: 600;
    fill: #666;
    pointer-events: none;
}

.region-group:hover .region-label,
.region-group:hover .region-count {
    fill: #ffffff;
}

.map-region.active ~ .region-label,
.map-region.active ~ .region-count {
    fill: #ffffff;
    font-weight: 900;
}

/* map-placeholder関連CSS削除済み */

/* 地域ボタン */
.region-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-top: 25px;
}

.region-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 12px;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.region-button:hover {
    background: #000000;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.region-button.active {
    background: #10b981;
    border-color: #10b981;
}

.region-button .region-name {
    font-size: 15px;
    font-weight: 700;
    color: #000000;
    margin-bottom: 4px;
    transition: color 0.3s ease;
}

.region-button:hover .region-name,
.region-button.active .region-name {
    color: #ffffff;
}

.region-button .region-count {
    font-size: 11px;
    color: #666666;
    transition: color 0.3s ease;
}

.region-button:hover .region-count,
.region-button.active .region-count {
    color: #cccccc;
}

/* 人気の都道府県 */
.popular-prefectures {
    margin-top: 30px;
    padding: 20px;
    background: #fafafa;
    border-radius: 12px;
}

.popular-title {
    font-size: 14px;
    font-weight: 700;
    color: #000000;
    margin-bottom: 15px;
    text-align: center;
}

.popular-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}

.popular-item {
    padding: 8px 16px;
    background: #000000;
    color: #ffffff;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.popular-item:hover {
    background: #10b981;
    transform: scale(1.05);
}

/* CTA */
.cta-section {
    text-align: center;
    padding: 80px 40px;
    background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
    border-radius: 30px;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(255, 255, 255, 0.02) 10px,
            rgba(255, 255, 255, 0.02) 20px
        );
}

.cta-content {
    position: relative;
    z-index: 1;
}

.cta-title {
    font-size: 36px;
    font-weight: 900;
    color: #ffffff;
    margin-bottom: 16px;
}

.cta-description {
    font-size: 16px;
    color: #cccccc;
    margin-bottom: 32px;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    padding: 20px 40px;
    background: #ffffff;
    color: #000000;
    border-radius: 999px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.cta-button::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
}

.cta-button:hover::before {
    width: 300px;
    height: 300px;
}

.button-text,
.button-icon {
    position: relative;
    z-index: 1;
}

.button-icon {
    transition: transform 0.3s ease;
}

.cta-button:hover .button-icon {
    transform: rotate(90deg);
}

/* アニメーション */
[data-aos] {
    opacity: 0;
    transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-aos="fade-up"] {
    transform: translateY(30px);
}

[data-aos].aos-animate {
    opacity: 1;
    transform: translateY(0);
}

/* 非常に小さなモバイルデバイス対応 */
@media (max-width: 480px) {
    .japan-map-svg {
        max-height: 300px;
        min-height: 250px;
    }
    
    .region-label {
        font-size: 10px !important;
    }
    
    .region-count {
        font-size: 8px !important;
    }
    
    .region-buttons {
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }
    
    .region-button {
        padding: 10px 6px;
        min-height: 50px;
    }
    
    .region-button .region-name {
        font-size: 11px;
    }
    
    .region-button .region-count {
        font-size: 9px;
    }
}

/* レスポンシブ */
@media (max-width: 1024px) {
    .main-categories-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    
    .regions-container {
        grid-template-columns: 1fr;
    }
    
    .japan-map {
        max-width: 400px;
        margin: 0 auto;
    }
}

@media (max-width: 640px) {
    .monochrome-categories {
        padding: 40px 0;
    }
    
    .section-container {
        padding: 0 15px;
    }
    
    .stats-row {
        flex-direction: column;
        gap: 30px;
    }
    
    /* スマホ専用：2×2グリッド レイアウト最適化 */
    .main-categories-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 30px;
        max-width: 100%;
    }
    
    .card-content {
        padding: 12px;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .card-header {
        margin-bottom: 10px;
        align-items: center;
    }
    
    .card-title {
        font-size: 13px;
        margin-bottom: 4px;
        line-height: 1.2;
        font-weight: 700;
        max-height: 2.4em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .card-description {
        font-size: 10px;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.2;
        opacity: 0.8;
    }
    
    .card-icon {
        width: 32px;
        height: 32px;
        font-size: 14px;
        border-radius: 10px;
    }
    
    .card-badge {
        padding: 4px 8px;
        border-radius: 8px;
    }
    
    .badge-count {
        font-size: 16px;
        font-weight: 800;
    }
    
    .badge-label {
        font-size: 9px;
    }
    
    .card-link {
        padding: 8px 12px;
        font-size: 11px;
        margin-top: auto;
        border-radius: 8px;
        font-weight: 600;
    }
    
    /* 最新の助成金セクションをモバイルでは非表示 */
    .recent-grants {
        display: none;
    }
    
    /* モバイルでカテゴリーカードのホバーエフェクトを簡略化 */
    .category-card:hover {
        transform: none;
    }
    
    .category-card:active {
        transform: scale(0.98);
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .prefecture-list {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .prefecture-item {
        padding: 8px 6px;
    }
    
    .prefecture-name {
        font-size: 12px;
    }
    
    .prefecture-count {
        font-size: 10px;
    }
    
    /* 日本地図 - スマホ対応強化 */
    .japan-map-container {
        margin-bottom: 25px;
        overflow: hidden;
        width: 100%;
        position: relative;
    }
    
    .map-title {
        font-size: 15px;
        margin-bottom: 15px;
        text-align: center;
    }
    
    .japan-map-svg {
        width: 100%;
        height: auto;
        max-height: 400px;
        min-height: 320px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        background: #f8f9fa;
        overflow: visible;
        display: block;
        margin: 0 auto;
        aspect-ratio: 600 / 750;
    }
    
    /* SVG内のテキストサイズ調整 */
    .region-label {
        font-size: 11px !important;
        font-weight: 700;
    }
    
    .region-count {
        font-size: 9px !important;
        font-weight: 600;
    }
    
    /* 地域ボタン - スマホ最適化 */
    .region-buttons {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-top: 20px;
    }
    
    .region-button {
        padding: 12px 8px;
        font-size: 12px;
        min-height: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .region-button .region-name {
        font-size: 13px;
        margin-bottom: 2px;
    }
    
    .region-button .region-count {
        font-size: 10px;
    }
    
    /* 人気の都道府県 */
    .popular-prefectures {
        margin-top: 20px;
        padding: 15px;
    }
    
    .popular-title {
        font-size: 13px;
        margin-bottom: 10px;
    }
    
    .popular-list {
        gap: 6px;
    }
    
    .popular-item {
        padding: 6px 12px;
        font-size: 11px;
    }
    
    .cta-section {
        padding: 30px 15px;
        margin-top: 30px;
    }
    
    .cta-title {
        font-size: 24px;
    }
    
    .section-title .title-ja {
        font-size: 28px;
    }
    
    .section-description {
        font-size: 16px;
    }
    
    .section-header {
        margin-bottom: 30px;
        text-align: center;
    }
    
    .section-title .title-en {
        font-size: 12px;
        margin-bottom: 8px;
    }
    
    .other-categories-section {
        margin-bottom: 40px;
    }
    
    .region-section {
        margin-bottom: 40px;
    }
    
    .region-header {
        margin-bottom: 25px;
    }
    
    .regions-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .all-prefectures-container {
        padding: 15px;
        max-height: 300px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 15px;
    }
    
    .main-regions-container {
        padding: 15px;
        border-radius: 15px;
    }
    
    .prefecture-list-title,
    .regions-title {
        font-size: 16px;
        margin-bottom: 15px;
    }
    
    /* スクロール最適化 */
    .all-prefectures-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .all-prefectures-container::-webkit-scrollbar-track {
        background: #f0f0f0;
        border-radius: 8px;
    }
    
    .all-prefectures-container::-webkit-scrollbar-thumb {
        background: #000000;
        border-radius: 8px;
    }
    
    /* タッチデバイス対応 */
    .map-region,
    .region-button,
    .prefecture-item {
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }
    
    .map-region:active,
    .region-button:active,
    .prefecture-item:active {
        opacity: 0.7;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 地域ごとの助成金数を更新
    function updateRegionCounts() {
        document.querySelectorAll('.region-button').forEach(button => {
            const region = button.getAttribute('data-region');
            const count = button.getAttribute('data-count') || 0;
            
            // SVG地図上の件数表示を更新
            const mapRegion = document.querySelector(`.region-group[data-region="${region}"] .region-count`);
            if (mapRegion) {
                mapRegion.textContent = count + '件';
            }
        });
    }
    
    // ページ読み込み時に地域件数を更新
    updateRegionCounts();
    
    // モバイルデバイス向けの日本地図最適化
    function optimizeMapForMobile() {
        const mapSvg = document.querySelector('.japan-map-svg');
        const isMobile = window.innerWidth <= 640;
        
        if (mapSvg && isMobile) {
            // SVGの高さを調整
            mapSvg.style.maxHeight = '350px';
            
            // モバイルでのタッチ操作を改善
            mapSvg.addEventListener('touchstart', function(e) {
                e.preventDefault();
            });
        }
    }
    
    // 初期化とリサイズ時の最適化
    optimizeMapForMobile();
    window.addEventListener('resize', optimizeMapForMobile);
    
    // カウンターアニメーション
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-counter'));
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current).toLocaleString();
                }, 30);
                counterObserver.unobserve(counter);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('[data-counter]').forEach(counter => {
        counterObserver.observe(counter);
    });
    
    // AOS風アニメーション
    const aosElements = document.querySelectorAll('[data-aos]');
    const aosObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.getAttribute('data-aos-delay') || 0;
                setTimeout(() => {
                    entry.target.classList.add('aos-animate');
                }, delay);
                aosObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    aosElements.forEach(element => {
        aosObserver.observe(element);
    });
    
    // カテゴリー開閉
    const toggleCategories = document.getElementById('toggle-categories');
    const otherCategories = document.getElementById('other-categories');
    
    if (toggleCategories && otherCategories) {
        toggleCategories.addEventListener('click', function() {
            const isOpen = otherCategories.classList.contains('show');
            
            if (isOpen) {
                otherCategories.classList.remove('show');
                this.classList.remove('active');
                this.querySelector('.toggle-text').textContent = 'その他のカテゴリーを表示';
                this.querySelector('.toggle-icon i').className = 'fas fa-plus';
            } else {
                otherCategories.classList.add('show');
                this.classList.add('active');
                this.querySelector('.toggle-text').textContent = 'カテゴリーを閉じる';
                this.querySelector('.toggle-icon i').className = 'fas fa-minus';
                
                // スムーズスクロール
                setTimeout(() => {
                    otherCategories.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest' 
                    });
                }, 100);
            }
        });
    }
    
    // カードホバーエフェクト
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mouseenter', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.style.setProperty('--mouse-x', x + 'px');
            this.style.setProperty('--mouse-y', y + 'px');
        });
    });
    
    // 地域と都道府県のマッピング
    const regionPrefectureMap = {
        'hokkaido': ['北海道'],
        'tohoku': ['青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県'],
        'kanto': ['茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県'],
        'chubu': ['新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県'],
        'kinki': ['三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県'],
        'chugoku': ['鳥取県', '島根県', '岡山県', '広島県', '山口県'],
        'shikoku': ['徳島県', '香川県', '愛媛県', '高知県'],
        'kyushu': ['福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']
    };
    
    // 地域をハイライトする関数
    function highlightRegion(region) {
        // 全ての地域のアクティブ状態をリセット
        document.querySelectorAll('.map-region').forEach(r => {
            r.classList.remove('active', 'highlight');
        });
        document.querySelectorAll('.region-button').forEach(b => {
            b.classList.remove('active');
        });
        
        // 選択された地域をアクティブに
        const mapRegion = document.querySelector(`.map-region[data-region="${region}"]`);
        if (mapRegion) {
            mapRegion.classList.add('active');
        }
        
        const regionButton = document.querySelector(`.region-button[data-region="${region}"]`);
        if (regionButton) {
            regionButton.classList.add('active');
        }
        
        // 該当する都道府県をハイライト
        document.querySelectorAll('.prefecture-item').forEach(item => {
            const itemRegion = item.getAttribute('data-region');
            if (itemRegion === region) {
                item.classList.add('highlighted');
                item.style.opacity = '1';
                item.style.background = '#E8F5E9';
                item.style.borderColor = '#4CAF50';
            } else {
                item.classList.remove('highlighted');
                item.style.opacity = '0.3';
                item.style.background = '';
                item.style.borderColor = '';
            }
        });
        
        // 都道府県リストコンテナをスクロール
        const prefectureContainer = document.querySelector('.all-prefectures-container');
        if (prefectureContainer) {
            const highlightedItem = prefectureContainer.querySelector('.prefecture-item.highlighted');
            if (highlightedItem) {
                highlightedItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
    
    // SVG地図の地域クリック
    document.querySelectorAll('.map-region').forEach(region => {
        region.addEventListener('click', function() {
            const regionId = this.getAttribute('data-region');
            
            // 地域内の都道府県slugを取得してarchive-grant.phpに遷移
            const prefectures = regionPrefectureMap[regionId] || [];
            const prefSlugs = [];
            
            prefectures.forEach(prefName => {
                const prefItem = Array.from(document.querySelectorAll('.prefecture-item')).find(item => 
                    item.querySelector('.prefecture-name').textContent === prefName
                );
                if (prefItem) {
                    const href = prefItem.getAttribute('href');
                    const url = new URL(href, window.location.origin);
                    const prefSlug = url.searchParams.get('prefecture');
                    if (prefSlug) {
                        prefSlugs.push(prefSlug);
                    }
                }
            });
            
            if (prefSlugs.length > 0) {
                const archiveUrl = '<?php echo $archive_base_url; ?>';
                const url = new URL(archiveUrl, window.location.origin);
                url.searchParams.set('prefecture', prefSlugs.join(','));
                window.location.href = url.toString();
            } else {
                // フォールバック: ハイライト表示のみ
                highlightRegion(regionId);
            }
        });
        
        // ホバー効果
        region.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.classList.add('highlight');
            }
        });
        
        region.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.classList.remove('highlight');
            }
        });
    });
    
    // 地域ボタンクリック
    document.querySelectorAll('.region-button').forEach(button => {
        button.addEventListener('click', function() {
            const region = this.getAttribute('data-region');
            
            // 地域内の都道府県slugを取得してarchive-grant.phpに遷移
            const prefectures = regionPrefectureMap[region] || [];
            const prefSlugs = [];
            
            prefectures.forEach(prefName => {
                const prefItem = Array.from(document.querySelectorAll('.prefecture-item')).find(item => 
                    item.querySelector('.prefecture-name').textContent === prefName
                );
                if (prefItem) {
                    const href = prefItem.getAttribute('href');
                    const url = new URL(href, window.location.origin);
                    const prefSlug = url.searchParams.get('prefecture');
                    if (prefSlug) {
                        prefSlugs.push(prefSlug);
                    }
                }
            });
            
            if (prefSlugs.length > 0) {
                const archiveUrl = '<?php echo $archive_base_url; ?>';
                const url = new URL(archiveUrl, window.location.origin);
                url.searchParams.set('prefecture', prefSlugs.join(','));
                window.location.href = url.toString();
            } else {
                // フォールバック: ハイライト表示のみ
                highlightRegion(region);
            }
        });
    });
    
    // 人気の都道府県クリック
    document.querySelectorAll('.popular-item').forEach(item => {
        item.addEventListener('click', function() {
            const prefName = this.textContent;
            const prefItem = Array.from(document.querySelectorAll('.prefecture-item')).find(item => 
                item.querySelector('.prefecture-name').textContent === prefName
            );
            
            if (prefItem) {
                // ハイライト
                document.querySelectorAll('.prefecture-item').forEach(i => {
                    i.classList.remove('highlighted');
                    i.style.opacity = '0.3';
                });
                
                prefItem.classList.add('highlighted');
                prefItem.style.opacity = '1';
                prefItem.style.background = '#E8F5E9';
                prefItem.style.borderColor = '#4CAF50';
                
                // スクロール
                prefItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
    
    // 都道府県アイテムクリック時に地域も連動
    document.querySelectorAll('.prefecture-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            const prefName = this.querySelector('.prefecture-name').textContent;
            
            // 該当する地域を探す
            for (const [region, prefs] of Object.entries(regionPrefectureMap)) {
                if (prefs.includes(prefName)) {
                    const regionBlock = document.querySelector(`.region-block[data-region="${region}"]`);
                    if (regionBlock) {
                        regionBlock.classList.add('hover');
                    }
                    break;
                }
            }
        });
        
        item.addEventListener('mouseleave', function() {
            document.querySelectorAll('.region-block').forEach(block => {
                block.classList.remove('hover');
            });
        });
    });
    
    // パフォーマンス最適化：Intersection Observerでの遅延読み込み
    const lazyLoadObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // ここで追加のコンテンツを読み込み
                const element = entry.target;
                element.classList.add('loaded');
                lazyLoadObserver.unobserve(element);
            }
        });
    }, {
        rootMargin: '100px'
    });
    
    // functions.phpとの連携：AJAX呼び出し例
    function loadCategoryGrants(categorySlug) {
        if (typeof gi_ajax === 'undefined') {
            console.warn('gi_ajax object not found');
            return;
        }
        
        fetch(gi_ajax.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'gi_load_grants',
                nonce: gi_ajax.nonce,
                categories: JSON.stringify([categorySlug]),
                view: 'grid'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Grants loaded:', data.data);
                // ここで取得したデータを表示
            }
        })
        .catch(error => {
            console.error('Error loading grants:', error);
        });
    }
    
    // カテゴリーカードクリック時のプレビュー機能
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.card-link')) {
                return; // リンククリック時は通常の動作
            }
            
            const category = this.getAttribute('data-category');
            if (category && typeof loadCategoryGrants === 'function') {
                loadCategoryGrants(category);
            }
        });
    });
    
    // モバイルでのパフォーマンス最適化
    if (window.innerWidth <= 640) {
        // 都道府県コンテナのスクロール性能改善
        const prefectureContainer = document.querySelector('.all-prefectures-container');
        if (prefectureContainer) {
            prefectureContainer.style.webkitOverflowScrolling = 'touch';
            prefectureContainer.style.overflowScrolling = 'touch';
        }
        
        // 地域セクションのスクロール最適化
        const regionSection = document.querySelector('.region-section');
        if (regionSection) {
            regionSection.style.transform = 'translateZ(0)';
            regionSection.style.willChange = 'transform';
        }
    }
    
    console.log('Monochrome Categories Section initialized successfully');
});
</script>

<?php
// デバッグ情報（開発環境のみ）
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- Categories Section Debug Info -->';
    echo '<!-- Total Categories: ' . count($all_categories) . ' -->';
    echo '<!-- Total Prefectures: ' . count($prefectures) . ' -->';
    echo '<!-- Theme Version: ' . GI_THEME_VERSION . ' -->';
}
?>
