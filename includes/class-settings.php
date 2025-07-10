<?php
/**
 * Settings management class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Auto_Post_Generator_Settings {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Settings groups
     */
    private $settings_groups = array(
        'general' => 'my_plugin_settings_group',
        'ai' => 'my_plugin_ai_settings_group',
        'content' => 'my_plugin_content_settings_group',
        'scheduling' => 'my_plugin_scheduling_settings_group',
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
            'ai_provider' => 'sanitize_text_field',
            'openai_api_key' => 'sanitize_text_field',
            'deepseek_api_key' => 'sanitize_text_field',
            'auto_post_category' => 'absint',
            'auto_post_tags' => 'sanitize_text_field',
            'auto_post_status' => 'sanitize_text_field',
            'interface_language' => 'sanitize_text_field',
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
            'openai_model' => 'sanitize_text_field',
            'deepseek_model' => 'sanitize_text_field',
            'ai_temperature' => 'sanitize_text_field',
            'ai_max_tokens' => 'absint',
            'ai_top_p' => 'sanitize_text_field',
            'ai_frequency_penalty' => 'sanitize_text_field',
            'ai_presence_penalty' => 'sanitize_text_field',
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
            'auto_post_prompt' => 'sanitize_textarea_field',
            'auto_post_word_count' => 'absint',
            'writing_style' => 'sanitize_text_field',
            'target_audience' => 'sanitize_text_field',
            'tone' => 'sanitize_text_field',
            'include_faq' => 'sanitize_text_field',
            'include_lists' => 'sanitize_text_field',
            'seo_focus' => 'sanitize_text_field',
            'title_max_length' => 'absint',
            'custom_instructions' => 'sanitize_textarea_field',
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
            'auto_scheduling_enabled' => 'sanitize_text_field',
            'posting_frequency' => 'sanitize_text_field',
            'posting_time' => 'sanitize_text_field',
            'posting_day' => 'sanitize_text_field',
            'auto_topics_list' => 'sanitize_textarea_field',
            'auto_delete_used_ideas' => 'sanitize_text_field',
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
        $provider = self::get_setting('ai_provider', 'openai');
        
        if ($provider === 'deepseek') {
            return array(
                'api_key' => self::get_setting('deepseek_api_key'),
                'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
                'model' => self::get_setting('deepseek_model', 'deepseek-chat'),
            );
        } else {
            return array(
                'api_key' => self::get_setting('openai_api_key'),
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
                'model' => self::get_setting('openai_model', 'gpt-4'),
            );
        }
    }
    
    /**
     * Get content generation settings
     */
    public static function get_content_settings() {
        return array(
            'writing_style' => self::get_setting('writing_style', 'informativo'),
            'target_audience' => self::get_setting('target_audience', 'general'),
            'tone' => self::get_setting('tone', 'profesional'),
            'include_faq' => self::get_setting('include_faq', 'yes'),
            'include_lists' => self::get_setting('include_lists', 'yes'),
            'seo_focus' => self::get_setting('seo_focus', 'medium'),
            'custom_instructions' => self::get_setting('custom_instructions', ''),
        );
    }
    
    /**
     * Get AI parameters
     */
    public static function get_ai_parameters() {
        return array(
            'temperature' => floatval(self::get_setting('ai_temperature', 0.7)),
            'max_tokens' => intval(self::get_setting('ai_max_tokens', 2000)),
            'top_p' => floatval(self::get_setting('ai_top_p', 1.0)),
            'frequency_penalty' => floatval(self::get_setting('ai_frequency_penalty', 0.0)),
            'presence_penalty' => floatval(self::get_setting('ai_presence_penalty', 0.0)),
        );
    }
    
    /**
     * Get current interface language
     */
    public static function get_interface_language() {
        return self::get_setting('interface_language', 'es');
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
            'es' => 'Genera el contenido en español. Utiliza un lenguaje natural y fluido apropiado para hispanohablantes.',
            'en' => 'Generate content in English. Use natural and fluent language appropriate for English speakers.',
            'ru' => 'Создайте контент на русском языке. Используйте естественный и плавный язык, подходящий для русскоязычных пользователей.'
        );
        
        return isset($instructions[$language]) ? $instructions[$language] : $instructions['es'];
    }
}