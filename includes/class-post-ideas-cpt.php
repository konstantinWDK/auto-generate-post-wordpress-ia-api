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
            'label' => __('Post Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'description' => __('Generated ideas for posts', AUTO_POST_GENERATOR_TEXT_DOMAIN),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'custom-fields'),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'auto-post-generator',
            'menu_position' => 5,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'show_in_rest' => false,
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
            
            echo '<select name="used_filter" id="used_filter">';
            echo '<option value="">' . __('All ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="with_keyword"' . selected($used_filter, 'with_keyword', false) . '>' . __('With keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '<option value="without_keyword"' . selected($used_filter, 'without_keyword', false) . '>' . __('Without keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</option>';
            echo '</select>';
        }
    }
    
    /**
     * Filter query
     */
    public function filter_query($query) {
        global $pagenow;
        
        if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'post_idea') {
            if (isset($_GET['used_filter']) && $_GET['used_filter'] !== '') {
                $meta_query = array();
                
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
                
                if (!empty($meta_query)) {
                    $query->set('meta_query', $meta_query);
                }
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
                width: 120px;
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
            }
            </style>
            <?php
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