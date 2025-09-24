<?php
/**
 * Grant Insight Ultra Stylish - Premium Header Template
 * 超スタイリッシュで洗練されたヘッダー（次世代デザイン）
 * 
 * @package Grant_Insight_Ultra_Stylish
 * @version 9.0.0-ultra-stylish
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    
    <?php wp_head(); ?>
    
    <!-- Preload fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ===============================================
           ULTRA STYLISH HEADER COMPLETE STYLES
           =============================================== */
        
        :root {
            /* カラーパレット */
            --color-white: #ffffff;
            --color-black: #000000;
            --color-yellow: #ffeb3b;
            --color-yellow-dark: #ffc107;
            --color-yellow-light: #fff59d;
            --color-yellow-glow: rgba(255, 235, 59, 0.3);
            
            /* グレースケール */
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
            
            /* グラスモーフィズム */
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-bg-strong: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --glass-backdrop: blur(20px);
            
            /* セマンティックカラー */
            --color-primary: var(--color-yellow);
            --color-secondary: var(--color-black);
            --color-success: #4caf50;
            --color-info: #2196f3;
            --color-warning: #ff9800;
            --color-danger: #f44336;
            
            /* テキストカラー */
            --text-primary: var(--color-gray-900);
            --text-secondary: var(--color-gray-600);
            --text-tertiary: var(--color-gray-500);
            --text-inverse: var(--color-white);
            
            /* 背景カラー */
            --bg-primary: var(--color-white);
            --bg-secondary: var(--color-gray-50);
            --bg-tertiary: var(--color-gray-100);
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            
            /* ボーダー */
            --border-light: rgba(0, 0, 0, 0.05);
            --border-medium: rgba(0, 0, 0, 0.1);
            --border-dark: rgba(0, 0, 0, 0.15);
            
            /* シャドウ */
            --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --shadow-glow: 0 0 20px var(--color-yellow-glow);
            
            /* スペーシング */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 2.5rem;
            --spacing-3xl: 3rem;
            
            /* ボーダーラディウス */
            --radius-xs: 0.125rem;
            --radius-sm: 0.25rem;
            --radius-md: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
            --radius-2xl: 1rem;
            --radius-3xl: 1.5rem;
            --radius-full: 9999px;
            
            /* トランジション */
            --transition-fast: 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            
            /* フォント */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-weight-light: 300;
            --font-weight-normal: 400;
            --font-weight-medium: 500;
            --font-weight-semibold: 600;
            --font-weight-bold: 700;
            --font-weight-extrabold: 800;
            --font-weight-black: 900;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            background: var(--bg-gradient);
            color: var(--text-primary);
        }
        
        .gi-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--spacing-md);
        }
        
        @media (min-width: 1024px) {
            .gi-container {
                padding: 0 var(--spacing-xl);
            }
        }
        
        /* Header Base Styles */
        .gi-header {
            position: fixed;
            top: var(--spacing-md);
            left: var(--spacing-md);
            right: var(--spacing-md);
            z-index: 1000;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: var(--glass-backdrop);
            -webkit-backdrop-filter: var(--glass-backdrop);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-3xl);
            transition: all var(--transition-slow);
            transform: translateY(0);
            box-shadow: var(--glass-shadow);
        }
        
        @media (min-width: 1024px) {
            .gi-header {
                top: var(--spacing-lg);
                left: var(--spacing-xl);
                right: var(--spacing-xl);
            }
        }
        
        .gi-header.scrolled {
            background: rgba(255, 255, 255, 0.12);
            box-shadow: var(--glass-shadow), var(--shadow-lg);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .gi-header.hidden {
            transform: translateY(-120%);
        }
        
        /* Floating Announcement Bar */
        .gi-announcement {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--color-black);
            color: var(--color-white);
            text-align: center;
            padding: var(--spacing-sm) var(--spacing-md);
            font-size: 0.8125rem;
            font-weight: var(--font-weight-medium);
            z-index: 999;
            overflow: hidden;
            transform: translateY(0);
            transition: transform var(--transition-slow);
        }
        
        .gi-announcement.hidden {
            transform: translateY(-100%);
        }
        
        .gi-announcement::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, var(--color-yellow-glow), transparent);
            animation: shimmer 4s infinite;
        }
        
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .gi-announcement a {
            color: var(--color-primary);
            text-decoration: none;
            margin-left: var(--spacing-sm);
            font-weight: var(--font-weight-semibold);
            transition: var(--transition-base);
        }
        
        .gi-announcement a:hover {
            color: var(--color-yellow-light);
            text-shadow: 0 0 8px var(--color-primary);
        }
        
        /* Header Container */
        .gi-header-container {
            padding: var(--spacing-sm);
        }
        
        /* Main Header Layout */
        .gi-header-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 3.5rem;
            padding: 0 var(--spacing-md);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-2xl);
            position: relative;
            overflow: hidden;
        }
        
        @media (min-width: 1024px) {
            .gi-header-main {
                height: 4rem;
                padding: 0 var(--spacing-lg);
            }
        }
        
        .gi-header-main::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--color-primary), transparent);
            animation: scan-line 3s infinite;
        }
        
        @keyframes scan-line {
            0%, 100% { left: -100%; opacity: 0; }
            50% { left: 100%; opacity: 1; }
        }
        
        /* Logo Section */
        .gi-logo {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            text-decoration: none;
            transition: all var(--transition-bounce);
            flex-shrink: 0;
            position: relative;
        }
        
        .gi-logo:hover {
            transform: translateY(-2px) scale(1.02);
        }
        
        .gi-logo-image {
            height: 2rem;
            width: auto;
            border-radius: var(--radius-lg);
            transition: all var(--transition-base);
            filter: drop-shadow(0 0 10px rgba(255, 235, 59, 0.3));
        }
        
        @media (min-width: 1024px) {
            .gi-logo-image {
                height: 2.5rem;
            }
        }
        
        .gi-logo:hover .gi-logo-image {
            filter: drop-shadow(0 0 15px rgba(255, 235, 59, 0.5));
        }
        
        .gi-logo-text h1 {
            margin: 0;
            font-size: 1rem;
            font-weight: var(--font-weight-black);
            color: var(--color-black);
            line-height: 1.2;
            letter-spacing: -0.02em;
            position: relative;
        }
        
        @media (min-width: 1024px) {
            .gi-logo-text h1 {
                font-size: 1.125rem;
            }
        }
        
        .gi-logo-text p {
            margin: 0;
            font-size: 0.6875rem;
            color: var(--text-secondary);
            font-weight: var(--font-weight-medium);
        }
        
        @media (min-width: 1024px) {
            .gi-logo-text p {
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 640px) {
            .gi-logo-text {
                display: none;
            }
        }
        
        /* Floating Navigation */
        .gi-nav {
            display: none;
            align-items: center;
            gap: var(--spacing-xs);
            flex: 1;
            justify-content: center;
            margin: 0 var(--spacing-lg);
        }
        
        @media (min-width: 1024px) {
            .gi-nav {
                display: flex;
            }
        }
        
        .gi-nav-link {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm) var(--spacing-md);
            color: var(--text-primary);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            font-size: 0.875rem;
            border-radius: var(--radius-full);
            position: relative;
            transition: all var(--transition-base);
            white-space: nowrap;
            background: transparent;
            border: 1px solid transparent;
            overflow: hidden;
        }
        
        .gi-nav-link::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-full);
            opacity: 0;
            transition: opacity var(--transition-base);
            z-index: -1;
        }
        
        .gi-nav-link:hover::before {
            opacity: 1;
        }
        
        .gi-nav-link:hover {
            color: var(--color-black);
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .gi-nav-link i {
            font-size: 0.8125rem;
        }
        
        /* Current page indicator */
        .gi-nav-link.current {
            color: var(--color-black);
            background: var(--color-primary);
            font-weight: var(--font-weight-semibold);
            border-color: var(--color-yellow-dark);
            box-shadow: var(--shadow-glow);
        }
        
        .gi-nav-link.current::before {
            display: none;
        }
        
        .gi-nav-link.current:hover {
            background: var(--color-yellow-dark);
            transform: translateY(-2px) scale(1.05);
        }
        
        /* Floating Actions */
        .gi-actions {
            display: none;
            align-items: center;
            gap: var(--spacing-sm);
            flex-shrink: 0;
        }
        
        @media (min-width: 1024px) {
            .gi-actions {
                display: flex;
            }
        }
        
        .gi-btn {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-full);
            text-decoration: none;
            font-weight: var(--font-weight-semibold);
            font-size: 0.8125rem;
            transition: all var(--transition-bounce);
            border: none;
            cursor: pointer;
            background: transparent;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }
        
        .gi-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-full);
            opacity: 0;
            transition: opacity var(--transition-base);
            z-index: -1;
        }
        
        .gi-btn-icon {
            width: 2.25rem;
            height: 2.25rem;
            padding: 0;
            color: var(--text-secondary);
            justify-content: center;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .gi-btn-icon::before {
            border-radius: var(--radius-lg);
        }
        
        .gi-btn-icon:hover::before {
            opacity: 1;
        }
        
        .gi-btn-icon:hover {
            color: var(--color-black);
            transform: translateY(-2px) scale(1.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .gi-btn-primary {
            background: var(--color-primary);
            color: var(--color-black);
            border: 1px solid var(--color-yellow-dark);
            box-shadow: var(--shadow-sm), var(--shadow-glow);
        }
        
        .gi-btn-primary::before {
            display: none;
        }
        
        .gi-btn-primary:hover {
            background: var(--color-yellow-dark);
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-md), var(--shadow-glow);
        }
        
        .gi-btn-secondary {
            background: rgba(0, 0, 0, 0.8);
            color: var(--color-white);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .gi-btn-secondary::before {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .gi-btn-secondary:hover::before {
            opacity: 1;
        }
        
        .gi-btn-secondary:hover {
            transform: translateY(-2px) scale(1.05);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        /* Mobile Menu Button */
        .gi-mobile-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--transition-bounce);
        }
        
        @media (min-width: 1024px) {
            .gi-mobile-btn {
                display: none;
            }
        }
        
        .gi-mobile-btn:hover {
            color: var(--color-black);
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px) scale(1.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        /* Floating Search Bar */
        .gi-search-bar {
            margin-top: var(--spacing-sm);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: var(--glass-backdrop);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            display: none;
            transform: translateY(-20px) scale(0.95);
            opacity: 0;
            transition: all var(--transition-slow);
            overflow: hidden;
            box-shadow: var(--glass-shadow);
        }
        
        .gi-search-bar.show {
            display: block;
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        
        .gi-search-form {
            padding: var(--spacing-lg);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        @media (min-width: 768px) {
            .gi-search-form {
                flex-direction: row;
                align-items: end;
            }
        }
        
        .gi-search-input-wrapper {
            flex: 1;
            position: relative;
        }
        
        .gi-search-input {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) 3rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-2xl);
            font-size: 0.9375rem;
            transition: all var(--transition-base);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: var(--text-primary);
            font-weight: var(--font-weight-medium);
            font-family: var(--font-family);
        }
        
        .gi-search-input:focus {
            outline: none;
            border-color: var(--color-primary);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 4px var(--color-yellow-glow);
            transform: translateY(-1px);
        }
        
        .gi-search-input::placeholder {
            color: var(--text-tertiary);
            font-weight: var(--font-weight-normal);
        }
        
        .gi-search-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-tertiary);
            font-size: 1rem;
        }
        
        .gi-search-filters {
            display: flex;
            gap: var(--spacing-sm);
            flex-wrap: wrap;
        }
        
        .gi-search-select {
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-2xl);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: var(--text-primary);
            font-size: 0.8125rem;
            font-weight: var(--font-weight-medium);
            min-width: 130px;
            transition: all var(--transition-base);
            cursor: pointer;
            font-family: var(--font-family);
        }
        
        .gi-search-select:focus {
            outline: none;
            border-color: var(--color-primary);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 4px var(--color-yellow-glow);
            transform: translateY(-1px);
        }
        
        .gi-search-submit {
            background: var(--color-black);
            color: var(--color-white);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: var(--spacing-md) var(--spacing-xl);
            border-radius: var(--radius-2xl);
            font-weight: var(--font-weight-semibold);
            font-size: 0.8125rem;
            cursor: pointer;
            transition: all var(--transition-bounce);
            backdrop-filter: blur(10px);
            white-space: nowrap;
            font-family: var(--font-family);
        }
        
        .gi-search-submit:hover {
            background: var(--color-gray-800);
            transform: translateY(-2px) scale(1.05);
            box-shadow: var(--shadow-md);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        /* Mobile Menu */
        .gi-mobile-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-slow);
        }
        
        .gi-mobile-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .gi-mobile-menu {
            position: fixed;
            top: var(--spacing-md);
            right: var(--spacing-md);
            bottom: var(--spacing-md);
            width: 18rem;
            max-width: calc(100vw - 2rem);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: var(--glass-backdrop);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            transform: translateX(110%) scale(0.9);
            transition: all var(--transition-slow);
            overflow: hidden;
            z-index: 1000;
            box-shadow: var(--glass-shadow);
        }
        
        .gi-mobile-menu.show {
            transform: translateX(0) scale(1);
        }
        
        .gi-mobile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .gi-mobile-title {
            font-size: 1rem;
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
        }
        
        .gi-mobile-close {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--transition-bounce);
        }
        
        .gi-mobile-close:hover {
            color: var(--color-danger);
            background: rgba(244, 67, 54, 0.1);
            transform: scale(1.1);
            border-color: rgba(244, 67, 54, 0.2);
        }
        
        .gi-mobile-search {
            padding: var(--spacing-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .gi-mobile-nav {
            padding: var(--spacing-md) 0;
            flex: 1;
            overflow-y: auto;
        }
        
        .gi-mobile-nav-link {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md) var(--spacing-lg);
            color: var(--text-primary);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            font-size: 0.875rem;
            transition: all var(--transition-base);
            border-left: 3px solid transparent;
            margin: var(--spacing-xs) 0;
        }
        
        .gi-mobile-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-black);
            border-left-color: var(--color-primary);
            padding-left: calc(var(--spacing-lg) + var(--spacing-xs));
        }
        
        .gi-mobile-nav-link.current {
            background: rgba(255, 235, 59, 0.1);
            color: var(--color-black);
            border-left-color: var(--color-primary);
            font-weight: var(--font-weight-semibold);
        }
        
        .gi-mobile-nav-link i {
            width: 1rem;
            text-align: center;
            font-size: 0.875rem;
        }
        
        .gi-mobile-actions {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--spacing-lg);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .gi-mobile-cta {
            background: var(--color-primary);
            color: var(--color-black);
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-2xl);
            text-decoration: none;
            font-weight: var(--font-weight-semibold);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            transition: all var(--transition-bounce);
            box-shadow: var(--shadow-sm), var(--shadow-glow);
            border: 1px solid var(--color-yellow-dark);
        }
        
        .gi-mobile-cta:hover {
            background: var(--color-yellow-dark);
            transform: translateY(-2px) scale(1.02);
            box-shadow: var(--shadow-md), var(--shadow-glow);
        }
        
        /* Statistics Display */
        .gi-stats {
            display: none;
            align-items: center;
            gap: var(--spacing-md);
            font-size: 0.6875rem;
            color: var(--text-secondary);
            margin-left: var(--spacing-lg);
            padding: var(--spacing-xs) var(--spacing-sm);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-full);
        }
        
        @media (min-width: 1280px) {
            .gi-stats {
                display: flex;
            }
        }
        
        .gi-stat-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            position: relative;
        }
        
        .gi-stat-item::after {
            content: '';
            position: absolute;
            right: calc(var(--spacing-md) * -0.5);
            width: 1px;
            height: 60%;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .gi-stat-item:last-child::after {
            display: none;
        }
        
        .gi-stat-number {
            font-weight: var(--font-weight-bold);
            color: var(--color-black);
        }
        
        .gi-stat-icon {
            width: 3px;
            height: 3px;
            background: var(--color-primary);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--color-primary);
            animation: pulse-glow 2s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { 
                opacity: 1; 
                transform: scale(1);
                box-shadow: 0 0 6px var(--color-primary);
            }
            50% { 
                opacity: 0.7; 
                transform: scale(1.2);
                box-shadow: 0 0 12px var(--color-primary);
            }
        }
        
        /* Utility Classes */
        .gi-hidden {
            display: none !important;
        }
        
        /* Loading State */
        .gi-loading {
            opacity: 0.6;
            pointer-events: none;
            position: relative;
        }
        
        .gi-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid var(--color-primary);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Focus States */
        button:focus-visible,
        a:focus-visible,
        input:focus-visible,
        select:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }
        
        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                --text-primary: #f8fafc;
                --text-secondary: #cbd5e1;
                --text-tertiary: #94a3b8;
            }
        }
    </style>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Floating Announcement Bar -->
<?php if (get_theme_mod('gi_show_announcement', true)): ?>
<div id="gi-announcement" class="gi-announcement">
    <i class="fas fa-sparkles" style="margin-right: 0.5rem; color: var(--color-primary);"></i>
    <?php echo esc_html(get_theme_mod('gi_announcement_text', '🎯 最新助成金情報を随時更新中！あなたにぴったりの支援制度を見つけよう')); ?>
    <?php if ($announcement_link = get_theme_mod('gi_announcement_link', get_post_type_archive_link('grant'))): ?>
        <a href="<?php echo esc_url($announcement_link); ?>">今すぐ検索する →</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Floating Header -->
<header id="gi-site-header" class="gi-header">
    <div class="gi-header-container">
        <div class="gi-header-main">
            <!-- Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="gi-logo">
                <img src="http://joseikin-insight.com/wp-content/uploads/2025/09/名称未設定のデザイン.png" 
                     alt="<?php bloginfo('name'); ?>" 
                     class="gi-logo-image"
                     loading="eager">
                
                <div class="gi-logo-text">
                    <h1><?php bloginfo('name'); ?></h1>
                    <?php if ($tagline = get_bloginfo('description')): ?>
                        <p><?php echo esc_html($tagline); ?></p>
                    <?php endif; ?>
                </div>
            </a>
            
            <!-- Floating Navigation -->
            <nav class="gi-nav" role="navigation">
                <?php
                // Get current page info for active state
                $current_url = home_url(add_query_arg(null, null));
                $home_url = home_url('/');
                $grants_url = get_post_type_archive_link('grant');
                
                $menu_items = array(
                    array(
                        'url' => $home_url, 
                        'title' => 'ホーム', 
                        'icon' => 'fas fa-home',
                        'current' => ($current_url === $home_url)
                    ),
                    array(
                        'url' => $grants_url, 
                        'title' => '助成金一覧', 
                        'icon' => 'fas fa-list-ul',
                        'current' => (strpos($current_url, 'grants') !== false || is_post_type_archive('grant') || is_singular('grant'))
                    ),
                    array(
                        'url' => home_url('/about/'), 
                        'title' => 'サイトについて', 
                        'icon' => 'fas fa-info-circle',
                        'current' => (strpos($current_url, '/about/') !== false)
                    ),
                );
                
                foreach ($menu_items as $item) {
                    $class = 'gi-nav-link';
                    if ($item['current']) {
                        $class .= ' current';
                    }
                    
                    echo '<a href="' . esc_url($item['url']) . '" class="' . $class . '">';
                    echo '<i class="' . esc_attr($item['icon']) . '"></i>';
                    echo '<span>' . esc_html($item['title']) . '</span>';
                    echo '</a>';
                }
                ?>
            </nav>
            
            <!-- Floating Actions -->
            <div class="gi-actions">
                <!-- Search Toggle -->
                <button type="button" id="gi-search-toggle" class="gi-btn gi-btn-icon" title="詳細検索" aria-label="詳細検索を開く">
                    <i class="fas fa-search"></i>
                </button>
                
                <!-- Stats Display -->
                <div class="gi-stats">
                    <?php
                    $stats = gi_get_cached_stats();
                    if ($stats && !empty($stats['total_grants'])) {
                        echo '<div class="gi-stat-item">';
                        echo '<div class="gi-stat-icon"></div>';
                        echo '<span class="gi-stat-number">' . number_format($stats['total_grants']) . '</span>';
                        echo '<span>件</span>';
                        echo '</div>';
                        
                        if (!empty($stats['active_grants'])) {
                            echo '<div class="gi-stat-item">';
                            echo '<div class="gi-stat-icon" style="background: var(--color-success); box-shadow: 0 0 6px var(--color-success);"></div>';
                            echo '<span class="gi-stat-number">' . number_format($stats['active_grants']) . '</span>';
                            echo '<span>募集中</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                
                <!-- CTA Button -->
                <a href="<?php echo esc_url(get_post_type_archive_link('grant')); ?>" class="gi-btn gi-btn-primary">
                    <i class="fas fa-search"></i>
                    <span>助成金を探す</span>
                </a>
            </div>
            
            <!-- Mobile Menu Button -->
            <button type="button" id="gi-mobile-menu-btn" class="gi-mobile-btn" aria-label="メニューを開く">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <!-- Floating Search Bar -->
        <div id="gi-search-bar" class="gi-search-bar">
            <form id="gi-search-form" class="gi-search-form">
                <div class="gi-search-input-wrapper">
                    <input type="text" 
                           id="gi-search-input"
                           name="search" 
                           placeholder="助成金名、実施組織名、対象事業者などで検索..." 
                           class="gi-search-input"
                           autocomplete="off">
                    <i class="fas fa-search gi-search-icon"></i>
                </div>
                
                <div class="gi-search-filters">
                    <select name="category" class="gi-search-select" aria-label="カテゴリーを選択">
                        <option value="">すべてのカテゴリー</option>
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => 'grant_category',
                            'hide_empty' => true,
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 30
                        ));
                        if ($categories && !is_wp_error($categories)) {
                            foreach ($categories as $category) {
                                echo '<option value="' . esc_attr($category->slug) . '">';
                                echo esc_html($category->name) . ' (' . $category->count . ')';
                                echo '</option>';
                            }
                        }
                        ?>
                    </select>
                    
                    <select name="prefecture" class="gi-search-select" aria-label="都道府県を選択">
                        <option value="">全国対象</option>
                        <?php
                        $prefectures = get_terms(array(
                            'taxonomy' => 'grant_prefecture',
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ));
                        if ($prefectures && !is_wp_error($prefectures)) {
                            foreach ($prefectures as $prefecture) {
                                echo '<option value="' . esc_attr($prefecture->slug) . '">';
                                echo esc_html($prefecture->name) . ' (' . $prefecture->count . ')';
                                echo '</option>';
                            }
                        }
                        ?>
                    </select>
                    
                    <button type="submit" class="gi-search-submit">
                        <i class="fas fa-search"></i>
                        <span>検索実行</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</header>

<!-- Floating Mobile Menu -->
<div id="gi-mobile-overlay" class="gi-mobile-overlay">
    <div id="gi-mobile-menu" class="gi-mobile-menu">
        <!-- Mobile Header -->
        <div class="gi-mobile-header">
            <div class="gi-mobile-title">
                <i class="fas fa-bars"></i>
                メニュー
            </div>
            <button type="button" id="gi-mobile-close" class="gi-mobile-close" aria-label="メニューを閉じる">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Mobile Search -->
        <div class="gi-mobile-search">
            <div class="gi-search-input-wrapper">
                <input type="text" 
                       placeholder="助成金を検索..." 
                       class="gi-search-input"
                       id="gi-mobile-search-input">
                <i class="fas fa-search gi-search-icon"></i>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <nav class="gi-mobile-nav">
            <?php
            foreach ($menu_items as $item) {
                $class = 'gi-mobile-nav-link';
                if ($item['current']) {
                    $class .= ' current';
                }
                
                echo '<a href="' . esc_url($item['url']) . '" class="' . $class . '">';
                echo '<i class="' . esc_attr($item['icon']) . '"></i>';
                echo '<span>' . esc_html($item['title']) . '</span>';
                if ($item['current']) {
                    echo '<div style="margin-left: auto; width: 4px; height: 4px; background: var(--color-primary); border-radius: 50%; box-shadow: 0 0 6px var(--color-primary);"></div>';
                }
                echo '</a>';
            }
            ?>
        </nav>
        
        <!-- Mobile Actions -->
        <div class="gi-mobile-actions">
            <a href="<?php echo esc_url(get_post_type_archive_link('grant')); ?>" class="gi-mobile-cta">
                <i class="fas fa-search"></i>
                <span>助成金を探す</span>
            </a>
            
            <?php if ($stats && !empty($stats['total_grants'])): ?>
            <div style="text-align: center; margin-top: var(--spacing-md); padding-top: var(--spacing-md); border-top: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.75rem; color: var(--text-secondary);">
                <i class="fas fa-info-circle" style="margin-right: var(--spacing-xs); color: var(--color-primary);"></i>
                現在 <strong style="color: var(--color-black);"><?php echo number_format($stats['total_grants']); ?>件</strong> の助成金情報を掲載中
                <?php if (!empty($stats['active_grants'])): ?>
                （<strong style="color: var(--color-success);"><?php echo number_format($stats['active_grants']); ?>件</strong> 募集中）
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===============================================
    // ULTRA STYLISH HEADER FUNCTIONALITY
    // ===============================================
    
    // Elements
    const header = document.getElementById('gi-site-header');
    const announcement = document.getElementById('gi-announcement');
    const searchToggle = document.getElementById('gi-search-toggle');
    const searchBar = document.getElementById('gi-search-bar');
    const searchForm = document.getElementById('gi-search-form');
    const searchInput = document.getElementById('gi-search-input');
    const mobileSearchInput = document.getElementById('gi-mobile-search-input');
    const mobileMenuBtn = document.getElementById('gi-mobile-menu-btn');
    const mobileOverlay = document.getElementById('gi-mobile-overlay');
    const mobileMenu = document.getElementById('gi-mobile-menu');
    const mobileClose = document.getElementById('gi-mobile-close');
    
    // State
    let lastScrollTop = 0;
    let isSearchOpen = false;
    let isMobileMenuOpen = false;
    let announcementHeight = announcement ? announcement.offsetHeight : 0;
    
    // ===============================================
    // SCROLL BEHAVIOR
    // ===============================================
    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Hide announcement bar on scroll
        if (announcement) {
            if (scrollTop > 50) {
                announcement.classList.add('hidden');
            } else {
                announcement.classList.remove('hidden');
            }
        }
        
        // Hide/show header on scroll
        if (scrollTop > lastScrollTop && scrollTop > 200 && !isMobileMenuOpen && !isSearchOpen) {
            header.classList.add('hidden');
        } else {
            header.classList.remove('hidden');
        }
        
        // Add scrolled effect
        if (scrollTop > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScrollTop = scrollTop;
    }
    
    // Throttled scroll handler
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(handleScroll, 8);
    });
    
    // ===============================================
    // SEARCH FUNCTIONALITY
    // ===============================================
    function toggleSearch() {
        isSearchOpen = !isSearchOpen;
        
        if (isSearchOpen) {
            searchBar.classList.add('show');
            searchBar.classList.remove('gi-hidden');
            header.classList.remove('hidden');
            
            setTimeout(() => {
                searchInput?.focus();
            }, 200);
            
            if (searchToggle) {
                searchToggle.innerHTML = '<i class="fas fa-times"></i>';
                searchToggle.title = '検索を閉じる';
            }
        } else {
            searchBar.classList.remove('show');
            setTimeout(() => {
                searchBar.classList.add('gi-hidden');
            }, 400);
            
            if (searchToggle) {
                searchToggle.innerHTML = '<i class="fas fa-search"></i>';
                searchToggle.title = '詳細検索';
            }
        }
    }
    
    searchToggle?.addEventListener('click', toggleSearch);
    
    // Search form submission
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.gi-search-submit');
            if (submitBtn) {
                submitBtn.classList.add('gi-loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>検索中...</span>';
            }
            
            const formData = new FormData(this);
            const params = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (value.trim()) {
                    params.append(key, value);
                }
            }
            
            const archiveUrl = '<?php echo esc_url(get_post_type_archive_link("grant")); ?>';
            const searchUrl = archiveUrl + (params.toString() ? '?' + params.toString() : '');
            
            setTimeout(() => {
                window.location.href = searchUrl;
            }, 300);
        });
    }
    
    // Mobile search
    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    const archiveUrl = '<?php echo esc_url(get_post_type_archive_link("grant")); ?>';
                    window.location.href = archiveUrl + '?search=' + encodeURIComponent(query);
                }
            }
        });
    }
    
    // ===============================================
    // MOBILE MENU
    // ===============================================
    function openMobileMenu() {
        isMobileMenuOpen = true;
        mobileOverlay?.classList.add('show');
        mobileMenu?.classList.add('show');
        header.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            const firstFocusable = mobileMenu?.querySelector('input, a, button');
            firstFocusable?.focus();
        }, 500);
    }
    
    function closeMobileMenu() {
        isMobileMenuOpen = false;
        mobileOverlay?.classList.remove('show');
        mobileMenu?.classList.remove('show');
        document.body.style.overflow = '';
        
        mobileMenuBtn?.focus();
    }
    
    mobileMenuBtn?.addEventListener('click', openMobileMenu);
    mobileClose?.addEventListener('click', closeMobileMenu);
    
    // Close on overlay click
    mobileOverlay?.addEventListener('click', function(e) {
        if (e.target === mobileOverlay) {
            closeMobileMenu();
        }
    });
    
    // ===============================================
    // KEYBOARD NAVIGATION
    // ===============================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (isMobileMenuOpen) {
                closeMobileMenu();
            } else if (isSearchOpen) {
                toggleSearch();
            }
        }
        
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (!isSearchOpen) {
                toggleSearch();
            }
        }
    });
    
    // ===============================================
    // ADVANCED INTERACTIONS
    // ===============================================
    
    // Parallax effect for floating elements
    let mouseX = 0;
    let mouseY = 0;
    
    document.addEventListener('mousemove', function(e) {
        mouseX = (e.clientX - window.innerWidth / 2) / 50;
        mouseY = (e.clientY - window.innerHeight / 2) / 50;
        
        if (header && !isMobileMenuOpen) {
            header.style.transform = `translate(${mouseX * 0.5}px, ${mouseY * 0.3}px)`;
        }
    });
    
    // Reset transform on mouse leave
    document.addEventListener('mouseleave', function() {
        if (header) {
            header.style.transform = 'translate(0, 0)';
        }
    });
    
    // ===============================================
    // INITIALIZATION
    // ===============================================
    searchBar?.classList.add('gi-hidden');
    
    // Dynamic margin adjustment
    const adjustMainContentMargin = () => {
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            const headerHeight = header ? header.offsetHeight : 0;
            const margin = announcementHeight + 80; // Base margin + spacing
            mainContent.style.marginTop = margin + 'px';
        }
    };
    
    // Initial margin adjustment
    setTimeout(adjustMainContentMargin, 100);
    
    // Adjust on window resize
    window.addEventListener('resize', adjustMainContentMargin);
    
    setTimeout(() => {
        document.body.classList.add('gi-loaded');
    }, 100);
    
    console.log('✨ Grant Insight Ultra Stylish Header initialized successfully!');
    
    // ===============================================
    // GLOBAL API
    // ===============================================
    window.GI_UltraStylishHeader = {
        toggleSearch: toggleSearch,
        openMobileMenu: openMobileMenu,
        closeMobileMenu: closeMobileMenu,
        isSearchOpen: () => isSearchOpen,
        isMobileMenuOpen: () => isMobileMenuOpen,
        adjustMainContentMargin: adjustMainContentMargin
    };
});
</script>

<!-- Main Content Area -->
<main id="main-content" class="gi-main-content">
    <!-- Content will be injected here by template files -->