<?php
/**
 * Post Ideas Custom Post Type class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Post_Ideas_CPT {
    
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
        add_filter('manage_miapg_post_idea_posts_columns', array($this, 'custom_columns'));
        add_action('manage_miapg_post_idea_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        add_filter('manage_edit-miapg_post_idea_sortable_columns', array($this, 'sortable_columns'));
        add_filter('post_row_actions', array($this, 'row_actions'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'admin_filter'));
        add_action('pre_get_posts', array($this, 'filter_query'));
        add_filter('post_updated_messages', array($this, 'updated_messages'));
        add_action('admin_head', array($this, 'admin_styles'));
        add_filter('bulk_actions-edit-miapg_post_idea', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-miapg_post_idea', array($this, 'handle_bulk_actions'), 10, 3);
        add_action('admin_notices', array($this, 'bulk_action_admin_notice'));
        add_action('current_screen', array($this, 'add_ideas_dashboard_widget'));
        add_action('admin_action_delete_all_ideas', array($this, 'handle_delete_all_ideas'));
        add_action('views_edit-miapg_post_idea', array($this, 'add_delete_all_button'));
    }
    
    /**
     * Register post ideas custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name' => __('Post Ideas', 'miapg-post-generator'),
            'singular_name' => __('Post Idea', 'miapg-post-generator'),
            'menu_name' => __('Post Ideas', 'miapg-post-generator'),
            'name_admin_bar' => __('Post Idea', 'miapg-post-generator'),
            'archives' => __('Ideas Archive', 'miapg-post-generator'),
            'attributes' => __('Idea Attributes', 'miapg-post-generator'),
            'parent_item_colon' => __('Parent Idea:', 'miapg-post-generator'),
            'all_items' => __('All Ideas', 'miapg-post-generator'),
            'add_new_item' => __('Add New Idea', 'miapg-post-generator'),
            'add_new' => __('Add New', 'miapg-post-generator'),
            'new_item' => __('New Idea', 'miapg-post-generator'),
            'edit_item' => __('Edit Idea', 'miapg-post-generator'),
            'update_item' => __('Update Idea', 'miapg-post-generator'),
            'view_item' => __('View Idea', 'miapg-post-generator'),
            'view_items' => __('View Ideas', 'miapg-post-generator'),
            'search_items' => __('Search Ideas', 'miapg-post-generator'),
            'not_found' => __('No ideas found', 'miapg-post-generator'),
            'not_found_in_trash' => __('No ideas found in trash', 'miapg-post-generator'),
            'featured_image' => __('Featured Image', 'miapg-post-generator'),
            'set_featured_image' => __('Set featured image', 'miapg-post-generator'),
            'remove_featured_image' => __('Remove featured image', 'miapg-post-generator'),
            'use_featured_image' => __('Use as featured image', 'miapg-post-generator'),
            'insert_into_item' => __('Insert into idea', 'miapg-post-generator'),
            'uploaded_to_this_item' => __('Uploaded to this idea', 'miapg-post-generator'),
            'items_list' => __('Ideas list', 'miapg-post-generator'),
            'items_list_navigation' => __('Ideas list navigation', 'miapg-post-generator'),
            'filter_items_list' => __('Filter ideas list', 'miapg-post-generator'),
        );
        
        $args = array(
            'label' => __('Post Ideas', 'miapg-post-generator'),
            'description' => __('Generated ideas for posts', 'miapg-post-generator'),
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
            'capability_type' => 'miapg_post_idea',
            'map_meta_cap' => true,
            'capabilities' => array(
                'edit_post' => 'edit_miapg_post_idea',
                'read_post' => 'read_miapg_post_idea',
                'delete_post' => 'delete_miapg_post_idea',
                'edit_posts' => 'edit_miapg_post_ideas',
                'edit_others_posts' => 'edit_others_miapg_post_ideas',
                'publish_posts' => 'publish_miapg_post_ideas',
                'read_private_posts' => 'read_private_miapg_post_ideas',
                'delete_posts' => 'delete_miapg_post_ideas',
                'delete_private_posts' => 'delete_private_miapg_post_ideas',
                'delete_published_posts' => 'delete_published_miapg_post_ideas',
                'delete_others_posts' => 'delete_others_miapg_post_ideas',
                'edit_private_posts' => 'edit_private_miapg_post_ideas',
                'edit_published_posts' => 'edit_published_miapg_post_ideas',
                'create_posts' => 'create_miapg_post_ideas',
            ),
            'show_in_rest' => false,
            'menu_icon' => 'dashicons-lightbulb',
        );
        
        register_post_type('miapg_post_idea', $args);
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'post_idea_details',
            __('Idea Details', 'miapg-post-generator'),
            array($this, 'details_meta_box_callback'),
            'miapg_post_idea',
            'normal',
            'default'
        );
        
        add_meta_box(
            'post_idea_actions',
            __('Actions', 'miapg-post-generator'),
            array($this, 'actions_meta_box_callback'),
            'miapg_post_idea',
            'side',
            'default'
        );
    }
    
    /**
     * Details meta box callback
     */
    public function details_meta_box_callback($post) {
        wp_nonce_field('post_idea_meta_box', 'post_idea_meta_box_nonce');
        
        $topic = get_post_meta($post->ID, '_miapg_idea_topic', true);
        $keyword = get_post_meta($post->ID, '_miapg_idea_keyword', true);
        $content_type = get_post_meta($post->ID, '_miapg_idea_content_type', true);
        $generated_date = get_post_meta($post->ID, '_miapg_idea_generated_date', true);
        $source_article = get_post_meta($post->ID, '_miapg_idea_source_article', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="post_idea_topic"><?php esc_html_e('Original Topic:', 'miapg-post-generator'); ?></label></th>
                <td><input type="text" id="post_idea_topic" name="post_idea_topic" value="<?php echo esc_attr(Miapg_Ideas_Generator::format_topic_text($topic)); ?>" style="width: 100%;" readonly /></td>
            </tr>
            <tr>
                <th><label for="post_idea_keyword"><?php esc_html_e('Keyword:', 'miapg-post-generator'); ?></label></th>
                <td>
                    <input type="text" id="post_idea_keyword" name="post_idea_keyword" value="<?php echo esc_attr($keyword); ?>" style="width: 100%;" placeholder="<?php esc_attr_e('e.g. digital marketing, SEO, WordPress', 'miapg-post-generator'); ?>" />
                    <p class="description"><?php esc_html_e('Main keyword to focus content on', 'miapg-post-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="post_idea_content_type"><?php esc_html_e('Content Type:', 'miapg-post-generator'); ?></label></th>
                <td><input type="text" id="post_idea_content_type" name="post_idea_content_type" value="<?php echo esc_attr($content_type); ?>" style="width: 100%;" readonly /></td>
            </tr>
            <tr>
                <th><label for="post_idea_generated_date"><?php esc_html_e('Generated Date:', 'miapg-post-generator'); ?></label></th>
                <td><input type="text" id="post_idea_generated_date" name="post_idea_generated_date" value="<?php echo esc_attr($generated_date); ?>" style="width: 100%;" readonly /></td>
            </tr>
            <?php if ($source_article): ?>
            <tr>
                <th><label for="post_idea_source_article"><?php esc_html_e('Source Article:', 'miapg-post-generator'); ?></label></th>
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
            <?php
            $create_url = wp_nonce_url(
                admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $post->ID),
                'create_from_idea_' . $post->ID
            );
            ?>
            <a href="<?php echo esc_url($create_url); ?>" class="button button-primary button-large" style="width: 100%; margin-bottom: 10px;">
                🚀 <?php esc_html_e('Generate Post with this Idea', 'miapg-post-generator'); ?>
            </a>
            <p class="description"><?php esc_html_e('Create a complete post based on this idea', 'miapg-post-generator'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Save meta data
     */
    public function save_meta_data($post_id) {
        if (!isset($_POST['post_idea_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['post_idea_meta_box_nonce'])), 'post_idea_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['post_idea_keyword'])) {
            update_post_meta($post_id, '_miapg_idea_keyword', sanitize_text_field(wp_unslash($_POST['post_idea_keyword'])));
        }
    }
    
    /**
     * Custom columns
     */
    public function custom_columns($columns) {
        $columns = array(
            'cb' => $columns['cb'],
            'title' => __('Idea Title', 'miapg-post-generator'),
            'topic' => __('Original Topic', 'miapg-post-generator'),
            'keyword' => __('Keyword', 'miapg-post-generator'),
            'content_type' => __('Content Type', 'miapg-post-generator'),
            'generated_date' => __('Generated Date', 'miapg-post-generator'),
            'actions' => __('Actions', 'miapg-post-generator'),
        );
        return $columns;
    }
    
    /**
     * Custom column content
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'topic':
                $topic = get_post_meta($post_id, '_miapg_idea_topic', true);
                echo esc_html(Miapg_Ideas_Generator::format_topic_text($topic));
                break;
            case 'keyword':
                $keyword = get_post_meta($post_id, '_miapg_idea_keyword', true);
                echo $keyword ? esc_html($keyword) : '<span style="color: #999;">' . esc_html__('Not defined', 'miapg-post-generator') . '</span>';
                break;
            case 'content_type':
                echo esc_html(get_post_meta($post_id, '_miapg_idea_content_type', true));
                break;
            case 'generated_date':
                echo esc_html(get_post_meta($post_id, '_miapg_idea_generated_date', true));
                break;
            case 'actions':
                $create_url = wp_nonce_url(
                    admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $post_id),
                    'create_from_idea_' . $post_id
                );
                echo '<a href="' . esc_url($create_url) . '" class="button button-primary">' . esc_html__('Generate Post', 'miapg-post-generator') . '</a>';
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
        if ($post->post_type === 'miapg_post_idea') {
            // Remove edit action
            unset($actions['edit']);
            unset($actions['inline hide-if-no-js']);
            
            $generate_url = wp_nonce_url(
                admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $post->ID),
                'create_from_idea_' . $post->ID
            );
            $actions['generate_post'] = '<a href="' . esc_url($generate_url) . '" title="' . esc_attr__('Generate post based on this idea', 'miapg-post-generator') . '">' . esc_html__('Generate Post', 'miapg-post-generator') . '</a>';
        }
        return $actions;
    }
    
    /**
     * Admin filter
     */
    public function admin_filter() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'miapg_post_idea') {
            // Admin list table filters - WordPress standard pattern
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $used_filter = isset($_GET['used_filter']) ? sanitize_text_field(wp_unslash($_GET['used_filter'])) : '';
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $content_type_filter = isset($_GET['content_type_filter']) ? sanitize_text_field(wp_unslash($_GET['content_type_filter'])) : '';
            
            // Keyword filter
            echo '<select name="used_filter" id="used_filter">';
            echo '<option value="">' . esc_html__('All ideas', 'miapg-post-generator') . '</option>';
            echo '<option value="with_keyword"' . selected($used_filter, 'with_keyword', false) . '>' . esc_html__('With keyword', 'miapg-post-generator') . '</option>';
            echo '<option value="without_keyword"' . selected($used_filter, 'without_keyword', false) . '>' . esc_html__('Without keyword', 'miapg-post-generator') . '</option>';
            echo '</select>';
            
            // Content type filter
            $content_types = array(
                'general' => __('General', 'miapg-post-generator'),
                'tutorial' => __('Tutorial', 'miapg-post-generator'),
                'list' => __('List', 'miapg-post-generator'),
                'comparison' => __('Comparison', 'miapg-post-generator'),
                'news' => __('News', 'miapg-post-generator'),
                'related' => __('Related', 'miapg-post-generator'),
                'expanded' => __('Expanded', 'miapg-post-generator'),
                'alternative' => __('Alternative', 'miapg-post-generator'),
                'practical' => __('Practical', 'miapg-post-generator'),
            );
            
            echo '<select name="content_type_filter" id="content_type_filter">';
            echo '<option value="">' . esc_html__('All content types', 'miapg-post-generator') . '</option>';
            foreach ($content_types as $type => $label) {
                echo '<option value="' . esc_attr($type) . '"' . selected($content_type_filter, $type, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            
            // Date range filter
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $date_filter = isset($_GET['date_filter']) ? sanitize_text_field(wp_unslash($_GET['date_filter'])) : '';
            echo '<select name="date_filter" id="date_filter">';
            echo '<option value="">' . esc_html__('All dates', 'miapg-post-generator') . '</option>';
            echo '<option value="today"' . selected($date_filter, 'today', false) . '>' . esc_html__('Today', 'miapg-post-generator') . '</option>';
            echo '<option value="yesterday"' . selected($date_filter, 'yesterday', false) . '>' . esc_html__('Yesterday', 'miapg-post-generator') . '</option>';
            echo '<option value="this_week"' . selected($date_filter, 'this_week', false) . '>' . esc_html__('This week', 'miapg-post-generator') . '</option>';
            echo '<option value="last_week"' . selected($date_filter, 'last_week', false) . '>' . esc_html__('Last week', 'miapg-post-generator') . '</option>';
            echo '<option value="this_month"' . selected($date_filter, 'this_month', false) . '>' . esc_html__('This month', 'miapg-post-generator') . '</option>';
            echo '<option value="last_month"' . selected($date_filter, 'last_month', false) . '>' . esc_html__('Last month', 'miapg-post-generator') . '</option>';
            echo '</select>';
        }
    }
    
    /**
     * Filter query
     */
    public function filter_query($query) {
        global $pagenow;
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ($pagenow === 'edit.php' && isset($_GET['post_type']) && sanitize_text_field(wp_unslash($_GET['post_type'])) === 'miapg_post_idea' && $query->is_main_query()) {
            $meta_query = array();
            $date_query = array();
            
            // Keyword filter
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['used_filter']) && sanitize_text_field(wp_unslash($_GET['used_filter'])) !== '') {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                if (sanitize_text_field(wp_unslash($_GET['used_filter'])) === 'with_keyword') {
                    $meta_query[] = array(
                        'key' => '_miapg_idea_keyword',
                        'value' => '',
                        'compare' => '!='
                    );
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                } elseif (sanitize_text_field(wp_unslash($_GET['used_filter'])) === 'without_keyword') {
                    $meta_query[] = array(
                        'relation' => 'OR',
                        array(
                            'key' => '_miapg_idea_keyword',
                            'value' => '',
                            'compare' => '='
                        ),
                        array(
                            'key' => '_miapg_idea_keyword',
                            'compare' => 'NOT EXISTS'
                        )
                    );
                }
            }
            
            // Content type filter
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['content_type_filter']) && sanitize_text_field(wp_unslash($_GET['content_type_filter'])) !== '') {
                $meta_query[] = array(
                    'key' => '_miapg_idea_content_type',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    'value' => sanitize_text_field(wp_unslash($_GET['content_type_filter'])),
                    'compare' => '='
                );
            }
            
            // Date filter
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['date_filter']) && sanitize_text_field(wp_unslash($_GET['date_filter'])) !== '') {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $date_filter = sanitize_text_field(wp_unslash($_GET['date_filter']));
                $current_time = current_time('timestamp');
                
                switch ($date_filter) {
                    case 'today':
                        $date_query[] = array(
                            'after' => gmdate('Y-m-d 00:00:00', $current_time),
                            'before' => gmdate('Y-m-d 23:59:59', $current_time),
                            'inclusive' => true,
                        );
                        break;
                    case 'yesterday':
                        $yesterday = $current_time - DAY_IN_SECONDS;
                        $date_query[] = array(
                            'after' => gmdate('Y-m-d 00:00:00', $yesterday),
                            'before' => gmdate('Y-m-d 23:59:59', $yesterday),
                            'inclusive' => true,
                        );
                        break;
                    case 'this_week':
                        $start_of_week = strtotime('last sunday', $current_time);
                        $date_query[] = array(
                            'after' => gmdate('Y-m-d 00:00:00', $start_of_week),
                            'inclusive' => true,
                        );
                        break;
                    case 'last_week':
                        $start_of_last_week = strtotime('last sunday', $current_time) - WEEK_IN_SECONDS;
                        $end_of_last_week = $start_of_last_week + WEEK_IN_SECONDS;
                        $date_query[] = array(
                            'after' => gmdate('Y-m-d 00:00:00', $start_of_last_week),
                            'before' => gmdate('Y-m-d 23:59:59', $end_of_last_week),
                            'inclusive' => true,
                        );
                        break;
                    case 'this_month':
                        $date_query[] = array(
                            'after' => gmdate('Y-m-01 00:00:00', $current_time),
                            'inclusive' => true,
                        );
                        break;
                    case 'last_month':
                        $first_day_last_month = strtotime('first day of last month', $current_time);
                        $last_day_last_month = strtotime('last day of last month', $current_time);
                        $date_query[] = array(
                            'after' => gmdate('Y-m-d 00:00:00', $first_day_last_month),
                            'before' => gmdate('Y-m-d 23:59:59', $last_day_last_month),
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
        $messages['miapg_post_idea'] = array(
            0 => '',
            1 => __('Idea updated.', 'miapg-post-generator'),
            2 => __('Custom field updated.', 'miapg-post-generator'),
            3 => __('Custom field deleted.', 'miapg-post-generator'),
            4 => __('Idea updated.', 'miapg-post-generator'),
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            5 => isset($_GET['revision']) ? __('Idea restored from revision', 'miapg-post-generator') : false,
            6 => __('Idea saved.', 'miapg-post-generator'),
            7 => __('Idea saved.', 'miapg-post-generator'),
            8 => __('Idea submitted.', 'miapg-post-generator'),
            9 => __('Idea scheduled.', 'miapg-post-generator'),
            10 => __('Idea draft updated.', 'miapg-post-generator'),
        );
        return $messages;
    }
    
    /**
     * Admin styles
     */
    public function admin_styles() {
        $screen = get_current_screen();
        if ($screen && ($screen->post_type === 'miapg_post_idea' || $screen->id === 'toplevel_page_miapg-post-generator')) {
            wp_enqueue_style(
                'miapg-post-ideas-cpt',
                MIAPG_PLUGIN_URL . 'assets/css/post-ideas-cpt.css',
                array(),
                MIAPG_VERSION
            );
        }
    }
    
    /**
     * Register bulk actions
     */
    public function register_bulk_actions($bulk_actions) {
        $bulk_actions['generate_posts'] = __('Generate Posts', 'miapg-post-generator');
        $bulk_actions['add_keywords'] = __('Add Keywords', 'miapg-post-generator');
        $bulk_actions['bulk_delete_ideas'] = __('Delete Ideas', 'miapg-post-generator');
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
                if (!$idea_post || $idea_post->post_type !== 'miapg_post_idea') {
                    $failed_count++;
                    continue;
                }
                
                // Get idea details
                $idea_keyword = get_post_meta($post_id, '_miapg_idea_keyword', true);
                $prompt = $idea_post->post_title;
                
                // Use default settings
                $category_id = Miapg_Settings::get_setting('miapg_post_category', 1);
                $tags = explode(',', Miapg_Settings::get_setting('miapg_post_tags', ''));
                $post_status = Miapg_Settings::get_setting('miapg_post_status', 'draft');
                $word_count = Miapg_Settings::get_setting('miapg_post_word_count', '500');
                $ai_provider = Miapg_Settings::get_setting('miapg_ai_provider', 'openai');
                
                // Generate post
                $result = Miapg_Post_Generator::generate_and_publish_post(
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
        // Check if user has proper permissions
        if (!current_user_can('edit_miapg_post_ideas')) {
            return;
        }
        
        // Check if we're on the correct post type screen
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'miapg_post_idea') {
            return;
        }
        
        // Handle bulk generation notices
        if (!empty($_REQUEST['bulk_generated'])) {
            $generated = intval($_REQUEST['bulk_generated']);
            $failed = isset($_REQUEST['bulk_failed']) ? intval($_REQUEST['bulk_failed']) : 0;
            
            // Validate that the numbers are reasonable (security check)
            if ($generated >= 0 && $failed >= 0 && ($generated + $failed) <= 1000) {
                if ($generated > 0) {
                    /* translators: %d: number of posts generated */
                    printf(
                        '<div class="notice notice-success is-dismissible"><p>' . 
                        // translators: %d is the number of posts generated
                        esc_html(_n('%d post generated successfully.', '%d posts generated successfully.', $generated, 'miapg-post-generator')) . 
                        '</p></div>',
                        esc_html($generated)
                    );
                }
                
                if ($failed > 0) {
                    /* translators: %d: number of posts that failed */
                    printf(
                        '<div class="notice notice-error is-dismissible"><p>' . 
                        // translators: %d is the number of posts that failed
                        esc_html(_n('%d post failed to generate.', '%d posts failed to generate.', $failed, 'miapg-post-generator')) . 
                        '</p></div>',
                        esc_html($failed)
                    );
                }
            }
        }
        
        // Handle bulk deletion notices
        if (!empty($_REQUEST['bulk_deleted'])) {
            $deleted = intval($_REQUEST['bulk_deleted']);
            $failed = isset($_REQUEST['bulk_delete_failed']) ? intval($_REQUEST['bulk_delete_failed']) : 0;
            
            // Validate that the numbers are reasonable (security check)
            if ($deleted >= 0 && $failed >= 0 && ($deleted + $failed) <= 1000) {
                if ($deleted > 0) {
                    /* translators: %d: number of ideas deleted */
                    printf(
                        '<div class="notice notice-success is-dismissible"><p>' . 
                        // translators: %d is the number of ideas deleted
                        esc_html(_n('%d idea deleted successfully.', '%d ideas deleted successfully.', $deleted, 'miapg-post-generator')) . 
                        '</p></div>',
                        esc_html($deleted)
                    );
                }
                
                if ($failed > 0) {
                    /* translators: %d: number of ideas that failed to delete */
                    printf(
                        '<div class="notice notice-error is-dismissible"><p>' . 
                        // translators: %d is the number of ideas that failed to delete
                        esc_html(_n('%d idea failed to delete.', '%d ideas failed to delete.', $failed, 'miapg-post-generator')) . 
                        '</p></div>',
                        esc_html($failed)
                    );
                }
            }
        }
        
        // Handle add keywords bulk action
        if (!empty($_REQUEST['bulk_action']) && sanitize_text_field(wp_unslash($_REQUEST['bulk_action'])) === 'add_keywords') {
            $selected_ids = isset($_REQUEST['selected_ids']) ? sanitize_text_field(wp_unslash($_REQUEST['selected_ids'])) : '';
            if ($selected_ids && preg_match('/^[\d,]+$/', $selected_ids)) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p>' . esc_html__('Add keywords to selected ideas:', 'miapg-post-generator') . '</p>';
                echo '<form method="post" style="display: inline-block;">';
                wp_nonce_field('bulk_keywords_action');
                echo '<input type="hidden" name="idea_ids" value="' . esc_attr($selected_ids) . '">';
                echo '<input type="text" name="bulk_keyword" placeholder="' . esc_attr__('Enter keyword', 'miapg-post-generator') . '" style="width: 300px;">';
                echo '<input type="submit" name="apply_keywords" value="' . esc_attr__('Apply Keywords', 'miapg-post-generator') . '" class="button button-primary">';
                echo '</form>';
                echo '</div>';
            }
        }
        
        // Handle keyword application
        if (!empty($_POST['apply_keywords']) && !empty($_POST['idea_ids']) && !empty($_POST['bulk_keyword'])) {
            // Verify nonce
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'bulk_keywords_action')) {
                wp_die(esc_html__('Security check failed', 'miapg-post-generator'));
            }
            $idea_ids = explode(',', sanitize_text_field(wp_unslash($_POST['idea_ids'])));
            $keyword = sanitize_text_field(wp_unslash($_POST['bulk_keyword']));
            $updated_count = 0;
            
            foreach ($idea_ids as $idea_id) {
                $idea_id = intval($idea_id);
                if ($idea_id > 0) {
                    update_post_meta($idea_id, '_miapg_idea_keyword', $keyword);
                    $updated_count++;
                }
            }
            
            if ($updated_count > 0) {
                /* translators: %1$d: number of ideas, %2$s: keyword */
                printf(
                    '<div class="notice notice-success is-dismissible"><p>' . 
                    // translators: %1$d is the number of ideas, %2$s is the keyword
                    esc_html(_n('%1$d idea updated with keyword "%2$s".', '%1$d ideas updated with keyword "%2$s".', $updated_count, 'miapg-post-generator')) . 
                    '</p></div>',
                    esc_html($updated_count),
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
        if ($screen && $screen->post_type === 'miapg_post_idea' && $screen->base === 'edit') {
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
                    <?php esc_html_e('Ideas Statistics', 'miapg-post-generator'); ?>
                </h3>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px;">
                    <div class="stat-card" style="background: #f0f6fc; padding: 15px; border-radius: 6px; border-left: 4px solid #2271b1;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo esc_html($stats['total']); ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php esc_html_e('Total Ideas', 'miapg-post-generator'); ?></div>
                    </div>
                    <div class="stat-card" style="background: #f0f9ff; padding: 15px; border-radius: 6px; border-left: 4px solid #0ea5e9;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #0ea5e9;"><?php echo esc_html($stats['with_keywords']); ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php esc_html_e('With Keywords', 'miapg-post-generator'); ?></div>
                    </div>
                    <div class="stat-card" style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #f59e0b;"><?php echo esc_html($stats['without_keywords']); ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php esc_html_e('Without Keywords', 'miapg-post-generator'); ?></div>
                    </div>
                    <div class="stat-card" style="background: #f0fdf4; padding: 15px; border-radius: 6px; border-left: 4px solid #22c55e;">
                        <div class="stat-number" style="font-size: 32px; font-weight: bold; color: #22c55e;"><?php echo esc_html($stats['this_week']); ?></div>
                        <div class="stat-label" style="color: #646970; font-size: 14px;"><?php esc_html_e('This Week', 'miapg-post-generator'); ?></div>
                    </div>
                </div>
                <div class="quick-actions" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <strong style="color: #1d2327; margin-right: 15px;"><?php esc_html_e('Quick Actions:', 'miapg-post-generator'); ?></strong>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=miapg-post-generator&tab=ideas')); ?>" class="button button-primary">
                        ✨ <?php esc_html_e('Generate New Ideas', 'miapg-post-generator'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=miapg_post_idea&used_filter=without_keyword')); ?>" class="button button-secondary">
                        🔑 <?php esc_html_e('Add Keywords', 'miapg-post-generator'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=miapg-post-generator&tab=create')); ?>" class="button button-secondary">
                        📝 <?php esc_html_e('Create Post', 'miapg-post-generator'); ?>
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
        // Check cache first
        $cache_key = 'miapg_ideas_cpt_stats';
        $cached_stats = wp_cache_get($cache_key, 'miapg_post_generator');
        
        if (false !== $cached_stats) {
            return $cached_stats;
        }
        
        $total_ideas = wp_count_posts('miapg_post_idea');
        
        // Get all published ideas and count those with keywords (compliant approach)
        $all_ideas = get_posts(array(
            'post_type' => 'miapg_post_idea',
            'post_status' => 'publish',
            'fields' => 'ids',
            'numberposts' => -1
        ));
        
        // Count ideas with non-empty keywords
        $with_keywords_count = 0;
        if (!empty($all_ideas)) {
            foreach ($all_ideas as $post_id) {
                $keyword = get_post_meta($post_id, '_miapg_idea_keyword', true);
                if (!empty($keyword)) {
                    $with_keywords_count++;
                }
            }
        }
        
        // Use WP_Query instead of direct database query for this week's ideas
        $last_sunday = gmdate('Y-m-d 00:00:00', strtotime('last sunday'));
        $ideas_this_week = new WP_Query(array(
            'post_type' => 'miapg_post_idea',
            'post_status' => 'publish',
            'date_query' => array(
                array(
                    'after' => $last_sunday,
                    'inclusive' => true
                )
            ),
            'fields' => 'ids',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        ));
        
        $stats = array(
            'total' => $total_ideas->publish,
            'with_keywords' => $with_keywords_count,
            'without_keywords' => $total_ideas->publish - $with_keywords_count,
            'this_week' => $ideas_this_week->found_posts
        );
        
        // Cache for 5 minutes
        wp_cache_set($cache_key, $stats, 'miapg_post_generator', 300);
        
        return $stats;
    }
    
    /**
     * Add delete all button to views
     */
    public function add_delete_all_button($views) {
        $total_ideas = wp_count_posts('miapg_post_idea');
        if ($total_ideas->publish > 0) {
            $delete_all_url = wp_nonce_url(
                admin_url('admin.php?action=delete_all_ideas&post_type=miapg_post_idea'),
                'delete_all_ideas'
            );
            
            $views['delete_all'] = sprintf(
                '<a href="%s" class="delete-all-ideas" style="color: #d63638; font-weight: bold;" onclick="return confirm(\'%s\');">🗑️ %s (%d)</a>',
                $delete_all_url,
                esc_js(__('Are you sure you want to delete ALL ideas? This action cannot be undone.', 'miapg-post-generator')),
                __('Delete All Ideas', 'miapg-post-generator'),
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
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'delete_all_ideas')) {
            wp_die(esc_html__('Security check failed', 'miapg-post-generator'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action', 'miapg-post-generator'));
        }
        
        // Get all post ideas
        $ideas = get_posts(array(
            'post_type' => 'miapg_post_idea',
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
        
        // Redirect with results and verification nonce
        $redirect_url = add_query_arg(array(
            'post_type' => 'miapg_post_idea',
            'all_deleted' => $deleted_count,
            'all_delete_failed' => $failed_count,
            '_notice_nonce' => wp_create_nonce('delete_all_notice')
        ), admin_url('edit.php'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Show delete all admin notice
     */
    public function show_delete_all_notice() {
        // Only show notice if we have the proper parameters and user has permissions
        if (!empty($_REQUEST['all_deleted']) && current_user_can('manage_options')) {
            // Verify the notice comes from a legitimate delete action
            if (!isset($_REQUEST['_notice_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_notice_nonce'])), 'delete_all_notice')) {
                return; // Silently ignore invalid notices
            }
            
            $deleted = intval($_REQUEST['all_deleted']);
            $failed = isset($_REQUEST['all_delete_failed']) ? intval($_REQUEST['all_delete_failed']) : 0;
            
            if ($deleted > 0) {
                printf(
                    '<div class="notice notice-success is-dismissible"><p>' . 
                    // translators: %d: number of ideas deleted
                    esc_html(__('All %d ideas have been deleted successfully.', 'miapg-post-generator')) . 
                    '</p></div>',
                    esc_html($deleted)
                );
            }
            
            if ($failed > 0) {
                printf(
                    '<div class="notice notice-error is-dismissible"><p>' . 
                    // translators: %d: number of ideas that failed to delete
                    esc_html(__('%d ideas failed to delete.', 'miapg-post-generator')) . 
                    '</p></div>',
                    esc_html($failed)
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