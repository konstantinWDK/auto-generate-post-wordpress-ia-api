<?php
/**
 * Settings management class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Settings {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Settings groups
     */
    private $settings_groups = array(
        'general' => 'miapg_settings_group',
        'ai' => 'miapg_ai_settings_group',
        'content' => 'miapg_content_settings_group',
        'scheduling' => 'miapg_scheduling_settings_group',
    );
    
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
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // General settings
        $this->register_general_settings();
        
        // AI settings
        $this->register_ai_settings();
        
        // Content settings
        $this->register_content_settings();
        
        // Scheduling settings
        $this->register_scheduling_settings();
    }
    
    /**
     * Register general settings
     */
    private function register_general_settings() {
        $settings = array(
            'miapg_ai_provider' => 'sanitize_text_field',
            'miapg_openai_api_key' => 'sanitize_text_field',
            'miapg_deepseek_api_key' => 'sanitize_text_field',
            'miapg_post_category' => 'absint',
            'miapg_post_tags' => 'sanitize_text_field',
            'miapg_post_status' => 'sanitize_text_field',
            'miapg_interface_language' => 'sanitize_text_field',
        );
        
        foreach ($settings as $setting => $sanitize_callback) {
            register_setting($this->settings_groups['general'], $setting, $sanitize_callback);
        }
    }
    
    /**
     * Register AI settings
     */
    private function register_ai_settings() {
        $settings = array(
            'miapg_openai_model' => 'sanitize_text_field',
            'miapg_deepseek_model' => 'sanitize_text_field',
            'miapg_ai_temperature' => 'sanitize_text_field',
            'miapg_ai_max_tokens' => 'absint',
            'miapg_ai_top_p' => 'sanitize_text_field',
            'miapg_ai_frequency_penalty' => 'sanitize_text_field',
            'miapg_ai_presence_penalty' => 'sanitize_text_field',
        );
        
        foreach ($settings as $setting => $sanitize_callback) {
            register_setting($this->settings_groups['ai'], $setting, $sanitize_callback);
        }
    }
    
    /**
     * Register content settings
     */
    private function register_content_settings() {
        $settings = array(
            'miapg_post_prompt' => 'sanitize_textarea_field',
            'miapg_post_word_count' => 'absint',
            'miapg_writing_style' => 'sanitize_text_field',
            'miapg_target_audience' => 'sanitize_text_field',
            'miapg_tone' => 'sanitize_text_field',
            'miapg_include_faq' => 'sanitize_text_field',
            'miapg_include_lists' => 'sanitize_text_field',
            'miapg_seo_focus' => 'sanitize_text_field',
            'miapg_title_max_length' => 'absint',
            'miapg_custom_instructions' => 'sanitize_textarea_field',
        );
        
        foreach ($settings as $setting => $sanitize_callback) {
            register_setting($this->settings_groups['content'], $setting, $sanitize_callback);
        }
    }
    
    /**
     * Register scheduling settings
     */
    private function register_scheduling_settings() {
        $settings = array(
            'miapg_scheduling_enabled' => 'sanitize_text_field',
            'miapg_posting_frequency' => 'sanitize_text_field',
            'miapg_posting_time' => 'sanitize_text_field',
            'miapg_posting_day' => 'sanitize_text_field',
            'miapg_topics_list' => 'sanitize_textarea_field',
            'miapg_delete_used_ideas' => 'sanitize_text_field',
        );
        
        foreach ($settings as $setting => $sanitize_callback) {
            register_setting($this->settings_groups['scheduling'], $setting, $sanitize_callback);
        }
    }
    
    /**
     * Get setting value
     */
    public static function get_setting($setting, $default = '') {
        return get_option($setting, $default);
    }
    
    /**
     * Update setting value
     */
    public static function update_setting($setting, $value) {
        return update_option($setting, $value);
    }
    
    /**
     * Get all AI provider settings
     */
    public static function get_ai_provider_settings() {
        $provider = self::get_setting('miapg_ai_provider', 'openai');
        
        if ($provider === 'deepseek') {
            return array(
                'api_key' => self::get_setting('miapg_deepseek_api_key'),
                'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
                'model' => self::get_setting('miapg_deepseek_model', 'deepseek-chat'),
            );
        } else {
            return array(
                'api_key' => self::get_setting('miapg_openai_api_key'),
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
                'model' => self::get_setting('miapg_openai_model', 'gpt-4'),
            );
        }
    }
    
    /**
     * Get content generation settings
     */
    public static function get_content_settings() {
        return array(
            'writing_style' => self::get_setting('miapg_writing_style', 'informativo'),
            'target_audience' => self::get_setting('miapg_target_audience', 'general'),
            'tone' => self::get_setting('miapg_tone', 'profesional'),
            'include_faq' => self::get_setting('miapg_include_faq', 'yes'),
            'include_lists' => self::get_setting('miapg_include_lists', 'yes'),
            'seo_focus' => self::get_setting('miapg_seo_focus', 'medium'),
            'custom_instructions' => self::get_setting('miapg_custom_instructions', ''),
        );
    }
    
    /**
     * Get AI parameters
     */
    public static function get_ai_parameters() {
        // Get raw values
        $temperature = floatval(self::get_setting('miapg_ai_temperature', 0.7));
        $max_tokens = intval(self::get_setting('miapg_ai_max_tokens', 2000));
        $top_p = floatval(self::get_setting('miapg_ai_top_p', 0.95));
        $frequency_penalty = floatval(self::get_setting('miapg_ai_frequency_penalty', 0.0));
        $presence_penalty = floatval(self::get_setting('miapg_ai_presence_penalty', 0.0));
        
        // Store original values for comparison
        $original_top_p = $top_p;
        
        // Validate and clamp values to API requirements
        $temperature = max(0.0, min(2.0, $temperature));
        $max_tokens = max(1, min(4096, $max_tokens));
        $top_p = max(0.01, min(0.99, $top_p)); // Valid range (0, 1) exclusive
        $frequency_penalty = max(-2.0, min(2.0, $frequency_penalty));
        $presence_penalty = max(-2.0, min(2.0, $presence_penalty));
        
        // Auto-fix invalid top_p values in database
        if ($original_top_p !== $top_p && ($original_top_p <= 0 || $original_top_p >= 1.0)) {
            self::update_setting('miapg_ai_top_p', $top_p);
            
            // Log the correction
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log("MIAPG: Auto-corrected invalid top_p value from {$original_top_p} to {$top_p}");
            }
        }
        
        return array(
            'temperature' => $temperature,
            'max_tokens' => $max_tokens,
            'top_p' => $top_p,
            'frequency_penalty' => $frequency_penalty,
            'presence_penalty' => $presence_penalty,
        );
    }
    
    /**
     * Get current interface language
     */
    public static function get_interface_language() {
        return self::get_setting('miapg_interface_language', 'es');
    }
    
    /**
     * Get available languages
     */
    public static function get_available_languages() {
        return array(
            'es' => 'Español',
            'en' => 'English',
            'ru' => 'Русский'
        );
    }
    
    /**
     * Get language-specific content generation instructions
     */
    public static function get_language_instructions($language = null) {
        if (!$language) {
            $language = self::get_interface_language();
        }
        
        $instructions = array(
            'es' => 'Escribe en español de forma natural y conversacional. Evita frases robóticas o demasiado formales. Usa expresiones cotidianas y varía la estructura de tus oraciones. Suena como un experto apasionado compartiendo conocimientos.',
            'en' => 'Write in natural, conversational English. Avoid robotic or overly formal phrases. Use everyday expressions and vary your sentence structure. Sound like a passionate expert sharing knowledge.',
            'ru' => 'Пишите на естественном, разговорном русском языке. Избегайте роботических или слишком формальных фраз. Используйте повседневные выражения и варьируйте структуру предложений. Звучите как увлечённый эксперт, делящийся знаниями.'
        );
        
        return isset($instructions[$language]) ? $instructions[$language] : $instructions['es'];
    }
}