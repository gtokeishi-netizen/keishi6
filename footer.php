<?php
/**
 * Grant Insight Perfect - Ultra Simple Footer Template
 * 超シンプル版 - functions.phpと完全連携
 * 
 * @package Grant_Insight_Perfect
 * @version 8.0.0-ultra-simple
 */

// 既存ヘルパー関数との完全連携
if (!function_exists('gi_get_sns_urls')) {
    function gi_get_sns_urls() {
        return [
            'twitter' => gi_get_theme_option('sns_twitter_url', ''),
            'facebook' => gi_get_theme_option('sns_facebook_url', ''),
            'linkedin' => gi_get_theme_option('sns_linkedin_url', ''),
            'instagram' => gi_get_theme_option('sns_instagram_url', ''),
            'youtube' => gi_get_theme_option('sns_youtube_url', '')
        ];
    }
}
?>

    </main>

    <!-- Modern Black & White Design - Tailwind CSS + Font Awesome + Google Fonts -->
    <?php if (!wp_script_is('tailwind-cdn', 'enqueued')): ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                        'space': ['Space Grotesk', 'sans-serif'],
                        'noto': ['Noto Sans JP', 'sans-serif']
                    },
                    boxShadow: {
                        'modern': '0 8px 32px rgba(0, 0, 0, 0.12)',
                        'modern-lg': '0 16px 48px rgba(0, 0, 0, 0.16)',
                        'modern-xl': '0 24px 64px rgba(0, 0, 0, 0.20)'
                    },
                    borderRadius: {
                        '4xl': '2rem',
                        '5xl': '2.5rem'
                    },
                    colors: {
                        'gray': {
                            '50': '#fafafa',
                            '100': '#f5f5f5',
                            '200': '#e5e5e5',
                            '300': '#d4d4d4',
                            '400': '#a3a3a3',
                            '500': '#737373',
                            '600': '#525252',
                            '700': '#404040',
                            '800': '#262626',
                            '900': '#171717'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&family=Noto+Sans+JP:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <?php endif; ?>

    <!-- Modern Black & White Footer -->
    <footer class="site-footer relative overflow-hidden bg-white border-t border-gray-200 transition-all duration-700 font-inter">
        
        <!-- Subtle Background Pattern -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30">
            <!-- Modern Grid Pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,theme(colors.gray.100)_1px,transparent_1px),linear-gradient(to_bottom,theme(colors.gray.100)_1px,transparent_1px)] bg-[size:2rem_2rem]"></div>
            
            <!-- Geometric Elements -->
            <div class="absolute top-10 right-10 w-32 h-32 border border-gray-100 rounded-full opacity-50"></div>
            <div class="absolute bottom-20 left-20 w-24 h-24 bg-gray-50 rounded-2xl opacity-60"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-1 h-20 bg-gray-200 opacity-40"></div>
        </div>

        <div class="relative z-10 py-16 lg:py-20">
            <div class="container mx-auto px-6 lg:px-8">
                
                <!-- Modern Brand Section -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center justify-center space-x-6 mb-8 group">
                        <div class="relative">
                            <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center shadow-modern group-hover:shadow-modern-lg transition-all duration-300 group-hover:scale-105">
                                <i class="fas fa-search text-white text-2xl"></i>
                            </div>
                        </div>
                        
                        <div class="text-left">
                            <h2 class="text-3xl lg:text-4xl font-black text-gray-900 leading-tight font-space">
                                <?php 
                                $site_name = get_bloginfo('name');
                                $name_parts = explode('・', $site_name);
                                if (count($name_parts) > 1) {
                                    echo '<span class="text-black">' . esc_html($name_parts[0]) . '</span>・';
                                    echo '<span class="text-gray-600">' . esc_html($name_parts[1]) . '</span>';
                                } else {
                                    echo '<span class="text-black">' . esc_html($site_name) . '</span>';
                                }
                                ?>
                            </h2>
                            <div class="flex items-center space-x-2 mt-2">
                                <div class="w-2 h-2 bg-black rounded-full"></div>
                                <span class="text-sm text-gray-600 font-medium">AI-Powered Grant Discovery</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-lg text-gray-700 max-w-2xl mx-auto leading-relaxed">
                        最先端のAI技術で、あなたのビジネスに最適な<br class="hidden md:block">
                        <span class="font-bold text-black">助成金・補助金を瞬時に発見</span>します。
                    </p>
                </div>

                <!-- Modern Navigation Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
                    
                    <!-- Grant Search Card -->
                    <div class="bg-white rounded-3xl p-8 shadow-modern hover:shadow-modern-lg transition-all duration-500 border border-gray-200 hover:border-gray-300 group">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-modern group-hover:scale-105 transition-transform duration-300">
                                <i class="fas fa-search text-white text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2 font-space">
                                補助金を探す
                                <div class="yellow-marker"></div>
                            </h3>
                            <p class="text-gray-600">最適な補助金を瞬時に発見</p>
                        </div>
                        
                        <div class="space-y-3">
                            <a href="<?php echo esc_url(home_url('/grants/')); ?>" 
                               class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-all duration-300 group/item border border-gray-200 hover:border-gray-300">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gray-900 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-list text-white text-sm"></i>
                                    </div>
                                    <span class="font-semibold text-gray-900">助成金一覧</span>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400 group-hover/item:text-gray-900 group-hover/item:translate-x-1 transition-all duration-200"></i>
                            </a>
                            
                            <?php
                            // 主要カテゴリーのみ表示
                            $main_categories = [
                                ['slug' => 'it', 'name' => 'IT・デジタル化', 'icon' => 'fas fa-laptop-code', 'color' => 'indigo'],
                                ['slug' => 'manufacturing', 'name' => 'ものづくり', 'icon' => 'fas fa-industry', 'color' => 'purple'],
                                ['slug' => 'startup', 'name' => '創業・起業', 'icon' => 'fas fa-rocket', 'color' => 'emerald'],
                                ['slug' => 'employment', 'name' => '雇用促進', 'icon' => 'fas fa-users', 'color' => 'yellow']
                            ];
                            
                            foreach ($main_categories as $category):
                            ?>
                            <a href="<?php echo esc_url(home_url('/grants/?category=' . $category['slug'])); ?>" 
                               class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-all duration-300 group/item border border-gray-200 hover:border-gray-300">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-white border-2 border-gray-900 rounded-xl flex items-center justify-center">
                                        <i class="<?php echo $category['icon']; ?> text-gray-900 text-sm"></i>
                                    </div>
                                    <span class="font-semibold text-gray-900"><?php echo esc_html($category['name']); ?></span>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400 group-hover/item:text-gray-900 group-hover/item:translate-x-1 transition-all duration-200"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Support & Information Card -->
                    <div class="bg-white rounded-3xl p-8 shadow-modern hover:shadow-modern-lg transition-all duration-500 border border-gray-200 hover:border-gray-300 group">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-modern group-hover:scale-105 transition-transform duration-300">
                                <i class="fas fa-info-circle text-white text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2 font-space">
                                サポート・情報
                                <div class="yellow-marker"></div>
                            </h3>
                            <p class="text-gray-600">お困りの際はこちらから</p>
                        </div>
                        
                        <div class="space-y-3">
                            <?php
                            // 重要なサポートリンクのみ
                            $support_links = [
                                ['url' => '/about/', 'name' => 'サービスについて', 'icon' => 'fas fa-info-circle'],
                                ['url' => '/contact/', 'name' => 'お問い合わせ', 'icon' => 'fas fa-envelope'],
                                ['url' => '/faq/', 'name' => 'よくある質問', 'icon' => 'fas fa-question-circle'],
                                ['url' => '/privacy/', 'name' => 'プライバシーポリシー', 'icon' => 'fas fa-shield-alt'],
                                ['url' => '/terms/', 'name' => '利用規約', 'icon' => 'fas fa-file-contract']
                            ];
                            
                            foreach ($support_links as $link):
                            ?>
                            <a href="<?php echo esc_url(home_url($link['url'])); ?>" 
                               class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-all duration-300 group/item border border-gray-200 hover:border-gray-300">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-white border-2 border-gray-900 rounded-xl flex items-center justify-center">
                                        <i class="<?php echo $link['icon']; ?> text-gray-900 text-sm"></i>
                                    </div>
                                    <span class="font-semibold text-gray-900"><?php echo esc_html($link['name']); ?></span>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400 group-hover/item:text-gray-900 group-hover/item:translate-x-1 transition-all duration-200"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Mobile Simple Menu -->
                <div class="lg:hidden mb-12">
                    <button id="gi-mobile-footer-toggle" class="w-full bg-white rounded-3xl p-5 shadow-modern border-2 border-gray-200 flex items-center justify-between text-gray-900 hover:border-gray-300 transition-all duration-300 group">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-900 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-bars text-white"></i>
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold text-lg">メニュー</h3>
                                <p class="text-gray-600">サービス一覧</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-300 text-gray-600" id="gi-mobile-toggle-icon"></i>
                    </button>
                </div>

                <!-- Mobile Content -->
                <div id="gi-mobile-footer-content" class="lg:hidden space-y-4 hidden overflow-hidden mb-12" style="max-height: 0; transition: max-height 0.3s ease-out;">
                    
                    <!-- Grant Search (Mobile) -->
                    <div class="bg-white rounded-3xl p-5 shadow-modern border border-gray-200">
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center text-lg">
                            <i class="fas fa-search mr-3 text-gray-900"></i>補助金を探す
                        </h3>
                        <div class="space-y-3">
                            <a href="<?php echo esc_url(home_url('/grants/')); ?>" class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors border border-gray-200">
                                <i class="fas fa-list mr-3 text-gray-900"></i>
                                <span class="font-semibold text-gray-900">助成金一覧</span>
                            </a>
                            <?php foreach ($main_categories as $category): ?>
                            <a href="<?php echo esc_url(home_url('/grants/?category=' . $category['slug'])); ?>" class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors border border-gray-200">
                                <i class="<?php echo $category['icon']; ?> mr-3 text-gray-900"></i>
                                <span class="font-semibold text-gray-900"><?php echo esc_html($category['name']); ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Support (Mobile) -->
                    <div class="bg-white rounded-3xl p-5 shadow-modern border border-gray-200">
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center text-lg">
                            <i class="fas fa-info-circle mr-3 text-gray-900"></i>サポート・情報
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($support_links as $link): ?>
                            <a href="<?php echo esc_url(home_url($link['url'])); ?>" class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors border border-gray-200">
                                <i class="<?php echo $link['icon']; ?> mr-3 text-gray-900"></i>
                                <span class="font-semibold text-gray-900"><?php echo esc_html($link['name']); ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer Bottom Section -->
                <div class="border-t border-gray-200 pt-12">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    
                    <!-- SNS & Features -->
                    <div class="text-center lg:text-left">
                        <h4 class="text-2xl font-bold text-gray-900 mb-6">最新情報をチェック</h4>
                        
                        <div class="flex justify-center lg:justify-start space-x-4 mb-8">
                            <?php
                            $sns_urls = gi_get_sns_urls();
                            $sns_data = [
                                'twitter' => ['icon' => 'fab fa-x-twitter', 'name' => 'X (Twitter)'],
                                'facebook' => ['icon' => 'fab fa-facebook-f', 'name' => 'Facebook'], 
                                'linkedin' => ['icon' => 'fab fa-linkedin-in', 'name' => 'LinkedIn'],
                                'instagram' => ['icon' => 'fab fa-instagram', 'name' => 'Instagram'],
                                'youtube' => ['icon' => 'fab fa-youtube', 'name' => 'YouTube']
                            ];

                            foreach ($sns_urls as $platform => $url): 
                                if (!empty($url)):
                            ?>
                            <a href="<?php echo esc_url($url); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               title="<?php echo esc_attr($sns_data[$platform]['name']); ?>"
                               class="w-12 h-12 bg-gray-900 hover:bg-gray-700 rounded-2xl flex items-center justify-center text-white shadow-modern hover:shadow-modern-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 group">
                                <i class="<?php echo $sns_data[$platform]['icon']; ?> text-lg"></i>
                            </a>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>

                        <!-- Feature Badges -->
                        <div class="flex flex-wrap justify-center lg:justify-start gap-3">
                            <span class="bg-gray-100 text-gray-900 px-4 py-2 rounded-2xl text-sm font-semibold border-2 border-gray-200 hover:border-gray-300 hover:scale-105 transition-all duration-300 cursor-default">
                                <i class="fas fa-check-circle mr-2"></i>無料診断
                            </span>
                            <span class="bg-black text-white px-4 py-2 rounded-2xl text-sm font-semibold hover:bg-gray-800 hover:scale-105 transition-all duration-300 cursor-default">
                                <i class="fas fa-robot mr-2"></i>AI支援
                            </span>
                            <span class="bg-gray-100 text-gray-900 px-4 py-2 rounded-2xl text-sm font-semibold border-2 border-gray-200 hover:border-gray-300 hover:scale-105 transition-all duration-300 cursor-default">
                                <i class="fas fa-shield-alt mr-2"></i>安心・安全
                            </span>
                        </div>
                    </div>

                    <!-- Copyright & Trust -->
                    <div class="text-center lg:text-right">
                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300">
                                <div class="w-12 h-12 bg-gray-100 border-2 border-gray-900 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-shield-alt text-gray-900 text-lg"></i>
                                </div>
                                <span class="font-bold text-gray-900 text-sm">SSL暗号化</span>
                            </div>
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300">
                                <div class="w-12 h-12 bg-gray-100 border-2 border-gray-900 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-lock text-gray-900 text-lg"></i>
                                </div>
                                <span class="font-bold text-gray-900 text-sm">情報保護</span>
                            </div>
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300">
                                <div class="w-12 h-12 bg-gray-900 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-award text-white text-lg"></i>
                                </div>
                                <span class="font-bold text-gray-900 text-sm">専門家監修</span>
                            </div>
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300">
                                <div class="w-12 h-12 bg-gray-900 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-robot text-white text-lg"></i>
                                </div>
                                <span class="font-bold text-gray-900 text-sm">AI技術</span>
                            </div>
                        </div>

                        <div class="border-t-2 border-gray-900 pt-6">
                            <p class="text-gray-900 mb-2 font-bold text-lg">
                                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.
                            </p>
                            <p class="text-gray-600 font-medium">
                                Powered by Next-Generation AI Technology
                            </p>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modern Back to Top Button -->
    <div id="gi-back-to-top" class="fixed bottom-6 right-6 z-50 opacity-0 pointer-events-none transition-all duration-300">
        <button class="w-14 h-14 bg-black hover:bg-gray-800 text-white rounded-2xl shadow-modern hover:shadow-modern-lg transition-all duration-300 transform hover:-translate-y-2 hover:scale-105 group" onclick="giScrollToTop()">
            <i class="fas fa-arrow-up text-lg"></i>
        </button>
    </div>

    <!-- Yellow Marker Styles -->
    <style>
    .yellow-marker {
        width: 40px;
        height: 3px;
        background: #ffeb3b;
        margin: 8px auto 0;
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
        width: 6px;
        height: 6px;
        background: #ffeb3b;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(255, 235, 59, 0.4);
    }
    </style>

    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // モバイルメニュー制御
        const mobileToggle = document.getElementById('gi-mobile-footer-toggle');
        const mobileContent = document.getElementById('gi-mobile-footer-content');
        const mobileIcon = document.getElementById('gi-mobile-toggle-icon');
        let isOpen = false;

        if (mobileToggle && mobileContent) {
            mobileToggle.addEventListener('click', function() {
                isOpen = !isOpen;
                
                if (isOpen) {
                    mobileContent.classList.remove('hidden');
                    mobileContent.style.maxHeight = mobileContent.scrollHeight + 'px';
                    mobileIcon.style.transform = 'rotate(180deg)';
                } else {
                    mobileContent.style.maxHeight = '0px';
                    mobileIcon.style.transform = 'rotate(0deg)';
                    setTimeout(() => {
                        mobileContent.classList.add('hidden');
                    }, 300);
                }
            });
        }

        // トップに戻るボタン制御
        let ticking = false;
        
        function updateBackToTop() {
            const backToTopButton = document.getElementById('gi-back-to-top');
            if (!backToTopButton) return;
            
            const scrolled = window.pageYOffset;
            
            if (scrolled > 300) {
                backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                backToTopButton.classList.add('opacity-100', 'pointer-events-auto');
            } else {
                backToTopButton.classList.add('opacity-0', 'pointer-events-none');
                backToTopButton.classList.remove('opacity-100', 'pointer-events-auto');
            }
            
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateBackToTop);
                ticking = true;
            }
        });

        // レスポンシブ対応
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024 && mobileContent && !mobileContent.classList.contains('hidden')) {
                mobileContent.classList.add('hidden');
                mobileContent.style.maxHeight = '0px';
                if (mobileIcon) {
                    mobileIcon.style.transform = 'rotate(0deg)';
                }
                isOpen = false;
            }
        });
    });

    // スムーズスクロール
    function giScrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // グローバル関数として公開
    window.giScrollToTop = giScrollToTop;
    </script>

    <?php wp_footer(); ?>

</body>
</html>