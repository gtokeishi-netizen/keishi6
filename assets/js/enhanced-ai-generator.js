/**
 * Enhanced AI Content Generator - Frontend
 * Advanced AI generation with context awareness and SEO optimization
 * 
 * @version 1.0.0
 */

class GI_EnhancedAIGenerator {
    constructor() {
        this.isGenerating = false;
        this.generatedFields = new Set();
        this.contextData = {};
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeUI());
        } else {
            this.initializeUI();
        }
    }
    
    initializeUI() {
        this.addAIButtons();
        this.setupEventListeners();
        this.startContextMonitoring();
        
        // Auto-generate on page load if enabled
        if (this.shouldAutoGenerate()) {
            setTimeout(() => this.performSmartAutoFill(), 2000);
        }
    }
    
    /**
     * Add AI generation buttons to form fields
     */
    addAIButtons() {
        const targetFields = [
            { selector: '#title', field: 'post_title', label: 'タイトル生成' },
            { selector: '#content', field: 'post_content', label: 'コンテンツ生成' },
            { selector: '#excerpt', field: 'post_excerpt', label: '概要生成' },
            { selector: '[name="eligibility_criteria"]', field: 'eligibility_criteria', label: '対象者生成' },
            { selector: '[name="application_process"]', field: 'application_process', label: '申請手順生成' },
            { selector: '[name="required_documents"]', field: 'required_documents', label: '必要書類生成' }
        ];
        
        targetFields.forEach(fieldConfig => {
            const field = document.querySelector(fieldConfig.selector);
            if (field) {
                this.addAIButtonToField(field, fieldConfig);
            }
        });
        
        // Add global AI panel
        this.addGlobalAIPanel();
    }
    
    /**
     * Add AI button to specific field
     */
    addAIButtonToField(field, config) {
        // Create button container
        const container = document.createElement('div');
        container.className = 'gi-ai-button-container';
        container.style.cssText = `
            display: flex;
            gap: 5px;
            margin-top: 5px;
            flex-wrap: wrap;
        `;
        
        // Main generate button
        const generateBtn = this.createAIButton(config.label, 'generate', () => {
            this.generateForField(config.field, field, 'smart_fill');
        });
        
        // Regenerate button (if field has content)
        const regenerateBtn = this.createAIButton('再生成', 'regenerate', () => {
            this.regenerateField(config.field, field);
        });
        regenerateBtn.style.display = field.value.trim() ? 'inline-block' : 'none';
        
        // Improve button (if field has content)
        const improveBtn = this.createAIButton('改善', 'improve', () => {
            this.regenerateField(config.field, field, 'improve');
        });
        improveBtn.style.display = field.value.trim() ? 'inline-block' : 'none';
        
        container.appendChild(generateBtn);
        container.appendChild(regenerateBtn);
        container.appendChild(improveBtn);
        
        // Insert after field
        field.parentNode.insertBefore(container, field.nextSibling);
        
        // Update button visibility when field changes
        field.addEventListener('input', () => {
            const hasContent = field.value.trim().length > 0;
            regenerateBtn.style.display = hasContent ? 'inline-block' : 'none';
            improveBtn.style.display = hasContent ? 'inline-block' : 'none';
        });
    }
    
    /**
     * Create AI button element
     */
    createAIButton(text, action, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `gi-ai-button gi-ai-${action}`;
        button.innerHTML = `
            <span class="gi-ai-icon">🤖</span>
            <span class="gi-ai-text">${text}</span>
            <span class="gi-ai-spinner" style="display: none;">⏳</span>
        `;
        
        button.style.cssText = `
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s;
        `;
        
        button.addEventListener('mouseover', () => {
            button.style.background = '#005a87';
        });
        
        button.addEventListener('mouseout', () => {
            button.style.background = '#007cba';
        });
        
        button.addEventListener('click', onClick);
        
        return button;
    }
    
    /**
     * Add global AI panel
     */
    addGlobalAIPanel() {
        const panel = document.createElement('div');
        panel.id = 'gi-global-ai-panel';
        panel.className = 'gi-ai-panel';
        panel.innerHTML = `
            <div class="gi-ai-panel-header">
                <h3>🤖 AI自動生成ツール</h3>
                <button type="button" class="gi-ai-panel-toggle">×</button>
            </div>
            <div class="gi-ai-panel-content">
                <div class="gi-ai-option">
                    <button type="button" id="gi-smart-autofill" class="gi-ai-action-btn">
                        🎯 スマート自動入力
                    </button>
                    <p>空欄フィールドを既存情報から自動生成</p>
                </div>
                <div class="gi-ai-option">
                    <button type="button" id="gi-regenerate-all" class="gi-ai-action-btn">
                        🔄 すべて再生成
                    </button>
                    <p>全フィールドを改善して再生成</p>
                </div>
                <div class="gi-ai-option">
                    <button type="button" id="gi-seo-optimize" class="gi-ai-action-btn">
                        🚀 SEO最適化
                    </button>
                    <p>SEOに配慮した内容に最適化</p>
                </div>
                <div class="gi-ai-settings">
                    <label>
                        <input type="checkbox" id="gi-auto-generate" ${this.getAutoGenerateSetting() ? 'checked' : ''}>
                        ページ読み込み時に自動生成
                    </label>
                </div>
            </div>
        `;
        
        this.addPanelStyles();
        
        // Add to page
        if (document.body) {
            document.body.appendChild(panel);
        }
        
        // Setup panel events
        this.setupPanelEvents(panel);
    }
    
    /**
     * Add CSS styles for AI panel
     */
    addPanelStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .gi-ai-panel {
                position: fixed;
                top: 50px;
                right: 20px;
                width: 300px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                font-family: Arial, sans-serif;
            }
            
            .gi-ai-panel-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px;
                background: #f8f9fa;
                border-bottom: 1px solid #ddd;
                border-radius: 8px 8px 0 0;
            }
            
            .gi-ai-panel-header h3 {
                margin: 0;
                font-size: 16px;
                color: #333;
            }
            
            .gi-ai-panel-toggle {
                background: none;
                border: none;
                font-size: 18px;
                cursor: pointer;
                color: #666;
            }
            
            .gi-ai-panel-content {
                padding: 15px;
            }
            
            .gi-ai-option {
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #eee;
            }
            
            .gi-ai-option:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }
            
            .gi-ai-action-btn {
                width: 100%;
                padding: 10px;
                background: #007cba;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                margin-bottom: 8px;
                transition: background 0.2s;
            }
            
            .gi-ai-action-btn:hover {
                background: #005a87;
            }
            
            .gi-ai-action-btn:disabled {
                background: #ccc;
                cursor: not-allowed;
            }
            
            .gi-ai-option p {
                margin: 0;
                font-size: 12px;
                color: #666;
            }
            
            .gi-ai-settings {
                padding-top: 10px;
                border-top: 1px solid #eee;
            }
            
            .gi-ai-settings label {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                color: #333;
                cursor: pointer;
            }
            
            .gi-ai-generating {
                opacity: 0.6;
                pointer-events: none;
            }
        `;
        
        document.head.appendChild(style);
    }
    
    /**
     * Setup panel event listeners
     */
    setupPanelEvents(panel) {
        // Toggle panel
        panel.querySelector('.gi-ai-panel-toggle').addEventListener('click', () => {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        });
        
        // Smart autofill
        panel.querySelector('#gi-smart-autofill').addEventListener('click', () => {
            this.performSmartAutoFill();
        });
        
        // Regenerate all
        panel.querySelector('#gi-regenerate-all').addEventListener('click', () => {
            this.regenerateAllFields();
        });
        
        // SEO optimize
        panel.querySelector('#gi-seo-optimize').addEventListener('click', () => {
            this.performSEOOptimization();
        });
        
        // Auto-generate setting
        panel.querySelector('#gi-auto-generate').addEventListener('change', (e) => {
            this.setAutoGenerateSetting(e.target.checked);
        });
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Monitor field changes for context
        const monitorFields = ['#title', '[name="organization"]', '[name="max_amount"]', '[name="deadline"]'];
        monitorFields.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) {
                field.addEventListener('input', () => this.updateContext());
            }
        });
        
        // Monitor taxonomy changes
        const taxonomyFields = document.querySelectorAll('[name="tax_input[grant_category][]"], [name="tax_input[grant_prefecture][]"]');
        taxonomyFields.forEach(field => {
            field.addEventListener('change', () => this.updateContext());
        });
    }
    
    /**
     * Start monitoring context changes
     */
    startContextMonitoring() {
        this.updateContext();
        
        // Update context periodically
        setInterval(() => this.updateContext(), 5000);
    }
    
    /**
     * Update context data from form fields
     */
    updateContext() {
        this.contextData = {
            title: this.getFieldValue('#title'),
            organization: this.getFieldValue('[name="organization"]'),
            max_amount: this.getFieldValue('[name="max_amount"]'),
            deadline: this.getFieldValue('[name="deadline"]'),
            content: this.getFieldValue('#content'),
            categories: this.getSelectedTaxonomy('grant_category'),
            prefectures: this.getSelectedTaxonomy('grant_prefecture')
        };
    }
    
    /**
     * Get field value safely
     */
    getFieldValue(selector) {
        const field = document.querySelector(selector);
        return field ? field.value.trim() : '';
    }
    
    /**
     * Get selected taxonomy values
     */
    getSelectedTaxonomy(taxonomy) {
        const checkboxes = document.querySelectorAll(`[name="tax_input[${taxonomy}][]"]:checked`);
        return Array.from(checkboxes).map(cb => cb.nextSibling ? cb.nextSibling.textContent.trim() : cb.value);
    }
    
    /**
     * Generate content for specific field
     */
    async generateForField(fieldName, fieldElement, mode = 'smart_fill') {
        if (this.isGenerating) return;
        
        this.setGenerating(true, fieldElement);
        
        try {
            const response = await this.callAI('gi_smart_generate', {
                existing_data: this.contextData,
                target_field: fieldName,
                mode: mode
            });
            
            if (response.success) {
                fieldElement.value = response.data.content;
                this.triggerFieldChange(fieldElement);
                this.generatedFields.add(fieldName);
                this.showSuccess(`${fieldName}を生成しました`);
            } else {
                throw new Error(response.data.message || 'Generation failed');
            }
            
        } catch (error) {
            this.showError(`生成エラー: ${error.message}`);
            
            // Use fallback if available
            if (error.fallback) {
                fieldElement.value = error.fallback;
                this.triggerFieldChange(fieldElement);
            }
        } finally {
            this.setGenerating(false, fieldElement);
        }
    }
    
    /**
     * Regenerate field with improvements
     */
    async regenerateField(fieldName, fieldElement, type = 'regenerate') {
        if (this.isGenerating) return;
        
        const currentContent = fieldElement.value.trim();
        if (!currentContent) {
            this.generateForField(fieldName, fieldElement);
            return;
        }
        
        this.setGenerating(true, fieldElement);
        
        try {
            const response = await this.callAI('gi_regenerate_content', {
                existing_data: this.contextData,
                target_field: fieldName,
                current_content: currentContent,
                type: type
            });
            
            if (response.success) {
                fieldElement.value = response.data.content;
                this.triggerFieldChange(fieldElement);
                this.showSuccess(`${fieldName}を${type === 'improve' ? '改善' : '再生成'}しました`);
            } else {
                throw new Error(response.data.message || 'Regeneration failed');
            }
            
        } catch (error) {
            this.showError(`再生成エラー: ${error.message}`);
        } finally {
            this.setGenerating(false, fieldElement);
        }
    }
    
    /**
     * Perform smart auto-fill for empty fields
     */
    async performSmartAutoFill() {
        if (this.isGenerating) return;
        
        const emptyFields = this.getEmptyFields();
        if (emptyFields.length === 0) {
            this.showInfo('すべてのフィールドに内容があります');
            return;
        }
        
        this.setGenerating(true);
        
        try {
            const response = await this.callAI('gi_contextual_fill', {
                existing_data: this.contextData,
                empty_fields: emptyFields
            });
            
            if (response.success) {
                let fillCount = 0;
                for (const [fieldName, content] of Object.entries(response.data.filled_fields)) {
                    const fieldElement = this.getFieldElement(fieldName);
                    if (fieldElement && content) {
                        fieldElement.value = content;
                        this.triggerFieldChange(fieldElement);
                        this.generatedFields.add(fieldName);
                        fillCount++;
                    }
                }
                
                this.showSuccess(`${fillCount}個のフィールドを自動入力しました`);
            } else {
                throw new Error(response.data.message || 'Auto-fill failed');
            }
            
        } catch (error) {
            this.showError(`自動入力エラー: ${error.message}`);
        } finally {
            this.setGenerating(false);
        }
    }
    
    /**
     * Regenerate all fields
     */
    async regenerateAllFields() {
        if (this.isGenerating) return;
        
        const fieldsToRegenerate = [
            'post_title', 'post_content', 'post_excerpt',
            'eligibility_criteria', 'application_process', 'required_documents'
        ];
        
        let processedCount = 0;
        
        for (const fieldName of fieldsToRegenerate) {
            const fieldElement = this.getFieldElement(fieldName);
            if (fieldElement) {
                try {
                    await this.generateForField(fieldName, fieldElement, 'professional');
                    processedCount++;
                    
                    // Small delay between generations
                    await this.sleep(1000);
                } catch (error) {
                    console.error(`Failed to regenerate ${fieldName}:`, error);
                }
            }
        }
        
        this.showSuccess(`${processedCount}個のフィールドを再生成しました`);
    }
    
    /**
     * Perform SEO optimization
     */
    async performSEOOptimization() {
        if (this.isGenerating) return;
        
        const seoFields = ['post_title', 'post_content', 'post_excerpt'];
        let optimizedCount = 0;
        
        for (const fieldName of seoFields) {
            const fieldElement = this.getFieldElement(fieldName);
            if (fieldElement && fieldElement.value.trim()) {
                try {
                    await this.regenerateField(fieldName, fieldElement, 'seo_optimize');
                    optimizedCount++;
                    
                    // Small delay between optimizations
                    await this.sleep(1000);
                } catch (error) {
                    console.error(`Failed to optimize ${fieldName}:`, error);
                }
            }
        }
        
        this.showSuccess(`${optimizedCount}個のフィールドをSEO最適化しました`);
    }
    
    /**
     * Get empty fields that can be filled
     */
    getEmptyFields() {
        const fieldMappings = {
            post_title: '#title',
            post_content: '#content',
            post_excerpt: '#excerpt',
            eligibility_criteria: '[name="eligibility_criteria"]',
            application_process: '[name="application_process"]',
            required_documents: '[name="required_documents"]'
        };
        
        const emptyFields = [];
        for (const [fieldName, selector] of Object.entries(fieldMappings)) {
            const element = document.querySelector(selector);
            if (element && !element.value.trim()) {
                emptyFields.push(fieldName);
            }
        }
        
        return emptyFields;
    }
    
    /**
     * Get field element by field name
     */
    getFieldElement(fieldName) {
        const fieldMappings = {
            post_title: '#title',
            post_content: '#content',
            post_excerpt: '#excerpt',
            eligibility_criteria: '[name="eligibility_criteria"]',
            application_process: '[name="application_process"]',
            required_documents: '[name="required_documents"]'
        };
        
        const selector = fieldMappings[fieldName];
        return selector ? document.querySelector(selector) : null;
    }
    
    /**
     * Call AI API
     */
    async callAI(action, data) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', gi_ajax.nonce);
        
        for (const [key, value] of Object.entries(data)) {
            if (typeof value === 'object') {
                formData.append(key, JSON.stringify(value));
            } else {
                formData.append(key, value);
            }
        }
        
        const response = await fetch(gi_ajax.ajax_url, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
    
    /**
     * Set generating state
     */
    setGenerating(isGenerating, specificElement = null) {
        this.isGenerating = isGenerating;
        
        // Update specific element or all AI buttons
        const buttons = specificElement 
            ? specificElement.parentNode.querySelectorAll('.gi-ai-button')
            : document.querySelectorAll('.gi-ai-button, .gi-ai-action-btn');
        
        buttons.forEach(button => {
            button.disabled = isGenerating;
            const spinner = button.querySelector('.gi-ai-spinner');
            const text = button.querySelector('.gi-ai-text');
            
            if (spinner && text) {
                spinner.style.display = isGenerating ? 'inline' : 'none';
                text.style.opacity = isGenerating ? '0.6' : '1';
            }
        });
        
        // Update panel
        const panel = document.getElementById('gi-global-ai-panel');
        if (panel) {
            panel.classList.toggle('gi-ai-generating', isGenerating);
        }
    }
    
    /**
     * Trigger field change event
     */
    triggerFieldChange(element) {
        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Update context after change
        setTimeout(() => this.updateContext(), 100);
    }
    
    /**
     * Show success message
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    /**
     * Show error message
     */
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    /**
     * Show info message
     */
    showInfo(message) {
        this.showNotification(message, 'info');
    }
    
    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `gi-ai-notification gi-ai-${type}`;
        notification.textContent = message;
        
        const colors = {
            success: { bg: '#d4edda', border: '#c3e6cb', text: '#155724' },
            error: { bg: '#f8d7da', border: '#f5c6cb', text: '#721c24' },
            info: { bg: '#d1ecf1', border: '#bee5eb', text: '#0c5460' }
        };
        
        const color = colors[type] || colors.info;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            background: ${color.bg};
            border: 1px solid ${color.border};
            color: ${color.text};
            border-radius: 4px;
            z-index: 10000;
            font-size: 14px;
            max-width: 300px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        `;
        
        document.body.appendChild(notification);
        
        // Remove after 4 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 4000);
    }
    
    /**
     * Check if auto-generate should run
     */
    shouldAutoGenerate() {
        return this.getAutoGenerateSetting() && this.hasMinimumContext();
    }
    
    /**
     * Check if there's minimum context for auto-generation
     */
    hasMinimumContext() {
        const title = this.getFieldValue('#title');
        const organization = this.getFieldValue('[name="organization"]');
        return title.length > 0 || organization.length > 0;
    }
    
    /**
     * Get auto-generate setting
     */
    getAutoGenerateSetting() {
        return localStorage.getItem('gi_auto_generate') === 'true';
    }
    
    /**
     * Set auto-generate setting
     */
    setAutoGenerateSetting(enabled) {
        localStorage.setItem('gi_auto_generate', enabled ? 'true' : 'false');
    }
    
    /**
     * Sleep utility
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Initialize when DOM is ready
if (typeof gi_ajax !== 'undefined') {
    new GI_EnhancedAIGenerator();
} else {
    console.warn('GI Enhanced AI Generator: gi_ajax not found. Make sure the script is loaded correctly.');
}