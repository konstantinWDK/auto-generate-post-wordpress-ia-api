<?php
/**
 * Admin AJAX class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Admin_Ajax {
    
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
        add_action('wp_ajax_generate_post_ideas', array($this, 'generate_post_ideas'));
        add_action('wp_ajax_get_recent_ideas', array($this, 'get_recent_ideas'));
        add_action('wp_ajax_validate_api_key', array($this, 'validate_api_key'));
        add_action('wp_ajax_save_setting', array($this, 'save_setting'));
        add_action('wp_ajax_delete_idea', array($this, 'delete_idea'));
        add_action('wp_ajax_bulk_ideas_action', array($this, 'bulk_ideas_action'));
    }
    
    /**
     * Generate post ideas via AJAX
     */
    public function generate_post_ideas() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'miapg_nonce')) {
            wp_die(__('Security check failed', MIAPG_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', MIAPG_TEXT_DOMAIN));
        }
        
        // Parse form data
        parse_str($_POST['form_data'], $form_data);
        
        $main_topic = sanitize_text_field($form_data['main_topic']);
        $ideas_count = absint($form_data['num_ideas']);
        $content_type = sanitize_text_field($form_data['content_type']);
        
        if (empty($main_topic)) {
            wp_send_json_error(array(
                'message' => __('Please enter a main topic.', MIAPG_TEXT_DOMAIN)
            ));
        }
        
        // Generate ideas
        $result = Miapg_Ideas_Generator::generate_post_ideas($main_topic, $ideas_count, $content_type);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => $result
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error generating ideas. Please check your API configuration.', MIAPG_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Get recent ideas via AJAX
     */
    public function get_recent_ideas() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'miapg_nonce')) {
            wp_die(__('Security check failed', MIAPG_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', MIAPG_TEXT_DOMAIN));
        }
        
        $recent_ideas = get_posts(array(
            'post_type' => 'miapg_post_idea',
            'numberposts' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if ($recent_ideas) {
            ob_start();
            ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Idea', MIAPG_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Topic', MIAPG_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Keyword', MIAPG_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Actions', MIAPG_TEXT_DOMAIN); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_ideas as $idea): ?>
                        <?php
                        $topic = get_post_meta($idea->ID, '_miapg_idea_topic', true);
                        $keyword = get_post_meta($idea->ID, '_miapg_idea_keyword', true);
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($idea->post_title); ?></strong></td>
                            <td><?php echo esc_html($topic); ?></td>
                            <td><?php echo $keyword ? esc_html($keyword) : '<em>' . __('Not defined', MIAPG_TEXT_DOMAIN) . '</em>'; ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $idea->ID); ?>" class="button button-small">
                                    <?php _e('Generate Post', MIAPG_TEXT_DOMAIN); ?>
                                </a>
                                <a href="<?php echo admin_url('edit.php?post_type=post_idea'); ?>" class="button button-small">
                                    <?php _e('View All', MIAPG_TEXT_DOMAIN); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $html = ob_get_clean();
            
            wp_send_json_success(array(
                'html' => $html
            ));
        } else {
            wp_send_json_success(array(
                'html' => '<p>' . __('No ideas found.', MIAPG_TEXT_DOMAIN) . '</p>'
            ));
        }
    }
    
    /**
     * Validate API key via AJAX
     */
    public function validate_api_key() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'miapg_nonce')) {
            wp_die(__('Security check failed', MIAPG_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', MIAPG_TEXT_DOMAIN));
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $provider = sanitize_text_field($_POST['provider']);
        
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => __('API key is required', MIAPG_TEXT_DOMAIN)
            ));
        }
        
        // Test API key
        $is_valid = $this->test_api_key($api_key, $provider);
        
        if ($is_valid) {
            wp_send_json_success(array(
                'message' => __('API key is valid', MIAPG_TEXT_DOMAIN)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('API key is invalid', MIAPG_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Save setting via AJAX
     */
    public function save_setting() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'miapg_nonce')) {
            wp_die(__('Security check failed', MIAPG_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', MIAPG_TEXT_DOMAIN));
        }
        
        $setting = sanitize_text_field($_POST['setting']);
        $value = sanitize_text_field($_POST['value']);
        
        // Save setting
        $result = Miapg_Settings::update_setting($setting, $value);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Setting saved', MIAPG_TEXT_DOMAIN)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error saving setting', MIAPG_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Delete idea via AJAX
     */
    public function delete_idea() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'miapg_nonce')) {
            wp_die(__('Security check failed', MIAPG_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', MIAPG_TEXT_DOMAIN));
        }
        
        $idea_id = absint($_POST['idea_id']);
        
        if (empty($idea_id)) {
            wp_send_json_error(array(
                'message' => __('Invalid idea ID', MIAPG_TEXT_DOMAIN)
            ));
        }
        
        // Get idea title for confirmation
        $idea_post = get_post($idea_id);
        if (!$idea_post || $idea_post->post_type !== 'miapg_post_idea') {
            wp_send_json_error(array(
                'message' => __('Idea not found', MIAPG_TEXT_DOMAIN)
            ));
        }
        
        // Delete idea
        $result = wp_delete_post($idea_id, true);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => sprintf(__('Idea "%s" deleted successfully', MIAPG_TEXT_DOMAIN), $idea_post->post_title)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error deleting idea', MIAPG_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Handle bulk ideas actions via AJAX
     */
    public function bulk_ideas_action() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'miapg_nonce')) {
            wp_die(__('Security check failed', MIAPG_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', MIAPG_TEXT_DOMAIN));
        }
        
        $bulk_action = sanitize_text_field($_POST['bulk_action']);
        $idea_ids = array_map('absint', $_POST['idea_ids']);
        
        if (empty($idea_ids)) {
            wp_send_json_error(array(
                'message' => __('No ideas selected', MIAPG_TEXT_DOMAIN)
            ));
        }
        
        $success_count = 0;
        $failed_count = 0;
        
        switch ($bulk_action) {
            case 'bulk_delete_selected':
                foreach ($idea_ids as $idea_id) {
                    $result = wp_delete_post($idea_id, true);
                    if ($result) {
                        $success_count++;
                    } else {
                        $failed_count++;
                    }
                }
                
                $message = sprintf(
                    _n('%d idea deleted successfully.', '%d ideas deleted successfully.', $success_count, MIAPG_TEXT_DOMAIN),
                    $success_count
                );
                break;
                
            case 'bulk_generate_posts':
                foreach ($idea_ids as $idea_id) {
                    $idea_post = get_post($idea_id);
                    if (!$idea_post || $idea_post->post_type !== 'miapg_post_idea') {
                        $failed_count++;
                        continue;
                    }
                    
                    // Get idea details
                    $idea_keyword = get_post_meta($idea_id, '_miapg_idea_keyword', true);
                    $prompt = $idea_post->post_title;
                    
                    // Use default settings
                    $category_id = Miapg_Settings::get_setting('auto_post_category', 1);
                    $tags = explode(',', Miapg_Settings::get_setting('auto_post_tags', ''));
                    $post_status = Miapg_Settings::get_setting('auto_post_status', 'draft');
                    $word_count = Miapg_Settings::get_setting('auto_post_word_count', '500');
                    $ai_provider = Miapg_Settings::get_setting('ai_provider', 'openai');
                    
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
                        $success_count++;
                    } else {
                        $failed_count++;
                    }
                }
                
                $message = sprintf(
                    _n('%d post generated successfully.', '%d posts generated successfully.', $success_count, MIAPG_TEXT_DOMAIN),
                    $success_count
                );
                break;
                
            case 'bulk_add_keyword':
                $keyword = sanitize_text_field($_POST['keyword']);
                if (empty($keyword)) {
                    wp_send_json_error(array(
                        'message' => __('Keyword is required', MIAPG_TEXT_DOMAIN)
                    ));
                }
                
                foreach ($idea_ids as $idea_id) {
                    $result = update_post_meta($idea_id, '_miapg_idea_keyword', $keyword);
                    if ($result !== false) {
                        $success_count++;
                    } else {
                        $failed_count++;
                    }
                }
                
                $message = sprintf(
                    _n('%d idea updated with keyword "%s".', '%d ideas updated with keyword "%s".', $success_count, MIAPG_TEXT_DOMAIN),
                    $success_count,
                    $keyword
                );
                break;
                
            default:
                wp_send_json_error(array(
                    'message' => __('Invalid bulk action', MIAPG_TEXT_DOMAIN)
                ));
        }
        
        if ($success_count > 0) {
            wp_send_json_success(array(
                'message' => $message,
                'success_count' => $success_count,
                'failed_count' => $failed_count
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('No actions completed successfully', MIAPG_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Test API key
     */
    private function test_api_key($api_key, $provider) {
        if ($provider === 'deepseek') {
            $endpoint = 'https://api.deepseek.com/v1/chat/completions';
            $model = 'deepseek-chat';
        } else {
            $endpoint = 'https://api.openai.com/v1/chat/completions';
            $model = 'gpt-3.5-turbo';
        }
        
        $data = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => 'Test'
                )
            ),
            'max_tokens' => 5,
            'temperature' => 0.1,
        );
        
        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        // Check if the response is valid
        if ($response_code === 200 && isset($result['choices'])) {
            return true;
        }
        
        return false;
    }
}