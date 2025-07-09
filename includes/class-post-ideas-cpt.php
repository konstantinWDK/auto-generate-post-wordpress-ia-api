<?php
/**
 * Post Ideas Custom Post Type class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Auto_Post_Generator_Post_Ideas_CPT {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'));
        add_filter('manage_post_idea_posts_columns', array($this, 'custom_columns'));
        add_action('manage_post_idea_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        add_filter('manage_edit-post_idea_sortable_columns', array($this, 'sortable_columns'));
        add_filter('post_row_actions', array($this, 'row_actions'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'admin_filter'));
        add_action('pre_get_posts', array($this, 'filter_query'));
        add_filter('post_updated_messages', array($this, 'updated_messages'));
        add_action('admin_head', array($this, 'admin_styles'));
        add_filter('bulk_actions-edit-post_idea', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-post_idea', array($this, 'handle_bulk_actions'), 10, 3);
        add_action('admin_notices', array($this, 'bulk_action_admin_notice'));
        add_action('current_screen', array($this, 'add_ideas_dashboard_widget'));
        add_action('admin_action_delete_all_ideas', array($this, 'handle_delete_all_ideas'));
        add_action('views_edit-post_idea', array($this, 'add_delete_all_button'));
    }
    
    /**
     * Register post ideas custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name' => __('Post Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'singular_name' => __('Post Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'menu_name' => __('Post Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'name_admin_bar' => __('Post Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'archives' => __('Ideas Archive', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'attributes' => __('Idea Attributes', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'parent_item_colon' => __('Parent Idea:', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'all_items' => __('All Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'add_new_item' => __('Add New Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'add_new' => __('Add New', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'new_item' => __('New Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'edit_item' => __('Edit Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'update_item' => __('Update Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'view_item' => __('View Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'view_items' => __('View Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'search_items' => __('Search Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'not_found' => __('No ideas found', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'not_found_in_trash' => __('No ideas found in trash', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'featured_image' => __('Featured Image', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'set_featured_image' => __('Set featured image', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'remove_featured_image' => __('Remove featured image', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'use_featured_image' => __('Use as featured image', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'insert_into_item' => __('Insert into idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'uploaded_to_this_item' => __('Uploaded to this idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'items_list' => __('Ideas list', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'items_list_navigation' => __('Ideas list navigation', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'filter_items_list' => __('Filter ideas list', AUTO_POST_GENERATOR_TEXT_DOMAIN),
        );
        
        $args = array(
            'label' => __('Post Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'description' => __('Generated ideas for posts', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'custom-fields'),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'show_in_rest' => false,
            'menu_icon' => 'dashicons-lightbulb',
        );
        
        register_post_type('post_idea', $args);
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'post_idea_details',
            __('Idea Details', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            array($this, 'details_meta_box_callback'),
            'post_idea',
            'normal',
            'default'
        );
        
        add_meta_box(
            'post_idea_actions',
            __('Actions', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            array($this, 'actions_meta_box_callback'),
            'post_idea',
            'side',
            'default'
        );
    }
    
    /**
     * Details meta box callback
     */
    public function details_meta_box_callback($post) {
        wp_nonce_field('post_idea_meta_box', 'post_idea_meta_box_nonce');
        
        $topic = get_post_meta($post->ID, '_post_idea_topic', true);
        $keyword = get_post_meta($post->ID, '_post_idea_keyword', true);
        $content_type = get_post_meta($post->ID, '_post_idea_content_type', true);
        $generated_date = get_post_meta($post->ID, '_post_idea_generated_date', true);
        $source_article = get_post_meta($post->ID, '_post_idea_source_article', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="post_idea_topic"><?php _e('Original Topic:', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></label></th>
                <td><input type="text" id="post_idea_topic" name="post_idea_topic" value="<?php echo esc_attr($topic); ?>" style="width: 100%;" readonly /></td>
            </tr>
            <tr>
                <th><label for="post_idea_keyword"><?php _e('Keyword:', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></label></th>
                <td>
                    <input type="text" id="post_idea_keyword" name="post_idea_keyword" value="<?php echo esc_attr($keyword); ?>" style="width: 100%;" placeholder="<?php _e('e.g. digital marketing, SEO, WordPress', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>" />
                    <p class="description"><?php _e('Main keyword to focus content on', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="post_idea_content_type"><?php _e('Content Type:', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></label></th>
                <td><input type="text" id="post_idea_content_type" name="post_idea_content_type" value="<?php echo esc_attr($content_type); ?>" style="width: 100%;" readonly /></td>
            </tr>
            <tr>
                <th><label for="post_idea_generated_date"><?php _e('Generated Date:', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></label></th>
                <td><input type="text" id="post_idea_generated_date" name="post_idea_generated_date" value="<?php echo esc_attr($generated_date); ?>" style="width: 100%;" readonly /></td>
            </tr>
            <?php if ($source_article): ?>
            <tr>
                <th><label for="post_idea_source_article"><?php _e('Source Article:', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></label></th>
                <td><textarea id="post_idea_source_article" name="post_idea_source_article" rows="3" style="width: 100%;" readonly><?php echo esc_textarea($source_article); ?></textarea></td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    }
    
    /**
     * Actions meta box callback
     */
    public function actions_meta_box_callback($post) {
        ?>
        <div style="text-align: center; padding: 20px;">
            <a href="<?php echo admin_url('admin.php?page=auto-post-generator&tab=create&idea_id=' . $post->ID); ?>" class="button button-primary button-large" style="width: 100%; margin-bottom: 10px;">
                🚀 <?php _e('Generate Post with this Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
            </a>
            <p class="description"><?php _e('Create a complete post based on this idea', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></p>
        </div>
        <?php
    }
    
    /**
     * Save meta data
     */
    public function save_meta_data($post_id) {
        if (!isset($_POST['post_idea_meta_box_nonce']) || !wp_verify_nonce($_POST['post_idea_meta_box_nonce'], 'post_idea_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['post_idea_keyword'])) {
            update_post_meta($post_id, '_post_idea_keyword', sanitize_text_field($_POST['post_idea_keyword']));
        }
    }
    
    /**
     * Custom columns
     */
    public function custom_columns($columns) {
        $columns = array(
            'cb' => $columns['cb'],
            'title' => __('Idea Title', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'topic' => __('Original Topic', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'keyword' => __('Keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'content_type' => __('Content Type', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'generated_date' => __('Generated Date', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'actions' => __('Actions', AUTO_POST_GENERATOR_TEXT_DOMAIN),
        );
        return $columns;
    }
    
    /**
     * Custom column content
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'topic':
                echo esc_html(get_post_meta($post_id, '_post_idea_topic', true));
                break;
            case 'keyword':
                $keyword = get_post_meta($post_id, '_post_idea_keyword', true);
                echo $keyword ? esc_html($keyword) : '<span style="color: #999;">' . __('Not defined', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</span>';
                break;
            case 'content_type':
                echo esc_html(get_post_meta($post_id, '_post_idea_content_type', true));
                break;
            case 'generated_date':
                echo esc_html(get_post_meta($post_id, '_post_idea_generated_date', true));
                break;
            case 'actions':
                echo '<a href="' . admin_url('admin.php?page=auto-post-generator&tab=create&idea_id=' . $post_id) . '" class="button button-primary">' . __('Generate Post', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</a>';
                break;
        }
    }
    
    /**
     * Sortable columns
     */
    public function sortable_columns($columns) {
        $columns['topic'] = 'topic';
        $columns['keyword'] = 'keyword';
        $columns['content_type'] = 'content_type';
        $columns['generated_date'] = 'generated_date';
        return $columns;
    }
    
    /**
     * Row actions
     */
    public function row_actions($actions, $post) {
        if ($post->post_type === 'post_idea') {
            $generate_url = admin_url('admin.php?page=auto-post-generator&tab=create&idea_id=' . $post->ID);
            $actions['generate_post'] = '<a href="' . $generate_url . '" title="' . __('Generate post based on this idea', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '">' . __('Generate Post', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</a>';
        }
        return $actions;
    }
    
    /**
     * Admin filter
     */
    public function admin_filter() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'post_idea') {
            $used_filter = isset($_GET['used_filter']) ? $_GET['used_filter'] : '';
            $content_type_filter = isset($_GET['content_type_filter']) ? $_GET['content_type_filter'] : '';
            
            // Keyword filter
            echo '<select name="used_filter" id="used_filter">';
            echo '<option value="">' . __('All ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="with_keyword"' . selected($used_filter, 'with_keyword', false) . '>' . __('With keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="without_keyword"' . selected($used_filter, 'without_keyword', false) . '>' . __('Without keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '</select>';
            
            // Content type filter
            $content_types = array(
                'general' => __('General', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'tutorial' => __('Tutorial', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'list' => __('List', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'comparison' => __('Comparison', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'news' => __('News', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'related' => __('Related', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'expanded' => __('Expanded', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'alternative' => __('Alternative', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                'practical' => __('Practical', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            );
            
            echo '<select name="content_type_filter" id="content_type_filter">';
            echo '<option value="">' . __('All content types', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            foreach ($content_types as $type => $label) {
                echo '<option value="' . esc_attr($type) . '"' . selected($content_type_filter, $type, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            
            // Date range filter
            $date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
            echo '<select name="date_filter" id="date_filter">';
            echo '<option value="">' . __('All dates', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="today"' . selected($date_filter, 'today', false) . '>' . __('Today', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="yesterday"' . selected($date_filter, 'yesterday', false) . '>' . __('Yesterday', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="this_week"' . selected($date_filter, 'this_week', false) . '>' . __('This week', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="last_week"' . selected($date_filter, 'last_week', false) . '>' . __('Last week', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="this_month"' . selected($date_filter, 'this_month', false) . '>' . __('This month', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="last_month"' . selected($date_filter, 'last_month', false) . '>' . __('Last month', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '</select>';
        }
    }
    
    /**
     * Filter query
     */
    public function filter_query($query) {
        global $pagenow;
        
        if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'post_idea' && $query->is_main_query()) {
            $meta_query = array();
            $date_query = array();
            
            // Keyword filter
            if (isset($_GET['used_filter']) && $_GET['used_filter'] !== '') {
                if ($_GET['used_filter'] === 'with_keyword') {
                    $meta_query[] = array(
                        'key' => '_post_idea_keyword',
                        'value' => '',
                        'compare' => '!='
                    );
                } elseif ($_GET['used_filter'] === 'without_keyword') {
                    $meta_query[] = array(
                        'relation' => 'OR',
                        array(
                            'key' => '_post_idea_keyword',
                            'value' => '',
                            'compare' => '='
                        ),
                        array(
                            'key' => '_post_idea_keyword',
                            'compare' => 'NOT EXISTS'
                        )
                    );
                }
            }
            
            // Content type filter
            if (isset($_GET['content_type_filter']) && $_GET['content_type_filter'] !== '') {
                $meta_query[] = array(
                    'key' => '_post_idea_content_type',
                    'value' => sanitize_text_field($_GET['content_type_filter']),
                    'compare' => '='
                );
            }
            
            // Date filter
            if (isset($_GET['date_filter']) && $_GET['date_filter'] !== '') {
                $date_filter = $_GET['date_filter'];
                $current_time = current_time('timestamp');
                
                switch ($date_filter) {
                    case 'today':
                        $date_query[] = array(
                            'after' => date('Y-m-d 00:00:00', $current_time),
                            'before' => date('Y-m-d 23:59:59', $current_time),
                            'inclusive' => true,
                        );
                        break;
                    case 'yesterday':
                        $yesterday = $current_time - DAY_IN_SECONDS;
                        $date_query[] = array(
                            'after' => date('Y-m-d 00:00:00', $yesterday),
                            'before' => date('Y-m-d 23:59:59', $yesterday),
                            'inclusive' => true,
                        );
                        break;
                    case 'this_week':
                        $start_of_week = strtotime('last sunday', $current_time);
                        $date_query[] = array(
                            'after' => date('Y-m-d 00:00:00', $start_of_week),
                            'inclusive' => true,
                        );
                        break;
                    case 'last_week':
                        $start_of_last_week = strtotime('last sunday', $current_time) - WEEK_IN_SECONDS;
                        $end_of_last_week = $start_of_last_week + WEEK_IN_SECONDS;
                        $date_query[] = array(
                            'after' => date('Y-m-d 00:00:00', $start_of_last_week),
                            'before' => date('Y-m-d 23:59:59', $end_of_last_week),
                            'inclusive' => true,
                        );
                        break;
                    case 'this_month':
                        $date_query[] = array(
                            'after' => date('Y-m-01 00:00:00', $current_time),
                            'inclusive' => true,
                        );
                        break;
                    case 'last_month':
                        $first_day_last_month = strtotime('first day of last month', $current_time);
                        $last_day_last_month = strtotime('last day of last month', $current_time);
                        $date_query[] = array(
                            'after' => date('Y-m-d 00:00:00', $first_day_last_month),
                            'before' => date('Y-m-d 23:59:59', $last_day_last_month),
                            'inclusive' => true,
                        );
                        break;
                }
            }
            
            if (!empty($meta_query)) {
                if (count($meta_query) > 1) {
                    $meta_query['relation'] = 'AND';
                }
                $query->set('meta_query', $meta_query);
            }
            
            if (!empty($date_query)) {
                $query->set('date_query', $date_query);
            }
        }
    }
    
    /**
     * Updated messages
     */
    public function updated_messages($messages) {
        $messages['post_idea'] = array(
            0 => '',
            1 => __('Idea updated.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            2 => __('Custom field updated.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            3 => __('Custom field deleted.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            4 => __('Idea updated.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            5 => isset($_GET['revision']) ? __('Idea restored from revision', AUTO_POST_GENERATOR_TEXT_DOMAIN) : false,
            6 => __('Idea saved.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            7 => __('Idea saved.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            8 => __('Idea submitted.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            9 => __('Idea scheduled.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            10 => __('Idea draft updated.', AUTO_POST_GENERATOR_TEXT_DOMAIN),
        );
        return $messages;
    }
    
    /**
     * Admin styles
     */
    public function admin_styles() {
        $screen = get_current_screen();
        if ($screen && ($screen->post_type === 'post_idea' || $screen->id === 'toplevel_page_auto-post-generator')) {
            ?>
            <style>
            .post-type-post_idea .column-actions {
                width: 180px;
            }
            .post-type-post_idea .column-keyword {
                width: 150px;
            }
            .post-type-post_idea .column-content_type {
                width: 120px;
            }
            .post-type-post_idea .column-generated_date {
                width: 120px;
            }
            .recent-ideas-list {
                margin-top: 20px;
            }
            .recent-ideas-list table {
                margin-top: 10px;
            }
            .recent-ideas-list td {
                vertical-align: middle;
            }
            .button.button-small {
                font-size: 11px;
                height: auto;
                line-height: 1.5;
                padding: 2px 6px;
                margin-right: 3px;
            }
            .idea-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                align-items: center;
            }
            .idea-actions .button {
                white-space: nowrap;
                margin: 0;
            }
            .ideas-list table {
                margin-top: 20px;
            }
            .ideas-list .button-small {
                font-size: 11px;
                padding: 3px 8px;
                height: auto;
                line-height: 1.4;
            }
            .ideas-count {
                font-weight: bold;
                color: #2271b1;
            }
            #ideas-generator-form, #ideas-from-article-form {
                background: #f9f9f9;
                padding: 20px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .ideas-generator h3, .ideas-from-article h3 {
                margin-top: 0;
                color: #1d2327;
            }
            .apg-message {
                padding: 12px;
                margin: 20px 0;
                border-radius: 4px;
                border-left: 4px solid #0073aa;
            }
            .apg-message.success {
                background-color: #d4edda;
                border-left-color: #28a745;
                color: #155724;
            }
            .apg-message.error {
                background-color: #f8d7da;
                border-left-color: #dc3545;
                color: #721c24;
            }
            .delete-all-ideas {
                transition: all 0.3s ease;
                text-decoration: none;
                padding: 5px 10px;
                border-radius: 3px;
                border: 1px solid #d63638;
                background: #fff;
                display: inline-block;
            }
            .delete-all-ideas:hover {
                background-color: #d63638;
                color: #fff !important;
                text-decoration: none;
            }
            .bulk-actions select option[value="bulk_delete_ideas"] {
                color: #d63638;
                font-weight: bold;
            }
            .bulk-actions select option[value="generate_posts"] {
                color: #2271b1;
                font-weight: bold;
            }
            </style>
            <?php
        }
    }
    
    /**
     * Register bulk actions
     */
    public function register_bulk_actions($bulk_actions) {
        $bulk_actions['generate_posts'] = __('Generate Posts', AUTO_POST_GENERATOR_TEXT_DOMAIN);
        $bulk_actions['add_keywords'] = __('Add Keywords', AUTO_POST_GENERATOR_TEXT_DOMAIN);
        $bulk_actions['bulk_delete_ideas'] = __('Delete Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN);
        return $bulk_actions;
    }
    
    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'generate_posts' && $doaction !== 'add_keywords' && $doaction !== 'bulk_delete_ideas') {
            return $redirect_to;
        }
        
        if ($doaction === 'generate_posts') {
            $generated_count = 0;
            $failed_count = 0;
            
            foreach ($post_ids as $post_id) {
                $idea_post = get_post($post_id);
                if (!$idea_post || $idea_post->post_type !== 'post_idea') {
                    $failed_count++;
                    continue;
                }
                
                // Get idea details
                $idea_keyword = get_post_meta($post_id, '_post_idea_keyword', true);
                $prompt = $idea_post->post_title;
                
                // Use default settings
                $category_id = Auto_Post_Generator_Settings::get_setting('auto_post_category', 1);
                $tags = explode(',', Auto_Post_Generator_Settings::get_setting('auto_post_tags', ''));
                $post_status = Auto_Post_Generator_Settings::get_setting('auto_post_status', 'draft');
                $word_count = Auto_Post_Generator_Settings::get_setting('auto_post_word_count', '500');
                $ai_provider = Auto_Post_Generator_Settings::get_setting('ai_provider', 'openai');
                
                // Generate post
                $result = Auto_Post_Generator_Post_Generator::generate_and_publish_post(
                    $prompt,
                    $category_id,
                    $tags,
                    $post_status,
                    current_time('mysql'),
                    $word_count,
                    $ai_provider,
                    array(),
                    $idea_keyword,
                    ''
                );
                
                if ($result && !is_wp_error($result)) {
                    $generated_count++;
                } else {
                    $failed_count++;
                }
            }
            
            $redirect_to = add_query_arg(array(
                'bulk_generated' => $generated_count,
                'bulk_failed' => $failed_count
            ), $redirect_to);
        }
        
        if ($doaction === 'add_keywords') {
            $redirect_to = add_query_arg(array(
                'bulk_action' => 'add_keywords',
                'selected_ids' => implode(',', $post_ids)
            ), $redirect_to);
        }
        
        if ($doaction === 'bulk_delete_ideas') {
            $deleted_count = 0;
            $failed_count = 0;
            
            foreach ($post_ids as $post_id) {
                $result = wp_delete_post($post_id, true);
                if ($result) {
                    $deleted_count++;
                } else {
                    $failed_count++;
                }
            }
            
            $redirect_to = add_query_arg(array(
                'bulk_deleted' => $deleted_count,
                'bulk_delete_failed' => $failed_count
            ), $redirect_to);
        }
        
        return $redirect_to;
    }
    
    /**
     * Show bulk action admin notice
     */
    public function bulk_action_admin_notice() {
        if (!empty($_REQUEST['bulk_generated'])) {
            $generated = intval($_REQUEST['bulk_generated']);
            $failed = intval($_REQUEST['bulk_failed']);
            
            if ($generated > 0) {
                printf(
                    '<div class="notice notice-success is-dismissible"><p>' . 
                    _n('%d post generated successfully.', '%d posts generated successfully.', $generated, AUTO_POST_GENERATOR_TEXT_DOMAIN) . 
                    '</p></div>',
                    $generated
                );
            }
            
            if ($failed > 0) {
                printf(
                    '<div class="notice notice-error is-dismissible"><p>' . 
                    _n('%d post failed to generate.', '%d posts failed to generate.', $failed, AUTO_POST_GENERATOR_TEXT_DOMAIN) . 
                    '</p></div>',
                    $failed
                );
            }
        }
        
        if (!empty($_REQUEST['bulk_deleted'])) {
            $deleted = intval($_REQUEST['bulk_deleted']);
            $failed = intval($_REQUEST['bulk_delete_failed']);
            
            if ($deleted > 0) {
                printf(
                    '<div class="notice notice-success is-dismissible"><p>' . 
                    _n('%d idea deleted successfully.', '%d ideas deleted successfully.', $deleted, AUTO_POST_GENERATOR_TEXT_DOMAIN) . 
                    '</p></div>',
                    $deleted
                );
            }
            
            if ($failed > 0) {
                printf(
                    '<div class="notice notice-error is-dismissible"><p>' . 
                    _n('%d idea failed to delete.', '%d ideas failed to delete.', $failed, AUTO_POST_GENERATOR_TEXT_DOMAIN) . 
                    '</p></div>',
                    $failed
                );
            }
        }
        
        if (!empty($_REQUEST['bulk_action']) && $_REQUEST['bulk_action'] === 'add_keywords') {
            $selected_ids = sanitize_text_field($_REQUEST['selected_ids']);
            if ($selected_ids) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p>' . __('Add keywords to selected ideas:', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</p>';
                echo '<form method="post" style="display: inline-block;">';
                echo '<input type="hidden" name="idea_ids" value="' . esc_attr($selected_ids) . '">';
                echo '<input type="text" name="bulk_keyword" placeholder="' . __('Enter keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '" style="width: 300px;">';
                echo '<input type="submit" name="apply_keywords" value="' . __('Apply Keywords', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '" class="button button-primary">';
                echo '</form>';
                echo '</div>';
            }
        }
        
        // Handle keyword application
        if (!empty($_POST['apply_keywords']) && !empty($_POST['idea_ids']) && !empty($_POST['bulk_keyword'])) {
            $idea_ids = explode(',', sanitize_text_field($_POST['idea_ids']));
            $keyword = sanitize_text_field($_POST['bulk_keyword']);
            $updated_count = 0;
            
            foreach ($idea_ids as $idea_id) {
                $idea_id = intval($idea_id);
                if ($idea_id > 0) {
                    update_post_meta($idea_id, '_post_idea_keyword', $keyword);
                    $updated_count++;
                }
            }
            
            if ($updated_count > 0) {
                printf(
                    '<div class="notice notice-success is-dismissible"><p>' . 
                    _n('%d idea updated with keyword "%s".', '%d ideas updated with keyword "%s".', $updated_count, AUTO_POST_GENERATOR_TEXT_DOMAIN) . 
                    '</p></div>',
                    $updated_count,
                    esc_html($keyword)
                );
            }
        }
        
        // Handle delete all notification
        $this->show_delete_all_notice();
    }
    
    /**
     * Add dashboard widget to ideas page
     */
    public function add_ideas_dashboard_widget() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'post_idea' && $screen->base === 'edit') {
            add_action('all_admin_notices', array($this, 'display_ideas_stats'));
        }
    }
    
    /**
     * Display ideas statistics
     */
    public function display_ideas_stats() {
        $stats = $this->get_post_ideas_stats();
        ?>
        <div class="wrap">
            <div class="ideas-stats-dashboard" style="background: #fff; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #1d2327; display: flex; align-items: center;">
                    <span class="dashicons dashicons-lightbulb" style="margin-right: 10px; color: #2271b1;"></span>
                    <?php _e('Ideas Statistics', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                </h3>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px;">
                    <div class="stat-card" style="background: #f0f6fc; padding: 15px; border-radius: 6px; border-left: 4px solid #2271b1;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo $stats['total']; ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php _e('Total Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></div>
                    </div>
                    <div class="stat-card" style="background: #f0f9ff; padding: 15px; border-radius: 6px; border-left: 4px solid #0ea5e9;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #0ea5e9;"><?php echo $stats['with_keywords']; ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php _e('With Keywords', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></div>
                    </div>
                    <div class="stat-card" style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #f59e0b;"><?php echo $stats['without_keywords']; ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php _e('Without Keywords', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></div>
                    </div>
                    <div class="stat-card" style="background: #f0fdf4; padding: 15px; border-radius: 6px; border-left: 4px solid #22c55e;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #22c55e;"><?php echo $stats['this_week']; ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php _e('This Week', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></div>
                    </div>
                </div>
                <div class="quick-actions" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <strong style="color: #1d2327; margin-right: 15px;"><?php _e('Quick Actions:', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></strong>
                    <a href="<?php echo admin_url('admin.php?page=auto-post-generator&tab=ideas'); ?>" class="button button-primary">
                        ✨ <?php _e('Generate New Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                    </a>
                    <a href="<?php echo admin_url('edit.php?post_type=post_idea&used_filter=without_keyword'); ?>" class="button button-secondary">
                        🔑 <?php _e('Add Keywords', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=auto-post-generator&tab=create'); ?>" class="button button-secondary">
                        📝 <?php _e('Create Post', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get post ideas statistics
     */
    private function get_post_ideas_stats() {
        $total_ideas = wp_count_posts('post_idea');
        
        $ideas_with_keywords = get_posts(array(
            'post_type' => 'post_idea',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_post_idea_keyword',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'fields' => 'ids'
        ));
        
        $ideas_this_week = get_posts(array(
            'post_type' => 'post_idea',
            'numberposts' => -1,
            'date_query' => array(
                array(
                    'after' => date('Y-m-d 00:00:00', strtotime('last sunday')),
                    'inclusive' => true,
                )
            ),
            'fields' => 'ids'
        ));
        
        return array(
            'total' => $total_ideas->publish,
            'with_keywords' => count($ideas_with_keywords),
            'without_keywords' => $total_ideas->publish - count($ideas_with_keywords),
            'this_week' => count($ideas_this_week)
        );
    }
    
    /**
     * Add delete all button to views
     */
    public function add_delete_all_button($views) {
        $total_ideas = wp_count_posts('post_idea');
        if ($total_ideas->publish > 0) {
            $delete_all_url = wp_nonce_url(
                admin_url('admin.php?action=delete_all_ideas&post_type=post_idea'),
                'delete_all_ideas'
            );
            
            $views['delete_all'] = sprintf(
                '<a href="%s" class="delete-all-ideas" style="color: #d63638; font-weight: bold;" onclick="return confirm(\'%s\');">🗑️ %s (%d)</a>',
                $delete_all_url,
                esc_js(__('Are you sure you want to delete ALL ideas? This action cannot be undone.', AUTO_POST_GENERATOR_TEXT_DOMAIN)),
                __('Delete All Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN),
                $total_ideas->publish
            );
        }
        return $views;
    }
    
    /**
     * Handle delete all ideas
     */
    public function handle_delete_all_ideas() {
        // Check nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_all_ideas')) {
            wp_die(__('Security check failed', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Get all post ideas
        $ideas = get_posts(array(
            'post_type' => 'post_idea',
            'numberposts' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ));
        
        $deleted_count = 0;
        $failed_count = 0;
        
        foreach ($ideas as $idea_id) {
            $result = wp_delete_post($idea_id, true);
            if ($result) {
                $deleted_count++;
            } else {
                $failed_count++;
            }
        }
        
        // Redirect with results
        $redirect_url = add_query_arg(array(
            'post_type' => 'post_idea',
            'all_deleted' => $deleted_count,
            'all_delete_failed' => $failed_count
        ), admin_url('edit.php'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Show delete all admin notice
     */
    public function show_delete_all_notice() {
        if (!empty($_REQUEST['all_deleted'])) {
            $deleted = intval($_REQUEST['all_deleted']);
            $failed = intval($_REQUEST['all_delete_failed']);
            
            if ($deleted > 0) {
                printf(
                    '<div class="notice notice-success is-dismissible"><p>' . 
                    __('All %d ideas have been deleted successfully.', AUTO_POST_GENERATOR_TEXT_DOMAIN) . 
                    '</p></div>',
                    $deleted
                );
            }
            
            if ($failed > 0) {
                printf(
                    '<div class="notice notice-error is-dismissible"><p>' . 
                    __('%d ideas failed to delete.', AUTO_POST_GENERATOR_TEXT_DOMAIN) . 
                    '</p></div>',
                    $failed
                );
            }
        }
    }
    
    /**
     * Static method to register post type (for activation hook)
     */
    public static function register_post_type_static() {
        $instance = new self();
        $instance->register_post_type();
    }
}