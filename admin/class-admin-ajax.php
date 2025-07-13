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
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'miapg_nonce')) {
            wp_die(esc_html(__('Security check failed', 'miapg-post-generator')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('Insufficient permissions', 'miapg-post-generator')));
        }
        
        // Parse form data
        $form_data_raw = isset($_POST['form_data']) ? sanitize_textarea_field(wp_unslash($_POST['form_data'])) : '';
        parse_str($form_data_raw, $form_data);
        
        $main_topic = isset($form_data['main_topic']) ? sanitize_text_field($form_data['main_topic']) : '';
        $ideas_count = isset($form_data['num_ideas']) ? absint($form_data['num_ideas']) : 5;
        $content_type = isset($form_data['content_type']) ? sanitize_text_field($form_data['content_type']) : 'general';
        
        if (empty($main_topic)) {
            wp_send_json_error(array(
                'message' => esc_html__('Please enter a main topic.', 'miapg-post-generator')
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
                'message' => esc_html__('Error generating ideas. Please check your API configuration.', 'miapg-post-generator')
            ));
        }
    }
    
    /**
     * Get recent ideas via AJAX
     */
    public function get_recent_ideas() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'miapg_nonce')) {
            wp_die(esc_html(__('Security check failed', 'miapg-post-generator')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('Insufficient permissions', 'miapg-post-generator')));
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
                        <th><?php esc_html_e('Idea', 'miapg-post-generator'); ?></th>
                        <th><?php esc_html_e('Topic', 'miapg-post-generator'); ?></th>
                        <th><?php esc_html_e('Keyword', 'miapg-post-generator'); ?></th>
                        <th><?php esc_html_e('Actions', 'miapg-post-generator'); ?></th>
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
                            <td><?php echo $keyword ? esc_html($keyword) : '<em>' . esc_html__('Not defined', 'miapg-post-generator') . '</em>'; ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $idea->ID)); ?>" class="button button-small">
                                    <?php esc_html_e('Generate Post', 'miapg-post-generator'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=post_idea')); ?>" class="button button-small">
                                    <?php esc_html_e('View All', 'miapg-post-generator'); ?>
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
                'html' => '<p>' . esc_html__('No ideas found.', 'miapg-post-generator') . '</p>'
            ));
        }
    }
    
    /**
     * Validate API key via AJAX
     */
    public function validate_api_key() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'miapg_nonce')) {
            wp_die(esc_html(__('Security check failed', 'miapg-post-generator')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('Insufficient permissions', 'miapg-post-generator')));
        }
        
        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        $provider = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
        
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => esc_html__('API key is required', 'miapg-post-generator')
            ));
        }
        
        // Test API key
        $is_valid = $this->test_api_key($api_key, $provider);
        
        if ($is_valid) {
            wp_send_json_success(array(
                'message' => esc_html__('API key is valid', 'miapg-post-generator')
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('API key is invalid', 'miapg-post-generator')
            ));
        }
    }
    
    /**
     * Save setting via AJAX
     */
    public function save_setting() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'miapg_nonce')) {
            wp_die(esc_html(__('Security check failed', 'miapg-post-generator')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('Insufficient permissions', 'miapg-post-generator')));
        }
        
        $setting = isset($_POST['setting']) ? sanitize_text_field(wp_unslash($_POST['setting'])) : '';
        $value = isset($_POST['value']) ? sanitize_text_field(wp_unslash($_POST['value'])) : '';
        
        // Save setting
        $result = Miapg_Settings::update_setting($setting, $value);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => esc_html__('Setting saved', 'miapg-post-generator')
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Error saving setting', 'miapg-post-generator')
            ));
        }
    }
    
    /**
     * Delete idea via AJAX
     */
    public function delete_idea() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'miapg_nonce')) {
            wp_die(esc_html(__('Security check failed', 'miapg-post-generator')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('Insufficient permissions', 'miapg-post-generator')));
        }
        
        $idea_id = isset($_POST['idea_id']) ? absint($_POST['idea_id']) : 0;
        
        if (empty($idea_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid idea ID', 'miapg-post-generator')
            ));
        }
        
        // Get idea title for confirmation
        $idea_post = get_post($idea_id);
        if (!$idea_post || $idea_post->post_type !== 'miapg_post_idea') {
            wp_send_json_error(array(
                'message' => esc_html__('Idea not found', 'miapg-post-generator')
            ));
        }
        
        // Delete idea
        $result = wp_delete_post($idea_id, true);
        
        if ($result) {
            wp_send_json_success(array(
                // translators: %s is the idea title
                'message' => sprintf(__('Idea "%s" deleted successfully', 'miapg-post-generator'), $idea_post->post_title)
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Error deleting idea', 'miapg-post-generator')
            ));
        }
    }
    
    /**
     * Handle bulk ideas actions via AJAX
     */
    public function bulk_ideas_action() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'miapg_nonce')) {
            wp_die(esc_html(__('Security check failed', 'miapg-post-generator')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('Insufficient permissions', 'miapg-post-generator')));
        }
        
        $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field(wp_unslash($_POST['bulk_action'])) : '';
        $idea_ids = isset($_POST['idea_ids']) ? array_map('absint', $_POST['idea_ids']) : array();
        
        if (empty($idea_ids)) {
            wp_send_json_error(array(
                'message' => esc_html__('No ideas selected', 'miapg-post-generator')
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
                    // translators: %d is the number of ideas deleted
                    _n('%d idea deleted successfully.', '%d ideas deleted successfully.', $success_count, 'miapg-post-generator'),
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
                    // translators: %d is the number of posts generated
                    _n('%d post generated successfully.', '%d posts generated successfully.', $success_count, 'miapg-post-generator'),
                    $success_count
                );
                break;
                
            case 'bulk_add_keyword':
                $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
                if (empty($keyword)) {
                    wp_send_json_error(array(
                        'message' => esc_html__('Keyword is required', 'miapg-post-generator')
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
                    // translators: %1$d is the number of ideas, %2$s is the keyword
                    _n('%1$d idea updated with keyword "%2$s".', '%1$d ideas updated with keyword "%2$s".', $success_count, 'miapg-post-generator'),
                    $success_count,
                    $keyword
                );
                break;
                
            default:
                wp_send_json_error(array(
                    'message' => esc_html__('Invalid bulk action', 'miapg-post-generator')
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
                'message' => esc_html__('No actions completed successfully', 'miapg-post-generator')
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