<?php
/**
 * Admin AJAX class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Auto_Post_Generator_Admin_Ajax {
    
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
    }
    
    /**
     * Generate post ideas via AJAX
     */
    public function generate_post_ideas() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'auto_post_generator_nonce')) {
            wp_die(__('Security check failed', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Parse form data
        parse_str($_POST['form_data'], $form_data);
        
        $main_topic = sanitize_text_field($form_data['main_topic']);
        $ideas_count = absint($form_data['ideas_count']);
        $content_type = sanitize_text_field($form_data['content_type']);
        
        if (empty($main_topic)) {
            wp_send_json_error(array(
                'message' => __('Please enter a main topic.', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        }
        
        // Generate ideas
        $result = Auto_Post_Generator_Ideas_Generator::generate_post_ideas($main_topic, $ideas_count, $content_type);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => $result
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error generating ideas. Please check your API configuration.', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Get recent ideas via AJAX
     */
    public function get_recent_ideas() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'auto_post_generator_nonce')) {
            wp_die(__('Security check failed', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        $recent_ideas = get_posts(array(
            'post_type' => 'post_idea',
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
                        <th><?php _e('Idea', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Topic', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Actions', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_ideas as $idea): ?>
                        <?php
                        $topic = get_post_meta($idea->ID, '_post_idea_topic', true);
                        $keyword = get_post_meta($idea->ID, '_post_idea_keyword', true);
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($idea->post_title); ?></strong></td>
                            <td><?php echo esc_html($topic); ?></td>
                            <td><?php echo $keyword ? esc_html($keyword) : '<em>' . __('Not defined', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</em>'; ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=auto-post-generator&tab=create&idea_id=' . $idea->ID); ?>" class="button button-small">
                                    <?php _e('Generate Post', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                                </a>
                                <a href="<?php echo admin_url('edit.php?post_type=post_idea'); ?>" class="button button-small">
                                    <?php _e('View All', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
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
                'html' => '<p>' . __('No ideas found.', AUTO_POST_GENERATOR_TEXT_DOMAIN) . '</p>'
            ));
        }
    }
    
    /**
     * Validate API key via AJAX
     */
    public function validate_api_key() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'auto_post_generator_nonce')) {
            wp_die(__('Security check failed', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $provider = sanitize_text_field($_POST['provider']);
        
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => __('API key is required', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        }
        
        // Test API key
        $is_valid = $this->test_api_key($api_key, $provider);
        
        if ($is_valid) {
            wp_send_json_success(array(
                'message' => __('API key is valid', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('API key is invalid', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Save setting via AJAX
     */
    public function save_setting() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'auto_post_generator_nonce')) {
            wp_die(__('Security check failed', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        $setting = sanitize_text_field($_POST['setting']);
        $value = sanitize_text_field($_POST['value']);
        
        // Save setting
        $result = Auto_Post_Generator_Settings::update_setting($setting, $value);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Setting saved', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error saving setting', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Delete idea via AJAX
     */
    public function delete_idea() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'auto_post_generator_nonce')) {
            wp_die(__('Security check failed', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        $idea_id = absint($_POST['idea_id']);
        
        if (empty($idea_id)) {
            wp_send_json_error(array(
                'message' => __('Invalid idea ID', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        }
        
        // Delete idea
        $result = wp_delete_post($idea_id, true);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Idea deleted successfully', AUTO_POST_GENERATOR_TEXT_DOMAIN)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error deleting idea', AUTO_POST_GENERATOR_TEXT_DOMAIN)
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