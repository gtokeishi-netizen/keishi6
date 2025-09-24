<?php
/**
 * Single Grant Template - Clean Monochrome Design
 * 助成金詳細ページ - シンプルモノクロデザイン
 * 
 * @package Grant_Insight_Clean
 * @version 9.0.0-monochrome
 */

get_header();

// Security and post validation
if (!have_posts()) {
    wp_redirect(home_url('/404'));
    exit;
}

the_post();
$post_id = get_the_ID();

// gi_safe_get_meta function is defined in inc/data-functions.php

// Core grant data
$ai_summary = gi_safe_get_meta($post_id, 'ai_summary', '');
$max_amount = gi_safe_get_meta($post_id, 'max_amount', '');
$max_amount_numeric = intval(gi_safe_get_meta($post_id, 'max_amount_numeric', 0));
$subsidy_rate = gi_safe_get_meta($post_id, 'subsidy_rate', '');
$deadline_date = gi_safe_get_meta($post_id, 'deadline_date', '');
$application_status = gi_safe_get_meta($post_id, 'application_status', 'open');
$grant_difficulty = gi_safe_get_meta($post_id, 'grant_difficulty', 'normal');
$grant_success_rate = intval(gi_safe_get_meta($post_id, 'grant_success_rate', 0));
$organization = gi_safe_get_meta($post_id, 'organization', '');
$grant_target = gi_safe_get_meta($post_id, 'grant_target', '');
$application_period = gi_safe_get_meta($post_id, 'application_period', '');
$official_url = gi_safe_get_meta($post_id, 'official_url', '');
$eligible_expenses = gi_safe_get_meta($post_id, 'eligible_expenses', '');
$application_method = gi_safe_get_meta($post_id, 'application_method', '');
$required_documents = gi_safe_get_meta($post_id, 'required_documents', '');
$contact_info = gi_safe_get_meta($post_id, 'contact_info', '');
$is_featured = gi_safe_get_meta($post_id, 'is_featured', false);

// Taxonomy data
$categories = get_the_terms($post_id, 'grant_category');
$prefectures = get_the_terms($post_id, 'grant_prefecture');
$industries = get_the_terms($post_id, 'grant_industry');

$main_category = ($categories && !is_wp_error($categories)) ? $categories[0] : null;
$main_prefecture = ($prefectures && !is_wp_error($prefectures)) ? $prefectures[0] : null;

// Format amount
$formatted_amount = '';
if ($max_amount_numeric > 0) {
    if ($max_amount_numeric >= 10000) {
        $formatted_amount = number_format($max_amount_numeric / 10000) . '億円';
    } else if ($max_amount_numeric >= 100) {
        $formatted_amount = number_format($max_amount_numeric / 100) . '万円';
    } else {
        $formatted_amount = number_format($max_amount_numeric) . '万円';
    }
} elseif ($max_amount) {
    $formatted_amount = $max_amount;
}

// Deadline calculation
$deadline_info = '';
$deadline_class = '';
if ($deadline_date) {
    $deadline_timestamp = is_numeric($deadline_date) ? intval($deadline_date) : strtotime($deadline_date);
    if ($deadline_timestamp && $deadline_timestamp > 0) {
        $deadline_info = date('Y年n月j日', $deadline_timestamp);
        $current_time = current_time('timestamp');
        $days_remaining = ceil(($deadline_timestamp - $current_time) / (60 * 60 * 24));
        
        if ($days_remaining <= 0) {
            $deadline_class = 'expired';
            $deadline_info .= ' (募集終了)';
        } elseif ($days_remaining <= 7) {
            $deadline_class = 'urgent';
            $deadline_info .= ' (残り' . $days_remaining . '日)';
        } elseif ($days_remaining <= 30) {
            $deadline_class = 'warning';
            $deadline_info .= ' (残り' . $days_remaining . '日)';
        }
    }
}

// Difficulty configuration
$difficulty_config = [
    'easy' => ['label' => '易しい', 'color' => '#22c55e', 'icon' => 'fa-smile'],
    'normal' => ['label' => '普通', 'color' => '#525252', 'icon' => 'fa-meh'],
    'hard' => ['label' => '難しい', 'color' => '#f59e0b', 'icon' => 'fa-frown'],
    'expert' => ['label' => '専門的', 'color' => '#ef4444', 'icon' => 'fa-dizzy']
];
$difficulty_data = $difficulty_config[$grant_difficulty] ?? $difficulty_config['normal'];

// Status mapping
$status_config = [
    'open' => ['label' => '募集中', 'color' => '#22c55e', 'icon' => 'fa-circle-check'],
    'upcoming' => ['label' => '募集予定', 'color' => '#f59e0b', 'icon' => 'fa-clock'],
    'closed' => ['label' => '募集終了', 'color' => '#6b7280', 'icon' => 'fa-times-circle'],
    'suspended' => ['label' => '一時停止', 'color' => '#ef4444', 'icon' => 'fa-pause-circle']
];
$status_data = $status_config[$application_status] ?? $status_config['open'];

// Update view count
$current_views = intval(get_post_meta($post_id, 'views_count', true));
update_post_meta($post_id, 'views_count', $current_views + 1);
?>

<style>
/* ===============================================
   CLEAN SINGLE GRANT PAGE - MONOCHROME DESIGN
   =============================================== */

:root {
    /* Monochrome Color System */
    --page-bg: #ffffff;
    --page-text: #171717;
    --page-text-muted: #737373;
    --page-text-light: #a3a3a3;
    --page-border: #e5e5e5;
    --page-border-dark: #d4d4d4;
    --page-accent: #000000;
    --page-hover: #f5f5f5;
    --page-section-bg: #fafafa;
    
    /* Semantic colors for status only */
    --page-success: #22c55e;
    --page-warning: #f59e0b;
    --page-danger: #ef4444;
    --page-info: #3b82f6;
    
    /* Shadows */
    --page-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --page-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --page-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    
    /* Transitions */
    --page-transition: all 0.2s ease;
}

/* Main container */
.grant-single {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    background: var(--page-bg);
}

@media (min-width: 768px) {
    .grant-single {
        padding: 3rem 2rem;
    }
}

/* Header section */
.grant-header {
    background: var(--page-bg);
    border: 1px solid var(--page-border);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--page-shadow-sm);
}

.grant-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.grant-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: white;
}

.badge-status { background: var(--page-success); }
.badge-status.upcoming { background: var(--page-warning); }
.badge-status.closed { background: #6b7280; }
.badge-status.urgent { background: var(--page-danger); }

.badge-featured { background: var(--page-accent); }
.badge-difficulty { background: #525252; }
.badge-category { background: #737373; }

.grant-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--page-text);
    line-height: 1.3;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .grant-title {
        font-size: 2.5rem;
    }
}

.grant-subtitle {
    color: var(--page-text-muted);
    font-size: 1.125rem;
    margin-bottom: 1.5rem;
}

.grant-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1.5rem;
    background: var(--page-section-bg);
    border-radius: 0.75rem;
    border: 1px solid var(--page-border);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.meta-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: var(--page-accent);
    color: white;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.meta-content h4 {
    margin: 0;
    font-size: 0.875rem;
    color: var(--page-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.meta-content p {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--page-text);
}

/* Content sections */
.grant-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (min-width: 1024px) {
    .grant-content {
        grid-template-columns: 2fr 1fr;
    }
}

.content-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.content-section {
    background: var(--page-bg);
    border: 1px solid var(--page-border);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--page-shadow-sm);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--page-text);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-icon {
    width: 1.5rem;
    height: 1.5rem;
    color: var(--page-accent);
}

.section-content {
    color: var(--page-text);
    line-height: 1.7;
}

.section-content p {
    margin-bottom: 1rem;
}

.section-content p:last-child {
    margin-bottom: 0;
}

/* Sidebar */
.content-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.sidebar-section {
    background: var(--page-bg);
    border: 1px solid var(--page-border);
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--page-shadow-sm);
}

.sidebar-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--page-text);
    margin-bottom: 1rem;
}

/* Action buttons */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: var(--page-transition);
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: var(--page-accent);
    color: white;
}

.btn-primary:hover {
    background: #262626;
    transform: translateY(-1px);
    box-shadow: var(--page-shadow-md);
}

.btn-secondary {
    background: transparent;
    color: var(--page-text);
    border: 2px solid var(--page-border);
}

.btn-secondary:hover {
    border-color: var(--page-accent);
    background: var(--page-hover);
}

.btn-outline {
    background: transparent;
    color: var(--page-text-muted);
    border: 1px solid var(--page-border);
}

.btn-outline:hover {
    background: var(--page-section-bg);
    color: var(--page-text);
}

/* Progress bars */
.progress-bar {
    width: 100%;
    height: 0.5rem;
    background: var(--page-border);
    border-radius: 0.25rem;
    overflow: hidden;
    margin-top: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: var(--page-accent);
    border-radius: 0.25rem;
    transition: width 0.3s ease;
}

/* Stats grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: var(--page-section-bg);
    border-radius: 0.75rem;
    border: 1px solid var(--page-border);
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--page-text);
    display: block;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--page-text-muted);
    margin-top: 0.25rem;
}

/* Tags */
.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    background: var(--page-section-bg);
    color: var(--page-text-muted);
    border: 1px solid var(--page-border);
    border-radius: 1.5rem;
    font-size: 0.875rem;
    text-decoration: none;
    transition: var(--page-transition);
}

.tag:hover {
    background: var(--page-hover);
    color: var(--page-text);
}

/* Responsive */
@media (max-width: 768px) {
    .grant-single {
        padding: 1rem;
    }
    
    .grant-header {
        padding: 1.5rem;
    }
    
    .grant-title {
        font-size: 1.75rem;
    }
    
    .grant-meta {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .content-section {
        padding: 1.5rem;
    }
    
    .action-buttons {
        position: sticky;
        bottom: 1rem;
        background: var(--page-bg);
        padding: 1rem;
        border-radius: 1rem;
        box-shadow: var(--page-shadow-lg);
        border: 1px solid var(--page-border);
    }
}

/* Print styles */
@media print {
    .grant-single {
        box-shadow: none;
        border: none;
    }
    
    .action-buttons,
    .content-sidebar {
        display: none;
    }
    
    .grant-content {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="grant-single">
    <!-- Header Section -->
    <header class="grant-header">
        <!-- Badges -->
        <div class="grant-badges">
            <span class="grant-badge badge-status <?php echo $application_status; ?> <?php echo $deadline_class; ?>" style="background-color: <?php echo $status_data['color']; ?>">
                <i class="fas <?php echo $status_data['icon']; ?>"></i>
                <?php echo $status_data['label']; ?>
            </span>
            
            <?php if ($is_featured): ?>
            <span class="grant-badge badge-featured">
                <i class="fas fa-star"></i>
                おすすめ
            </span>
            <?php endif; ?>
            
            <?php if ($grant_difficulty !== 'normal'): ?>
            <span class="grant-badge badge-difficulty" style="background-color: <?php echo $difficulty_data['color']; ?>">
                <i class="fas <?php echo $difficulty_data['icon']; ?>"></i>
                <?php echo $difficulty_data['label']; ?>
            </span>
            <?php endif; ?>
            
            <?php if ($main_category): ?>
            <span class="grant-badge badge-category">
                <i class="fas fa-tag"></i>
                <?php echo esc_html($main_category->name); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Title -->
        <h1 class="grant-title"><?php the_title(); ?></h1>
        
        <!-- Subtitle -->
        <?php if ($ai_summary): ?>
        <p class="grant-subtitle"><?php echo esc_html(wp_trim_words($ai_summary, 30, '...')); ?></p>
        <?php endif; ?>
        
        <!-- Key Meta Information -->
        <div class="grant-meta">
            <?php if ($formatted_amount): ?>
            <div class="meta-item">
                <div class="meta-icon">
                    <i class="fas fa-yen-sign"></i>
                </div>
                <div class="meta-content">
                    <h4>助成額</h4>
                    <p><?php echo esc_html($formatted_amount); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($subsidy_rate): ?>
            <div class="meta-item">
                <div class="meta-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="meta-content">
                    <h4>補助率</h4>
                    <p><?php echo esc_html($subsidy_rate); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($deadline_info): ?>
            <div class="meta-item">
                <div class="meta-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="meta-content">
                    <h4>締切日</h4>
                    <p class="<?php echo $deadline_class; ?>"><?php echo esc_html($deadline_info); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_success_rate > 0): ?>
            <div class="meta-item">
                <div class="meta-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="meta-content">
                    <h4>採択率</h4>
                    <p><?php echo $grant_success_rate; ?>%</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Content Grid -->
    <div class="grant-content">
        <!-- Main Content -->
        <div class="content-main">
            <?php if ($ai_summary): ?>
            <!-- AI Summary -->
            <section class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-robot section-icon"></i>
                    AI要約
                </h2>
                <div class="section-content">
                    <p><?php echo esc_html($ai_summary); ?></p>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Main Content -->
            <section class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-file-alt section-icon"></i>
                    詳細情報
                </h2>
                <div class="section-content">
                    <?php the_content(); ?>
                </div>
            </section>
            
            <?php if ($eligible_expenses): ?>
            <!-- Eligible Expenses -->
            <section class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-list-check section-icon"></i>
                    対象経費
                </h2>
                <div class="section-content">
                    <?php echo wpautop(esc_html($eligible_expenses)); ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if ($required_documents): ?>
            <!-- Required Documents -->
            <section class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-clipboard-list section-icon"></i>
                    必要書類
                </h2>
                <div class="section-content">
                    <?php echo wpautop(esc_html($required_documents)); ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <aside class="content-sidebar">
            <!-- Action Buttons -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">アクション</h3>
                <div class="action-buttons">
                    <?php if ($official_url): ?>
                    <a href="<?php echo esc_url($official_url); ?>" class="action-btn btn-primary" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt"></i>
                        公式サイト
                    </a>
                    <?php endif; ?>
                    
                    <button class="action-btn btn-secondary" onclick="toggleFavorite(<?php echo $post_id; ?>)">
                        <i class="fas fa-heart"></i>
                        お気に入り
                    </button>
                    
                    <button class="action-btn btn-outline" onclick="shareGrant()">
                        <i class="fas fa-share-alt"></i>
                        シェア
                    </button>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <?php if ($grant_success_rate > 0 || $organization): ?>
            <div class="sidebar-section">
                <h3 class="sidebar-title">統計情報</h3>
                <div class="stats-grid">
                    <?php if ($grant_success_rate > 0): ?>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $grant_success_rate; ?>%</span>
                        <span class="stat-label">採択率</span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $grant_success_rate; ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($organization): ?>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo mb_strlen($organization) > 10 ? mb_substr($organization, 0, 10) . '...' : $organization; ?></span>
                        <span class="stat-label">実施機関</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Additional Info -->
            <?php if ($application_method || $contact_info): ?>
            <div class="sidebar-section">
                <h3 class="sidebar-title">申請情報</h3>
                
                <?php if ($application_method): ?>
                <div class="section-content">
                    <h4>申請方法</h4>
                    <p><?php echo esc_html($application_method); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($contact_info): ?>
                <div class="section-content">
                    <h4>お問い合わせ</h4>
                    <p><?php echo esc_html($contact_info); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Tags -->
            <?php if ($categories || $prefectures): ?>
            <div class="sidebar-section">
                <h3 class="sidebar-title">関連タグ</h3>
                <div class="tags-list">
                    <?php if ($categories && !is_wp_error($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                        <a href="<?php echo get_term_link($category); ?>" class="tag">
                            <i class="fas fa-tag"></i>
                            <?php echo esc_html($category->name); ?>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if ($prefectures && !is_wp_error($prefectures)): ?>
                        <?php foreach ($prefectures as $prefecture): ?>
                        <a href="<?php echo get_term_link($prefecture); ?>" class="tag">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo esc_html($prefecture->name); ?>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</main>

<script>
// Enhanced functionality
function toggleFavorite(postId) {
    // Implement favorite toggle functionality
    console.log('Toggle favorite for post:', postId);
    // Add your favorite logic here
}

function shareGrant() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: '<?php echo esc_js(wp_trim_words($ai_summary, 20, "")); ?>',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('URLをコピーしました');
        });
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 300);
    });
});
</script>

<?php get_footer(); ?>