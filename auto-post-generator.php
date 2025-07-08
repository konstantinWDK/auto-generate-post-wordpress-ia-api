<?php
/**
 * Plugin Name: Auto Post Generator
 * Plugin URI: https://webdesignerk.com
 * Description: Advanced AI-powered content generator with idea management system, article-based generation, category selection, and optimized HTML output for WordPress.
 * Version: 3.1
 * Author: konstantinWDK
 * Author URI: https://webdesignerk.com
 * Text Domain: auto-post-generator
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AUTO_POST_GENERATOR_VERSION', '3.1');
define('AUTO_POST_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AUTO_POST_GENERATOR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AUTO_POST_GENERATOR_PLUGIN_FILE', __FILE__);
define('AUTO_POST_GENERATOR_TEXT_DOMAIN', 'auto-post-generator');

/**
 * Main plugin class
 */
class Auto_Post_Generator {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
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
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('Auto_Post_Generator', 'uninstall'));
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            AUTO_POST_GENERATOR_TEXT_DOMAIN,
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize components
        $this->init_components();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core includes
        require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'includes/class-post-generator.php';
        require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'includes/class-ideas-generator.php';
        require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'includes/class-post-ideas-cpt.php';
        require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'includes/class-scheduler.php';
        require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'includes/class-settings.php';
        
        // Admin includes
        if (is_admin()) {
            require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'admin/class-admin.php';
            require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'admin/class-admin-pages.php';
            require_once AUTO_POST_GENERATOR_PLUGIN_PATH . 'admin/class-admin-ajax.php';
        }
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize core components
        Auto_Post_Generator_Post_Ideas_CPT::get_instance();
        Auto_Post_Generator_Settings::get_instance();
        Auto_Post_Generator_Scheduler::get_instance();
        
        // Initialize admin components
        if (is_admin()) {
            Auto_Post_Generator_Admin::get_instance();
            Auto_Post_Generator_Admin_Ajax::get_instance();
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check requirements
        if (!$this->check_requirements()) {
            return;
        }
        
        // Load dependencies first
        $this->load_dependencies();
        
        // Create database tables if needed
        $this->create_database_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Register custom post types
        if (class_exists('Auto_Post_Generator_Post_Ideas_CPT')) {
            Auto_Post_Generator_Post_Ideas_CPT::register_post_type_static();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Schedule cron events
        if (class_exists('Auto_Post_Generator_Scheduler')) {
            Auto_Post_Generator_Scheduler::schedule_events();
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Load dependencies first
        $this->load_dependencies();
        
        // Clear scheduled events
        if (class_exists('Auto_Post_Generator_Scheduler')) {
            Auto_Post_Generator_Scheduler::clear_scheduled_events();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove options
        self::remove_options();
        
        // Remove custom post types data
        self::remove_post_ideas_data();
        
        // Remove database tables
        self::remove_database_tables();
    }
    
    /**
     * Check plugin requirements
     */
    private function check_requirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            return false;
        }
        
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            return false;
        }
        
        // Check if required WordPress functions exist
        if (!function_exists('register_post_type') || !function_exists('wp_schedule_event')) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Create database tables
     */
    private function create_database_tables() {
        // Add custom tables if needed in future versions
        // Currently using WordPress post system
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        $default_options = array(
            'ai_provider' => 'openai',
            'writing_style' => 'informativo',
            'target_audience' => 'general',
            'tone' => 'profesional',
            'auto_post_word_count' => 500,
            'include_faq' => 'yes',
            'include_lists' => 'yes',
            'seo_focus' => 'medium',
            'title_max_length' => 60,
            'ai_temperature' => 0.7,
            'ai_max_tokens' => 2000,
            'ai_top_p' => 1.0,
            'ai_frequency_penalty' => 0.0,
            'ai_presence_penalty' => 0.0,
            'auto_scheduling_enabled' => 'no',
            'posting_frequency' => 'weekly',
            'posting_time' => '09:00',
            'posting_day' => 'monday',
            'auto_delete_used_ideas' => 'no',
        );
        
        foreach ($default_options as $option => $value) {
            if (false === get_option($option)) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Remove plugin options
     */
    private static function remove_options() {
        $options = array(
            'ai_provider',
            'openai_api_key',
            'deepseek_api_key',
            'openai_model',
            'deepseek_model',
            'writing_style',
            'target_audience',
            'tone',
            'auto_post_prompt',
            'auto_post_category',
            'auto_post_tags',
            'auto_post_status',
            'auto_post_word_count',
            'include_faq',
            'include_lists',
            'seo_focus',
            'title_max_length',
            'custom_instructions',
            'ai_temperature',
            'ai_max_tokens',
            'ai_top_p',
            'ai_frequency_penalty',
            'ai_presence_penalty',
            'auto_scheduling_enabled',
            'posting_frequency',
            'posting_time',
            'posting_day',
            'auto_topics_list',
            'auto_delete_used_ideas',
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
    }
    
    /**
     * Remove post ideas data
     */
    private static function remove_post_ideas_data() {
        global $wpdb;
        
        // Delete all post ideas
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'post_idea'");
        
        // Delete related meta data
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})");
    }
    
    /**
     * Remove database tables
     */
    private static function remove_database_tables() {
        // Remove custom tables if any exist in future versions
    }
}

// Initialize plugin
Auto_Post_Generator::get_instance();