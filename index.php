<?php
/**
 * Grant Insight V4テーマのメインテンプレートファイル（Tailwind CSS Play CDN完全対応版）
 *
 * これはWordPressテーマの中で最も汎用的なテンプレートであり、
 * クエリに対してより具体的なテンプレートが見つからない場合に最終的に使用されます。
 * 例えば、home.phpが存在しない場合にブログのトップページとして機能します。
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Grant_Insight_V4
 */

get_header(); 

// 検索結果の場合は search.php にリダイレクト
if (is_search()) {
    get_template_part('search');
    get_footer();
    return;
}
?>

<!-- Tailwind CSS Play CDNの読み込み（ページのhead部分に配置） -->
<?php if (!wp_script_is('tailwind-cdn', 'enqueued')): ?>

<script>
    tailwind.config = {
        theme: {
            extend: {
                animation: {
                    'fade-in': 'fadeIn 0.6s ease-in-out',
                    'fade-in-up': 'fadeInUp 0.6s ease-out',
                    'slide-in-left': 'slideInLeft 0.5s ease-out',
                    'bounce-gentle': 'bounceGentle 2s ease-in-out infinite',
                    'shimmer': 'shimmer 2s linear infinite'
                },
                keyframes: {
                    fadeIn: {
                        '0%': { opacity: '0' },
                        '100%': { opacity: '1' }
                    },
                    fadeInUp: {
                        '0%': {
                            opacity: '0',
                            transform: 'translateY(20px)'
                        },
                        '100%': {
                            opacity: '1',
                            transform: 'translateY(0)'
                        }
                    },
                    slideInLeft: {
                        '0%': {
                            opacity: '0',
                            transform: 'translateX(-20px)'
                        },
                        '100%': {
                            opacity: '1',
                            transform: 'translateX(0)'
                        }
                    },
                    bounceGentle: {
                        '0%, 100%': {
                            transform: 'translateY(0)',
                            animationTimingFunction: 'cubic-bezier(0.8, 0, 1, 1)'
                        },
                        '50%': {
                            transform: 'translateY(-3px)',
                            animationTimingFunction: 'cubic-bezier(0, 0, 0.2, 1)'
                        }
                    },
                    shimmer: {
                        '0%': { backgroundPosition: '-200px 0' },
                        '100%': { backgroundPosition: '200px 0' }
                    }
                }
            }
        }
    }
</script>
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php endif; ?>

<main class="container mx-auto px-4 py-12 min-h-screen bg-gray-50">
    <!-- ページヘッダー -->
    <div class="bg-white p-8 rounded-2xl shadow-lg mb-8 border border-gray-100 animate-fade-in-up">
        <!-- パンくずリスト -->
        <?php if (!is_front_page()): ?>
        <nav class="mb-6 text-sm" aria-label="パンくず">
            <ol class="flex items-center space-x-2 text-gray-500">
                <li><a href="<?php echo home_url(); ?>" class="hover:text-blue-600 transition-colors">ホーム</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <?php if (is_search()): ?>
                <li class="text-gray-900 font-medium">検索結果</li>
                <?php elseif (is_archive()): ?>
                <li class="text-gray-900 font-medium"><?php single_cat_title(); ?></li>
                <?php else: ?>
                <li class="text-gray-900 font-medium">記事一覧</li>
                <?php endif; ?>
            </ol>
        </nav>
        <?php endif; ?>

        <!-- ページタイトル -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 flex items-center">
                    <?php if (is_search()): ?>
                        <i class="fas fa-search text-blue-600 mr-3"></i>
                        検索結果: <span class="text-blue-600 ml-2">"<?php echo get_search_query(); ?>"</span>
                    <?php elseif (is_home() && !is_front_page()): ?>
                        <i class="fas fa-newspaper text-green-600 mr-3"></i>
                        <?php single_post_title(); ?>
                    <?php elseif (is_archive()): ?>
                        <?php if (is_category()): ?>
                            <i class="fas fa-folder text-purple-600 mr-3"></i>
                        <?php elseif (is_tag()): ?>
                            <i class="fas fa-tag text-orange-600 mr-3"></i>
                        <?php elseif (is_date()): ?>
                            <i class="fas fa-calendar text-teal-600 mr-3"></i>
                        <?php else: ?>
                            <i class="fas fa-archive text-indigo-600 mr-3"></i>
                        <?php endif; ?>
                        <?php the_archive_title(); ?>
                    <?php else: ?>
                        <i class="fas fa-list text-gray-600 mr-3"></i>
                        記事一覧
                    <?php endif; ?>
                </h1>
                
                <?php
                // アーカイブのディスクリプション（説明文）があれば表示
                if (is_archive() && !is_search()) {
                    the_archive_description('<div class="text-gray-600 leading-relaxed">', '</div>');
                } elseif (is_search()) {
                    global $wp_query;
                    $found_posts = $wp_query->found_posts;
                    echo '<div class="text-gray-600">約 ' . number_format($found_posts) . ' 件の結果が見つかりました</div>';
                }
                ?>
            </div>
            
            <!-- ソート・表示オプション -->
            <?php if (have_posts() && !is_search()): ?>
            <div class="flex items-center gap-4 ml-6">
                <div class="flex items-center gap-2">
                    <label for="sort-posts" class="text-sm font-medium text-gray-600">並び順:</label>
                    <select id="sort-posts" class="px-3 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="date">新着順</option>
                        <option value="title">タイトル順</option>
                        <option value="modified">更新順</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (have_posts()): ?>
        <!-- 投稿一覧 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12" id="posts-container">
            <?php
            $post_count = 0;
            // ループを開始
            while (have_posts()):
                the_post();
                $post_count++;
                ?>
                <div class="animate-fade-in-up" style="animation-delay: <?php echo ($post_count * 0.1); ?>s;">
                    <?php
                    /**
                     * テンプレートパーツの優先順位:
                     * 1. grant-card-v3 (助成金専用)
                     * 2. post-card (一般投稿用)
                     * 3. フォールバック表示
                     */
                    if (locate_template('template-parts/cards/grant-card-v3.php')) {
                        get_template_part('template-parts/cards/grant-card-v3');
                    } elseif (locate_template('template-parts/cards/post-card.php')) {
                        get_template_part('template-parts/cards/post-card');
                    } else {
                        // フォールバック: 基本的な投稿カード
                        ?>
                        <article class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden group">
                            <?php if (has_post_thumbnail()): ?>
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden">
                                <?php the_post_thumbnail('medium_large', array(
                                    'class' => 'w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300'
                                )); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <!-- カテゴリ -->
                                <?php $categories = get_the_category(); ?>
                                <?php if (!empty($categories)): ?>
                                <div class="mb-3">
                                    <?php foreach (array_slice($categories, 0, 2) as $category): ?>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full mr-2">
                                        <?php echo esc_html($category->name); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <!-- タイトル -->
                                <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    <a href="<?php the_permalink(); ?>" class="hover:underline">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <!-- 抜粋 -->
                                <div class="text-gray-600 text-sm mb-4 line-clamp-3">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </div>

                                <!-- メタ情報 -->
                                <div class="flex items-center justify-between text-xs text-gray-500 pt-4 border-t border-gray-100">
                                    <time datetime="<?php echo get_the_date('c'); ?>" class="flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?php echo get_the_date(); ?>
                                    </time>
                                    <div class="flex items-center">
                                        <i class="fas fa-user mr-1"></i>
                                        <?php the_author(); ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php
                    }
                    ?>
                </div>
            <?php endwhile; // ループの終了 ?>
        </div>

        <!-- ページネーション -->
        <nav class="pagination-nav animate-fade-in-up" aria-label="ページネーション" style="animation-delay: 0.6s;">
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => '<i class="fas fa-chevron-left mr-2"></i>前のページ',
                    'next_text' => '次のページ<i class="fas fa-chevron-right ml-2"></i>',
                    'before_page_number' => '<span class="sr-only">ページ </span>',
                    'class'     => 'flex justify-center'
                ));
                ?>
            </div>
        </nav>

    <?php else: ?>
        
        <!-- 投稿が見つからない場合 -->
        <div class="bg-white p-12 rounded-2xl shadow-lg text-center border border-gray-100 animate-fade-in-up">
            <div class="max-w-md mx-auto">
                <!-- アイコン -->
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <?php if (is_search()): ?>
                        <i class="fas fa-search-minus text-gray-400 text-2xl"></i>
                    <?php else: ?>
                        <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                    <?php endif; ?>
                </div>

                <!-- メッセージ -->
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    <?php if (is_search()): ?>
                        検索結果が見つかりませんでした
                    <?php else: ?>
                        投稿が見つかりませんでした
                    <?php endif; ?>
                </h2>
                
                <p class="text-gray-600 mb-8 leading-relaxed">
                    <?php if (is_search()): ?>
                        「<?php echo get_search_query(); ?>」に一致する投稿は見つかりませんでした。<br>
                        別のキーワードで検索してみてください。
                    <?php else: ?>
                        お探しのページは見つかりませんでした。<br>
                        ホームページから他のコンテンツをご覧ください。
                    <?php endif; ?>
                </p>
                
                <!-- 検索フォーム -->
                <div class="max-w-sm mx-auto mb-6">
                    <form role="search" method="get" action="<?php echo home_url('/'); ?>" class="flex">
                        <input type="search" 
                               name="s" 
                               value="<?php echo get_search_query(); ?>"
                               placeholder="キーワードを入力..."
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- 関連リンク -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo home_url(); ?>" 
                       class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        ホームに戻る
                    </a>
                    <?php if (is_search()): ?>
                    <a href="<?php echo home_url('/grants/'); ?>" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-list mr-2"></i>
                        助成金一覧を見る
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>

</main>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ソート機能
    const sortSelect = document.getElementById('sort-posts');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            const container = document.getElementById('posts-container');
            const posts = Array.from(container.children);
            
            // 簡単なクライアントサイドソート（実際の実装では適切なAJAX処理を使用）
            console.log('Sorting by:', sortBy);
            showNotification('ソート機能は開発中です', 'info');
        });
    }

    // アニメーション遅延の設定
    const animatedElements = document.querySelectorAll('.animate-fade-in-up');
    animatedElements.forEach((el, index) => {
        if (!el.style.animationDelay) {
            el.style.animationDelay = `${index * 0.1}s`;
        }
    });

    // Intersection Observer でスクロールアニメーション
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, observerOptions);
    
    // 要素を監視対象に追加
    animatedElements.forEach(el => {
        el.style.animationFillMode = 'both';
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });

    // 通知表示関数
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg transform translate-x-full transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // グローバル関数として公開
    window.showNotification = showNotification;
});
</script>

<?php get_footer(); ?>
