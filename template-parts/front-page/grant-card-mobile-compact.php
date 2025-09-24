<?php
/**
 * Grant Card Mobile Compact Template
 * 
 * モバイル専用コンパクトカードデザイン
 * - 左上ステータスアイコン
 * - 重要情報の優先表示
 * - タッチ操作最適化
 * - 44px以上のタッチターゲット
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// 必要なデータを取得
$post_id = get_the_ID();
$grant_amount = gi_safe_get_meta($post_id, 'grant_amount', 0);
$success_rate = gi_safe_get_meta($post_id, 'grant_success_rate', 0);
$difficulty = gi_safe_get_meta($post_id, 'grant_difficulty', 'normal');
$prefecture = gi_get_prefecture_name($post_id);
$category = gi_get_category_name($post_id);
$application_status = gi_safe_get_meta($post_id, 'application_status', 'closed');
$deadline = gi_safe_get_meta($post_id, 'application_deadline', '');

// ステータスアイコンの決定
$status_icon = 'fas fa-coins';
$status_color = 'bg-emerald-500';
$status_pulse = '';

switch ($application_status) {
    case 'open':
        $status_icon = 'fas fa-check-circle';
        $status_color = 'bg-green-500';
        $status_pulse = 'animate-pulse';
        break;
    case 'upcoming':
        $status_icon = 'fas fa-clock';
        $status_color = 'bg-yellow-500';
        break;
    case 'closed':
        $status_icon = 'fas fa-times-circle';
        $status_color = 'bg-gray-500';
        break;
}

// 難易度表示
$difficulty_stars = '';
$difficulty_color = 'text-green-400';
$difficulty_text = '易しい';

switch ($difficulty) {
    case 'easy':
        $difficulty_stars = '<i class="fas fa-star"></i>';
        $difficulty_color = 'text-green-400';
        $difficulty_text = '易しい';
        break;
    case 'normal':
        $difficulty_stars = '<i class="fas fa-star"></i><i class="fas fa-star"></i>';
        $difficulty_color = 'text-blue-400';
        $difficulty_text = '普通';
        break;
    case 'hard':
        $difficulty_stars = '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
        $difficulty_color = 'text-orange-400';
        $difficulty_text = '難しい';
        break;
    case 'expert':
        $difficulty_stars = '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
        $difficulty_color = 'text-red-400';
        $difficulty_text = '専門的';
        break;
}

// 採択率の色分け
$success_rate_color = 'text-gray-600';
if ($success_rate >= 70) {
    $success_rate_color = 'text-green-600';
} elseif ($success_rate >= 50) {
    $success_rate_color = 'text-yellow-600';
} elseif ($success_rate > 0) {
    $success_rate_color = 'text-red-600';
}
?>

<div class="grant-card-enhanced relative bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-3 animate-fade-in" 
     data-post-id="<?php echo $post_id; ?>">
     
    <!-- 左上のステータスアイコン -->
    <div class="absolute top-2 left-2 w-8 h-8 <?php echo $status_color; ?> rounded-full flex items-center justify-center z-10 border-2 border-white shadow-sm <?php echo $status_pulse; ?>">
        <i class="<?php echo $status_icon; ?> text-white text-xs"></i>
    </div>
    
    <!-- カードヘッダー -->
    <div class="pl-10 pr-1">
        <h3 class="grant-card-title text-sm font-semibold leading-tight mb-2 line-clamp-2 min-h-[2.5rem]">
            <a href="<?php echo esc_url(get_permalink()); ?>" 
               class="text-gray-900 hover:text-emerald-600 transition-colors touch-manipulation"
               style="min-height: 44px; display: block; padding-top: 2px;">
                <?php echo esc_html(get_the_title()); ?>
            </a>
        </h3>
        
        <!-- メタ情報（簡略化） -->
        <div class="grant-card-meta flex flex-wrap gap-1 mb-2">
            <?php if ($prefecture): ?>
            <span class="grant-meta-item text-xs px-2 py-0.5 bg-blue-50 text-blue-700 rounded">
                📍 <?php echo esc_html($prefecture); ?>
            </span>
            <?php endif; ?>
            
            <?php if ($category): ?>
            <span class="grant-meta-item text-xs px-2 py-0.5 bg-green-50 text-green-700 rounded">
                🏷️ <?php echo esc_html(mb_strimwidth($category, 0, 10, '...')); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <!-- 金額表示 -->
        <?php if ($grant_amount > 0): ?>
        <div class="grant-card-amount text-base font-bold text-emerald-600 mb-2">
            💰 <?php echo gi_format_amount($grant_amount); ?>
        </div>
        <?php else: ?>
        <div class="grant-card-amount text-base font-bold text-gray-500 mb-2">
            💰 要相談
        </div>
        <?php endif; ?>
        
        <!-- 採択率と難易度を横並び -->
        <div class="grant-card-stats flex justify-between items-center mb-3 gap-2">
            <?php if ($success_rate > 0): ?>
            <div class="success-rate-mobile flex items-center gap-1 text-xs px-2 py-1 bg-gray-50 rounded flex-1">
                <span class="text-xs">📊</span>
                <span class="<?php echo $success_rate_color; ?> font-medium"><?php echo $success_rate; ?>%</span>
            </div>
            <?php else: ?>
            <div class="success-rate-mobile flex items-center gap-1 text-xs px-2 py-1 bg-gray-50 rounded flex-1">
                <span class="text-xs">📊</span>
                <span class="text-gray-500">未公開</span>
            </div>
            <?php endif; ?>
            
            <div class="difficulty-mobile flex items-center gap-1 text-xs px-2 py-1 bg-gray-50 rounded flex-1">
                <div class="flex <?php echo $difficulty_color; ?>" style="font-size: 10px;">
                    <?php echo $difficulty_stars; ?>
                </div>
                <span class="text-gray-700 text-xs"><?php echo $difficulty_text; ?></span>
            </div>
        </div>
        
        <!-- 締切情報（重要な場合のみ表示） -->
        <?php if ($deadline && $application_status === 'open'): ?>
            <?php
            $deadline_date = DateTime::createFromFormat('Y-m-d', $deadline);
            $now = new DateTime();
            $interval = $now->diff($deadline_date);
            
            if ($deadline_date > $now && $interval->days <= 30): ?>
            <div class="mb-2 px-2 py-1 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                ⏰ 締切まで<?php echo $interval->days; ?>日
            </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- アクションボタン -->
        <div class="grant-card-actions flex gap-1">
            <a href="<?php echo esc_url(get_permalink()); ?>" 
               class="grant-card-btn flex-1 py-2 px-3 bg-emerald-600 text-white text-center hover:bg-emerald-700 transition-colors text-xs font-medium rounded touch-manipulation"
               style="min-height: 44px; display: flex; align-items: center; justify-content: center;">
                詳細を見る
            </a>
            
            <button class="grant-card-btn w-10 h-10 border border-gray-300 text-gray-600 favorite-btn hover:bg-gray-50 hover:border-gray-400 transition-colors rounded touch-manipulation flex items-center justify-center" 
                    data-post-id="<?php echo $post_id; ?>"
                    title="お気に入りに追加"
                    style="min-height: 44px; min-width: 44px;">
                <i class="far fa-heart text-sm"></i>
            </button>
            
            <button class="grant-card-btn w-10 h-10 border border-gray-300 text-gray-600 share-btn hover:bg-gray-50 hover:border-gray-400 transition-colors rounded touch-manipulation flex items-center justify-center"
                    data-url="<?php echo esc_url(get_permalink()); ?>"
                    data-title="<?php echo esc_attr(get_the_title()); ?>"
                    title="シェア"
                    style="min-height: 44px; min-width: 44px;">
                <i class="fas fa-share text-sm"></i>
            </button>
        </div>
    </div>
    
    <!-- ホバー効果用のオーバーレイ -->
    <div class="absolute inset-0 bg-emerald-50 opacity-0 hover:opacity-10 transition-opacity duration-200 rounded-lg pointer-events-none"></div>
</div>