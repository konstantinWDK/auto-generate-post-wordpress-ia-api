<?php
/**
 * Plugin Name: MaestrIA post generator
 * Plugin URI: https://webdesignerk.com
 * Description: Advanced AI-powered content generator with idea management system, article-based generation, category selection, and optimized HTML output for WordPress.
 * Version: 3.1
 * Author: konstantinWDK
 * Author URI: https://webdesignerk.com
 * Text Domain: miapg-post-generator
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
define('MIAPG_VERSION', '3.1');
define('MIAPG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MIAPG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MIAPG_PLUGIN_FILE', __FILE__);
define('MIAPG_TEXT_DOMAIN', 'miapg-post-generator');

/**
 * Main plugin class
 */
class Miapg_Main {
    
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
        register_uninstall_hook(__FILE__, array('Miapg_Main', 'uninstall'));
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            MIAPG_TEXT_DOMAIN,
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
        require_once MIAPG_PLUGIN_PATH . 'includes/class-post-generator.php';
        require_once MIAPG_PLUGIN_PATH . 'includes/class-ideas-generator.php';
        require_once MIAPG_PLUGIN_PATH . 'includes/class-post-ideas-cpt.php';
        require_once MIAPG_PLUGIN_PATH . 'includes/class-scheduler.php';
        require_once MIAPG_PLUGIN_PATH . 'includes/class-settings.php';
        require_once MIAPG_PLUGIN_PATH . 'includes/class-translator.php';
        
        // Admin includes
        if (is_admin()) {
            require_once MIAPG_PLUGIN_PATH . 'admin/class-admin.php';
            require_once MIAPG_PLUGIN_PATH . 'admin/class-admin-pages.php';
            require_once MIAPG_PLUGIN_PATH . 'admin/class-admin-ajax.php';
        }
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize core components
        Miapg_Post_Ideas_CPT::get_instance();
        Miapg_Settings::get_instance();
        Miapg_Scheduler::get_instance();
        Miapg_Translator::get_instance();
        
        // Initialize admin components
        if (is_admin()) {
            Miapg_Admin::get_instance();
            Miapg_Admin_Ajax::get_instance();
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
        if (class_exists('Miapg_Post_Ideas_CPT')) {
            Miapg_Post_Ideas_CPT::register_post_type_static();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Schedule cron events
        if (class_exists('Miapg_Scheduler')) {
            Miapg_Scheduler::schedule_events();
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Load dependencies first
        $this->load_dependencies();
        
        // Clear scheduled events
        if (class_exists('Miapg_Scheduler')) {
            Miapg_Scheduler::clear_scheduled_events();
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
            'miapg_ai_provider' => 'openai',
            'miapg_writing_style' => 'informativo',
            'miapg_target_audience' => 'general',
            'miapg_tone' => 'profesional',
            'miapg_post_word_count' => 500,
            'miapg_include_faq' => 'yes',
            'miapg_include_lists' => 'yes',
            'miapg_seo_focus' => 'medium',
            'miapg_title_max_length' => 60,
            'miapg_ai_temperature' => 0.7,
            'miapg_ai_max_tokens' => 2000,
            'miapg_ai_top_p' => 1.0,
            'miapg_ai_frequency_penalty' => 0.0,
            'miapg_ai_presence_penalty' => 0.0,
            'miapg_scheduling_enabled' => 'no',
            'miapg_posting_frequency' => 'weekly',
            'miapg_posting_time' => '09:00',
            'miapg_posting_day' => 'monday',
            'miapg_delete_used_ideas' => 'no',
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
            'miapg_ai_provider',
            'miapg_openai_api_key',
            'miapg_deepseek_api_key',
            'miapg_openai_model',
            'miapg_deepseek_model',
            'miapg_writing_style',
            'miapg_target_audience',
            'miapg_tone',
            'miapg_post_prompt',
            'miapg_post_category',
            'miapg_post_tags',
            'miapg_post_status',
            'miapg_post_word_count',
            'miapg_include_faq',
            'miapg_include_lists',
            'miapg_seo_focus',
            'miapg_title_max_length',
            'miapg_custom_instructions',
            'miapg_ai_temperature',
            'miapg_ai_max_tokens',
            'miapg_ai_top_p',
            'miapg_ai_frequency_penalty',
            'miapg_ai_presence_penalty',
            'miapg_scheduling_enabled',
            'miapg_posting_frequency',
            'miapg_posting_time',
            'miapg_posting_day',
            'miapg_topics_list',
            'miapg_delete_used_ideas',
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
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'miapg_post_idea'");
        
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
Miapg_Main::get_instance();