<?php
/**
 * Grant Insight Perfect - Display Functions
 *
 * テンプレート表示、カードレンダリング、モバイル最適化を統合管理
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
 * 1. カードレンダリングシステム
 * =============================================================================
 */

/**
 * GrantCardRenderer クラス
 * シングルトンパターンでカード表示を統一管理
 */
class GrantCardRenderer {
    
    private static $instance = null;
    private $user_favorites_cache = null;
    
    /**
     * シングルトンパターンのインスタンス取得
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * プライベートコンストラクタ
     */
    private function __construct() {
        $this->load_dependencies();
    }
    
    /**
     * 依存関数の確認
     */
    private function load_dependencies() {
        // gi_get_user_favorites関数は inc/data-functions.php で定義済み
    }
    
    /**
     * メインレンダリング関数
     */
    public function render($post_id, $view = 'grid', $additional_classes = '') {
        if (!$post_id || !get_post($post_id)) {
            return $this->render_error_card('投稿が見つかりません');
        }
        
        // データ取得
        $grant_data = $this->get_grant_data($post_id);
        $user_favorites = $this->get_user_favorites_cached();
        $is_favorite = in_array($post_id, $user_favorites);
        
        // ビューに応じてレンダリング
        switch ($view) {
            case 'list':
                return $this->render_list_card($grant_data, $is_favorite, $additional_classes);
            case 'compact':
                return $this->render_compact_card($grant_data, $is_favorite, $additional_classes);
            default:
                return $this->render_grid_card($grant_data, $is_favorite, $additional_classes);
        }
    }
    
    /**
     * 助成金データ取得
     */
    private function get_grant_data($post_id) {
        $post = get_post($post_id);
        if (!$post) return [];
        
        // 基本データ
        $data = [
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'permalink' => get_permalink($post_id),
            'excerpt' => get_the_excerpt($post_id),
            'date' => get_the_date('Y-m-d', $post_id),
            'thumbnail' => get_the_post_thumbnail_url($post_id, 'medium'),
        ];
        
        // メタデータ
        $meta_fields = [
            'organization' => '',
            'max_amount' => '',
            'max_amount_numeric' => 0,
            'deadline' => '',
            'deadline_date' => '',
            'application_status' => 'active',
            'grant_difficulty' => 'normal',
            'grant_success_rate' => 0,
            'grant_target' => '',
            'eligible_expenses' => '',
            'application_method' => 'online',
            'official_url' => '',
            'is_featured' => false
        ];
        
        foreach ($meta_fields as $field => $default) {
            $value = get_post_meta($post_id, $field, true);
            $data[$field] = $value !== '' ? $value : $default;
        }
        
        // タクソノミーデータ
        $data['categories'] = wp_get_post_terms($post_id, 'grant_category', ['fields' => 'names']);
        $data['prefectures'] = wp_get_post_terms($post_id, 'grant_prefecture', ['fields' => 'names']);
        $data['tags'] = wp_get_post_terms($post_id, 'grant_tag', ['fields' => 'names']);
        
        // 表示用データ
        $data['main_category'] = !empty($data['categories']) ? $data['categories'][0] : '';
        $data['prefecture'] = !empty($data['prefectures']) ? $data['prefectures'][0] : '';
        $data['amount_formatted'] = $this->format_amount($data['max_amount_numeric'], $data['max_amount']);
        $data['deadline_formatted'] = $this->format_deadline($data['deadline_date'] ?: $data['deadline']);
        $data['status_display'] = $this->map_status($data['application_status']);
        $data['difficulty_info'] = $this->get_difficulty_info($data['grant_difficulty']);
        
        return $data;
    }
    
    /**
     * グリッドカードレンダリング
     */
    private function render_grid_card($data, $is_favorite, $additional_classes = '') {
        $post_id = $data['id'];
        $favorite_class = $is_favorite ? 'is-favorite' : '';
        
        ob_start();
        ?>
        <div class="grant-card grant-card-grid <?php echo esc_attr($favorite_class . ' ' . $additional_classes); ?>" 
             data-post-id="<?php echo $post_id; ?>">
            
            <!-- ステータスバッジ -->
            <div class="grant-status-badge status-<?php echo esc_attr($data['application_status']); ?>">
                <?php echo esc_html($data['status_display']); ?>
            </div>
            
            <!-- お気に入りボタン -->
            <button class="favorite-btn" data-post-id="<?php echo $post_id; ?>" 
                    title="<?php echo $is_favorite ? 'お気に入りから削除' : 'お気に入りに追加'; ?>">
                <span class="favorite-icon <?php echo $is_favorite ? 'active' : ''; ?>">♥</span>
            </button>
            
            <!-- サムネイル -->
            <?php if ($data['thumbnail']) : ?>
                <div class="grant-thumbnail">
                    <img src="<?php echo esc_url($data['thumbnail']); ?>" 
                         alt="<?php echo esc_attr($data['title']); ?>" loading="lazy">
                </div>
            <?php endif; ?>
            
            <!-- メインコンテンツ -->
            <div class="grant-content">
                <h3 class="grant-title">
                    <a href="<?php echo esc_url($data['permalink']); ?>">
                        <?php echo esc_html($data['title']); ?>
                    </a>
                </h3>
                
                <div class="grant-amount">
                    最大 <?php echo esc_html($data['amount_formatted']); ?>
                </div>
                
                <div class="grant-meta">
                    <?php if ($data['organization']) : ?>
                        <span class="grant-organization">実施: <?php echo esc_html($data['organization']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($data['prefecture']) : ?>
                        <span class="grant-prefecture">地域: <?php echo esc_html($data['prefecture']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($data['main_category']) : ?>
                        <span class="grant-category"><?php echo esc_html($data['main_category']); ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ($data['excerpt']) : ?>
                    <div class="grant-excerpt">
                        <?php echo wp_trim_words($data['excerpt'], 20); ?>
                    </div>
                <?php endif; ?>
                
                <!-- 追加情報 -->
                <div class="grant-extra-info">
                    <?php if ($data['deadline_formatted'] !== '未定') : ?>
                        <span class="grant-deadline">締切: <?php echo esc_html($data['deadline_formatted']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($data['grant_success_rate'] > 0) : ?>
                        <span class="grant-success-rate">採択率: <?php echo intval($data['grant_success_rate']); ?>%</span>
                    <?php endif; ?>
                    
                    <span class="grant-difficulty difficulty-<?php echo esc_attr($data['grant_difficulty']); ?>">
                        <?php echo esc_html($data['difficulty_info']['label'] ?? $data['grant_difficulty']); ?>
                    </span>
                </div>
            </div>
            
            <!-- アクションボタン -->
            <div class="grant-actions">
                <a href="<?php echo esc_url($data['permalink']); ?>" class="btn btn-primary">
                    詳細を見る
                </a>
                
                <?php if ($data['official_url']) : ?>
                    <a href="<?php echo esc_url($data['official_url']); ?>" class="btn btn-outline" target="_blank" rel="noopener">
                        公式サイト
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * リストカードレンダリング
     */
    private function render_list_card($data, $is_favorite, $additional_classes = '') {
        $post_id = $data['id'];
        $favorite_class = $is_favorite ? 'is-favorite' : '';
        
        ob_start();
        ?>
        <div class="grant-card grant-card-list <?php echo esc_attr($favorite_class . ' ' . $additional_classes); ?>" 
             data-post-id="<?php echo $post_id; ?>">
            
            <div class="grant-list-content">
                <div class="grant-main">
                    <h3 class="grant-title">
                        <a href="<?php echo esc_url($data['permalink']); ?>">
                            <?php echo esc_html($data['title']); ?>
                        </a>
                    </h3>
                    
                    <div class="grant-meta-inline">
                        <span class="grant-organization"><?php echo esc_html($data['organization']); ?></span>
                        <?php if ($data['main_category']) : ?>
                            <span class="grant-category"><?php echo esc_html($data['main_category']); ?></span>
                        <?php endif; ?>
                        <span class="grant-prefecture"><?php echo esc_html($data['prefecture']); ?></span>
                    </div>
                </div>
                
                <div class="grant-amount-wrapper">
                    <div class="grant-amount"><?php echo esc_html($data['amount_formatted']); ?></div>
                    <div class="grant-status status-<?php echo esc_attr($data['application_status']); ?>">
                        <?php echo esc_html($data['status_display']); ?>
                    </div>
                </div>
                
                <div class="grant-actions-list">
                    <button class="favorite-btn" data-post-id="<?php echo $post_id; ?>">
                        <span class="favorite-icon <?php echo $is_favorite ? 'active' : ''; ?>">♥</span>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * コンパクトカードレンダリング
     */
    private function render_compact_card($data, $is_favorite, $additional_classes = '') {
        ob_start();
        ?>
        <div class="grant-card grant-card-compact <?php echo esc_attr($additional_classes); ?>" 
             data-post-id="<?php echo $data['id']; ?>">
            
            <div class="grant-compact-content">
                <h4 class="grant-title">
                    <a href="<?php echo esc_url($data['permalink']); ?>">
                        <?php echo esc_html(wp_trim_words($data['title'], 8)); ?>
                    </a>
                </h4>
                
                <div class="grant-amount-compact">
                    <?php echo esc_html($data['amount_formatted']); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * エラーカード
     */
    private function render_error_card($message) {
        return sprintf(
            '<div class="grant-card-error">%s</div>',
            esc_html($message)
        );
    }
    
    /**
     * ユーザーお気に入り取得（キャッシュ付き）
     */
    private function get_user_favorites_cached() {
        if ($this->user_favorites_cache === null) {
            $this->user_favorites_cache = gi_get_user_favorites();
        }
        return $this->user_favorites_cache;
    }
    
    /**
     * 金額フォーマット
     */
    private function format_amount($amount_numeric, $amount_text = '') {
        $amount = intval($amount_numeric);
        
        if ($amount >= 100000000) {
            $oku = $amount / 100000000;
            return ($oku == floor($oku) ? number_format($oku) : number_format($oku, 1)) . '億円';
        } elseif ($amount >= 10000) {
            $man = $amount / 10000;
            return ($man == floor($man) ? number_format($man) : number_format($man, 1)) . '万円';
        } elseif ($amount > 0) {
            return number_format($amount) . '円';
        }
        
        return !empty($amount_text) ? $amount_text : '要問合せ';
    }
    
    /**
     * 締切フォーマット
     */
    private function format_deadline($deadline) {
        if (empty($deadline)) return '未定';
        
        $timestamp = is_numeric($deadline) ? intval($deadline) : strtotime($deadline);
        if ($timestamp) {
            return date('Y年n月j日', $timestamp);
        }
        
        return $deadline;
    }
    
    /**
     * ステータスマッピング
     */
    private function map_status($status) {
        $status_map = [
            'open' => '募集中',
            'active' => '募集中',
            'upcoming' => '募集予定',
            'closed' => '募集終了',
            'suspended' => '一時停止'
        ];
        
        return $status_map[$status] ?? $status;
    }
    
    /**
     * 難易度情報取得
     */
    private function get_difficulty_info($difficulty) {
        $difficulty_config = [
            'easy' => ['label' => '易しい', 'color' => 'green', 'stars' => 1],
            'normal' => ['label' => '普通', 'color' => 'blue', 'stars' => 2],
            'hard' => ['label' => '難しい', 'color' => 'orange', 'stars' => 3],
            'expert' => ['label' => '専門的', 'color' => 'red', 'stars' => 4]
        ];
        
        return $difficulty_config[$difficulty] ?? $difficulty_config['normal'];
    }
}

/**
 * =============================================================================
 * 2. テンプレートタグ関数
 * =============================================================================
 */

/**
 * グローバルカード表示関数
 */
function gi_render_grant_card($post_id, $view = 'grid', $additional_classes = '') {
    if (class_exists('GrantCardRenderer')) {
        $renderer = GrantCardRenderer::getInstance();
        return $renderer->render($post_id, $view, $additional_classes);
    }
    
    // フォールバック
    return '<div class="grant-card-error">カードレンダラーが利用できません</div>';
}

/**
 * 統一カード表示関数（互換性用）
 */
function render_grant_card_unified($post_id, $view = 'grid', $user_favorites = []) {
    return gi_render_grant_card($post_id, $view);
}

/**
 * 助成金ループ表示
 */
function gi_render_grants_loop($posts, $view = 'grid', $container_class = 'grants-container') {
    if (empty($posts)) {
        return '<div class="no-grants-found">該当する助成金が見つかりませんでした。</div>';
    }
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($container_class . ' view-' . $view); ?>">
        <?php foreach ($posts as $post) : ?>
            <?php echo gi_render_grant_card($post->ID, $view); ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * =============================================================================
 * 3. モバイル最適化機能
 * =============================================================================
 */

/**
 * 高度なモバイル判定
 */
function gi_is_mobile_device() {
    if (wp_is_mobile()) {
        return true;
    }
    
    // タブレット除外判定
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $tablet_keywords = ['iPad', 'tablet', 'Kindle', 'Silk', 'GT-P', 'SM-T'];
    
    foreach ($tablet_keywords as $keyword) {
        if (stripos($user_agent, $keyword) !== false) {
            return false; // タブレットはモバイル扱いしない
        }
    }
    
    // 画面幅による判定（JavaScript側で設定されたCookie）
    if (isset($_COOKIE['gi_screen_width'])) {
        $screen_width = intval($_COOKIE['gi_screen_width']);
        return $screen_width <= 768;
    }
    
    return false;
}

/**
 * レスポンシブ画像のsrcset生成
 */
function gi_generate_responsive_srcset($attachment_id, $sizes = []) {
    if (empty($sizes)) {
        $sizes = [
            'thumbnail' => '300w',
            'medium' => '600w',
            'large' => '900w',
            'full' => '1200w'
        ];
    }
    
    $srcset = [];
    foreach ($sizes as $size => $descriptor) {
        $image = wp_get_attachment_image_src($attachment_id, $size);
        if ($image) {
            $srcset[] = esc_url($image[0]) . ' ' . $descriptor;
        }
    }
    
    return implode(', ', $srcset);
}

/**
 * モバイル用の軽量画像取得
 */
function gi_get_mobile_optimized_image($attachment_id, $fallback_url = '') {
    if (gi_is_mobile_device()) {
        // モバイルでは小さいサイズを優先
        $image = wp_get_attachment_image_src($attachment_id, 'medium');
        if ($image) {
            return $image[0];
        }
    }
    
    // デスクトップまたはフォールバック
    $image = wp_get_attachment_image_src($attachment_id, 'large');
    return $image ? $image[0] : $fallback_url;
}

/**
 * 動的CSSの生成（モバイル最適化）
 */
function gi_generate_mobile_css() {
    if (!gi_is_mobile_device()) return;
    
    ?>
    <style id="gi-mobile-optimization">
        .grant-card-grid {
            margin-bottom: 1rem;
            padding: 1rem;
        }
        .grant-content {
            padding: 0.75rem;
        }
        .grant-title {
            font-size: 1rem;
            line-height: 1.3;
        }
        .grant-amount {
            font-size: 1.1rem;
        }
        .grant-meta {
            font-size: 0.85rem;
        }
        .grants-container.view-grid {
            display: block;
        }
        .grants-container.view-grid .grant-card {
            margin-bottom: 1rem;
        }
    </style>
    <?php
}
add_action('wp_head', 'gi_generate_mobile_css', 20);

/**
 * タッチデバイス用のイベント最適化
 */
function gi_add_touch_optimization() {
    if (!gi_is_mobile_device()) return;
    
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // タッチデバイスでホバー効果を調整
        document.body.classList.add('touch-device');
        
        // 高速クリック対応
        var cards = document.querySelectorAll('.grant-card');
        cards.forEach(function(card) {
            card.addEventListener('touchstart', function() {
                this.classList.add('touch-active');
            });
            card.addEventListener('touchend', function() {
                var self = this;
                setTimeout(function() {
                    self.classList.remove('touch-active');
                }, 150);
            });
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'gi_add_touch_optimization');

/**
 * =============================================================================
 * 4. パフォーマンス最適化
 * =============================================================================
 */

/**
 * 画像の遅延読み込み設定
 */
function gi_setup_lazy_loading() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.add('loaded');
                            observer.unobserve(img);
                        }
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'gi_setup_lazy_loading');

/**
 * CSS/JS の最適化
 */
function gi_optimize_assets() {
    // 不要なスクリプトの削除（管理画面以外）
    if (!is_admin()) {
        // WordPress絵文字スクリプトを無効化
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        
        // 不要なRSSフィードリンクを削除
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
    }
}
add_action('init', 'gi_optimize_assets');

/**
 * アクセシビリティ向上
 */
function gi_add_accessibility_features() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // キーボードナビゲーション対応
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
        
        // スクリーンリーダー用のラベル追加
        var favoriteButtons = document.querySelectorAll('.favorite-btn');
        favoriteButtons.forEach(function(btn) {
            if (!btn.getAttribute('aria-label')) {
                btn.setAttribute('aria-label', 'お気に入りに追加または削除');
            }
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'gi_add_accessibility_features');