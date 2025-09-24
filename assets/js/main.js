/**
 * Grant Insight Theme - Unified JavaScript
 * 統合されたメインJavaScript（基本機能 + モバイル最適化）
 * 
 * @version 3.0 - Unified Mobile Enhancement
 */

// メイン名前空間の作成（グローバルスコープ汚染を防ぐ）
const GrantInsight = {
    // 設定オブジェクト
    config: {
        debounceDelay: 300,
        toastDuration: 3000,
        scrollTrackingInterval: 250,
        apiEndpoint: '/wp-admin/admin-ajax.php',
        searchMinLength: 2
    },

    // 初期化フラグ
    initialized: false,
    
    // モバイル関連の状態
    mobile: {
        lastScrollY: 0,
        headerHeight: 0,
        filterSheet: null,
        searchSuggestions: null,
        activeFilters: new Map(),
        touchStartY: 0,
        touchEndY: 0,
        isScrolling: false
    },

    /**
     * メイン初期化関数
     */
    init() {
        if (this.initialized) return;
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupAll());
        } else {
            this.setupAll();
        }
    },

    /**
     * 全機能のセットアップ
     */
    setupAll() {
        try {
            this.setupUtils();
            this.setupSearch();
            this.setupFilters();
            this.setupMobile();
            this.setupAccessibility();
            this.setupPerformance();
            
            this.initialized = true;
            console.log('Grant Insight initialized successfully');
        } catch (error) {
            console.error('Initialization error:', error);
        }
    },

    /* ===============================================
       ユーティリティ関数群
       =============================================== */
    setupUtils() {
        // HTMLエスケープ関数
        this.escapeHtml = function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        };

        // デバウンス関数
        this.debounce = function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };

        // トースト通知関数
        this.showToast = function(message, type = 'info') {
            const existingToast = document.querySelector('.gi-toast');
            if (existingToast) {
                existingToast.remove();
            }
            
            const toast = document.createElement('div');
            toast.className = `gi-toast ${type}`;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, this.config.toastDuration);
        };

        // AJAX関数
        this.ajax = function(url, options = {}) {
            return fetch(url, {
                method: options.method || 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    ...options.headers
                },
                body: options.data ? new URLSearchParams(options.data).toString() : null,
                ...options
            }).then(response => response.json());
        };
    },

    /* ===============================================
       検索機能
       =============================================== */
    setupSearch() {
        const searchInputs = document.querySelectorAll('.gi-search-input, #grant-search');
        
        searchInputs.forEach(input => {
            // 検索入力のデバウンス処理
            const debouncedSearch = this.debounce((value) => {
                if (value.length >= this.config.searchMinLength) {
                    this.performSearch(value);
                    this.showSearchSuggestions(value);
                }
            }, this.config.debounceDelay);

            input.addEventListener('input', (e) => {
                debouncedSearch(e.target.value);
            });

            // エンターキーでの検索実行
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.executeSearch(e.target.value);
                }
            });

            // フォーカス時の処理
            input.addEventListener('focus', () => {
                this.mobile.lastFocusedInput = input;
                if (input.value.length >= this.config.searchMinLength) {
                    this.showSearchSuggestions(input.value);
                }
            });

            // フォーカス外時の処理
            input.addEventListener('blur', () => {
                setTimeout(() => this.hideSearchSuggestions(), 150);
            });
        });
    },

    /**
     * 検索実行
     */
    performSearch(query) {
        this.ajax(this.config.apiEndpoint, {
            data: {
                action: 'gi_search_grants',
                query: query,
                nonce: window.gi_ajax_nonce
            }
        }).then(response => {
            if (response.success) {
                this.updateSearchResults(response.data);
            }
        }).catch(error => {
            console.error('Search error:', error);
        });
    },

    /**
     * 検索候補表示
     */
    showSearchSuggestions(query) {
        this.ajax(this.config.apiEndpoint, {
            data: {
                action: 'gi_get_search_suggestions',
                query: query,
                nonce: window.gi_ajax_nonce
            }
        }).then(response => {
            if (response.success) {
                this.renderSearchSuggestions(response.data);
            }
        });
    },

    /**
     * 検索候補のレンダリング
     */
    renderSearchSuggestions(suggestions) {
        let container = document.querySelector('.gi-search-suggestions');
        if (!container) {
            container = document.createElement('div');
            container.className = 'gi-search-suggestions';
            const searchContainer = document.querySelector('.gi-search-container');
            if (searchContainer) {
                searchContainer.appendChild(container);
            }
        }

        if (suggestions.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.innerHTML = suggestions.map(item => `
            <div class="gi-suggestion-item" data-value="${this.escapeHtml(item.value)}">
                <i class="fas fa-search"></i>
                <span>${this.escapeHtml(item.label)}</span>
            </div>
        `).join('');

        container.style.display = 'block';
        container.classList.add('active');

        // クリックイベントの設定
        container.querySelectorAll('.gi-suggestion-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const value = e.currentTarget.dataset.value;
                this.executeSearch(value);
                this.hideSearchSuggestions();
            });
        });
    },

    /**
     * 検索実行
     */
    executeSearch(query) {
        const input = document.querySelector('.gi-search-input, #grant-search');
        if (input) {
            input.value = query;
        }
        
        // 検索結果ページに移動またはAJAXで結果更新
        if (window.location.pathname === '/') {
            window.location.href = `/grants/?search=${encodeURIComponent(query)}`;
        } else {
            this.performSearch(query);
        }
    },

    /**
     * 検索候補を隠す
     */
    hideSearchSuggestions() {
        const container = document.querySelector('.gi-search-suggestions');
        if (container) {
            container.classList.remove('active');
            setTimeout(() => {
                container.style.display = 'none';
            }, 150);
        }
    },

    /* ===============================================
       フィルター機能
       =============================================== */
    setupFilters() {
        // フィルターボタンのイベント
        const filterButtons = document.querySelectorAll('.gi-filter-chip, .filter-button');
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.toggleFilter(button);
            });
        });

        // フィルター表示ボタン
        const filterTrigger = document.querySelector('.gi-filter-trigger, #filter-toggle');
        if (filterTrigger) {
            filterTrigger.addEventListener('click', () => {
                this.showFilterBottomSheet();
            });
        }

        // フィルター適用
        const applyButton = document.querySelector('.gi-btn-filter-apply');
        if (applyButton) {
            applyButton.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        // フィルタークリア
        const clearButton = document.querySelector('.gi-btn-filter-clear');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                this.clearFilters();
            });
        }
    },

    /**
     * フィルター切り替え
     */
    toggleFilter(button) {
        const filterType = button.dataset.filter;
        const filterValue = button.dataset.value;
        
        if (!filterType || !filterValue) return;

        button.classList.toggle('active');
        
        if (button.classList.contains('active')) {
            this.mobile.activeFilters.set(`${filterType}-${filterValue}`, {
                type: filterType,
                value: filterValue,
                label: button.textContent
            });
        } else {
            this.mobile.activeFilters.delete(`${filterType}-${filterValue}`);
        }

        // リアルタイムフィルタリング
        this.applyFilters();
    },

    /**
     * フィルター適用
     */
    applyFilters() {
        const filters = Object.fromEntries(
            Array.from(this.mobile.activeFilters.entries()).map(([key, value]) => [value.type, value.value])
        );

        this.ajax(this.config.apiEndpoint, {
            data: {
                action: 'gi_filter_grants',
                filters: filters,
                nonce: window.gi_ajax_nonce
            }
        }).then(response => {
            if (response.success) {
                this.updateSearchResults(response.data);
                this.showToast(`${response.data.total}件の助成金が見つかりました`, 'success');
            }
        }).catch(error => {
            console.error('Filter error:', error);
            this.showToast('フィルター処理中にエラーが発生しました', 'error');
        });

        this.hideFilterBottomSheet();
    },

    /**
     * フィルタークリア
     */
    clearFilters() {
        this.mobile.activeFilters.clear();
        
        document.querySelectorAll('.gi-filter-chip.active, .filter-button.active').forEach(button => {
            button.classList.remove('active');
        });

        this.applyFilters();
    },

    /* ===============================================
       モバイル最適化機能
       =============================================== */
    setupMobile() {
        this.setupMobileHeader();
        this.setupTouchOptimizations();
        this.setupCardInteractions();
        this.setupBottomNavigation();
    },

    /**
     * モバイルヘッダーのセットアップ
     */
    setupMobileHeader() {
        let header = document.querySelector('.gi-mobile-header');
        if (!header) {
            header = this.createMobileHeader();
        }
        
        this.mobile.headerHeight = header.offsetHeight;
        
        // スマートヘッダー表示/非表示
        let scrollTimer = null;
        
        window.addEventListener('scroll', () => {
            if (scrollTimer) clearTimeout(scrollTimer);
            
            scrollTimer = setTimeout(() => {
                const currentScrollY = window.scrollY;
                const scrollDelta = Math.abs(currentScrollY - this.mobile.lastScrollY);
                
                if (scrollDelta < 10) return;
                
                if (currentScrollY > this.mobile.lastScrollY && currentScrollY > this.mobile.headerHeight) {
                    header.classList.add('header-hidden');
                } else {
                    header.classList.remove('header-hidden');
                }
                
                this.mobile.lastScrollY = currentScrollY;
            }, 10);
        }, { passive: true });
    },

    /**
     * モバイルヘッダーの作成
     */
    createMobileHeader() {
        const header = document.createElement('div');
        header.className = 'gi-mobile-header';
        header.innerHTML = `
            <div class="gi-mobile-header-content">
                <a href="/" class="gi-logo-mobile">助成金検索</a>
                <div class="gi-search-container">
                    <input type="text" class="gi-search-input" placeholder="助成金を検索...">
                    <div class="gi-search-suggestions"></div>
                </div>
                <button class="gi-filter-trigger" aria-label="フィルター">
                    <i class="fas fa-sliders-h"></i>
                </button>
            </div>
        `;
        
        document.body.insertBefore(header, document.body.firstChild);
        return header;
    },

    /**
     * タッチ最適化
     */
    setupTouchOptimizations() {
        // リップルエフェクトの追加
        document.querySelectorAll('button, .btn, .gi-filter-chip').forEach(element => {
            if (!element.classList.contains('gi-ripple')) {
                element.classList.add('gi-ripple');
            }
        });

        // プレスアニメーションの追加
        document.querySelectorAll('.gi-grant-card-enhanced, .card').forEach(element => {
            element.classList.add('gi-press-scale');
        });

        // プルトゥリフレッシュ
        this.setupPullToRefresh();
    },

    /**
     * カードインタラクションの設定
     */
    setupCardInteractions() {
        const cards = document.querySelectorAll('.gi-grant-card-enhanced, .card');
        
        cards.forEach(card => {
            // タッチフィードバック
            card.addEventListener('touchstart', () => {
                card.style.transform = 'scale(0.98)';
            });

            card.addEventListener('touchend', () => {
                card.style.transform = '';
            });

            // クリックでの詳細表示
            card.addEventListener('click', (e) => {
                if (!e.target.matches('button, .btn, a')) {
                    const link = card.querySelector('a[href]');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
    },

    /**
     * ボトムナビゲーションの設定
     */
    setupBottomNavigation() {
        let bottomNav = document.querySelector('.gi-bottom-nav');
        if (!bottomNav) {
            bottomNav = this.createBottomNavigation();
        }

        // アクティブ状態の管理
        const currentPath = window.location.pathname;
        const navItems = bottomNav.querySelectorAll('.gi-bottom-nav-item');
        
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.startsWith(href)) {
                item.classList.add('active');
            }
        });
    },

    /**
     * ボトムナビゲーションの作成
     */
    createBottomNavigation() {
        const bottomNav = document.createElement('nav');
        bottomNav.className = 'gi-bottom-nav';
        bottomNav.innerHTML = `
            <a href="/" class="gi-bottom-nav-item">
                <div class="gi-bottom-nav-icon">🏠</div>
                <div class="gi-bottom-nav-label">ホーム</div>
            </a>
            <a href="/grants/" class="gi-bottom-nav-item">
                <div class="gi-bottom-nav-icon">🔍</div>
                <div class="gi-bottom-nav-label">検索</div>
            </a>
            <a href="/favorites/" class="gi-bottom-nav-item">
                <div class="gi-bottom-nav-icon">❤️</div>
                <div class="gi-bottom-nav-label">お気に入り</div>
            </a>
            <a href="/my-page/" class="gi-bottom-nav-item">
                <div class="gi-bottom-nav-icon">👤</div>
                <div class="gi-bottom-nav-label">マイページ</div>
            </a>
        `;
        
        document.body.appendChild(bottomNav);
        return bottomNav;
    },

    /**
     * プルトゥリフレッシュの設定
     */
    setupPullToRefresh() {
        let startY = 0;
        let currentY = 0;
        let isRefreshing = false;

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0 && !isRefreshing) {
                startY = e.touches[0].clientY;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (window.scrollY === 0 && startY > 0) {
                currentY = e.touches[0].clientY;
                const pullDistance = currentY - startY;
                
                if (pullDistance > 100 && !isRefreshing) {
                    this.showPullToRefreshIndicator();
                }
            }
        }, { passive: true });

        document.addEventListener('touchend', () => {
            if (currentY - startY > 100 && !isRefreshing) {
                this.triggerRefresh();
            }
            startY = 0;
            currentY = 0;
        });
    },

    /**
     * リフレッシュ実行
     */
    triggerRefresh() {
        this.showToast('更新中...', 'info');
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    },

    /* ===============================================
       フィルター関連UI
       =============================================== */
    /**
     * フィルターボトムシート表示
     */
    showFilterBottomSheet() {
        if (!this.mobile.filterSheet) {
            this.mobile.filterSheet = this.createFilterBottomSheet();
        }
        
        document.body.appendChild(this.mobile.filterSheet);
        setTimeout(() => {
            this.mobile.filterSheet.classList.add('active');
        }, 10);
    },

    /**
     * フィルターボトムシート非表示
     */
    hideFilterBottomSheet() {
        if (this.mobile.filterSheet) {
            this.mobile.filterSheet.classList.remove('active');
            setTimeout(() => {
                if (this.mobile.filterSheet.parentNode) {
                    this.mobile.filterSheet.parentNode.removeChild(this.mobile.filterSheet);
                }
            }, 300);
        }
    },

    /**
     * フィルターボトムシートの作成
     */
    createFilterBottomSheet() {
        const sheet = document.createElement('div');
        sheet.className = 'gi-filter-bottom-sheet';
        sheet.innerHTML = `
            <div class="gi-filter-sheet-header">
                <h3 class="gi-filter-sheet-title">フィルター</h3>
                <button class="gi-filter-sheet-close" aria-label="閉じる">×</button>
            </div>
            <div class="gi-filter-sheet-content">
                <div class="gi-filter-group">
                    <div class="gi-filter-group-title">カテゴリー</div>
                    <div class="gi-filter-options">
                        <label class="gi-filter-option">
                            <input type="checkbox" class="gi-filter-option-checkbox" data-filter="category" data-value="business">
                            <span>事業助成</span>
                        </label>
                        <label class="gi-filter-option">
                            <input type="checkbox" class="gi-filter-option-checkbox" data-filter="category" data-value="research">
                            <span>研究助成</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="gi-filter-sheet-footer">
                <button class="gi-btn-filter-clear">クリア</button>
                <button class="gi-btn-filter-apply">適用</button>
            </div>
        `;

        // イベントリスナーの設定
        const closeBtn = sheet.querySelector('.gi-filter-sheet-close');
        closeBtn.addEventListener('click', () => this.hideFilterBottomSheet());

        return sheet;
    },

    /* ===============================================
       結果更新・UI更新
       =============================================== */
    /**
     * 検索結果の更新
     */
    updateSearchResults(data) {
        const container = document.querySelector('.gi-grants-grid, .grants-grid, #grants-container');
        if (!container) return;

        if (data.grants && data.grants.length > 0) {
            container.innerHTML = data.grants.map(grant => this.renderGrantCard(grant)).join('');
            this.setupCardInteractions(); // 新しいカードにもインタラクションを設定
        } else {
            container.innerHTML = '<div class="text-center py-8">該当する助成金が見つかりませんでした。</div>';
        }
    },

    /**
     * 助成金カードのレンダリング
     */
    renderGrantCard(grant) {
        return `
            <div class="gi-grant-card-enhanced">
                <div class="gi-card-image-container">
                    <img src="${grant.image || '/assets/images/default-grant.jpg'}" 
                         alt="${this.escapeHtml(grant.title)}" class="gi-card-image">
                    <div class="gi-card-badges">
                        ${grant.is_new ? '<span class="gi-card-badge new">新着</span>' : ''}
                        ${grant.is_featured ? '<span class="gi-card-badge featured">注目</span>' : ''}
                    </div>
                </div>
                <div class="gi-card-content">
                    <h3 class="gi-card-title">${this.escapeHtml(grant.title)}</h3>
                    <div class="gi-card-meta">
                        <div class="gi-card-amount">${grant.amount ? `${grant.amount}円` : '金額未定'}</div>
                        <div class="gi-card-organization">${this.escapeHtml(grant.organization)}</div>
                        <div class="gi-card-deadline">${grant.deadline ? `締切: ${grant.deadline}` : ''}</div>
                    </div>
                    <div class="gi-card-actions">
                        <a href="${grant.url}" class="gi-btn-card-primary">詳細を見る</a>
                        <button class="gi-btn-card-secondary gi-bookmark-btn" data-grant-id="${grant.id}">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    },

    /* ===============================================
       アクセシビリティとパフォーマンス
       =============================================== */
    setupAccessibility() {
        // キーボードナビゲーション
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideSearchSuggestions();
                this.hideFilterBottomSheet();
            }
        });

        // フォーカス管理
        const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        const firstFocusableElement = document.querySelectorAll(focusableElements)[0];
        
        if (firstFocusableElement) {
            firstFocusableElement.addEventListener('keydown', (e) => {
                if (e.key === 'Tab' && e.shiftKey) {
                    // 最初の要素で Shift+Tab が押された場合の処理
                }
            });
        }
    },

    setupPerformance() {
        // 画像遅延読み込み
        const images = document.querySelectorAll('img[data-src]');
        if (images.length > 0 && 'IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('loading');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        }

        // インフィニットスクロール
        this.setupInfiniteScroll();
    },

    setupInfiniteScroll() {
        let page = 2;
        let isLoading = false;

        window.addEventListener('scroll', this.debounce(() => {
            if (isLoading) return;

            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            if (scrollTop + windowHeight >= documentHeight - 1000) {
                isLoading = true;
                
                this.ajax(this.config.apiEndpoint, {
                    data: {
                        action: 'gi_load_more_grants',
                        page: page,
                        nonce: window.gi_ajax_nonce
                    }
                }).then(response => {
                    if (response.success && response.data.grants.length > 0) {
                        const container = document.querySelector('.gi-grants-grid, .grants-grid');
                        if (container) {
                            const newCards = response.data.grants.map(grant => this.renderGrantCard(grant)).join('');
                            container.insertAdjacentHTML('beforeend', newCards);
                            this.setupCardInteractions();
                        }
                        page++;
                    }
                    isLoading = false;
                }).catch(error => {
                    console.error('Load more error:', error);
                    isLoading = false;
                });
            }
        }, 200));
    }
};

// 初期化実行
GrantInsight.init();

// グローバルアクセス用（後方互換性）
window.GrantInsight = GrantInsight;