<?php
/**
 * Complete Single Grant Template - All ACF Fields & Taxonomies
 * 助成金詳細ページ完全版 - 全ACFフィールド・タクソノミー対応
 * 
 * @package Grant_Insight_Perfect_Complete
 * @version 10.0.0-complete
 */

get_header();

// Security and post validation
if (!have_posts()) {
    wp_redirect(home_url('/404'));
    exit;
}

the_post();
$post_id = get_the_ID();

// Comprehensive ACF field retrieval (all 30+ fields)
$grant_data = array(
    // 基本情報
    'organization' => get_field('organization', $post_id) ?: '',
    'organization_type' => get_field('organization_type', $post_id) ?: '',
    
    // 金額情報
    'max_amount' => get_field('max_amount', $post_id) ?: '',
    'max_amount_numeric' => intval(get_field('max_amount_numeric', $post_id)),
    'min_amount' => intval(get_field('min_amount', $post_id)),
    'subsidy_rate' => get_field('subsidy_rate', $post_id) ?: '',
    'amount_note' => get_field('amount_note', $post_id) ?: '',
    
    // 期間・締切情報
    'deadline' => get_field('deadline', $post_id) ?: '',
    'deadline_date' => get_field('deadline_date', $post_id) ?: '',
    'application_period' => get_field('application_period', $post_id) ?: '',
    'deadline_note' => get_field('deadline_note', $post_id) ?: '',
    'application_status' => get_field('application_status', $post_id) ?: 'open',
    
    // 対象・条件
    'grant_target' => get_field('grant_target', $post_id) ?: '',
    'eligible_expenses' => get_field('eligible_expenses', $post_id) ?: '',
    'grant_difficulty' => get_field('grant_difficulty', $post_id) ?: 'normal',
    'grant_success_rate' => intval(get_field('grant_success_rate', $post_id)),
    'required_documents' => get_field('required_documents', $post_id) ?: '',
    
    // 申請・連絡先
    'application_method' => get_field('application_method', $post_id) ?: '',
    'contact_info' => get_field('contact_info', $post_id) ?: '',
    'official_url' => get_field('official_url', $post_id) ?: '',
    'external_link' => get_field('external_link', $post_id) ?: '',
    
    // 管理設定
    'is_featured' => get_field('is_featured', $post_id) ?: false,
    'priority_order' => intval(get_field('priority_order', $post_id)),
    'views_count' => intval(get_field('views_count', $post_id)),
    'last_updated' => get_field('last_updated', $post_id) ?: '',
    'admin_notes' => get_field('admin_notes', $post_id) ?: '',
    
    // AI関連（拡張フィールド）
    'ai_summary' => get_field('ai_summary', $post_id) ?: get_post_meta($post_id, 'ai_summary', true),
    
    // Excel/Import関連の追加フィールド
    'recruitment_start' => get_post_meta($post_id, 'recruitment_start', true) ?: '',
    'application_deadline' => get_post_meta($post_id, 'application_deadline', true) ?: '',
    'target_expenses' => get_post_meta($post_id, 'target_expenses', true) ?: '',
    'success_rate' => get_post_meta($post_id, 'success_rate', true) ?: '',
    'difficulty' => get_post_meta($post_id, 'difficulty', true) ?: '',
);

// Comprehensive taxonomy data
$taxonomies = array(
    'categories' => get_the_terms($post_id, 'grant_category'),
    'prefectures' => get_the_terms($post_id, 'grant_prefecture'),
    'tags' => get_the_terms($post_id, 'grant_tag'),
    'industries' => get_the_terms($post_id, 'grant_industry'), // 追加タクソノミー
);

$main_category = ($taxonomies['categories'] && !is_wp_error($taxonomies['categories'])) ? $taxonomies['categories'][0] : null;
$main_prefecture = ($taxonomies['prefectures'] && !is_wp_error($taxonomies['prefectures'])) ? $taxonomies['prefectures'][0] : null;

// Format amounts
$formatted_amount = '';
$max_amount_yen = $grant_data['max_amount_numeric'];
if ($max_amount_yen > 0) {
    if ($max_amount_yen >= 100000000) { // 1億円以上
        $formatted_amount = number_format($max_amount_yen / 100000000, 1) . '億円';
    } elseif ($max_amount_yen >= 10000) { // 1万円以上
        $formatted_amount = number_format($max_amount_yen / 10000) . '万円';
    } else {
        $formatted_amount = number_format($max_amount_yen) . '円';
    }
} elseif ($grant_data['max_amount']) {
    $formatted_amount = $grant_data['max_amount'];
}

$formatted_min_amount = '';
if ($grant_data['min_amount'] > 0) {
    if ($grant_data['min_amount'] >= 10000) {
        $formatted_min_amount = number_format($grant_data['min_amount'] / 10000) . '万円';
    } else {
        $formatted_min_amount = number_format($grant_data['min_amount']) . '円';
    }
}

// Organization type mapping
$org_type_labels = array(
    'national' => '国（省庁）',
    'prefecture' => '都道府県',
    'city' => '市区町村', 
    'public_org' => '公的機関',
    'private_org' => '民間団体',
    'other' => 'その他'
);

// Application method mapping
$method_labels = array(
    'online' => 'オンライン申請',
    'mail' => '郵送申請',
    'visit' => '窓口申請',
    'mixed' => 'オンライン・郵送併用'
);

// Deadline calculation
$deadline_info = '';
$deadline_class = '';
$days_remaining = 0;

if ($grant_data['deadline_date']) {
    $deadline_timestamp = strtotime($grant_data['deadline_date']);
    if ($deadline_timestamp && $deadline_timestamp > 0) {
        $deadline_info = date('Y年n月j日', $deadline_timestamp);
        $current_time = current_time('timestamp');
        $days_remaining = ceil(($deadline_timestamp - $current_time) / (60 * 60 * 24));
        
        if ($days_remaining <= 0) {
            $deadline_class = 'expired';
            $deadline_info .= ' (募集終了)';
        } elseif ($days_remaining <= 7) {
            $deadline_class = 'urgent';
            $deadline_info .= ' (あと' . $days_remaining . '日)';
        } elseif ($days_remaining <= 30) {
            $deadline_class = 'warning';
            $deadline_info .= ' (あと' . $days_remaining . '日)';
        }
    }
} elseif ($grant_data['deadline']) {
    $deadline_info = $grant_data['deadline'];
}

// Difficulty configuration
$difficulty_configs = array(
    'easy' => array('label' => '易しい', 'color' => '#22c55e', 'icon' => 'fa-smile'),
    'normal' => array('label' => '普通', 'color' => '#525252', 'icon' => 'fa-meh'),
    'hard' => array('label' => '難しい', 'color' => '#f59e0b', 'icon' => 'fa-frown'),
    'expert' => array('label' => '専門的', 'color' => '#ef4444', 'icon' => 'fa-dizzy')
);
$difficulty = $grant_data['difficulty'] ?: $grant_data['grant_difficulty'];
$difficulty_data = $difficulty_configs[$difficulty] ?? $difficulty_configs['normal'];

// Status mapping
$status_configs = array(
    'open' => array('label' => '募集中', 'color' => '#22c55e', 'icon' => 'fa-circle-check'),
    'upcoming' => array('label' => '募集予定', 'color' => '#f59e0b', 'icon' => 'fa-clock'),
    'closed' => array('label' => '募集終了', 'color' => '#6b7280', 'icon' => 'fa-times-circle'),
    'suspended' => array('label' => '一時停止', 'color' => '#ef4444', 'icon' => 'fa-pause-circle')
);
$status_data = $status_configs[$grant_data['application_status']] ?? $status_configs['open'];

// Update view count
$grant_data['views_count']++;
update_post_meta($post_id, 'views_count', $grant_data['views_count']);
?>

<style>
/* ===============================================
   COMPLETE GRANT SINGLE PAGE - ALL FIELDS DESIGN
   =============================================== */

:root {
    /* Comprehensive Color System */
    --grant-bg: #ffffff;
    --grant-text: #1a1a1a;
    --grant-text-muted: #6b7280;
    --grant-text-light: #9ca3af;
    --grant-border: #e5e7eb;
    --grant-border-dark: #d1d5db;
    --grant-accent: #000000;
    --grant-accent-light: #374151;
    --grant-hover: #f9fafb;
    --grant-section-bg: #fafafa;
    
    /* Status colors */
    --grant-success: #10b981;
    --grant-warning: #f59e0b;
    --grant-danger: #ef4444;
    --grant-info: #3b82f6;
    --grant-featured: #8b5cf6;
    
    /* Highlight colors */
    --grant-highlight: #fef3c7;
    --grant-highlight-strong: #f59e0b;
    
    /* Shadows */
    --grant-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --grant-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --grant-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    
    /* Transitions */
    --grant-transition: all 0.2s ease;
}

/* Main container */
.grant-complete {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
    background: var(--grant-bg);
    font-family: "Noto Sans JP", -apple-system, BlinkMacSystemFont, sans-serif;
}

@media (min-width: 768px) {
    .grant-complete {
        padding: 3rem 2rem;
    }
}

/* Header section with comprehensive badges */
.grant-header-complete {
    background: var(--grant-bg);
    border: 1px solid var(--grant-border);
    border-radius: 1.25rem;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--grant-shadow-md);
}

.grant-badges-complete {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.grant-badge-complete {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: white;
    transition: var(--grant-transition);
}

.grant-badge-complete:hover {
    transform: translateY(-1px);
    box-shadow: var(--grant-shadow-sm);
}

.badge-status-complete { background: var(--grant-success); }
.badge-status-complete.upcoming { background: var(--grant-warning); }
.badge-status-complete.closed { background: #6b7280; }
.badge-status-complete.urgent { background: var(--grant-danger); }
.badge-featured-complete { background: var(--grant-featured); }
.badge-difficulty-complete { background: var(--grant-accent-light); }
.badge-category-complete { background: var(--grant-info); }
.badge-prefecture-complete { background: #059669; }

/* Title and meta section */
.grant-title-complete {
    font-size: 2.25rem;
    font-weight: 800;
    color: var(--grant-text);
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
    .grant-title-complete {
        font-size: 3rem;
    }
}

.grant-subtitle-complete {
    color: var(--grant-text-muted);
    font-size: 1.25rem;
    line-height: 1.6;
    margin-bottom: 2rem;
}

/* Comprehensive meta grid */
.grant-meta-complete {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
    background: var(--grant-section-bg);
    border-radius: 1rem;
    border: 1px solid var(--grant-border);
    margin-bottom: 2rem;
}

.meta-item-complete {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.meta-icon-complete {
    width: 3rem;
    height: 3rem;
    background: var(--grant-accent);
    color: white;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.25rem;
}

.meta-content-complete h4 {
    margin: 0;
    font-size: 0.875rem;
    color: var(--grant-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.meta-content-complete p {
    margin: 0.25rem 0 0 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--grant-text);
}

.meta-content-complete .highlight {
    background: var(--grant-highlight);
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    color: var(--grant-highlight-strong);
    font-weight: 800;
}

/* Content layout */
.grant-content-complete {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (min-width: 1200px) {
    .grant-content-complete {
        grid-template-columns: 2fr 1fr;
    }
}

.content-main-complete {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.content-section-complete {
    background: var(--grant-bg);
    border: 1px solid var(--grant-border);
    border-radius: 1.25rem;
    padding: 2.5rem;
    box-shadow: var(--grant-shadow-sm);
    transition: var(--grant-transition);
}

.content-section-complete:hover {
    box-shadow: var(--grant-shadow-md);
}

.section-title-complete {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--grant-text);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--grant-border);
}

.section-icon-complete {
    width: 2rem;
    height: 2rem;
    color: var(--grant-accent);
}

.section-content-complete {
    color: var(--grant-text);
    line-height: 1.8;
}

.section-content-complete p {
    margin-bottom: 1.25rem;
}

.section-content-complete ul, 
.section-content-complete ol {
    padding-left: 1.5rem;
    margin-bottom: 1.25rem;
}

.section-content-complete li {
    margin-bottom: 0.75rem;
}

/* Sidebar enhancements */
.content-sidebar-complete {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.sidebar-section-complete {
    background: var(--grant-bg);
    border: 1px solid var(--grant-border);
    border-radius: 1.25rem;
    padding: 2rem;
    box-shadow: var(--grant-shadow-sm);
}

.sidebar-title-complete {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--grant-text);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Enhanced action buttons */
.action-buttons-complete {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.action-btn-complete {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-radius: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: var(--grant-transition);
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary-complete {
    background: var(--grant-accent);
    color: white;
}

.btn-primary-complete:hover {
    background: #262626;
    transform: translateY(-2px);
    box-shadow: var(--grant-shadow-lg);
}

.btn-secondary-complete {
    background: transparent;
    color: var(--grant-text);
    border: 2px solid var(--grant-border);
}

.btn-secondary-complete:hover {
    border-color: var(--grant-accent);
    background: var(--grant-hover);
    transform: translateY(-1px);
}

/* Comprehensive stats grid */
.stats-grid-complete {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.stat-item-complete {
    text-align: center;
    padding: 1.5rem 1rem;
    background: var(--grant-section-bg);
    border-radius: 1rem;
    border: 1px solid var(--grant-border);
    transition: var(--grant-transition);
}

.stat-item-complete:hover {
    transform: translateY(-2px);
    box-shadow: var(--grant-shadow-sm);
}

.stat-number-complete {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--grant-text);
    display: block;
    margin-bottom: 0.5rem;
}

.stat-label-complete {
    font-size: 0.875rem;
    color: var(--grant-text-muted);
    font-weight: 500;
}

/* Progress bars with enhanced styling */
.progress-bar-complete {
    width: 100%;
    height: 0.75rem;
    background: var(--grant-border);
    border-radius: 0.5rem;
    overflow: hidden;
    margin-top: 0.75rem;
    position: relative;
}

.progress-fill-complete {
    height: 100%;
    background: linear-gradient(90deg, var(--grant-success), #059669);
    border-radius: 0.5rem;
    transition: width 0.8s ease;
    position: relative;
}

.progress-fill-complete::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 2s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Enhanced tags and taxonomy display */
.tags-section-complete {
    margin-top: 2rem;
}

.tags-list-complete {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1rem;
}

.tag-complete {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--grant-section-bg);
    color: var(--grant-text-muted);
    border: 1px solid var(--grant-border);
    border-radius: 2rem;
    font-size: 0.875rem;
    text-decoration: none;
    transition: var(--grant-transition);
    font-weight: 500;
}

.tag-complete:hover {
    background: var(--grant-hover);
    color: var(--grant-text);
    transform: translateY(-1px);
    box-shadow: var(--grant-shadow-sm);
}

/* Comprehensive info table */
.info-table-complete {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    background: var(--grant-bg);
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: var(--grant-shadow-sm);
}

.info-table-complete th,
.info-table-complete td {
    padding: 1rem 1.5rem;
    text-align: left;
    border-bottom: 1px solid var(--grant-border);
}

.info-table-complete th {
    background: var(--grant-section-bg);
    font-weight: 600;
    color: var(--grant-text-muted);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.info-table-complete td {
    font-weight: 500;
    color: var(--grant-text);
}

.info-table-complete tr:hover {
    background: var(--grant-hover);
}

/* Responsive design */
@media (max-width: 768px) {
    .grant-complete {
        padding: 1rem;
    }
    
    .grant-header-complete {
        padding: 2rem;
    }
    
    .grant-title-complete {
        font-size: 2rem;
    }
    
    .grant-meta-complete {
        grid-template-columns: 1fr;
        padding: 1.5rem;
    }
    
    .content-section-complete {
        padding: 2rem;
    }
    
    .action-buttons-complete {
        position: sticky;
        bottom: 1rem;
        background: var(--grant-bg);
        padding: 1.5rem;
        border-radius: 1.25rem;
        box-shadow: var(--grant-shadow-lg);
        border: 1px solid var(--grant-border);
    }
}

/* Print styles */
@media print {
    .grant-complete {
        box-shadow: none;
        border: none;
    }
    
    .action-buttons-complete,
    .content-sidebar-complete {
        display: none;
    }
    
    .grant-content-complete {
        grid-template-columns: 1fr;
    }
}

/* Additional utility classes */
.highlight-amount { 
    color: var(--grant-success); 
    font-weight: 800; 
}

.highlight-deadline { 
    color: var(--grant-danger); 
    font-weight: 700; 
}

.highlight-rate { 
    color: var(--grant-info); 
    font-weight: 700; 
}

.text-small { 
    font-size: 0.875rem; 
    color: var(--grant-text-muted); 
}

.admin-note {
    background: #fef7cd;
    border-left: 4px solid #f59e0b;
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
    font-style: italic;
}
</style>

<main class="grant-complete">
    <!-- Comprehensive Header Section -->
    <header class="grant-header-complete">
        <!-- Enhanced Badges with all status types -->
        <div class="grant-badges-complete">
            <span class="grant-badge-complete badge-status-complete <?php echo $grant_data['application_status']; ?> <?php echo $deadline_class; ?>" 
                  style="background-color: <?php echo $status_data['color']; ?>">
                <i class="fas <?php echo $status_data['icon']; ?>"></i>
                <?php echo $status_data['label']; ?>
                <?php if ($days_remaining > 0 && $days_remaining <= 30): ?>
                    (<?php echo $days_remaining; ?>日)
                <?php endif; ?>
            </span>
            
            <?php if ($grant_data['is_featured']): ?>
            <span class="grant-badge-complete badge-featured-complete">
                <i class="fas fa-star"></i>
                注目の助成金
            </span>
            <?php endif; ?>
            
            <?php if ($difficulty !== 'normal'): ?>
            <span class="grant-badge-complete badge-difficulty-complete" style="background-color: <?php echo $difficulty_data['color']; ?>">
                <i class="fas <?php echo $difficulty_data['icon']; ?>"></i>
                申請難易度: <?php echo $difficulty_data['label']; ?>
            </span>
            <?php endif; ?>
            
            <?php if ($main_category): ?>
            <span class="grant-badge-complete badge-category-complete">
                <i class="fas fa-tag"></i>
                <?php echo esc_html($main_category->name); ?>
            </span>
            <?php endif; ?>
            
            <?php if ($main_prefecture): ?>
            <span class="grant-badge-complete badge-prefecture-complete">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo esc_html($main_prefecture->name); ?>
            </span>
            <?php endif; ?>
            
            <?php if ($grant_data['organization_type'] && $grant_data['organization_type'] !== 'national'): ?>
            <span class="grant-badge-complete" style="background-color: #6b7280;">
                <i class="fas fa-building"></i>
                <?php echo $org_type_labels[$grant_data['organization_type']] ?? $grant_data['organization_type']; ?>
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Title -->
        <h1 class="grant-title-complete"><?php the_title(); ?></h1>
        
        <!-- Enhanced subtitle with AI summary -->
        <?php if ($grant_data['ai_summary']): ?>
        <div class="grant-subtitle-complete"><?php echo esc_html(wp_trim_words($grant_data['ai_summary'], 40, '...')); ?></div>
        <?php endif; ?>
        
        <!-- Comprehensive Meta Information Grid -->
        <div class="grant-meta-complete">
            <?php if ($formatted_amount): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-yen-sign"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>最大助成額</h4>
                    <p><span class="highlight"><?php echo esc_html($formatted_amount); ?></span></p>
                    <?php if ($formatted_min_amount): ?>
                        <p class="text-small">最小: <?php echo esc_html($formatted_min_amount); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['subsidy_rate']): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>補助率</h4>
                    <p class="highlight-rate"><?php echo esc_html($grant_data['subsidy_rate']); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($deadline_info): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>申請締切</h4>
                    <p class="<?php echo $deadline_class === 'urgent' ? 'highlight-deadline' : ''; ?>">
                        <?php echo esc_html($deadline_info); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['recruitment_start']): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>募集開始</h4>
                    <p><?php echo esc_html($grant_data['recruitment_start']); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['grant_success_rate'] > 0): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>採択率</h4>
                    <p class="highlight-rate"><?php echo $grant_data['grant_success_rate']; ?>%</p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['organization']): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-building"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>実施機関</h4>
                    <p><?php echo esc_html($grant_data['organization']); ?></p>
                    <?php if ($grant_data['organization_type']): ?>
                        <p class="text-small"><?php echo $org_type_labels[$grant_data['organization_type']] ?? $grant_data['organization_type']; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['application_method']): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-file-upload"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>申請方法</h4>
                    <p><?php echo $method_labels[$grant_data['application_method']] ?? $grant_data['application_method']; ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['views_count'] > 0): ?>
            <div class="meta-item-complete">
                <div class="meta-icon-complete">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="meta-content-complete">
                    <h4>閲覧数</h4>
                    <p><?php echo number_format($grant_data['views_count']); ?> 回</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Main Content Grid -->
    <div class="grant-content-complete">
        <!-- Main Content Area -->
        <div class="content-main-complete">
            <?php if ($grant_data['ai_summary']): ?>
            <!-- AI Summary Section -->
            <section class="content-section-complete">
                <h2 class="section-title-complete">
                    <i class="fas fa-robot section-icon-complete"></i>
                    AI要約
                </h2>
                <div class="section-content-complete">
                    <p><?php echo esc_html($grant_data['ai_summary']); ?></p>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Main Content -->
            <section class="content-section-complete">
                <h2 class="section-title-complete">
                    <i class="fas fa-file-text section-icon-complete"></i>
                    詳細情報
                </h2>
                <div class="section-content-complete">
                    <?php the_content(); ?>
                </div>
            </section>
            
            <!-- Comprehensive Information Table -->
            <section class="content-section-complete">
                <h2 class="section-title-complete">
                    <i class="fas fa-table section-icon-complete"></i>
                    助成金詳細一覧
                </h2>
                <div class="section-content-complete">
                    <table class="info-table-complete">
                        <?php if ($grant_data['organization']): ?>
                        <tr>
                            <th>実施機関</th>
                            <td>
                                <?php echo esc_html($grant_data['organization']); ?>
                                <?php if ($grant_data['organization_type']): ?>
                                    <span class="text-small">(<?php echo $org_type_labels[$grant_data['organization_type']] ?? $grant_data['organization_type']; ?>)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($formatted_amount): ?>
                        <tr>
                            <th>助成額</th>
                            <td>
                                <strong class="highlight-amount"><?php echo esc_html($formatted_amount); ?></strong>
                                <?php if ($formatted_min_amount): ?>
                                    <br><span class="text-small">最小助成額: <?php echo esc_html($formatted_min_amount); ?></span>
                                <?php endif; ?>
                                <?php if ($grant_data['amount_note']): ?>
                                    <br><span class="text-small"><?php echo esc_html($grant_data['amount_note']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($grant_data['subsidy_rate']): ?>
                        <tr>
                            <th>補助率</th>
                            <td><strong class="highlight-rate"><?php echo esc_html($grant_data['subsidy_rate']); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($deadline_info || $grant_data['application_period']): ?>
                        <tr>
                            <th>申請期間</th>
                            <td>
                                <?php if ($grant_data['recruitment_start']): ?>
                                    開始: <?php echo esc_html($grant_data['recruitment_start']); ?><br>
                                <?php endif; ?>
                                <?php if ($deadline_info): ?>
                                    締切: <strong class="<?php echo $deadline_class === 'urgent' ? 'highlight-deadline' : ''; ?>"><?php echo esc_html($deadline_info); ?></strong><br>
                                <?php endif; ?>
                                <?php if ($grant_data['application_period']): ?>
                                    <?php echo esc_html($grant_data['application_period']); ?>
                                <?php endif; ?>
                                <?php if ($grant_data['deadline_note']): ?>
                                    <br><span class="text-small"><?php echo esc_html($grant_data['deadline_note']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($grant_data['application_method']): ?>
                        <tr>
                            <th>申請方法</th>
                            <td><?php echo $method_labels[$grant_data['application_method']] ?? esc_html($grant_data['application_method']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($grant_data['grant_success_rate'] > 0): ?>
                        <tr>
                            <th>採択率</th>
                            <td><strong class="highlight-rate"><?php echo $grant_data['grant_success_rate']; ?>%</strong></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($difficulty !== 'normal'): ?>
                        <tr>
                            <th>申請難易度</th>
                            <td style="color: <?php echo $difficulty_data['color']; ?>;">
                                <i class="fas <?php echo $difficulty_data['icon']; ?>"></i>
                                <?php echo $difficulty_data['label']; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($grant_data['last_updated']): ?>
                        <tr>
                            <th>最終更新</th>
                            <td><?php echo esc_html($grant_data['last_updated']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </section>
            
            <?php if ($grant_data['grant_target']): ?>
            <!-- Target Details -->
            <section class="content-section-complete">
                <h2 class="section-title-complete">
                    <i class="fas fa-bullseye section-icon-complete"></i>
                    対象者・対象事業
                </h2>
                <div class="section-content-complete">
                    <?php echo wp_kses_post($grant_data['grant_target']); ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if ($grant_data['eligible_expenses'] || $grant_data['target_expenses']): ?>
            <!-- Eligible Expenses -->
            <section class="content-section-complete">
                <h2 class="section-title-complete">
                    <i class="fas fa-list-check section-icon-complete"></i>
                    対象経費
                </h2>
                <div class="section-content-complete">
                    <?php if ($grant_data['eligible_expenses']): ?>
                        <?php echo wp_kses_post($grant_data['eligible_expenses']); ?>
                    <?php elseif ($grant_data['target_expenses']): ?>
                        <p><?php echo esc_html($grant_data['target_expenses']); ?></p>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if ($grant_data['required_documents']): ?>
            <!-- Required Documents -->
            <section class="content-section-complete">
                <h2 class="section-title-complete">
                    <i class="fas fa-clipboard-list section-icon-complete"></i>
                    必要書類
                </h2>
                <div class="section-content-complete">
                    <?php echo wp_kses_post($grant_data['required_documents']); ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if ($grant_data['contact_info']): ?>
            <!-- Contact Information -->
            <section class="content-section-complete">
                <h2 class="section-title-complete">
                    <i class="fas fa-phone section-icon-complete"></i>
                    お問い合わせ先
                </h2>
                <div class="section-content-complete">
                    <div style="background: var(--grant-section-bg); padding: 1.5rem; border-radius: 0.75rem; border: 1px solid var(--grant-border);">
                        <?php echo nl2br(esc_html($grant_data['contact_info'])); ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        </div>
        
        <!-- Enhanced Sidebar -->
        <aside class="content-sidebar-complete">
            <!-- Action Buttons -->
            <div class="sidebar-section-complete">
                <h3 class="sidebar-title-complete">
                    <i class="fas fa-rocket"></i>
                    アクション
                </h3>
                <div class="action-buttons-complete">
                    <?php if ($grant_data['official_url']): ?>
                    <a href="<?php echo esc_url($grant_data['official_url']); ?>" class="action-btn-complete btn-primary-complete" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt"></i>
                        公式サイトで申請
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($grant_data['external_link']): ?>
                    <a href="<?php echo esc_url($grant_data['external_link']); ?>" class="action-btn-complete btn-secondary-complete" target="_blank" rel="noopener">
                        <i class="fas fa-link"></i>
                        関連リンク
                    </a>
                    <?php endif; ?>
                    
                    <button class="action-btn-complete btn-secondary-complete" onclick="toggleFavorite(<?php echo $post_id; ?>)">
                        <i class="fas fa-heart"></i>
                        お気に入りに追加
                    </button>
                    
                    <button class="action-btn-complete btn-secondary-complete" onclick="shareGrant()">
                        <i class="fas fa-share-alt"></i>
                        この助成金をシェア
                    </button>
                    
                    <button class="action-btn-complete btn-secondary-complete" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        印刷用ページ
                    </button>
                </div>
            </div>
            
            <!-- Comprehensive Statistics -->
            <div class="sidebar-section-complete">
                <h3 class="sidebar-title-complete">
                    <i class="fas fa-chart-bar"></i>
                    統計情報
                </h3>
                <div class="stats-grid-complete">
                    <?php if ($grant_data['grant_success_rate'] > 0): ?>
                    <div class="stat-item-complete">
                        <span class="stat-number-complete"><?php echo $grant_data['grant_success_rate']; ?>%</span>
                        <span class="stat-label-complete">採択率</span>
                        <div class="progress-bar-complete">
                            <div class="progress-fill-complete" style="width: <?php echo $grant_data['grant_success_rate']; ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="stat-item-complete">
                        <span class="stat-number-complete"><?php echo number_format($grant_data['views_count']); ?></span>
                        <span class="stat-label-complete">閲覧数</span>
                    </div>
                    
                    <?php if ($grant_data['priority_order'] > 0 && $grant_data['priority_order'] < 100): ?>
                    <div class="stat-item-complete">
                        <span class="stat-number-complete"><?php echo $grant_data['priority_order']; ?></span>
                        <span class="stat-label-complete">優先度</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($days_remaining > 0): ?>
                    <div class="stat-item-complete">
                        <span class="stat-number-complete"><?php echo $days_remaining; ?></span>
                        <span class="stat-label-complete">残り日数</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Comprehensive Tags and Taxonomies -->
            <?php if ($taxonomies['categories'] || $taxonomies['prefectures'] || $taxonomies['tags']): ?>
            <div class="sidebar-section-complete">
                <h3 class="sidebar-title-complete">
                    <i class="fas fa-tags"></i>
                    関連分類
                </h3>
                <div class="tags-section-complete">
                    <?php if ($taxonomies['categories'] && !is_wp_error($taxonomies['categories'])): ?>
                        <h4 style="margin-bottom: 0.75rem; color: var(--grant-text-muted); font-size: 0.875rem;">カテゴリー</h4>
                        <div class="tags-list-complete">
                            <?php foreach ($taxonomies['categories'] as $category): ?>
                            <a href="<?php echo get_term_link($category); ?>" class="tag-complete">
                                <i class="fas fa-tag"></i>
                                <?php echo esc_html($category->name); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($taxonomies['prefectures'] && !is_wp_error($taxonomies['prefectures'])): ?>
                        <h4 style="margin: 1.5rem 0 0.75rem 0; color: var(--grant-text-muted); font-size: 0.875rem;">対象地域</h4>
                        <div class="tags-list-complete">
                            <?php foreach ($taxonomies['prefectures'] as $prefecture): ?>
                            <a href="<?php echo get_term_link($prefecture); ?>" class="tag-complete">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo esc_html($prefecture->name); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($taxonomies['tags'] && !is_wp_error($taxonomies['tags'])): ?>
                        <h4 style="margin: 1.5rem 0 0.75rem 0; color: var(--grant-text-muted); font-size: 0.875rem;">タグ</h4>
                        <div class="tags-list-complete">
                            <?php foreach ($taxonomies['tags'] as $tag): ?>
                            <a href="<?php echo get_term_link($tag); ?>" class="tag-complete">
                                <i class="fas fa-hashtag"></i>
                                <?php echo esc_html($tag->name); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Admin Information (if admin notes exist) -->
            <?php if (current_user_can('edit_posts') && $grant_data['admin_notes']): ?>
            <div class="sidebar-section-complete">
                <h3 class="sidebar-title-complete">
                    <i class="fas fa-user-shield"></i>
                    管理者情報
                </h3>
                <div class="admin-note">
                    <strong>管理者メモ:</strong><br>
                    <?php echo nl2br(esc_html($grant_data['admin_notes'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</main>

<script>
// Enhanced functionality with comprehensive features
function toggleFavorite(postId) {
    // Enhanced favorite functionality
    const button = event.target.closest('.action-btn-complete');
    const icon = button.querySelector('i');
    
    // Simulate toggle (implement with AJAX)
    if (icon.classList.contains('fa-heart')) {
        icon.classList.remove('fa-heart');
        icon.classList.add('fa-heart', 'fas');
        button.innerHTML = '<i class="fas fa-heart" style="color: #ef4444;"></i> お気に入りに登録済み';
    }
    
    // Add your AJAX implementation here
    console.log('Toggle favorite for post:', postId);
}

function shareGrant() {
    const title = document.title;
    const url = window.location.href;
    const text = '<?php echo esc_js(wp_trim_words($grant_data["ai_summary"], 20, "")); ?>';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('URLをクリップボードにコピーしました');
        }).catch(err => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('URLをクリップボードにコピーしました');
        });
    }
}

// Initialize comprehensive page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars with enhanced timing
    document.querySelectorAll('.progress-fill-complete').forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 500 + (index * 200));
    });
    
    // Enhanced smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add lazy loading for heavy content
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                }
            });
        });
        
        document.querySelectorAll('.content-section-complete').forEach(section => {
            observer.observe(section);
        });
    }
    
    // Track page engagement
    let engagementTime = 0;
    const startTime = Date.now();
    
    window.addEventListener('beforeunload', () => {
        engagementTime = Date.now() - startTime;
        // Send engagement data if needed
        console.log('Page engagement time:', engagementTime / 1000, 'seconds');
    });
});

// Enhanced print functionality
window.addEventListener('beforeprint', function() {
    document.body.classList.add('printing');
});

window.addEventListener('afterprint', function() {
    document.body.classList.remove('printing');
});
</script>

<?php get_footer(); ?>