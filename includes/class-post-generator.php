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
            $post_date = gmdate('Y-m-d H:i:s', strtotime($post_date));
        }
        
        // Get AI provider settings
        $ai_settings = Miapg_Settings::get_ai_provider_settings();
        
        if (!$ai_settings['api_key']) {
            return sprintf(
                // translators: %s is the AI provider name
                __('No API key configured for %s. Please check your settings.', 'miapg-post-generator'), 
                ucfirst($ai_provider)
            );
        }
        
        // Validate basic parameters
        if (empty($prompt)) {
            return __('No topic or prompt provided for content generation.', 'miapg-post-generator');
        }
        
        if ($word_count < 50 || $word_count > 5000) {
            return __('Word count must be between 50 and 5000 words.', 'miapg-post-generator');
        }
        
        // Generate content
        $content_result = self::generate_content($prompt, $word_count, $keyword, $source_article, $ai_settings);
        
        if (is_wp_error($content_result)) {
            // Log the error for debugging
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('MIAPG: Content generation failed: ' . $content_result->get_error_message());
            }
            return __('Content generation failed: ', 'miapg-post-generator') . $content_result->get_error_message();
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
                $post_title = __('Generated Post', 'miapg-post-generator');
            } else {
                $post_title = $title_result;
            }
        }
        
        // Create post
        $post_id = self::create_post($post_title, $content_result, $post_status, $post_date, $category_id, $tags);
        
        if (is_wp_error($post_id)) {
            return $post_id->get_error_message();
        }
        
        /* translators: %1$d: post ID, %2$s: scheduled date */
        return sprintf(
            // translators: %1$d is the post ID, %2$s is the scheduled date
            __('Post created with ID: %1$d, scheduled for: %2$s', 'miapg-post-generator'),
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
                    'content' => __('You are an experienced content writer who creates engaging, natural-sounding blog posts. Write in a conversational yet informative style that connects with readers. Avoid overly promotional language and focus on providing genuine value. Use HTML formatting when specified.', 'miapg-post-generator') . ' ' . $language_instructions
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
                // translators: %s: error message
                __('Error in AI API request: %s', 'miapg-post-generator'),
                $response->get_error_message()
            ));
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (!isset($result['choices'][0]['message']['content'])) {
            // Log detailed error information
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('MIAPG: No content generated from API. Full response: ' . wp_json_encode($result));
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('MIAPG: API endpoint used: ' . $ai_settings['endpoint']);
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('MIAPG: Model used: ' . $ai_settings['model']);
            }
            
            // Check if there's an error message from the API
            if (isset($result['error']['message'])) {
                return new WP_Error('api_error', sprintf(
                    // translators: %s is the error message from the AI API
                    __('AI API Error: %s', 'miapg-post-generator'), 
                    $result['error']['message']
                ));
            }
            
            return new WP_Error('no_content', __('No content generated from AI provider. Please check your API settings and try again.', 'miapg-post-generator'));
        }
        
        return $result['choices'][0]['message']['content'];
    }
    
    /**
     * Generate title using AI
     */
    private static function generate_title($prompt, $keyword, $ai_settings) {
        $title_length = Miapg_Settings::get_setting('title_max_length', 60);
        $language_instructions = Miapg_Settings::get_language_instructions();
        
        /* translators: %1$d: maximum characters, %2$s: article topic */
        $title_prompt = sprintf(
            // translators: %1$d is the maximum character count, %2$s is the topic
            __('Generate an attractive and concise SEO title (maximum %1$d characters) for an article about: %2$s.', 'miapg-post-generator'),
            $title_length,
            $prompt
        );
        
        if (!empty($keyword)) {
            /* translators: %s: keyword */
            $title_prompt .= ' ' . sprintf(
                // translators: %s is the keyword to include
                __('IMPORTANT: Include the keyword "%s" in the title naturally.', 'miapg-post-generator'),
                $keyword
            );
        }
        
        $title_prompt .= ' ' . __('Do not use quotes in the title.', 'miapg-post-generator');
        $title_prompt .= ' ' . $language_instructions;
        
        $ai_parameters = Miapg_Settings::get_ai_parameters();
        
        $title_data = array(
            'model' => $ai_settings['model'],
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => __('You are a creative copywriter who crafts compelling, click-worthy titles that feel natural and engaging. Create titles that spark curiosity without being clickbait. Never use quotes around the title.', 'miapg-post-generator') . ' ' . $language_instructions
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
            return new WP_Error('title_error', __('Error generating title', 'miapg-post-generator'));
        }
        
        $title_body = wp_remote_retrieve_body($title_response);
        $title_result = json_decode($title_body, true);
        
        if (!isset($title_result['choices'][0]['message']['content'])) {
            return new WP_Error('no_title', __('No title generated', 'miapg-post-generator'));
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
            // translators: %1$s: writing style, %2$s: tone, %3$s: target audience
            __('Write naturally as an expert in the field, using a %1$s style with a %2$s tone. Your audience consists of %3$s. Focus on being helpful and authentic rather than promotional. Use personal insights, real examples, and avoid corporate jargon.', 'miapg-post-generator'),
            $content_settings['writing_style'],
            $content_settings['tone'],
            $content_settings['target_audience']
        );
        
        $seo_prompt .= ' ' . $language_instructions;
        
        if (!empty($keyword)) {
            $seo_prompt .= ' ' . sprintf(
                // translators: %s: keyword
                __('Naturally incorporate the topic "%s" throughout your content where it flows organically. Don\'t force it - let it appear naturally in context.', 'miapg-post-generator'),
                $keyword
            );
        }
        
        if (!empty($source_article)) {
            $seo_prompt .= ' ' . sprintf(
                // translators: %1$d: word count, %2$s: article topic, %3$s: reference article
                __('Based on the following reference article, create a unique %1$d-word article about: %2$s. Reference article: %3$s. IMPORTANT: Do not copy the content, but create a fresh and original approach while maintaining the main ideas.', 'miapg-post-generator'),
                $word_count,
                $prompt,
                $source_article
            );
        } else {
            $seo_prompt .= ' ' . sprintf(
                // translators: %1$d: word count, %2$s: topic
                __('Create a blog article of approximately %1$d words about the following topic: %2$s.', 'miapg-post-generator'),
                $word_count,
                $prompt
            );
        }
        
        // Add HTML format requirements
        $seo_prompt .= ' ' . __('REQUIRED FORMAT: Use ONLY the following basic HTML tags:', 'miapg-post-generator');
        $seo_prompt .= ' - <h2> ' . __('for main headings', 'miapg-post-generator');
        $seo_prompt .= ' - <h3> ' . __('for subheadings', 'miapg-post-generator');
        $seo_prompt .= ' - <strong> ' . __('for bold text', 'miapg-post-generator');
        $seo_prompt .= ' - <em> ' . __('for italic text', 'miapg-post-generator');
        $seo_prompt .= ' - <p> ' . __('for paragraphs', 'miapg-post-generator');
        $seo_prompt .= ' - <ul> and <li> ' . __('for bullet lists', 'miapg-post-generator');
        $seo_prompt .= ' - <ol> and <li> ' . __('for numbered lists', 'miapg-post-generator');
        $seo_prompt .= ' - <br> ' . __('for line breaks if necessary', 'miapg-post-generator');
        $seo_prompt .= ' ' . __('DO NOT use: div, span, class, id, style, table, img, script, or any complex HTML tags.', 'miapg-post-generator');
        
        // Add content structure
        $seo_prompt .= ' ' . __('Content structure guidelines:', 'miapg-post-generator');
        $seo_prompt .= ' ' . __('Start with an engaging introduction that hooks the reader.', 'miapg-post-generator');
        $seo_prompt .= ' ' . __('Break content into logical sections with <h2> headings that are descriptive and interesting.', 'miapg-post-generator');
        $seo_prompt .= ' ' . __('Use <h3> subheadings to organize detailed points within sections.', 'miapg-post-generator');
        
        if ($content_settings['include_lists'] === 'yes') {
            $seo_prompt .= ' ' . __('Include practical lists where they add value (use <ul><li> for bullets or <ol><li> for numbered steps).', 'miapg-post-generator');
        }
        
        $seo_prompt .= ' ' . __('Use <strong> for emphasis and <em> for subtle highlighting - but sparingly for maximum impact.', 'miapg-post-generator');
        $seo_prompt .= ' ' . __('End with a thoughtful conclusion that ties everything together and gives readers a clear takeaway.', 'miapg-post-generator');
        
        if ($content_settings['include_faq'] === 'yes') {
            $seo_prompt .= ' ' . __('Consider adding a FAQ section if it naturally fits, with 3 genuine questions readers might have.', 'miapg-post-generator');
        }
        
        if ($content_settings['custom_instructions']) {
            $seo_prompt .= ' ' . __('Special considerations:', 'miapg-post-generator') . ' ' . $content_settings['custom_instructions'];
        }
        
        $seo_prompt .= ' ' . __('Important: Write like a knowledgeable friend sharing insights, not like an AI or corporate blog. Be conversational but professional. Vary your sentence length and structure. Use ONLY the HTML tags specified above. Never include a title at the beginning.', 'miapg-post-generator');
        
        return $seo_prompt;
    }
    
    /**
     * Create WordPress post
     */
    private static function create_post($title, $content, $status, $date, $category_id, $tags) {
        // Sanitize and validate inputs
        $title = wp_strip_all_tags(trim($title));
        if (empty($title)) {
            return new WP_Error('invalid_title', __('Post title cannot be empty', 'miapg-post-generator'));
        }
        
        // Ensure valid post status
        $valid_statuses = array('publish', 'draft', 'future', 'private');
        if (!in_array($status, $valid_statuses)) {
            $status = 'draft';
        }
        
        // Validate and format date
        if ($status === 'future' && strtotime($date) <= current_time('timestamp')) {
            $date = gmdate('Y-m-d H:i:s', current_time('timestamp') + 3600); // Set 1 hour from now
        }
        
        // Prepare categories array
        $categories = array();
        if ($category_id > 0 && get_category($category_id)) {
            $categories[] = absint($category_id);
        } else {
            $categories[] = absint(get_option('default_category', 1));
        }
        
        // Clean tags array
        $clean_tags = array();
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $clean_tags[] = sanitize_text_field($tag);
                }
            }
        }
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_author' => get_current_user_id(),
            'post_category' => $categories,
            'tags_input' => $clean_tags,
            'post_date' => $date,
            'post_date_gmt' => get_gmt_from_date($date),
            'post_type' => 'post',
            'meta_input' => array(
                '_miapg_generated' => true,
                '_miapg_generation_date' => current_time('mysql'),
                '_miapg_word_count' => str_word_count(wp_strip_all_tags($content))
            )
        );
        
        // Insert post with error checking
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            // Log error for debugging
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('MIAPG: Post creation failed: ' . $post_id->get_error_message());
            }
            
            return new WP_Error('post_creation_failed', sprintf(
                // translators: %s: error message
                __('Error creating post: %s', 'miapg-post-generator'),
                $post_id->get_error_message()
            ));
        }
        
        // Log successful creation
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log("MIAPG: Successfully created post ID: $post_id with title: $title");
        }
        
        return $post_id;
    }
}