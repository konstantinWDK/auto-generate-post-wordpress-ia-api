<?php
/**
 * Scheduler class for automatic post generation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Auto_Post_Generator_Scheduler {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Cron hook name
     */
    private static $cron_hook = 'auto_generate_post_hook';
    
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
        add_action(self::$cron_hook, array($this, 'auto_generate_post'));
        add_action('init', array($this, 'schedule_posts'));
    }
    
    /**
     * Schedule automatic posts
     */
    public function schedule_posts() {
        if (Auto_Post_Generator_Settings::get_setting('auto_scheduling_enabled') !== 'yes') {
            return;
        }
        
        $frequency = Auto_Post_Generator_Settings::get_setting('posting_frequency', 'weekly');
        $time = Auto_Post_Generator_Settings::get_setting('posting_time', '09:00');
        $day = Auto_Post_Generator_Settings::get_setting('posting_day', 'monday');
        
        // Calculate next post time
        $next_post_time = $this->calculate_next_post_time($frequency, $time, $day);
        
        // Schedule WordPress event
        if (!wp_next_scheduled(self::$cron_hook)) {
            wp_schedule_event($next_post_time, $this->get_cron_interval($frequency), self::$cron_hook);
        }
    }
    
    /**
     * Calculate next post time
     */
    private function calculate_next_post_time($frequency, $time, $day) {
        $current_time = current_time('timestamp');
        
        switch ($frequency) {
            case 'daily':
                $next_time = strtotime('tomorrow ' . $time);
                break;
            case 'weekly':
                $next_time = strtotime('next ' . $day . ' ' . $time);
                break;
            case 'biweekly':
                $next_time = strtotime('+2 weeks ' . $day . ' ' . $time);
                break;
            case 'monthly':
                $next_time = strtotime('+1 month ' . $time);
                break;
            default:
                $next_time = strtotime('tomorrow ' . $time);
        }
        
        return $next_time;
    }
    
    /**
     * Get cron interval
     */
    private function get_cron_interval($frequency) {
        $intervals = array(
            'daily' => 'daily',
            'weekly' => 'weekly',
            'biweekly' => 'biweekly',
            'monthly' => 'monthly',
        );
        
        return isset($intervals[$frequency]) ? $intervals[$frequency] : 'daily';
    }
    
    /**
     * Auto generate post
     */
    public function auto_generate_post() {
        // First try to use saved ideas
        $available_ideas = get_posts(array(
            'post_type' => 'post_idea',
            'numberposts' => 1,
            'post_status' => 'publish',
            'orderby' => 'rand'
        ));
        
        if (!empty($available_ideas)) {
            // Use saved idea
            $idea = $available_ideas[0];
            $prompt = $idea->post_title;
            $keyword = get_post_meta($idea->ID, '_post_idea_keyword', true);
            
            $category_id = Auto_Post_Generator_Settings::get_setting('auto_post_category', 1);
            $tags = explode(',', Auto_Post_Generator_Settings::get_setting('auto_post_tags', ''));
            $post_status = Auto_Post_Generator_Settings::get_setting('auto_post_status', 'publish');
            $word_count = Auto_Post_Generator_Settings::get_setting('auto_post_word_count', '500');
            $ai_provider = Auto_Post_Generator_Settings::get_setting('ai_provider', 'openai');
            
            $result = Auto_Post_Generator_Post_Generator::generate_and_publish_post(
                $prompt,
                $category_id,
                $tags,
                $post_status,
                current_time('mysql'),
                $word_count,
                $ai_provider,
                array(),
                $keyword
            );
            
            // Optionally delete used idea
            if (Auto_Post_Generator_Settings::get_setting('auto_delete_used_ideas', 'no') === 'yes') {
                wp_delete_post($idea->ID, true);
            }
        } else {
            // Fallback to topic list
            $topics_list = Auto_Post_Generator_Settings::get_setting('auto_topics_list', '');
            $topics = array_filter(explode("\n", $topics_list));
            
            if (empty($topics)) {
                return;
            }
            
            // Get random topic
            $random_topic = $topics[array_rand($topics)];
            
            // Generate post
            $category_id = Auto_Post_Generator_Settings::get_setting('auto_post_category', 1);
            $tags = explode(',', Auto_Post_Generator_Settings::get_setting('auto_post_tags', ''));
            $post_status = Auto_Post_Generator_Settings::get_setting('auto_post_status', 'publish');
            $word_count = Auto_Post_Generator_Settings::get_setting('auto_post_word_count', '500');
            $ai_provider = Auto_Post_Generator_Settings::get_setting('ai_provider', 'openai');
            
            Auto_Post_Generator_Post_Generator::generate_and_publish_post(
                $random_topic,
                $category_id,
                $tags,
                $post_status,
                current_time('mysql'),
                $word_count,
                $ai_provider,
                array(),
                ''
            );
        }
    }
    
    /**
     * Schedule events (for activation hook)
     */
    public static function schedule_events() {
        // Add custom cron intervals
        add_filter('cron_schedules', array('Auto_Post_Generator_Scheduler', 'add_cron_intervals'));
        
        // Schedule initial event
        $instance = self::get_instance();
        $instance->schedule_posts();
    }
    
    /**
     * Clear scheduled events (for deactivation hook)
     */
    public static function clear_scheduled_events() {
        wp_clear_scheduled_hook(self::$cron_hook);
    }
    
    /**
     * Add custom cron intervals
     */
    public static function add_cron_intervals($schedules) {
        $schedules['weekly'] = array(
            'interval' => 604800, // 1 week
            'display' => __('Weekly', AUTO_POST_GENERATOR_TEXT_DOMAIN)
        );
        
        $schedules['biweekly'] = array(
            'interval' => 1209600, // 2 weeks
            'display' => __('Biweekly', AUTO_POST_GENERATOR_TEXT_DOMAIN)
        );
        
        $schedules['monthly'] = array(
            'interval' => 2635200, // 1 month (approximate)
            'display' => __('Monthly', AUTO_POST_GENERATOR_TEXT_DOMAIN)
        );
        
        return $schedules;
    }
}