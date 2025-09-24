<?php
/**
 * 補助金・助成金情報サイト - スタイリッシュヒーローセクション
 * Grant & Subsidy Information Site - Stylish Hero Section
 * @package Grant_Insight_Stylish
 * @version 28.0-stylish-clean
 * 
 * === 主要機能 ===
 * 1. 白・黒・黄色のモダンカラーパレット
 * 2. PC + タブレット + スマートフォン表示
 * 3. シンプルでスタイリッシュなデザイン
 * 4. クリーンなタイポグラフィ
 * 5. ミニマルなインタラクション
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// ヘルパー関数
if (!function_exists('gip_safe_output')) {
    function gip_safe_output($text, $allow_html = false) {
        return $allow_html ? wp_kses_post($text) : esc_html($text);
    }
}

if (!function_exists('gip_get_option')) {
    function gip_get_option($key, $default = '') {
        $value = get_option('gip_' . $key, $default);
        return !empty($value) ? $value : $default;
    }
}

// 設定データ
$hero_config = array(
    'main_title' => gip_get_option('hero_main_title', '補助金・助成金を'),
    'sub_title' => gip_get_option('hero_sub_title', 'AIが瞬時に発見'),
    'description' => gip_get_option('hero_description', 'あなたのビジネスに最適な補助金・助成金情報を、最新AIテクノロジーが瞬時に発見。専門家による申請サポートで成功率98.7%を実現します。'),
    'cta_primary_text' => gip_get_option('hero_cta_primary_text', '無料で助成金を探す'),
    'cta_secondary_text' => gip_get_option('hero_cta_secondary_text', 'AI専門家に相談')
);

// リアルタイム統計データ
$live_stats = array(
    array('number' => '12,847', 'label' => '助成金データベース', 'icon' => '📊', 'animatable' => true),
    array('number' => '98.7%', 'label' => 'マッチング精度', 'icon' => '🎯', 'animatable' => true),
    array('number' => '24時間', 'label' => 'AI自動更新', 'icon' => '⚡', 'animatable' => true),
    array('number' => '完全無料', 'label' => 'サービス利用', 'icon' => '✨', 'animatable' => false)
);

// タブレット用統計データ
$tablet_stats = array(
    array('number' => '2,847', 'label' => '今月の新着', 'icon' => '📈'),
    array('number' => '156', 'label' => '申請成功', 'icon' => '✅'),
    array('number' => '24/7', 'label' => 'サポート', 'icon' => '🛠️')
);

// スマートフォン用クイック統計
$mobile_quick_stats = array(
    array('number' => '98.7%', 'label' => '成功率'),
    array('number' => '3分', 'label' => '検索時間'),
    array('number' => '無料', 'label' => '利用料金')
);
?>

<section id="hero-section" class="hero-stylish" role="banner" aria-label="補助金・助成金AIプラットフォーム">
    
    <!-- 背景システム -->
    <div class="bg-system" aria-hidden="true">
        <div class="bg-layer bg-gradient"></div>
        <div class="bg-layer bg-pattern"></div>
        <div class="floating-dots">
            <?php for ($i = 1; $i <= 8; $i++): ?>
            <div class="dot dot-<?php echo $i; ?>"></div>
            <?php endfor; ?>
        </div>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="container-main">
        
        <!-- デスクトップレイアウト -->
        <div class="desktop-layout">
            <div class="content-grid">
                
                <!-- 左側：メインコンテンツ -->
                <div class="content-main">
                    
                    <!-- ステータスバッジ -->
                    <div class="status-badge" role="note" aria-label="プレミアムAIプラットフォーム">
                        <div class="badge-dot"></div>
                        <span class="badge-text">AI POWERED PLATFORM</span>
                    </div>
                    
                    <!-- メインタイトル -->
                    <h1 class="main-title">
                        <span class="title-line title-line-1"><?php echo gip_safe_output($hero_config['main_title']); ?></span>
                        <span class="title-line title-line-2">
                            <span class="ai-highlight"><?php echo gip_safe_output($hero_config['sub_title']); ?></span>
                        </span>
                        <span class="title-line title-line-3">成功まで完全サポート</span>
                    </h1>
                    
                    <!-- 説明文 -->
                    <p class="description">
                        <?php echo gip_safe_output($hero_config['description']); ?>
                    </p>
                    
                    <!-- CTAボタン -->
                    <div class="cta-container">
                        <button onclick="startGrantSearch()" class="btn-primary" aria-label="無料で助成金を探す">
                            <span class="btn-icon">🔍</span>
                            <span class="btn-text"><?php echo gip_safe_output($hero_config['cta_primary_text']); ?></span>
                        </button>
                        
                        <button onclick="openAIConsultation()" class="btn-secondary" aria-label="AI専門家に相談">
                            <span class="btn-icon">💬</span>
                            <span class="btn-text"><?php echo gip_safe_output($hero_config['cta_secondary_text']); ?></span>
                        </button>
                    </div>
                    
                    <!-- 統計表示 -->
                    <div class="stats-display">
                        <?php foreach (array_slice($live_stats, 0, 2) as $stat): ?>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo gip_safe_output($stat['number']); ?></div>
                            <div class="stat-label"><?php echo gip_safe_output($stat['label']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- 右側：マルチデバイスビジュアル -->
                <div class="visual-main">
                    <div class="multidevice-system">
                        
                        <!-- PCモニター（メイン） -->
                        <div class="pc-monitor">
                            <div class="monitor-frame">
                                <div class="monitor-screen">
                                    <div class="screen-content">
                                        
                                        <!-- システムヘッダー -->
                                        <div class="system-header">
                                            <div class="window-controls">
                                                <div class="control-btn close"></div>
                                                <div class="control-btn minimize"></div>
                                                <div class="control-btn maximize"></div>
                                            </div>
                                            <div class="system-title">
                                                <span class="title-icon">📊</span>
                                                助成金マッチングシステム
                                            </div>
                                            <div class="system-status">
                                                <div class="status-indicator"></div>
                                                <span>稼働中</span>
                                            </div>
                                        </div>
                                        
                                        <!-- メインダッシュボード -->
                                        <div class="dashboard-main">
                                            
                                            <!-- 統計パネル -->
                                            <div class="stats-panel">
                                                <div class="panel-header">
                                                    <h3>📈 リアルタイム統計</h3>
                                                    <div class="live-indicator">
                                                        <div class="live-dot"></div>
                                                        <span>LIVE</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="stats-grid">
                                                    <?php foreach ($live_stats as $stat): ?>
                                                    <div class="stat-card">
                                                        <div class="stat-icon"><?php echo $stat['icon']; ?></div>
                                                        <div class="stat-content">
                                                            <div class="stat-number" data-target="<?php echo esc_attr($stat['number']); ?>">
                                                                <?php echo gip_safe_output($stat['number']); ?>
                                                            </div>
                                                            <div class="stat-label"><?php echo gip_safe_output($stat['label']); ?></div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- プログレス表示 -->
                                            <div class="progress-section">
                                                <div class="progress-container">
                                                    <div class="progress-circle">
                                                        <div class="progress-inner">
                                                            <div class="progress-number">98.7%</div>
                                                            <div class="progress-label">マッチング精度</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- アクティビティフィード -->
                                            <div class="activity-feed">
                                                <div class="activity-header">
                                                    <h4>🔄 最新アクティビティ</h4>
                                                </div>
                                                <div class="activity-list">
                                                    <div class="activity-item">
                                                        <div class="activity-icon">✅</div>
                                                        <div class="activity-text">
                                                            <span>新規助成金情報を3件追加</span>
                                                            <span class="activity-time">2分前</span>
                                                        </div>
                                                    </div>
                                                    <div class="activity-item">
                                                        <div class="activity-icon">🎯</div>
                                                        <div class="activity-text">
                                                            <span>マッチング精度を更新</span>
                                                            <span class="activity-time">5分前</span>
                                                        </div>
                                                    </div>
                                                    <div class="activity-item">
                                                        <div class="activity-icon">🔍</div>
                                                        <div class="activity-text">
                                                            <span>データベース同期完了</span>
                                                            <span class="activity-time">10分前</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- モニタースタンド -->
                            <div class="monitor-stand">
                                <div class="stand-neck"></div>
                                <div class="stand-base"></div>
                            </div>
                        </div>
                        
                        <!-- タブレット（重なり表示） -->
                        <div class="tablet-device">
                            <div class="tablet-frame">
                                <div class="tablet-screen">
                                    <div class="tablet-content">
                                        
                                        <!-- タブレットヘッダー -->
                                        <div class="tablet-header">
                                            <div class="tablet-time">14:32</div>
                                            <div class="tablet-status-icons">
                                                <span>📶</span>
                                                <span>🔋</span>
                                            </div>
                                        </div>
                                        
                                        <!-- タブレットアプリ画面 -->
                                        <div class="tablet-app">
                                            <div class="app-header">
                                                <div class="app-icon">📱</div>
                                                <div class="app-title">Grant Finder</div>
                                            </div>
                                            
                                            <!-- タブレット統計 -->
                                            <div class="tablet-stats">
                                                <?php foreach ($tablet_stats as $stat): ?>
                                                <div class="tablet-stat-card">
                                                    <div class="tablet-stat-icon"><?php echo $stat['icon']; ?></div>
                                                    <div class="tablet-stat-content">
                                                        <div class="tablet-stat-number"><?php echo gip_safe_output($stat['number']); ?></div>
                                                        <div class="tablet-stat-label"><?php echo gip_safe_output($stat['label']); ?></div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <!-- タブレットチャート -->
                                            <div class="tablet-chart">
                                                <div class="chart-title">📊 月間推移</div>
                                                <div class="chart-bars">
                                                    <?php for ($i = 0; $i < 7; $i++): ?>
                                                    <div class="chart-bar" style="height: <?php echo rand(30, 80); ?>%;"></div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- スマートフォン（重なり表示） -->
                        <div class="smartphone-device">
                            <div class="smartphone-frame">
                                <div class="smartphone-screen">
                                    <div class="smartphone-content">
                                        
                                        <!-- スマートフォンステータスバー -->
                                        <div class="smartphone-statusbar">
                                            <div class="statusbar-time">14:32</div>
                                            <div class="statusbar-icons">
                                                <span>📶</span>
                                                <span>🔋</span>
                                            </div>
                                        </div>
                                        
                                        <!-- スマートフォンアプリ -->
                                        <div class="smartphone-app">
                                            <div class="smartphone-app-header">
                                                <div class="smartphone-app-icon">💰</div>
                                                <div class="smartphone-app-title">助成金AI</div>
                                            </div>
                                            
                                            <!-- クイック統計 -->
                                            <div class="smartphone-quick-stats">
                                                <?php foreach ($mobile_quick_stats as $stat): ?>
                                                <div class="smartphone-stat-item">
                                                    <div class="smartphone-stat-number"><?php echo gip_safe_output($stat['number']); ?></div>
                                                    <div class="smartphone-stat-label"><?php echo gip_safe_output($stat['label']); ?></div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <!-- アクションボタン -->
                                            <div class="smartphone-action">
                                                <div class="smartphone-btn">
                                                    <span>🔍</span>
                                                    <span>検索開始</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- モバイルレイアウト -->
        <div class="mobile-layout">
            <div class="mobile-content">
                
                <!-- モバイルバッジ -->
                <div class="mobile-badge">
                    <div class="mobile-status-dot"></div>
                    <span>AI POWERED PLATFORM</span>
                </div>
                
                <!-- モバイルタイトル -->
                <h1 class="mobile-title">
                    <span class="mobile-title-1"><?php echo gip_safe_output($hero_config['main_title']); ?></span>
                    <span class="mobile-title-2">
                        <span class="mobile-ai-highlight"><?php echo gip_safe_output($hero_config['sub_title']); ?></span>
                    </span>
                </h1>
                
                <!-- モバイル説明 -->
                <p class="mobile-description">
                    最新AIテクノロジーがあなたのビジネスに最適な補助金・助成金を瞬時に発見。専門家による完全サポートで成功率98.7%を実現。
                </p>
                
                <!-- モバイル統計セクションを削除し、シンプルな3行目タイトル追加 -->
                <div class="mobile-title-continuation">
                    <span class="mobile-title-3">成功まで完全サポート</span>
                </div>
                
                <!-- モバイルCTA -->
                <div class="mobile-cta">
                    <button onclick="startGrantSearch()" class="mobile-btn-primary">
                        <span class="btn-icon">🔍</span>
                        <span><?php echo gip_safe_output($hero_config['cta_primary_text']); ?></span>
                    </button>
                    
                    <button onclick="openAIConsultation()" class="mobile-btn-secondary">
                        <span class="btn-icon">💬</span>
                        <span><?php echo gip_safe_output($hero_config['cta_secondary_text']); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
:root {
    /* === モダンカラーパレット === */
    --color-white: #ffffff;
    --color-black: #000000;
    --color-yellow: #ffeb3b;
    --color-yellow-dark: #ffc107;
    --color-yellow-light: #fff59d;
    
    /* === グレースケール === */
    --color-gray-50: #fafafa;
    --color-gray-100: #f5f5f5;
    --color-gray-200: #eeeeee;
    --color-gray-300: #e0e0e0;
    --color-gray-400: #bdbdbd;
    --color-gray-500: #9e9e9e;
    --color-gray-600: #757575;
    --color-gray-700: #616161;
    --color-gray-800: #424242;
    --color-gray-900: #212121;
    
    /* === セマンティックカラー === */
    --color-primary: var(--color-yellow);
    --color-secondary: var(--color-black);
    --color-accent: var(--color-yellow-dark);
    --color-success: #4caf50;
    --color-info: #2196f3;
    --color-warning: #ff9800;
    --color-danger: #f44336;
    
    /* === テキストカラー === */
    --text-primary: var(--color-gray-900);
    --text-secondary: var(--color-gray-600);
    --text-tertiary: var(--color-gray-500);
    --text-inverse: var(--color-white);
    
    /* === 背景カラー === */
    --bg-primary: var(--color-white);
    --bg-secondary: var(--color-gray-50);
    --bg-tertiary: var(--color-gray-100);
    --bg-dark: var(--color-gray-900);
    
    /* === ボーダーカラー === */
    --border-light: var(--color-gray-200);
    --border-medium: var(--color-gray-300);
    --border-dark: var(--color-gray-400);
    
    /* === シャドウ === */
    --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    
    /* === スペーシング === */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    --spacing-2xl: 2.5rem;
    --spacing-3xl: 3rem;
    
    /* === ボーダーラディウス === */
    --radius-xs: 0.125rem;
    --radius-sm: 0.25rem;
    --radius-md: 0.375rem;
    --radius-lg: 0.5rem;
    --radius-xl: 0.75rem;
    --radius-2xl: 1rem;
    --radius-3xl: 1.5rem;
    --radius-full: 9999px;
    
    /* === トランジション === */
    --transition-fast: 0.15s ease-out;
    --transition-base: 0.2s ease-out;
    --transition-slow: 0.3s ease-out;
    --transition-slower: 0.5s ease-out;
    
    /* === タイポグラフィ === */
    --font-size-xs: 0.75rem;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
    --font-size-2xl: 1.5rem;
    --font-size-3xl: 1.875rem;
    --font-size-4xl: 2.25rem;
    --font-size-5xl: 3rem;
    --font-size-6xl: 3.75rem;
    
    --font-weight-light: 300;
    --font-weight-normal: 400;
    --font-weight-medium: 500;
    --font-weight-semibold: 600;
    --font-weight-bold: 700;
    --font-weight-extrabold: 800;
    --font-weight-black: 900;
    
    --line-height-tight: 1.25;
    --line-height-snug: 1.375;
    --line-height-normal: 1.5;
    --line-height-relaxed: 1.625;
    --line-height-loose: 2;
}

/* === ベーススタイル === */
.hero-stylish {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: var(--spacing-xl) 0;
    background: var(--bg-primary);
    color: var(--text-primary);
}

/* === 背景システム === */
.bg-system {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.bg-layer {
    position: absolute;
    inset: 0;
}

.bg-gradient {
    background: linear-gradient(135deg, 
        var(--bg-primary) 0%, 
        var(--bg-secondary) 30%, 
        var(--bg-tertiary) 70%,
        var(--bg-primary) 100%);
}

.bg-pattern {
    background-image: 
        linear-gradient(90deg, var(--border-light) 1px, transparent 1px),
        linear-gradient(var(--border-light) 1px, transparent 1px);
    background-size: 50px 50px;
    opacity: 0.3;
}

.floating-dots {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.dot {
    position: absolute;
    width: 4px;
    height: 4px;
    background: var(--color-primary);
    border-radius: 50%;
    opacity: 0;
    animation: float-dot 8s ease-in-out infinite;
}

.dot-1 { top: 10%; left: 10%; animation-delay: 0s; }
.dot-2 { top: 20%; right: 15%; animation-delay: 1s; }
.dot-3 { top: 30%; left: 20%; animation-delay: 2s; }
.dot-4 { top: 40%; right: 25%; animation-delay: 3s; }
.dot-5 { top: 60%; left: 15%; animation-delay: 4s; }
.dot-6 { top: 70%; right: 20%; animation-delay: 5s; }
.dot-7 { bottom: 20%; left: 25%; animation-delay: 6s; }
.dot-8 { bottom: 10%; right: 10%; animation-delay: 7s; }

/* === コンテナ === */
.container-main {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 var(--spacing-lg);
}

/* === デスクトップレイアウト === */
.desktop-layout {
    display: none;
}

@media (min-width: 1024px) {
    .desktop-layout {
        display: block;
    }
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: var(--spacing-3xl);
    align-items: center;
    min-height: calc(100vh - var(--spacing-xl) * 2);
}

/* === メインコンテンツ === */
.content-main {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xl);
    max-width: 600px;
}

/* === ステータスバッジ === */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    background: var(--color-black);
    color: var(--color-white);
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    width: fit-content;
    cursor: pointer;
    transition: var(--transition-base);
}

.status-badge:hover {
    background: var(--color-gray-800);
    transform: translateY(-2px);
}

.badge-dot {
    width: 6px;
    height: 6px;
    background: var(--color-primary);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

/* === メインタイトル === */
.main-title {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.title-line {
    line-height: var(--line-height-tight);
    letter-spacing: -0.02em;
}

.title-line-1 {
    font-size: var(--font-size-5xl);
    font-weight: var(--font-weight-light);
    color: var(--text-secondary);
    opacity: 0;
    transform: translateY(30px);
    animation: fade-up 0.8s ease-out 0.2s forwards;
}

.title-line-2 {
    font-size: var(--font-size-6xl);
    font-weight: var(--font-weight-black);
    opacity: 0;
    transform: translateY(30px);
    animation: fade-up 0.8s ease-out 0.4s forwards;
}

.ai-highlight {
    color: var(--color-black);
    position: relative;
}

.ai-highlight::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 8px;
    background: var(--color-primary);
    z-index: -1;
    transform: scaleX(0);
    animation: highlight-expand 1s ease-out 1s forwards;
}

.title-line-3 {
    font-size: var(--font-size-5xl);
    font-weight: var(--font-weight-light);
    color: var(--text-primary);
    opacity: 0;
    transform: translateY(30px);
    animation: fade-up 0.8s ease-out 0.6s forwards;
}

/* === 説明文 === */
.description {
    font-size: var(--font-size-lg);
    line-height: var(--line-height-relaxed);
    color: var(--text-secondary);
    font-weight: var(--font-weight-normal);
    opacity: 0;
    transform: translateY(20px);
    animation: fade-up 0.8s ease-out 0.8s forwards;
}

/* === CTAボタン === */
.cta-container {
    display: flex;
    gap: var(--spacing-lg);
    align-items: center;
    flex-wrap: wrap;
    opacity: 0;
    transform: translateY(20px);
    animation: fade-up 0.8s ease-out 1s forwards;
}

.btn-primary,
.btn-secondary {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    border: none;
    border-radius: var(--radius-xl);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    cursor: pointer;
    transition: var(--transition-base);
    text-decoration: none;
    padding: var(--spacing-lg) var(--spacing-xl);
}

.btn-primary {
    background: var(--color-primary);
    color: var(--color-black);
    box-shadow: var(--shadow-lg);
}

.btn-primary:hover {
    background: var(--color-yellow-dark);
    transform: translateY(-3px);
    box-shadow: var(--shadow-xl);
}

.btn-secondary {
    background: transparent;
    color: var(--text-primary);
    border: 2px solid var(--border-dark);
}

.btn-secondary:hover {
    background: var(--color-black);
    color: var(--color-white);
    border-color: var(--color-black);
    transform: translateY(-2px);
}

/* === 統計表示 === */
.stats-display {
    display: flex;
    gap: var(--spacing-xl);
    opacity: 0;
    transform: translateY(20px);
    animation: fade-up 0.8s ease-out 1.2s forwards;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-black);
    color: var(--text-primary);
    margin-bottom: var(--spacing-xs);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    font-weight: var(--font-weight-medium);
}

/* === マルチデバイスビジュアル === */
.visual-main {
    display: flex;
    justify-content: center;
    align-items: center;
    perspective: 1500px;
}

.multidevice-system {
    position: relative;
    transform-style: preserve-3d;
    transition: var(--transition-slow);
    width: 100%;
    height: 500px;
}

.multidevice-system:hover {
    transform: rotateY(-2deg) rotateX(1deg);
}

/* === PCモニター === */
.pc-monitor {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
}

.monitor-frame {
    position: relative;
    width: 480px;
    height: 300px;
    background: var(--color-gray-800);
    border-radius: var(--radius-xl);
    padding: 12px 12px 35px 12px;
    box-shadow: var(--shadow-2xl);
}

.monitor-screen {
    position: relative;
    width: 100%;
    height: 100%;
    background: var(--color-white);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: inset var(--shadow-sm);
}

.screen-content {
    position: relative;
    width: 100%;
    height: 100%;
    color: var(--text-primary);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    display: flex;
    flex-direction: column;
}

/* === システムヘッダー === */
.system-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-sm);
    background: var(--bg-tertiary);
    border-bottom: 1px solid var(--border-light);
}

.window-controls {
    display: flex;
    gap: var(--spacing-sm);
}

.control-btn {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition-fast);
}

.control-btn.close { background: #ff5f56; }
.control-btn.minimize { background: #ffbd2e; }
.control-btn.maximize { background: #27ca3f; }

.control-btn:hover {
    transform: scale(1.1);
}

.system-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.system-status {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
}

.status-indicator {
    width: 6px;
    height: 6px;
    background: var(--color-success);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

/* === メインダッシュボード === */
.dashboard-main {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    flex: 1;
}

.stats-panel {
    background: var(--bg-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: var(--spacing-md);
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid var(--border-light);
}

.panel-header h3 {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0;
}

.live-indicator {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.live-dot {
    width: 6px;
    height: 6px;
    background: var(--color-danger);
    border-radius: 50%;
    animation: pulse-red 1.5s ease-in-out infinite;
}

.live-indicator span {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    color: var(--color-danger);
    letter-spacing: 0.05em;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-sm);
}

.stat-card {
    background: var(--color-white);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: var(--spacing-sm);
    cursor: pointer;
    transition: var(--transition-base);
}

.stat-card:hover {
    transform: translateY(-2px);
    border-color: var(--color-primary);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-sm);
}

.stat-content .stat-number {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-black);
    color: var(--text-primary);
    margin-bottom: var(--spacing-xs);
}

.stat-content .stat-label {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    font-weight: var(--font-weight-medium);
}

/* === プログレス表示 === */
.progress-section {
    background: var(--bg-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: var(--spacing-md);
    text-align: center;
}

.progress-container {
    display: flex;
    justify-content: center;
}

.progress-circle {
    position: relative;
    width: 80px;
    height: 80px;
    background: conic-gradient(
        var(--color-primary) 0deg 
        calc(98.7% * 3.6deg), 
        var(--border-light) 
        calc(98.7% * 3.6deg) 360deg
    );
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-inner {
    width: 60px;
    height: 60px;
    background: var(--color-white);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.progress-number {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-black);
    color: var(--text-primary);
}

.progress-label {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
}

/* === アクティビティフィード === */
.activity-feed {
    background: var(--bg-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: var(--spacing-md);
}

.activity-header {
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid var(--border-light);
}

.activity-header h4 {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    border-radius: var(--radius-md);
    transition: var(--transition-base);
}

.activity-item:hover {
    background: var(--bg-tertiary);
}

.activity-icon {
    font-size: var(--font-size-sm);
    flex-shrink: 0;
}

.activity-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.activity-text span:first-child {
    font-size: var(--font-size-xs);
    color: var(--text-primary);
    font-weight: var(--font-weight-medium);
}

.activity-time {
    font-size: var(--font-size-xs);
    color: var(--text-tertiary);
}

/* === モニタースタンド === */
.monitor-stand {
    position: absolute;
    bottom: -50px;
    left: 50%;
    transform: translateX(-50%);
    z-index: -1;
}

.stand-neck {
    width: 30px;
    height: 50px;
    background: linear-gradient(180deg, var(--color-gray-700), var(--color-gray-800));
    border-radius: 0 0 var(--radius-md) var(--radius-md);
    margin: 0 auto;
}

.stand-base {
    width: 100px;
    height: 15px;
    background: linear-gradient(135deg, var(--color-gray-700), var(--color-gray-800));
    border-radius: var(--radius-xl);
    margin-top: -5px;
    box-shadow: var(--shadow-lg);
}

/* === タブレット === */
.tablet-device {
    position: absolute;
    top: 80px;
    right: -50px;
    z-index: 2;
    transform: rotate(5deg);
    transition: var(--transition-slow);
}

.multidevice-system:hover .tablet-device {
    transform: rotate(3deg) translateY(-5px);
}

.tablet-frame {
    width: 200px;
    height: 280px;
    background: var(--color-gray-700);
    border-radius: var(--radius-xl);
    padding: 20px 15px;
    box-shadow: var(--shadow-xl);
}

.tablet-screen {
    width: 100%;
    height: 100%;
    background: var(--color-white);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.tablet-content {
    width: 100%;
    height: 100%;
    color: var(--text-primary);
    display: flex;
    flex-direction: column;
}

.tablet-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-sm);
    background: var(--bg-tertiary);
    font-size: var(--font-size-xs);
}

.tablet-time {
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.tablet-status-icons {
    display: flex;
    gap: var(--spacing-xs);
    font-size: var(--font-size-xs);
}

.tablet-app {
    flex: 1;
    padding: var(--spacing-md);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.app-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.app-icon {
    font-size: var(--font-size-lg);
}

.app-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.tablet-stats {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.tablet-stat-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-light);
}

.tablet-stat-icon {
    font-size: var(--font-size-base);
}

.tablet-stat-content {
    flex: 1;
}

.tablet-stat-number {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.tablet-stat-label {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
}

.tablet-chart {
    margin-top: var(--spacing-md);
}

.chart-title {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.chart-bars {
    display: flex;
    align-items: end;
    gap: 2px;
    height: 40px;
    padding: var(--spacing-sm);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
}

.chart-bar {
    flex: 1;
    background: linear-gradient(to top, var(--color-primary), var(--color-yellow-light));
    border-radius: 1px;
    min-height: 20%;
    animation: chart-grow 2s ease-out infinite;
}

/* === スマートフォン === */
.smartphone-device {
    position: absolute;
    top: 120px;
    left: -30px;
    z-index: 1;
    transform: rotate(-8deg);
    transition: var(--transition-slow);
}

.multidevice-system:hover .smartphone-device {
    transform: rotate(-5deg) translateY(-3px);
}

.smartphone-frame {
    width: 120px;
    height: 220px;
    background: var(--color-gray-800);
    border-radius: var(--radius-2xl);
    padding: 15px 8px;
    box-shadow: var(--shadow-xl);
}

.smartphone-screen {
    width: 100%;
    height: 100%;
    background: var(--color-white);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.smartphone-content {
    width: 100%;
    height: 100%;
    color: var(--text-primary);
    display: flex;
    flex-direction: column;
}

.smartphone-statusbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-sm);
    background: var(--bg-tertiary);
    font-size: var(--font-size-xs);
}

.statusbar-time {
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.statusbar-icons {
    display: flex;
    gap: var(--spacing-xs);
    font-size: var(--font-size-xs);
}

.smartphone-app {
    flex: 1;
    padding: var(--spacing-sm);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.smartphone-app-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.smartphone-app-icon {
    font-size: var(--font-size-base);
}

.smartphone-app-title {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.smartphone-quick-stats {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.smartphone-stat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-sm);
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-light);
}

.smartphone-stat-number {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.smartphone-stat-label {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
}

.smartphone-action {
    margin-top: auto;
    padding: var(--spacing-sm);
}

.smartphone-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: var(--color-primary);
    color: var(--color-black);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    cursor: pointer;
    transition: var(--transition-base);
}

.smartphone-btn:hover {
    background: var(--color-yellow-dark);
    transform: scale(0.98);
}

/* === モバイルレイアウト === */
.mobile-layout {
    display: block;
    padding: var(--spacing-xl) 0;
}

@media (min-width: 1024px) {
    .mobile-layout {
        display: none;
    }
}

.mobile-content {
    max-width: 480px;
    margin: 0 auto;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xl);
}

.mobile-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    background: var(--color-black);
    color: var(--color-white);
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin: 0 auto;
    width: fit-content;
}

.mobile-status-dot {
    width: 6px;
    height: 6px;
    background: var(--color-primary);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

.mobile-title {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.mobile-title-1 {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-light);
    color: var(--text-secondary);
    line-height: var(--line-height-tight);
}

.mobile-title-2 {
    font-size: var(--font-size-4xl);
    font-weight: var(--font-weight-black);
    line-height: var(--line-height-tight);
}

.mobile-ai-highlight {
    color: var(--color-black);
    position: relative;
}

.mobile-ai-highlight::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: var(--color-primary);
    z-index: -1;
}

.mobile-title-continuation {
    margin-top: var(--spacing-sm);
}

.mobile-title-3 {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-light);
    color: var(--text-primary);
    line-height: var(--line-height-tight);
}

.mobile-description {
    font-size: var(--font-size-base);
    line-height: var(--line-height-relaxed);
    color: var(--text-secondary);
    font-weight: var(--font-weight-normal);
}

/* 以下のモバイル統計関連CSSを削除（モバイル簡素化のため）
CSS removed for mobile simplification */

.mobile-cta {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.mobile-btn-primary,
.mobile-btn-secondary {
    width: 100%;
    border: none;
    border-radius: var(--radius-xl);
    padding: var(--spacing-lg);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    cursor: pointer;
    transition: var(--transition-base);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
}

.mobile-btn-primary {
    background: var(--color-primary);
    color: var(--color-black);
    box-shadow: var(--shadow-lg);
}

.mobile-btn-primary:hover {
    background: var(--color-yellow-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
}

.mobile-btn-secondary {
    background: transparent;
    color: var(--text-primary);
    border: 2px solid var(--border-dark);
}

.mobile-btn-secondary:hover {
    background: var(--color-black);
    color: var(--color-white);
    border-color: var(--color-black);
}

/* === アニメーション === */
@keyframes float-dot {
    0%, 100% { 
        opacity: 0; 
        transform: translateY(0) scale(0.8); 
    }
    50% { 
        opacity: 1; 
        transform: translateY(-20px) scale(1.2); 
    }
}

@keyframes pulse {
    0%, 100% { 
        opacity: 1; 
        transform: scale(1); 
    }
    50% { 
        opacity: 0.7; 
        transform: scale(1.2); 
    }
}

@keyframes pulse-red {
    0%, 100% { 
        opacity: 1; 
        transform: scale(1); 
    }
    50% { 
        opacity: 0.7; 
        transform: scale(1.3); 
    }
}

@keyframes fade-up {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

@keyframes highlight-expand {
    from { 
        transform: scaleX(0); 
    }
    to { 
        transform: scaleX(1); 
    }
}

@keyframes chart-grow {
    0%, 100% { 
        transform: scaleY(0.8); 
    }
    50% { 
        transform: scaleY(1.2); 
    }
}

/* === レスポンシブ調整 === */
@media (max-width: 1200px) {
    .monitor-frame {
        width: 400px;
        height: 250px;
    }
    
    .tablet-device {
        right: -40px;
    }
    
    .smartphone-device {
        left: -25px;
    }
    
    .title-line-1,
    .title-line-3 {
        font-size: var(--font-size-4xl);
    }
    
    .title-line-2 {
        font-size: var(--font-size-5xl);
    }
}

@media (max-width: 768px) {
    .container-main {
        padding: 0 var(--spacing-md);
    }
    
    .mobile-title-1 {
        font-size: var(--font-size-2xl);
    }
    
    .mobile-title-2 {
        font-size: var(--font-size-3xl);
    }
    
    .mobile-title-3 {
        font-size: var(--font-size-2xl);
    }
    
    .mobile-description {
        font-size: var(--font-size-sm);
    }
}

@media (max-width: 640px) {
    .mobile-title-1 {
        font-size: var(--font-size-xl);
    }
    
    .mobile-title-2 {
        font-size: var(--font-size-2xl);
    }
    
    .mobile-title-3 {
        font-size: var(--font-size-xl);
    }
    
    .mobile-description {
        font-size: var(--font-size-sm);
        line-height: var(--line-height-normal);
    }
    
    .mobile-content {
        gap: var(--spacing-lg);
    }
}

/* === アクセシビリティ === */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

button:focus,
a:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.5);
}

/* === プリント対応 === */
@media print {
    .hero-stylish {
        background: white !important;
        color: black !important;
    }
    
    .floating-dots {
        display: none !important;
    }
}
</style>

<script>
/**
 * スタイリッシュ補助金・助成金サイト JavaScript システム
 */
class GrantHeroStylishSystem {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupAnimations();
        this.setupInteractions();
        this.setupCounters();
        this.setupMultideviceEffects();
        this.setupAccessibility();
    }
    
    setupAnimations() {
        // フローティングドットの初期化
        const dots = document.querySelectorAll('.dot');
        dots.forEach((dot, index) => {
            dot.style.animationDelay = `${index * 1}s`;
        });
        
        // Intersection Observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.stats-grid, .activity-list').forEach(el => {
            observer.observe(el);
        });
    }
    
    setupInteractions() {
        // ボタンホバーエフェクト
        const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .mobile-btn-primary, .mobile-btn-secondary');
        buttons.forEach(btn => {
            btn.addEventListener('mouseenter', this.handleButtonHover.bind(this));
            btn.addEventListener('mouseleave', this.handleButtonLeave.bind(this));
        });
        
        // 統計カードインタラクション
        const statCards = document.querySelectorAll('.stat-card, .mobile-stat-card, .tablet-stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', this.handleStatHover.bind(this));
            card.addEventListener('mouseleave', this.handleStatLeave.bind(this));
        });
        
        // ウィンドウコントロールボタン
        const controlBtns = document.querySelectorAll('.control-btn');
        controlBtns.forEach(btn => {
            btn.addEventListener('click', this.handleControlClick.bind(this));
        });
        
        // デバイスインタラクション
        this.setupDeviceInteractions();
    }
    
    setupDeviceInteractions() {
        const tabletDevice = document.querySelector('.tablet-device');
        if (tabletDevice) {
            tabletDevice.addEventListener('click', () => {
                this.showNotification('📱 タブレット版', 'タブレット用アプリケーションを表示中');
                this.animateDevice(tabletDevice);
            });
        }
        
        const smartphoneDevice = document.querySelector('.smartphone-device');
        if (smartphoneDevice) {
            smartphoneDevice.addEventListener('click', () => {
                this.showNotification('📱 スマートフォン版', 'モバイル用アプリケーションを表示中');
                this.animateDevice(smartphoneDevice);
            });
        }
    }
    
    animateDevice(device) {
        device.style.transform += ' scale(1.05)';
        setTimeout(() => {
            device.style.transform = device.style.transform.replace(' scale(1.05)', '');
        }, 200);
    }
    
    setupCounters() {
        const counters = document.querySelectorAll('[data-target]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        counters.forEach(counter => observer.observe(counter));
    }
    
    setupMultideviceEffects() {
        const multideviceSystem = document.querySelector('.multidevice-system');
        if (multideviceSystem) {
            // マウス追従3Dエフェクト
            multideviceSystem.addEventListener('mousemove', (e) => {
                const rect = multideviceSystem.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / centerY * 2;
                const rotateY = (x - centerX) / centerX * -2;
                
                multideviceSystem.style.transform = `perspective(1500px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            });
            
            multideviceSystem.addEventListener('mouseleave', () => {
                multideviceSystem.style.transform = 'perspective(1500px) rotateX(0deg) rotateY(0deg)';
            });
        }
    }
    
    setupAccessibility() {
        // キーボードナビゲーション
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
    }
    
    animateCounter(element) {
        const target = element.getAttribute('data-target');
        const numericMatch = target.match(/[\d.]+/);
        if (!numericMatch) return;
        
        const numericTarget = parseFloat(numericMatch[0]);
        if (!isFinite(numericTarget) || isNaN(numericTarget)) return;
        
        let current = 0;
        const increment = numericTarget / 100;
        const duration = 2000;
        const stepTime = duration / 100;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= numericTarget) {
                current = numericTarget;
                clearInterval(timer);
            }
            
            if (target.includes('%')) {
                element.textContent = current.toFixed(1) + '%';
            } else if (target.includes('時間')) {
                element.textContent = Math.floor(current) + '時間';
            } else if (target.includes(',')) {
                element.textContent = Math.floor(current).toLocaleString();
            } else {
                element.textContent = Math.floor(current);
            }
        }, stepTime);
    }
    
    handleButtonHover(e) {
        const btn = e.currentTarget;
        btn.style.transform = 'translateY(-3px) scale(1.02)';
    }
    
    handleButtonLeave(e) {
        const btn = e.currentTarget;
        btn.style.transform = '';
    }
    
    handleStatHover(e) {
        const card = e.currentTarget;
        card.style.transform = 'translateY(-3px) scale(1.02)';
        card.style.borderColor = 'var(--color-primary)';
    }
    
    handleStatLeave(e) {
        const card = e.currentTarget;
        card.style.transform = '';
        card.style.borderColor = '';
    }
    
    handleControlClick(e) {
        const btn = e.currentTarget;
        
        btn.style.transform = 'scale(0.9)';
        setTimeout(() => {
            btn.style.transform = '';
        }, 150);
        
        if (btn.classList.contains('close')) {
            this.showNotification('❌ システム終了', 'アプリケーションを終了しますか？');
        } else if (btn.classList.contains('minimize')) {
            this.showNotification('➖ 最小化', 'アプリケーションを最小化しました');
        } else if (btn.classList.contains('maximize')) {
            this.showNotification('⬜ 最大化', 'アプリケーションを最大化しました');
        }
    }
    
    showNotification(title, message) {
        // 既存の通知を削除
        const existingNotifications = document.querySelectorAll('.system-notification');
        existingNotifications.forEach(notification => {
            notification.remove();
        });
        
        const notification = document.createElement('div');
        notification.className = 'system-notification';
        notification.innerHTML = `
            <div class="notification-header">
                <span class="notification-title">${title}</span>
            </div>
            <div class="notification-message">${message}</div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--color-white);
            border: 2px solid var(--color-primary);
            border-radius: var(--radius-xl);
            padding: var(--spacing-lg);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: var(--font-size-sm);
            max-width: 320px;
            z-index: 10000;
            box-shadow: var(--shadow-xl);
            transform: translateX(100%);
            transition: transform var(--transition-base);
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
}

// グローバル関数
function startGrantSearch() {
    console.log('助成金検索を開始します');
    
    const system = window.grantHeroStylishSystem;
    if (system && system.showNotification) {
        system.showNotification('🔍 助成金検索開始', 'AI が12,847件のデータベースから最適な助成金を検索中...');
    }
    
    // 視覚的フィードバック
    const button = event?.target?.closest('button');
    if (button) {
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = '';
        }, 150);
    }
    
    // AIアシスタントを開いて検索を開始
    if (window.aiAssistant) {
        window.aiAssistant.openChat();
        window.aiAssistant.elements.input.value = 'おすすめの助成金を教えてください';
        window.aiAssistant.sendMessage();
    } else {
        // フォールバック: 助成金一覧ページへ遷移
        window.location.href = '/grant/';
    }
}

function openAIConsultation() {
    console.log('AI相談を開始します');
    
    const system = window.grantHeroStylishSystem;
    if (system && system.showNotification) {
        system.showNotification('💬 AI専門家相談', 'AI専門家が最適な助成金・補助金をご提案いたします');
    }
    
    // AIアシスタントを開く
    if (window.aiAssistant) {
        window.aiAssistant.openChat();
    } else {
        // AIアシスタントがまだ読み込まれていない場合は遅延実行
        setTimeout(() => {
            if (window.aiAssistant) {
                window.aiAssistant.openChat();
            }
        }, 1000);
    }
}

// 初期化
document.addEventListener('DOMContentLoaded', () => {
    try {
        window.grantHeroStylishSystem = new GrantHeroStylishSystem();
        console.log('✨ Grant Hero Stylish System initialized successfully');
    } catch (error) {
        console.error('❌ Initialization error:', error);
    }
});
</script>

<!-- 通知システム用CSS -->
<style>
.notification-header {
    margin-bottom: var(--spacing-sm);
    font-weight: var(--font-weight-bold);
}

.notification-message {
    font-size: var(--font-size-sm);
    opacity: 0.8;
    line-height: var(--line-height-normal);
}

.system-notification {
    animation: notification-slide var(--transition-base);
}

@keyframes notification-slide {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}

/* キーボードナビゲーション時のフォーカススタイル */
body.keyboard-navigation button:focus,
body.keyboard-navigation a:focus {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}
</style>