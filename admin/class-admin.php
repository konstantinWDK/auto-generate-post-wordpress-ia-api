<?php
/**
 * Admin class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Auto Post Generator', 'miapg-post-generator'),
            __('Auto Post Generator', 'miapg-post-generator'),
            'manage_options',
            'miapg-post-generator',
            array($this, 'admin_page_content'),
            'dashicons-edit-page',
            30
        );
        
        add_submenu_page(
            'miapg-post-generator',
            __('Settings', 'miapg-post-generator'),
            __('Settings', 'miapg-post-generator'),
            'manage_options',
            'miapg-post-generator',
            array($this, 'admin_page_content')
        );
        
        add_submenu_page(
            'miapg-post-generator',
            __('Manage Ideas', 'miapg-post-generator'),
            __('Ideas Manager', 'miapg-post-generator'),
            'edit_miapg_post_ideas',
            'miapg-ideas-manager',
            array($this, 'ideas_manager_page')
        );
        
        add_submenu_page(
            'miapg-post-generator',
            __('System Diagnostics', 'miapg-post-generator'),
            __('Diagnostics', 'miapg-post-generator'),
            'manage_options',
            'miapg-diagnostics',
            array($this, 'diagnostics_page')
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page_content() {
        // Load admin pages handler
        Miapg_Admin_Pages::get_instance()->render_main_page();
    }
    
    /**
     * Ideas manager page content
     */
    public function ideas_manager_page() {
        // Handle form submissions first
        $this->handle_ideas_manager_actions();
        
        // Get ideas
        $ideas = get_posts(array(
            'post_type' => 'miapg_post_idea',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Ideas Manager', 'miapg-post-generator'); ?></h1>
            
            <div class="miapg-ideas-manager">
                <!-- Danger Zone -->
                <div class="postbox" style="border: 2px solid #d63638; margin-bottom: 20px;">
                    <div class="postbox-header">
                        <h2 class="hndle" style="color: #d63638;">
                            ⚠️ <?php esc_html_e('Danger Zone', 'miapg-post-generator'); ?>
                        </h2>
                    </div>
                    <div class="inside">
                        <p style="color: #646970;">
                            <?php esc_html_e('This will permanently delete ALL ideas and related data from the database. This action cannot be undone.', 'miapg-post-generator'); ?>
                        </p>
                        <form method="post" onsubmit="return confirm('<?php echo esc_js(__('WARNING: This will delete ALL ideas and cannot be undone. Are you absolutely sure?', 'miapg-post-generator')); ?>');">
                            <?php wp_nonce_field('miapg_clear_ideas_data', 'clear_ideas_nonce'); ?>
                            <button type="submit" name="clear_all_ideas" class="button button-secondary" style="color: #d63638; border-color: #d63638;">
                                🗑️ <?php esc_html_e('Clear All Ideas Data', 'miapg-post-generator'); ?>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Ideas List -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle">
                            <?php printf(esc_html__('Saved Ideas (%d)', 'miapg-post-generator'), count($ideas)); ?>
                        </h2>
                    </div>
                    <div class="inside">
                        <?php if (!empty($ideas)): ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Title', 'miapg-post-generator'); ?></th>
                                        <th><?php esc_html_e('Topic', 'miapg-post-generator'); ?></th>
                                        <th><?php esc_html_e('Keyword', 'miapg-post-generator'); ?></th>
                                        <th><?php esc_html_e('Date', 'miapg-post-generator'); ?></th>
                                        <th><?php esc_html_e('Status', 'miapg-post-generator'); ?></th>
                                        <th><?php esc_html_e('Actions', 'miapg-post-generator'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ideas as $idea): ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($idea->post_title); ?></strong></td>
                                            <td><?php 
                                                $topic = get_post_meta($idea->ID, '_miapg_idea_topic', true);
                                                if ($topic) {
                                                    echo esc_html(Miapg_Ideas_Generator::format_topic_text($topic));
                                                } else {
                                                    echo esc_html__('Not defined', 'miapg-post-generator');
                                                }
                                            ?></td>
                                            <td>
                                                <input type="text" 
                                                       value="<?php echo esc_attr(get_post_meta($idea->ID, '_miapg_idea_keyword', true)); ?>" 
                                                       class="idea-keyword-input" 
                                                       data-idea-id="<?php echo esc_attr($idea->ID); ?>"
                                                       placeholder="<?php esc_attr_e('Add keyword...', 'miapg-post-generator'); ?>" />
                                                <button type="button" class="button button-small save-keyword-btn" data-idea-id="<?php echo esc_attr($idea->ID); ?>">
                                                    💾
                                                </button>
                                            </td>
                                            <td><?php echo esc_html(get_the_date('', $idea->ID)); ?></td>
                                            <td>
                                                <?php
                                                $status = get_post_meta($idea->ID, '_miapg_idea_status', true) ?: 'available';
                                                $status_colors = array(
                                                    'available' => '#00a32a',
                                                    'used' => '#646970',
                                                    'draft' => '#dba617'
                                                );
                                                $color = isset($status_colors[$status]) ? $status_colors[$status] : '#646970';
                                                ?>
                                                <span style="color: <?php echo esc_attr($color); ?>; font-weight: bold;">
                                                    <?php echo esc_html(ucfirst($status)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="row-actions">
                                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $idea->ID . '&action=edit')); ?>" 
                                                       class="button button-small">
                                                        📝 <?php esc_html_e('Edit', 'miapg-post-generator'); ?>
                                                    </a>
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $idea->ID)); ?>" 
                                                       class="button button-small button-primary">
                                                        🚀 <?php esc_html_e('Generate Post', 'miapg-post-generator'); ?>
                                                    </a>
                                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=miapg-ideas-manager&action=delete&idea_id=' . $idea->ID), 'delete_idea_' . $idea->ID)); ?>" 
                                                       class="button button-small button-link-delete"
                                                       onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this idea?', 'miapg-post-generator')); ?>')">
                                                        🗑️ <?php esc_html_e('Delete', 'miapg-post-generator'); ?>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p><?php esc_html_e('No ideas found. Create some ideas first.', 'miapg-post-generator'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.save-keyword-btn').click(function() {
                var ideaId = $(this).data('idea-id');
                var keyword = $(this).siblings('.idea-keyword-input').val();
                var button = $(this);
                
                $.ajax({
                    url: miapgAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_idea_keyword',
                        idea_id: ideaId,
                        keyword: keyword,
                        nonce: miapgAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            button.html('✅');
                            setTimeout(function() {
                                button.html('💾');
                            }, 2000);
                        } else {
                            alert('Error: ' + response.data.message);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Diagnostics page content
     */
    public function diagnostics_page() {
        // Handle test generation
        if (isset($_POST['test_generation']) && isset($_POST['diagnostics_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['diagnostics_nonce'])), 'miapg_diagnostics') &&
            current_user_can('manage_options')) {
            $this->run_generation_test();
        }
        
        // Handle fix parameters
        if (isset($_POST['fix_parameters']) && isset($_POST['diagnostics_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['diagnostics_nonce'])), 'miapg_diagnostics') &&
            current_user_can('manage_options')) {
            $this->fix_ai_parameters();
        }
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('System Diagnostics', 'miapg-post-generator'); ?></h1>
            
            <div class="miapg-diagnostics">
                <!-- System Status -->
                <div class="postbox" style="margin-bottom: 20px;">
                    <div class="postbox-header">
                        <h2 class="hndle">
                            🔍 <?php esc_html_e('System Status', 'miapg-post-generator'); ?>
                        </h2>
                    </div>
                    <div class="inside">
                        <?php $this->display_system_status(); ?>
                    </div>
                </div>
                
                <!-- Test Generation -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle">
                            🧪 <?php esc_html_e('Test Content Generation', 'miapg-post-generator'); ?>
                        </h2>
                    </div>
                    <div class="inside">
                        <p>
                            <?php esc_html_e('Test the AI content generation functionality with a simple test post.', 'miapg-post-generator'); ?>
                        </p>
                        <form method="post" style="display: inline;">
                            <?php wp_nonce_field('miapg_diagnostics', 'diagnostics_nonce'); ?>
                            <button type="submit" name="test_generation" class="button button-primary">
                                🚀 <?php esc_html_e('Run Generation Test', 'miapg-post-generator'); ?>
                            </button>
                        </form>
                        <form method="post" style="display: inline; margin-left: 10px;">
                            <?php wp_nonce_field('miapg_diagnostics', 'diagnostics_nonce'); ?>
                            <button type="submit" name="fix_parameters" class="button button-secondary">
                                🔧 <?php esc_html_e('Fix AI Parameters', 'miapg-post-generator'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display system status
     */
    private function display_system_status() {
        $ai_settings = Miapg_Settings::get_ai_provider_settings();
        $provider = Miapg_Settings::get_setting('miapg_ai_provider', 'openai');
        
        echo '<table class="wp-list-table widefat striped">';
        echo '<thead><tr><th>' . esc_html__('Component', 'miapg-post-generator') . '</th><th>' . esc_html__('Status', 'miapg-post-generator') . '</th><th>' . esc_html__('Details', 'miapg-post-generator') . '</th></tr></thead>';
        echo '<tbody>';
        
        // AI Provider
        $provider_status = !empty($ai_settings['api_key']) ? '✅ ' . __('Configured', 'miapg-post-generator') : '❌ ' . __('Not configured', 'miapg-post-generator');
        echo '<tr><td>' . esc_html__('AI Provider', 'miapg-post-generator') . '</td><td>' . $provider_status . '</td><td>' . esc_html(ucfirst($provider)) . '</td></tr>';
        
        // API Key
        $api_key_status = !empty($ai_settings['api_key']) ? '✅ ' . __('Present', 'miapg-post-generator') : '❌ ' . __('Missing', 'miapg-post-generator');
        $api_key_length = !empty($ai_settings['api_key']) ? strlen($ai_settings['api_key']) : 0;
        echo '<tr><td>' . esc_html__('API Key', 'miapg-post-generator') . '</td><td>' . $api_key_status . '</td><td>' . sprintf(esc_html__('%d characters', 'miapg-post-generator'), $api_key_length) . '</td></tr>';
        
        // Model
        $model = !empty($ai_settings['model']) ? $ai_settings['model'] : __('Not set', 'miapg-post-generator');
        echo '<tr><td>' . esc_html__('AI Model', 'miapg-post-generator') . '</td><td>ℹ️</td><td>' . esc_html($model) . '</td></tr>';
        
        // AI Parameters
        $ai_params = Miapg_Settings::get_ai_parameters();
        $params_valid = ($ai_params['top_p'] > 0 && $ai_params['top_p'] < 1.0) ? '✅' : '❌';
        $params_details = sprintf(
            'temp: %.1f, top_p: %.2f, max_tokens: %d',
            $ai_params['temperature'],
            $ai_params['top_p'],
            $ai_params['max_tokens']
        );
        echo '<tr><td>' . esc_html__('AI Parameters', 'miapg-post-generator') . '</td><td>' . $params_valid . '</td><td>' . esc_html($params_details) . '</td></tr>';
        
        // Custom Post Type Capabilities
        $has_capabilities = current_user_can('edit_miapg_post_ideas');
        $capabilities_status = $has_capabilities ? '✅ ' . __('Working', 'miapg-post-generator') : '❌ ' . __('Missing', 'miapg-post-generator');
        echo '<tr><td>' . esc_html__('Ideas Capabilities', 'miapg-post-generator') . '</td><td>' . $capabilities_status . '</td><td>' . ($has_capabilities ? __('User can edit ideas', 'miapg-post-generator') : __('User cannot edit ideas', 'miapg-post-generator')) . '</td></tr>';
        
        // Ideas Count
        $ideas_count = wp_count_posts('miapg_post_idea');
        $total_ideas = isset($ideas_count->publish) ? $ideas_count->publish : 0;
        echo '<tr><td>' . esc_html__('Saved Ideas', 'miapg-post-generator') . '</td><td>ℹ️</td><td>' . sprintf(esc_html__('%d ideas', 'miapg-post-generator'), $total_ideas) . '</td></tr>';
        
        // WordPress Version
        global $wp_version;
        echo '<tr><td>' . esc_html__('WordPress', 'miapg-post-generator') . '</td><td>ℹ️</td><td>' . esc_html($wp_version) . '</td></tr>';
        
        // PHP Version
        echo '<tr><td>' . esc_html__('PHP Version', 'miapg-post-generator') . '</td><td>ℹ️</td><td>' . esc_html(PHP_VERSION) . '</td></tr>';
        
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Run generation test
     */
    private function run_generation_test() {
        echo '<div class="notice notice-info"><p>' . esc_html__('Running generation test...', 'miapg-post-generator') . '</p></div>';
        
        $test_result = Miapg_Post_Generator::generate_and_publish_post(
            'Test: WordPress SEO Optimization Tips',
            1,
            array('test', 'seo'),
            'draft',
            current_time('mysql'),
            200,
            Miapg_Settings::get_setting('miapg_ai_provider', 'openai'),
            array(),
            'SEO',
            '',
            false
        );
        
        if (is_wp_error($test_result)) {
            echo '<div class="notice notice-error"><p><strong>' . esc_html__('Test Failed:', 'miapg-post-generator') . '</strong> ' . esc_html($test_result->get_error_message()) . '</p></div>';
        } elseif (strpos($test_result, 'Post created') !== false) {
            echo '<div class="notice notice-success"><p><strong>' . esc_html__('Test Successful:', 'miapg-post-generator') . '</strong> ' . esc_html($test_result) . '</p></div>';
        } else {
            echo '<div class="notice notice-warning"><p><strong>' . esc_html__('Test Result:', 'miapg-post-generator') . '</strong> ' . esc_html($test_result) . '</p></div>';
        }
    }
    
    /**
     * Fix AI parameters
     */
    private function fix_ai_parameters() {
        // Get current values
        $current_temp = floatval(Miapg_Settings::get_setting('miapg_ai_temperature', 0.7));
        $current_max_tokens = intval(Miapg_Settings::get_setting('miapg_ai_max_tokens', 2000));
        $current_top_p = floatval(Miapg_Settings::get_setting('miapg_ai_top_p', 0.95));
        $current_freq_penalty = floatval(Miapg_Settings::get_setting('miapg_ai_frequency_penalty', 0.0));
        $current_pres_penalty = floatval(Miapg_Settings::get_setting('miapg_ai_presence_penalty', 0.0));
        
        $fixed_params = array();
        
        // Fix temperature (0 to 2.0)
        if ($current_temp < 0 || $current_temp > 2.0) {
            $new_temp = max(0.0, min(2.0, $current_temp));
            Miapg_Settings::update_setting('miapg_ai_temperature', $new_temp);
            $fixed_params[] = sprintf('temperature: %.1f → %.1f', $current_temp, $new_temp);
        }
        
        // Fix max_tokens (1 to 4096)
        if ($current_max_tokens < 1 || $current_max_tokens > 4096) {
            $new_max_tokens = max(1, min(4096, $current_max_tokens));
            Miapg_Settings::update_setting('miapg_ai_max_tokens', $new_max_tokens);
            $fixed_params[] = sprintf('max_tokens: %d → %d', $current_max_tokens, $new_max_tokens);
        }
        
        // Fix top_p (0.01 to 0.99)
        if ($current_top_p <= 0 || $current_top_p >= 1.0) {
            $new_top_p = max(0.01, min(0.99, $current_top_p));
            if ($current_top_p >= 1.0) {
                $new_top_p = 0.95; // Use safe default
            }
            Miapg_Settings::update_setting('miapg_ai_top_p', $new_top_p);
            $fixed_params[] = sprintf('top_p: %.2f → %.2f', $current_top_p, $new_top_p);
        }
        
        // Fix frequency_penalty (-2.0 to 2.0)
        if ($current_freq_penalty < -2.0 || $current_freq_penalty > 2.0) {
            $new_freq_penalty = max(-2.0, min(2.0, $current_freq_penalty));
            Miapg_Settings::update_setting('miapg_ai_frequency_penalty', $new_freq_penalty);
            $fixed_params[] = sprintf('frequency_penalty: %.1f → %.1f', $current_freq_penalty, $new_freq_penalty);
        }
        
        // Fix presence_penalty (-2.0 to 2.0)
        if ($current_pres_penalty < -2.0 || $current_pres_penalty > 2.0) {
            $new_pres_penalty = max(-2.0, min(2.0, $current_pres_penalty));
            Miapg_Settings::update_setting('miapg_ai_presence_penalty', $new_pres_penalty);
            $fixed_params[] = sprintf('presence_penalty: %.1f → %.1f', $current_pres_penalty, $new_pres_penalty);
        }
        
        if (!empty($fixed_params)) {
            echo '<div class="notice notice-success"><p><strong>' . esc_html__('Parameters Fixed:', 'miapg-post-generator') . '</strong><br>' . esc_html(implode('<br>', $fixed_params)) . '</p></div>';
            
            // Log the corrections
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('MIAPG: AI parameters fixed: ' . implode(', ', $fixed_params));
            }
        } else {
            echo '<div class="notice notice-info"><p>' . esc_html__('All AI parameters are already within valid ranges.', 'miapg-post-generator') . '</p></div>';
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'miapg-post-generator') === false) {
            return;
        }
        
        wp_enqueue_style(
            'miapg-post-generator-admin',
            MIAPG_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MIAPG_VERSION
        );
        
        wp_enqueue_script(
            'miapg-post-generator-admin',
            MIAPG_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MIAPG_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'miapg-post-generator-admin',
            'miapgAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('miapg_nonce'),
                'strings' => array(
                    'confirm_delete' => __('Are you sure you want to delete this idea?', 'miapg-post-generator'),
                    'generating' => __('Generating...', 'miapg-post-generator'),
                    'error' => __('An error occurred. Please try again.', 'miapg-post-generator'),
                )
            )
        );
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        // Check for custom post type capabilities
        $current_user = wp_get_current_user();
        if (current_user_can('manage_options') && !current_user_can('edit_miapg_post_ideas')) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                    printf(
                        // translators: %s is the reset URL
                        wp_kses(__('MaestrIA Post Generator: Custom post type capabilities missing. <a href="%s">Click here to fix permissions</a>', 'miapg-post-generator'), array('a' => array('href' => array()))),
                        esc_url(wp_nonce_url(admin_url('admin.php?page=miapg-post-generator&miapg_action=reset_capabilities'), 'miapg_reset_capabilities'))
                    );
                    ?>
                </p>
            </div>
            <?php
        }
        
        // Check if API keys are configured
        $openai_key = Miapg_Settings::get_setting('miapg_openai_api_key');
        $deepseek_key = Miapg_Settings::get_setting('miapg_deepseek_api_key');
        $provider = Miapg_Settings::get_setting('miapg_ai_provider', 'openai');
        
        $show_notice = false;
        
        if ($provider === 'openai' && empty($openai_key)) {
            $show_notice = true;
        } elseif ($provider === 'deepseek' && empty($deepseek_key)) {
            $show_notice = true;
        }
        
        if ($show_notice) {
            $settings_url = admin_url('admin.php?page=miapg-post-generator&tab=general');
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php
                    printf(
                        // translators: %1$s is the API provider name, %2$s is the settings URL
                        wp_kses(__('Auto Post Generator: Please configure your %1$s API key in the <a href="%2$s">settings</a> to start generating content.', 'miapg-post-generator'), array('a' => array('href' => array()))),
                        esc_html(ucfirst($provider)),
                        esc_url($settings_url)
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        // Handle capabilities reset
        if (isset($_GET['miapg_action']) && $_GET['miapg_action'] === 'reset_capabilities' && 
            isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'miapg_reset_capabilities') &&
            current_user_can('manage_options')) {
            
            // Reset capabilities
            $this->reset_capabilities();
            
            // Redirect with success message
            wp_redirect(add_query_arg(array(
                'page' => 'miapg-post-generator',
                'miapg_message' => 'capabilities_reset'
            ), admin_url('admin.php')));
            exit;
        }
        
        // Show success message
        if (isset($_GET['miapg_message']) && $_GET['miapg_message'] === 'capabilities_reset') {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Custom post type capabilities have been reset successfully.', 'miapg-post-generator'); ?></p>
                </div>
                <?php
            });
        }
    }
    
    /**
     * Handle ideas manager actions
     */
    private function handle_ideas_manager_actions() {
        // Handle clear all ideas data
        if (isset($_POST['clear_all_ideas']) && isset($_POST['clear_ideas_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['clear_ideas_nonce'])), 'miapg_clear_ideas_data') &&
            current_user_can('manage_options')) {
            
            $this->clear_all_ideas_data();
            
            // Redirect with success message
            wp_redirect(add_query_arg(array(
                'page' => 'miapg-ideas-manager',
                'miapg_message' => 'ideas_cleared'
            ), admin_url('admin.php')));
            exit;
        }
        
        // Handle single idea deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && 
            isset($_GET['idea_id']) && isset($_GET['_wpnonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'delete_idea_' . absint($_GET['idea_id'])) &&
            current_user_can('delete_miapg_post_ideas')) {
            
            $idea_id = absint($_GET['idea_id']);
            
            // Get idea title before deletion
            $idea_post = get_post($idea_id);
            $idea_title = $idea_post ? $idea_post->post_title : '';
            
            if (wp_delete_post($idea_id, true)) {
                // Redirect with success message
                wp_redirect(add_query_arg(array(
                    'page' => 'miapg-ideas-manager',
                    'miapg_message' => 'idea_deleted',
                    'idea_title' => urlencode($idea_title)
                ), admin_url('admin.php')));
            } else {
                // Redirect with error message
                wp_redirect(add_query_arg(array(
                    'page' => 'miapg-ideas-manager',
                    'miapg_message' => 'delete_error'
                ), admin_url('admin.php')));
            }
            exit;
        }
        
        // Show messages based on URL parameters
        if (isset($_GET['miapg_message'])) {
            add_action('admin_notices', array($this, 'show_ideas_manager_notices'));
            add_action('admin_footer', array($this, 'clean_url_after_message'));
        }
    }
    
    /**
     * Show ideas manager notices
     */
    public function show_ideas_manager_notices() {
        if (!isset($_GET['miapg_message'])) {
            return;
        }
        
        $message_type = sanitize_text_field(wp_unslash($_GET['miapg_message']));
        
        switch ($message_type) {
            case 'idea_deleted':
                $idea_title = isset($_GET['idea_title']) ? sanitize_text_field(wp_unslash($_GET['idea_title'])) : '';
                $idea_title = urldecode($idea_title);
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Success!', 'miapg-post-generator'); ?></strong>
                        <?php 
                        if ($idea_title) {
                            printf(
                                // translators: %s is the idea title
                                esc_html__('The idea "%s" has been deleted successfully.', 'miapg-post-generator'),
                                esc_html($idea_title)
                            );
                        } else {
                            esc_html_e('The idea has been deleted successfully.', 'miapg-post-generator');
                        }
                        ?>
                    </p>
                </div>
                <?php
                break;
                
            case 'delete_error':
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Error!', 'miapg-post-generator'); ?></strong>
                        <?php esc_html_e('There was a problem deleting the idea. Please try again.', 'miapg-post-generator'); ?>
                    </p>
                </div>
                <?php
                break;
                
            case 'ideas_cleared':
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Success!', 'miapg-post-generator'); ?></strong>
                        <?php esc_html_e('All ideas data has been cleared successfully.', 'miapg-post-generator'); ?>
                    </p>
                </div>
                <?php
                break;
        }
    }
    
    /**
     * Clean URL after showing message
     */
    public function clean_url_after_message() {
        ?>
        <script>
        // Clean URL after showing the message
        if (window.location.href.indexOf('miapg_message=') > -1) {
            var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?page=miapg-ideas-manager';
            window.history.replaceState({}, document.title, cleanUrl);
        }
        </script>
        <?php
    }
    
    /**
     * Clear all ideas data from database
     */
    private function clear_all_ideas_data() {
        global $wpdb;
        
        // Get all idea posts
        $idea_posts = get_posts(array(
            'post_type' => 'miapg_post_idea',
            'numberposts' => -1,
            'post_status' => 'any'
        ));
        
        // Delete all idea posts and their metadata
        foreach ($idea_posts as $post) {
            wp_delete_post($post->ID, true);
        }
        
        // Clean up any orphaned metadata
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
            '_miapg_idea_%'
        ));
        
        // Clean up any orphaned post relationships
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->posts} WHERE post_type = %s",
            'miapg_post_idea'
        ));
        
        // Flush rewrite rules to regenerate custom post type structure
        flush_rewrite_rules();
        
        // Log the action
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('MIAPG: All ideas data cleared by user ' . get_current_user_id());
        }
    }
    
    /**
     * Reset custom capabilities
     */
    private function reset_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $capabilities = array(
                'edit_miapg_post_idea',
                'read_miapg_post_idea',
                'delete_miapg_post_idea',
                'edit_miapg_post_ideas',
                'edit_others_miapg_post_ideas',
                'publish_miapg_post_ideas',
                'read_private_miapg_post_ideas',
                'delete_miapg_post_ideas',
                'delete_private_miapg_post_ideas',
                'delete_published_miapg_post_ideas',
                'delete_others_miapg_post_ideas',
                'edit_private_miapg_post_ideas',
                'edit_published_miapg_post_ideas',
                'create_miapg_post_ideas',
            );
            
            foreach ($capabilities as $cap) {
                $role->add_cap($cap);
            }
        }
    }
}