<?php
/**
 * Ideas generator class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Ideas_Generator {
    
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
     * Generate ideas from topic and save as custom post type
     */
    public static function generate_post_ideas($topic, $count, $content_type) {
        // Get AI provider settings
        $ai_settings = Miapg_Settings::get_ai_provider_settings();
        
        if (!$ai_settings['api_key']) {
            return false;
        }
        
        // Build prompt for ideas generation
        $prompt = self::build_ideas_prompt($topic, $count, $content_type);
        
        // Generate ideas using AI
        $ideas_result = self::call_ai_api($prompt, $ai_settings);
        
        if (is_wp_error($ideas_result)) {
            return false;
        }
        
        // Process and save ideas
        return self::save_ideas($ideas_result, $topic, $content_type);
    }
    
    /**
     * Generate ideas from article and save as custom post type
     */
    public static function generate_ideas_from_article($article, $count, $approach) {
        // Get AI provider settings
        $ai_settings = Miapg_Settings::get_ai_provider_settings();
        
        if (!$ai_settings['api_key']) {
            return false;
        }
        
        // Build prompt for article-based ideas generation
        $prompt = self::build_article_ideas_prompt($article, $count, $approach);
        
        // Generate ideas using AI
        $ideas_result = self::call_ai_api($prompt, $ai_settings);
        
        if (is_wp_error($ideas_result)) {
            return false;
        }
        
        // Process and save ideas
        return self::save_ideas_from_article($ideas_result, $approach, $article);
    }
    
    /**
     * Build prompt for ideas generation from topic
     */
    private static function build_ideas_prompt($topic, $count, $content_type) {
        $content_type_instructions = array(
            'general' => __('general post ideas', 'miapg-post-generator'),
            'tutorial' => __('step-by-step tutorials', 'miapg-post-generator'),
            'lista' => __('lists and compilations', 'miapg-post-generator'),
            'comparacion' => __('comparisons and reviews', 'miapg-post-generator'),
            'noticias' => __('news and updates', 'miapg-post-generator'),
        );
        
        $instruction = isset($content_type_instructions[$content_type]) ? $content_type_instructions[$content_type] : $content_type_instructions['general'];
        $language_instructions = Miapg_Settings::get_language_instructions();
        
        $prompt = sprintf(
            // translators: %1$d is the number to generate, %2$s is the type (ideas/titles), %3$s is the topic
            __('Generate %1$d %2$s about the topic "%3$s".', 'miapg-post-generator'),
            $count,
            $instruction,
            $topic
        );
        
        $prompt .= ' ' . $language_instructions;
        $prompt .= ' ' . __('Each idea should be:', 'miapg-post-generator');
        $prompt .= ' 1. ' . __('Specific and attractive', 'miapg-post-generator');
        $prompt .= ' 2. ' . __('SEO optimized', 'miapg-post-generator');
        $prompt .= ' 3. ' . __('Useful for the audience', 'miapg-post-generator');
        $prompt .= ' 4. ' . __('Feasible to write', 'miapg-post-generator');
        $prompt .= ' ' . __('Present each idea as an attractive title on a separate line, without numbering.', 'miapg-post-generator');
        
        return $prompt;
    }
    
    /**
     * Build prompt for ideas generation from article
     */
    private static function build_article_ideas_prompt($article, $count, $approach) {
        $approach_instructions = array(
            'related' => __('related and complementary topic ideas', 'miapg-post-generator'),
            'expanded' => __('ideas that expand and deepen the mentioned concepts', 'miapg-post-generator'),
            'alternative' => __('ideas with alternative approaches and different perspectives', 'miapg-post-generator'),
            'practical' => __('practical application and use case ideas', 'miapg-post-generator'),
        );
        
        $instruction = isset($approach_instructions[$approach]) ? $approach_instructions[$approach] : $approach_instructions['related'];
        $language_instructions = Miapg_Settings::get_language_instructions();
        
        $prompt = sprintf(
            // translators: %1$d is the number to generate, %2$s is the type (ideas/titles)
            __('Analyze the following article and generate %1$d %2$s for new blog posts based on its content.', 'miapg-post-generator'),
            $count,
            $instruction
        );
        
        $prompt .= ' ' . $language_instructions;
        $prompt .= "\n\n" . __('Reference article:', 'miapg-post-generator') . "\n{$article}\n\n";
        
        $prompt .= __('Each idea should be:', 'miapg-post-generator') . "\n";
        $prompt .= '1. ' . __('Specific and attractive', 'miapg-post-generator') . "\n";
        $prompt .= '2. ' . __('Unique and different from the original article', 'miapg-post-generator') . "\n";
        $prompt .= '3. ' . __('SEO optimized', 'miapg-post-generator') . "\n";
        $prompt .= '4. ' . __('Feasible to write as an independent post', 'miapg-post-generator') . "\n";
        $prompt .= __('Present each idea as an attractive title on a separate line, without numbering.', 'miapg-post-generator');
        
        return $prompt;
    }
    
    /**
     * Call AI API
     */
    private static function call_ai_api($prompt, $ai_settings) {
        $language_instructions = Miapg_Settings::get_language_instructions();
        $data = array(
            'model' => $ai_settings['model'],
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => __('You are a content marketing expert who generates creative ideas for blog posts.', 'miapg-post-generator') . ' ' . $language_instructions
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 1000,
            'temperature' => 0.8,
        );
        
        $response = wp_remote_post($ai_settings['endpoint'], array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $ai_settings['api_key'],
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
            'timeout' => 60,
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (!isset($result['choices'][0]['message']['content'])) {
            return new WP_Error('no_content', __('No content generated', 'miapg-post-generator'));
        }
        
        return $result['choices'][0]['message']['content'];
    }
    
    /**
     * Save ideas from topic
     */
    private static function save_ideas($ideas_text, $topic, $content_type) {
        $ideas_array = array_filter(explode("\n", $ideas_text));
        $saved_ideas = array();
        $current_time = current_time('Y-m-d H:i:s');
        
        foreach ($ideas_array as $idea) {
            $idea = trim($idea);
            if (empty($idea)) {
                continue;
            }
            
            // Clean numbering if it exists
            $idea = preg_replace('/^\d+\.?\s*/', '', $idea);
            $idea = trim($idea);
            // Remove quotes and asterisks from idea title
            $idea = str_replace(array('"', "'", '*'), '', $idea);
            
            if (empty($idea)) {
                continue;
            }
            
            // Create idea post with proper WordPress structure
            $post_data = array(
                'post_title' => wp_strip_all_tags($idea),
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'miapg_post_idea',
                'post_author' => get_current_user_id(),
                'post_date' => current_time('mysql'),
                'post_date_gmt' => current_time('mysql', 1),
                'meta_input' => array(
                    '_miapg_idea_topic' => sanitize_text_field($topic),
                    '_miapg_idea_content_type' => sanitize_text_field($content_type),
                    '_miapg_idea_generated_date' => $current_time,
                    '_miapg_idea_status' => 'available',
                )
            );
            
            $post_id = wp_insert_post($post_data, true);
            
            if (!is_wp_error($post_id) && $post_id > 0) {
                $saved_ideas[] = $post_id;
                
                // Log successful creation for debugging
                if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log("MIAPG: Successfully created idea post ID: $post_id with title: $idea");
                }
            } else {
                // Log error for debugging
                if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                    $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log("MIAPG: Failed to create idea post. Error: $error_message");
                }
            }
        }
        
        if (!empty($saved_ideas)) {
            $message = sprintf(
                // translators: %d: number of post ideas
                __('%d post ideas have been generated and saved.', 'miapg-post-generator'),
                count($saved_ideas)
            );
            $message .= ' <a href="' . admin_url('admin.php?page=miapg-ideas-manager') . '" class="button button-primary">' . __('Manage Ideas', 'miapg-post-generator') . '</a>';
            $message .= ' <a href="' . admin_url('edit.php?post_type=miapg_post_idea') . '" class="button button-secondary" style="margin-left: 5px;">' . __('WordPress List', 'miapg-post-generator') . '</a>';
            return $message;
        }
        
        return false;
    }
    
    /**
     * Save ideas from article
     */
    private static function save_ideas_from_article($ideas_text, $approach, $article) {
        $ideas_array = array_filter(explode("\n", $ideas_text));
        $saved_ideas = array();
        $current_time = current_time('Y-m-d H:i:s');
        
        foreach ($ideas_array as $idea) {
            $idea = trim($idea);
            if (empty($idea)) {
                continue;
            }
            
            // Clean numbering if it exists
            $idea = preg_replace('/^\d+\.?\s*/', '', $idea);
            $idea = trim($idea);
            // Remove quotes and asterisks from idea title
            $idea = str_replace(array('"', "'", '*'), '', $idea);
            
            if (empty($idea)) {
                continue;
            }
            
            // Create idea post with proper WordPress structure
            $post_data = array(
                'post_title' => wp_strip_all_tags($idea),
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'miapg_post_idea',
                'post_author' => get_current_user_id(),
                'post_date' => current_time('mysql'),
                'post_date_gmt' => current_time('mysql', 1),
                'meta_input' => array(
                    '_miapg_idea_topic' => __('Based on reference article', 'miapg-post-generator'),
                    '_miapg_idea_content_type' => sanitize_text_field($approach),
                    '_miapg_idea_generated_date' => $current_time,
                    '_miapg_idea_source_article' => wp_trim_words(sanitize_textarea_field($article), 50),
                    '_miapg_idea_status' => 'available',
                )
            );
            
            $post_id = wp_insert_post($post_data, true);
            
            if (!is_wp_error($post_id) && $post_id > 0) {
                $saved_ideas[] = $post_id;
                
                // Log successful creation for debugging
                if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log("MIAPG: Successfully created article-based idea post ID: $post_id with title: $idea");
                }
            } else {
                // Log error for debugging
                if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                    $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log("MIAPG: Failed to create article-based idea post. Error: $error_message");
                }
            }
        }
        
        if (!empty($saved_ideas)) {
            $message = sprintf(
                // translators: %d: number of ideas
                __('%d ideas have been generated and saved based on the reference article.', 'miapg-post-generator'),
                count($saved_ideas)
            );
            $message .= ' <a href="' . admin_url('admin.php?page=miapg-ideas-manager') . '" class="button button-primary">' . __('Manage Ideas', 'miapg-post-generator') . '</a>';
            $message .= ' <a href="' . admin_url('edit.php?post_type=miapg_post_idea') . '" class="button button-secondary" style="margin-left: 5px;">' . __('WordPress List', 'miapg-post-generator') . '</a>';
            return $message;
        }
        
        return false;
    }
    
    /**
     * Get post ideas statistics
     */
    public static function get_post_ideas_stats() {
        // Check cache first
        $cache_key = 'miapg_ideas_stats';
        $cached_stats = wp_cache_get($cache_key, 'miapg_post_generator');
        
        if (false !== $cached_stats) {
            return $cached_stats;
        }
        
        $total_ideas = wp_count_posts('miapg_post_idea');
        
        // Get all published ideas and count those with keywords
        $all_ideas = get_posts(array(
            'post_type' => 'miapg_post_idea',
            'post_status' => 'publish',
            'fields' => 'ids',
            'numberposts' => -1
        ));
        
        // Count ideas with non-empty keywords
        $with_keywords_count = 0;
        if (!empty($all_ideas)) {
            foreach ($all_ideas as $post_id) {
                $keyword = get_post_meta($post_id, '_miapg_idea_keyword', true);
                if (!empty($keyword)) {
                    $with_keywords_count++;
                }
            }
        }
        
        $stats = array(
            'total' => $total_ideas->publish,
            'with_keywords' => $with_keywords_count,
            'without_keywords' => $total_ideas->publish - $with_keywords_count
        );
        
        // Cache for 5 minutes
        wp_cache_set($cache_key, $stats, 'miapg_post_generator', 300);
        
        return $stats;
    }
    
    /**
     * Format topic text - separate words that are joined together
     */
    public static function format_topic_text($text) {
        if (empty($text)) {
            return $text;
        }
        
        // Store original for debugging
        $original = $text;
        
        // Method 1: Add space before uppercase letters (camelCase handling)
        $formatted = preg_replace('/([a-z\d])([A-Z])/', '$1 $2', $text);
        
        // Method 2: Add space before accented uppercase letters
        $formatted = preg_replace('/([a-záéíóúñü\d])([ÁÉÍÓÚÑÜ])/', '$1 $2', $formatted);
        
        // Method 3: Add space between letters and numbers
        $formatted = preg_replace('/([a-zA-ZáéíóúñüÁÉÍÓÚÑÜ])(\d)/', '$1 $2', $formatted);
        $formatted = preg_replace('/(\d)([a-zA-ZáéíóúñüÁÉÍÓÚÑÜ])/', '$1 $2', $formatted);
        
        // Clean up multiple spaces
        $formatted = preg_replace('/\s+/', ' ', $formatted);
        
        // Trim and return
        $result = trim($formatted);
        
        // If no change was made, try a more aggressive approach for all-lowercase strings
        if ($result === $original && strlen($original) > 15 && ctype_lower(str_replace(['á','é','í','ó','ú','ñ','ü'], 'a', $original))) {
            // Split long lowercase strings at likely word boundaries
            // Common Spanish word patterns
            $patterns = array(
                '/([a-záéíóúñü]{5,})(en|de|para|con|por|sobre|entre|bajo|tras)([a-záéíóúñü]{3,})/' => '$1 $2 $3',
                '/([a-záéíóúñü]{4,})(ment|ción|sión|idad|ismo)([a-záéíóúñü]{4,})/' => '$1$2 $3',
                '/([a-záéíóúñü]{6,})([a-záéíóúñü]{6,})/' => '$1 $2',
            );
            
            foreach ($patterns as $pattern => $replacement) {
                $temp = preg_replace($pattern, $replacement, $result);
                if ($temp !== $result) {
                    $result = $temp;
                    break;
                }
            }
        }
        
        // Debug logging if needed
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && $original !== $result) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log("MIAPG: Topic formatted from '$original' to '$result'");
        }
        
        return $result;
    }
}