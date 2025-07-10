<?php
/**
 * Post generator class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Post_Generator {
    
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
        // Constructor can be empty for now
    }
    
    /**
     * Generate and publish post
     */
    public static function generate_and_publish_post($prompt, $category_id, $tags, $post_status, $post_date, $word_count, $ai_provider = 'openai', $custom_settings = array(), $keyword = '', $source_article = '', $is_from_idea = false) {
        // Validate and sanitize input parameters
        $prompt = sanitize_text_field($prompt);
        $category_id = absint($category_id);
        $tags = array_map('sanitize_text_field', $tags);
        $post_status = sanitize_key($post_status);
        $post_date = sanitize_text_field($post_date);
        $word_count = absint($word_count);
        
        // Validate post status
        $allowed_post_statuses = array('publish', 'draft', 'future');
        if (!in_array($post_status, $allowed_post_statuses)) {
            $post_status = 'draft';
        }
        
        // Validate publication date
        if (!$post_date || strtotime($post_date) === false) {
            $post_date = current_time('mysql');
        } else {
            $post_date = date('Y-m-d H:i:s', strtotime($post_date));
        }
        
        // Get AI provider settings
        $ai_settings = Miapg_Settings::get_ai_provider_settings();
        
        if (!$ai_settings['api_key']) {
            return __('No API key provided for ' . $ai_provider, MIAPG_TEXT_DOMAIN);
        }
        
        // Generate content
        $content_result = self::generate_content($prompt, $word_count, $keyword, $source_article, $ai_settings);
        
        if (is_wp_error($content_result)) {
            return $content_result->get_error_message();
        }
        
        // Generate title
        if ($is_from_idea) {
            // For posts generated from ideas, use the idea title directly
            $post_title = $prompt;
            // Remove quotes from title
            $post_title = str_replace(array('"', "'"), '', $post_title);
        } else {
            // Generate title using AI for other cases
            $title_result = self::generate_title($prompt, $keyword, $ai_settings);
            
            if (is_wp_error($title_result)) {
                $post_title = __('Generated Post', MIAPG_TEXT_DOMAIN);
            } else {
                $post_title = $title_result;
            }
        }
        
        // Create post
        $post_id = self::create_post($post_title, $content_result, $post_status, $post_date, $category_id, $tags);
        
        if (is_wp_error($post_id)) {
            return $post_id->get_error_message();
        }
        
        return sprintf(
            __('Post created with ID: %d, scheduled for: %s', MIAPG_TEXT_DOMAIN),
            $post_id,
            $post_date
        );
    }
    
    /**
     * Generate content using AI
     */
    private static function generate_content($prompt, $word_count, $keyword, $source_article, $ai_settings) {
        // Get content settings
        $content_settings = Miapg_Settings::get_content_settings();
        
        // Build custom prompt
        $seo_prompt = self::build_content_prompt($prompt, $word_count, $keyword, $source_article, $content_settings);
        
        // Get AI parameters
        $ai_parameters = Miapg_Settings::get_ai_parameters();
        
        // Prepare API request data
        $language_instructions = Miapg_Settings::get_language_instructions();
        $data = array(
            'model' => $ai_settings['model'],
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => __('You are an SEO expert who generates blog content with HTML formatting.', MIAPG_TEXT_DOMAIN) . ' ' . $language_instructions
                ),
                array(
                    'role' => 'user',
                    'content' => $seo_prompt
                )
            ),
            'max_tokens' => $ai_parameters['max_tokens'],
            'temperature' => $ai_parameters['temperature'],
            'top_p' => $ai_parameters['top_p'],
            'frequency_penalty' => $ai_parameters['frequency_penalty'],
            'presence_penalty' => $ai_parameters['presence_penalty'],
        );
        
        // Make API request
        $response = wp_remote_post($ai_settings['endpoint'], array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $ai_settings['api_key'],
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
            'timeout' => 120,
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', sprintf(
                __('Error in OpenAI request: %s', MIAPG_TEXT_DOMAIN),
                $response->get_error_message()
            ));
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (!isset($result['choices'][0]['message']['content'])) {
            return new WP_Error('no_content', __('No content generated. API response:', MIAPG_TEXT_DOMAIN) . ' ' . print_r($result, true));
        }
        
        return $result['choices'][0]['message']['content'];
    }
    
    /**
     * Generate title using AI
     */
    private static function generate_title($prompt, $keyword, $ai_settings) {
        $title_length = Miapg_Settings::get_setting('title_max_length', 60);
        $language_instructions = Miapg_Settings::get_language_instructions();
        
        $title_prompt = sprintf(
            __('Generate an attractive and concise SEO title (maximum %d characters) for an article about: %s.', MIAPG_TEXT_DOMAIN),
            $title_length,
            $prompt
        );
        
        if (!empty($keyword)) {
            $title_prompt .= ' ' . sprintf(
                __('IMPORTANT: Include the keyword "%s" in the title naturally.', MIAPG_TEXT_DOMAIN),
                $keyword
            );
        }
        
        $title_prompt .= ' ' . __('Do not use quotes in the title.', MIAPG_TEXT_DOMAIN);
        $title_prompt .= ' ' . $language_instructions;
        
        $ai_parameters = Miapg_Settings::get_ai_parameters();
        
        $title_data = array(
            'model' => $ai_settings['model'],
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => __('You are an SEO expert who generates attractive titles without quotes.', MIAPG_TEXT_DOMAIN) . ' ' . $language_instructions
                ),
                array(
                    'role' => 'user',
                    'content' => $title_prompt
                )
            ),
            'max_tokens' => 60,
            'temperature' => $ai_parameters['temperature'],
        );
        
        $title_response = wp_remote_post($ai_settings['endpoint'], array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $ai_settings['api_key'],
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($title_data),
            'timeout' => 30,
        ));
        
        if (is_wp_error($title_response)) {
            return new WP_Error('title_error', __('Error generating title', MIAPG_TEXT_DOMAIN));
        }
        
        $title_body = wp_remote_retrieve_body($title_response);
        $title_result = json_decode($title_body, true);
        
        if (!isset($title_result['choices'][0]['message']['content'])) {
            return new WP_Error('no_title', __('No title generated', MIAPG_TEXT_DOMAIN));
        }
        
        $post_title = trim($title_result['choices'][0]['message']['content']);
        
        // Remove quotes from title
        $post_title = str_replace(array('"', "'"), '', $post_title);
        
        return $post_title;
    }
    
    /**
     * Build content prompt
     */
    private static function build_content_prompt($prompt, $word_count, $keyword, $source_article, $content_settings) {
        // Add language instructions
        $language_instructions = Miapg_Settings::get_language_instructions();
        
        $seo_prompt = sprintf(
            __('Act as an SEO and content writing expert with %s style and %s tone. Target audience: %s.', MIAPG_TEXT_DOMAIN),
            $content_settings['writing_style'],
            $content_settings['tone'],
            $content_settings['target_audience']
        );
        
        $seo_prompt .= ' ' . $language_instructions;
        
        if (!empty($keyword)) {
            $seo_prompt .= ' ' . sprintf(
                __('IMPORTANT: Focus on the main keyword "%s" and use it strategically throughout the content for SEO.', MIAPG_TEXT_DOMAIN),
                $keyword
            );
        }
        
        if (!empty($source_article)) {
            $seo_prompt .= ' ' . sprintf(
                __('Based on the following reference article, create a unique %d-word article about: %s. Reference article: %s. IMPORTANT: Do not copy the content, but create a fresh and original approach while maintaining the main ideas.', MIAPG_TEXT_DOMAIN),
                $word_count,
                $prompt,
                $source_article
            );
        } else {
            $seo_prompt .= ' ' . sprintf(
                __('Create a blog article of approximately %d words about the following topic: %s.', MIAPG_TEXT_DOMAIN),
                $word_count,
                $prompt
            );
        }
        
        // Add HTML format requirements
        $seo_prompt .= ' ' . __('REQUIRED FORMAT: Use ONLY the following basic HTML tags:', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <h2> ' . __('for main headings', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <h3> ' . __('for subheadings', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <strong> ' . __('for bold text', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <em> ' . __('for italic text', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <p> ' . __('for paragraphs', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <ul> and <li> ' . __('for bullet lists', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <ol> and <li> ' . __('for numbered lists', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' - <br> ' . __('for line breaks if necessary', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' ' . __('DO NOT use: div, span, class, id, style, table, img, script, or any complex HTML tags.', MIAPG_TEXT_DOMAIN);
        
        // Add content structure
        $seo_prompt .= ' ' . __('Structure the content as follows:', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' 1. ' . __('Introduce the topic with an attractive paragraph.', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' 2. ' . __('Use <h2> headings for main sections.', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' 3. ' . __('Use <h3> headings for subsections when necessary.', MIAPG_TEXT_DOMAIN);
        
        if ($content_settings['include_lists'] === 'yes') {
            $seo_prompt .= ' 4. ' . __('Include at least one bullet list (<ul><li>) or numbered list (<ol><li>).', MIAPG_TEXT_DOMAIN);
        }
        
        $seo_prompt .= ' 5. ' . __('Use <strong> for bold and <em> for italic when appropriate.', MIAPG_TEXT_DOMAIN);
        $seo_prompt .= ' 6. ' . __('Conclude with a summary paragraph.', MIAPG_TEXT_DOMAIN);
        
        if ($content_settings['include_faq'] === 'yes') {
            $seo_prompt .= ' 7. ' . __('Add an FAQ section with 3 questions and answers related to the topic at the end of the article.', MIAPG_TEXT_DOMAIN);
        }
        
        if ($content_settings['custom_instructions']) {
            $seo_prompt .= ' ' . __('Additional instructions:', MIAPG_TEXT_DOMAIN) . ' ' . $content_settings['custom_instructions'];
        }
        
        $seo_prompt .= ' ' . __('Make sure the content is informative, engaging and SEO optimized. Use ONLY the basic HTML tags mentioned. DO NOT include a title for the article.', MIAPG_TEXT_DOMAIN);
        
        return $seo_prompt;
    }
    
    /**
     * Create WordPress post
     */
    private static function create_post($title, $content, $status, $date, $category_id, $tags) {
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_author' => get_current_user_id(),
            'post_category' => array($category_id),
            'tags_input' => $tags,
            'post_date' => $date,
        );
        
        // Temporarily disable content filters
        remove_filter('content_save_pre', 'wp_filter_post_kses');
        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
        
        $post_id = wp_insert_post($post_data);
        
        // Reactivate content filters
        add_filter('content_save_pre', 'wp_filter_post_kses');
        add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
        
        if (is_wp_error($post_id)) {
            return new WP_Error('post_creation_failed', sprintf(
                __('Error creating post: %s', MIAPG_TEXT_DOMAIN),
                $post_id->get_error_message()
            ));
        }
        
        return $post_id;
    }
}