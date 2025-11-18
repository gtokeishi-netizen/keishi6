<?php
/**
 * WordPress - Google Sheets 連携 REST API エンドポイント
 * GAS（Google Apps Script）からのアクセス用カスタムエンドポイント
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

/**
 * カスタム REST API エンドポイントを追加
 */
class GI_GAS_Sync_Endpoints {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_endpoints'));
        add_filter('rest_grant_query', array($this, 'modify_grant_query'), 10, 2);
    }
    
    /**
     * REST API エンドポイントを登録
     */
    public function register_endpoints() {
        
        // 助成金投稿の拡張エンドポイント
        register_rest_route('wp/v2', '/grant', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_grants_with_meta'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'per_page' => array(
                    'default' => 100,
                    'sanitize_callback' => 'absint',
                ),
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ),
                'include_meta' => array(
                    'default' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'include_taxonomies' => array(
                    'default' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
            )
        ));
        
        // 助成金投稿の一括更新エンドポイント
        register_rest_route('wp/v2', '/grant/batch', array(
            'methods' => 'POST',
            'callback' => array($this, 'batch_update_grants'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'create' => array(
                    'type' => 'array',
                    'default' => array(),
                ),
                'update' => array(
                    'type' => 'array', 
                    'default' => array(),
                ),
                'delete' => array(
                    'type' => 'array',
                    'default' => array(),
                ),
            )
        ));
        
        // 同期状態確認エンドポイント
        register_rest_route('gi/v1', '/sync/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sync_status'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        // タクソノミー情報エンドポイント
        register_rest_route('gi/v1', '/taxonomies', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_taxonomies_info'),
            'permission_callback' => '__return_true', // 公開情報
        ));
    }
    
    /**
     * メタデータとタクソノミー付きで助成金投稿を取得
     */
    public function get_grants_with_meta($request) {
        $params = array(
            'post_type' => 'grant',
            'posts_per_page' => $request->get_param('per_page'),
            'paged' => $request->get_param('page'),
            'post_status' => array('publish', 'draft', 'private'),
            'meta_query' => array(),
        );
        
        $query = new WP_Query($params);
        $posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $post_data = array(
                    'id' => $post_id,
                    'title' => array(
                        'rendered' => get_the_title($post_id)
                    ),
                    'content' => array(
                        'rendered' => get_the_content()
                    ),
                    'excerpt' => array(
                        'rendered' => get_the_excerpt($post_id)
                    ),
                    'status' => get_post_status($post_id),
                    'date' => get_the_date('c', $post_id),
                    'modified' => get_the_modified_date('c', $post_id),
                );
                
                // メタデータを含める
                if ($request->get_param('include_meta')) {
                    $post_data['meta'] = $this->get_grant_meta($post_id);
                }
                
                // タクソノミーデータを含める
                if ($request->get_param('include_taxonomies')) {
                    $post_data['grant_category'] = $this->get_post_taxonomy_terms($post_id, 'grant_category');
                    $post_data['grant_prefecture'] = $this->get_post_taxonomy_terms($post_id, 'grant_prefecture');
                    $post_data['grant_tag'] = $this->get_post_taxonomy_terms($post_id, 'grant_tag');
                    $post_data['grant_municipality'] = $this->get_post_taxonomy_terms($post_id, 'grant_municipality');
                }
                
                $posts[] = $post_data;
            }
        }
        
        wp_reset_postdata();
        
        // ページネーション情報を追加
        $response_data = array(
            'posts' => $posts,
            'pagination' => array(
                'total' => $query->found_posts,
                'pages' => $query->max_num_pages,
                'current_page' => $request->get_param('page'),
                'per_page' => $request->get_param('per_page'),
            )
        );
        
        return new WP_REST_Response($response_data, 200);
    }
    
    /**
     * 助成金投稿の一括処理
     */
    public function batch_update_grants($request) {
        $results = array(
            'created' => array(),
            'updated' => array(),
            'deleted' => array(),
            'errors' => array(),
        );
        
        // 作成処理
        $create_data = $request->get_param('create');
        if (!empty($create_data) && is_array($create_data)) {
            foreach ($create_data as $post_data) {
                $result = $this->create_grant_post($post_data);
                if ($result['success']) {
                    $results['created'][] = $result['id'];
                } else {
                    $results['errors'][] = array(
                        'action' => 'create',
                        'data' => $post_data,
                        'error' => $result['error']
                    );
                }
            }
        }
        
        // 更新処理
        $update_data = $request->get_param('update');
        if (!empty($update_data) && is_array($update_data)) {
            foreach ($update_data as $post_data) {
                if (empty($post_data['id'])) {
                    $results['errors'][] = array(
                        'action' => 'update',
                        'error' => 'ID is required for update'
                    );
                    continue;
                }
                
                $result = $this->update_grant_post($post_data['id'], $post_data);
                if ($result['success']) {
                    $results['updated'][] = $post_data['id'];
                } else {
                    $results['errors'][] = array(
                        'action' => 'update',
                        'id' => $post_data['id'],
                        'error' => $result['error']
                    );
                }
            }
        }
        
        // 削除処理
        $delete_ids = $request->get_param('delete');
        if (!empty($delete_ids) && is_array($delete_ids)) {
            foreach ($delete_ids as $post_id) {
                $result = $this->delete_grant_post($post_id);
                if ($result['success']) {
                    $results['deleted'][] = $post_id;
                } else {
                    $results['errors'][] = array(
                        'action' => 'delete',
                        'id' => $post_id,
                        'error' => $result['error']
                    );
                }
            }
        }
        
        return new WP_REST_Response($results, 200);
    }
    
    /**
     * 同期状態を取得
     */
    public function get_sync_status($request) {
        $status = array(
            'last_sync' => get_option('gi_gas_last_sync', ''),
            'sync_count' => get_option('gi_gas_sync_count', 0),
            'error_count' => get_option('gi_gas_error_count', 0),
            'post_count' => wp_count_posts('grant'),
            'taxonomies' => array(
                'categories' => wp_count_terms('grant_category'),
                'prefectures' => wp_count_terms('grant_prefecture'),
                'tags' => wp_count_terms('grant_tag'),
                'municipalities' => wp_count_terms('grant_municipality'),
            ),
        );
        
        return new WP_REST_Response($status, 200);
    }
    
    /**
     * タクソノミー情報を取得
     */
    public function get_taxonomies_info($request) {
        $taxonomies = array(
            'grant_category' => array(
                'name' => 'grant_category',
                'label' => '助成金カテゴリー',
                'hierarchical' => true,
                'terms' => $this->get_taxonomy_terms_list('grant_category'),
            ),
            'grant_prefecture' => array(
                'name' => 'grant_prefecture',
                'label' => '対象都道府県',
                'hierarchical' => false,
                'terms' => $this->get_taxonomy_terms_list('grant_prefecture'),
            ),
            'grant_tag' => array(
                'name' => 'grant_tag',
                'label' => '助成金タグ',
                'hierarchical' => false,
                'terms' => $this->get_taxonomy_terms_list('grant_tag'),
            ),
            'grant_municipality' => array(
                'name' => 'grant_municipality',
                'label' => '対象市町村',
                'hierarchical' => true,
                'terms' => $this->get_taxonomy_terms_list('grant_municipality'),
            ),
        );
        
        return new WP_REST_Response($taxonomies, 200);
    }
    
    /**
     * 助成金投稿を作成
     */
    private function create_grant_post($post_data) {
        try {
            $post_args = array(
                'post_type' => 'grant',
                'post_title' => sanitize_text_field($post_data['title'] ?? ''),
                'post_content' => wp_kses_post($post_data['content'] ?? ''),
                'post_excerpt' => sanitize_text_field($post_data['excerpt'] ?? ''),
                'post_status' => sanitize_text_field($post_data['status'] ?? 'draft'),
                'meta_input' => array(),
            );
            
            $post_id = wp_insert_post($post_args);
            
            if (is_wp_error($post_id)) {
                return array(
                    'success' => false,
                    'error' => $post_id->get_error_message()
                );
            }
            
            // メタデータを保存
            if (!empty($post_data['meta']) && is_array($post_data['meta'])) {
                $this->save_grant_meta($post_id, $post_data['meta']);
            }
            
            // タクソノミーを設定
            $this->save_grant_taxonomies($post_id, $post_data);
            
            return array(
                'success' => true,
                'id' => $post_id
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * 助成金投稿を更新
     */
    private function update_grant_post($post_id, $post_data) {
        try {
            $post_args = array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($post_data['title'] ?? ''),
                'post_content' => wp_kses_post($post_data['content'] ?? ''),
                'post_excerpt' => sanitize_text_field($post_data['excerpt'] ?? ''),
                'post_status' => sanitize_text_field($post_data['status'] ?? 'draft'),
            );
            
            $result = wp_update_post($post_args);
            
            if (is_wp_error($result)) {
                return array(
                    'success' => false,
                    'error' => $result->get_error_message()
                );
            }
            
            // メタデータを保存
            if (!empty($post_data['meta']) && is_array($post_data['meta'])) {
                $this->save_grant_meta($post_id, $post_data['meta']);
            }
            
            // タクソノミーを設定
            $this->save_grant_taxonomies($post_id, $post_data);
            
            return array('success' => true);
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * 助成金投稿を削除
     */
    private function delete_grant_post($post_id) {
        try {
            $result = wp_delete_post($post_id, true);
            
            if ($result === false) {
                return array(
                    'success' => false,
                    'error' => 'Failed to delete post'
                );
            }
            
            return array('success' => true);
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * 助成金のメタデータを取得
     */
    private function get_grant_meta($post_id) {
        $meta_fields = array(
            'organization',
            'organization_type',
            'max_amount',
            'min_amount',
            'max_grant_amount',
            'subsidy_rate',
            'amount_notes',
            'application_deadline',
            'recruitment_start',
            'deadline_date',
            'deadline_notes',
            'application_status',
            'target_prefecture',
            'target_municipality',
            'regional_limitation',
            'region_notes',
            'grant_target',
            'eligible_expenses',
            'difficulty',
            'success_rate',
            'eligibility_requirements',
            'application_procedure',
            'application_method',
            'required_documents',
            'contact_info',
            'official_url',
            'is_featured',
        );
        
        $meta_data = array();
        
        foreach ($meta_fields as $field) {
            $value = get_post_meta($post_id, $field, true);
            if ($value !== '') {
                $meta_data[$field] = $value;
            }
        }
        
        return $meta_data;
    }
    
    /**
     * 助成金のメタデータを保存
     */
    private function save_grant_meta($post_id, $meta_data) {
        foreach ($meta_data as $key => $value) {
            update_post_meta($post_id, $key, sanitize_text_field($value));
        }
    }
    
    /**
     * 投稿のタクソノミータームを取得
     */
    private function get_post_taxonomy_terms($post_id, $taxonomy) {
        $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));
        
        if (is_wp_error($terms)) {
            return array();
        }
        
        return $terms;
    }
    
    /**
     * タクソノミータームのリストを取得
     */
    private function get_taxonomy_terms_list($taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        
        if (is_wp_error($terms)) {
            return array();
        }
        
        $term_list = array();
        foreach ($terms as $term) {
            $term_list[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $term->count,
            );
        }
        
        return $term_list;
    }
    
    /**
     * 助成金のタクソノミーを保存
     */
    private function save_grant_taxonomies($post_id, $post_data) {
        $taxonomies = array('grant_category', 'grant_prefecture', 'grant_tag', 'grant_municipality');
        
        foreach ($taxonomies as $taxonomy) {
            if (isset($post_data[$taxonomy]) && is_array($post_data[$taxonomy])) {
                $term_ids = array();
                
                foreach ($post_data[$taxonomy] as $term_name) {
                    if (empty($term_name)) continue;
                    
                    // タームが存在するかチェック
                    $term = get_term_by('name', $term_name, $taxonomy);
                    
                    if (!$term) {
                        // タームが存在しない場合は作成
                        $term_result = wp_insert_term($term_name, $taxonomy);
                        if (!is_wp_error($term_result)) {
                            $term_ids[] = $term_result['term_id'];
                        }
                    } else {
                        $term_ids[] = $term->term_id;
                    }
                }
                
                // タクソノミーを設定
                wp_set_post_terms($post_id, $term_ids, $taxonomy);
            }
        }
    }
    
    /**
     * 権限チェック
     */
    public function check_permissions() {
        return current_user_can('edit_posts');
    }
}

// インスタンス化
new GI_GAS_Sync_Endpoints();

/**
 * 同期ログを記録
 */
function gi_log_gas_sync($action, $details = array()) {
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'action' => $action,
        'details' => $details,
    );
    
    $logs = get_option('gi_gas_sync_logs', array());
    array_unshift($logs, $log_entry);
    
    // 最新100件のみ保持
    $logs = array_slice($logs, 0, 100);
    
    update_option('gi_gas_sync_logs', $logs);
}

/**
 * 最後の同期時刻を更新
 */
function gi_update_last_sync_time() {
    update_option('gi_gas_last_sync', current_time('mysql'));
    
    // 同期回数をインクリメント
    $count = get_option('gi_gas_sync_count', 0);
    update_option('gi_gas_sync_count', $count + 1);
}

/**
 * エラーカウントを更新
 */
function gi_increment_error_count() {
    $count = get_option('gi_gas_error_count', 0);
    update_option('gi_gas_error_count', $count + 1);
}