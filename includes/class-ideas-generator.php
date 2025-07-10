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
            'general' => __('general post ideas', MIAPG_TEXT_DOMAIN),
            'tutorial' => __('step-by-step tutorials', MIAPG_TEXT_DOMAIN),
            'lista' => __('lists and compilations', MIAPG_TEXT_DOMAIN),
            'comparacion' => __('comparisons and reviews', MIAPG_TEXT_DOMAIN),
            'noticias' => __('news and updates', MIAPG_TEXT_DOMAIN),
        );
        
        $instruction = isset($content_type_instructions[$content_type]) ? $content_type_instructions[$content_type] : $content_type_instructions['general'];
        $language_instructions = Miapg_Settings::get_language_instructions();
        
        $prompt = sprintf(
            __('Generate %d %s about the topic "%s".', MIAPG_TEXT_DOMAIN),
            $count,
            $instruction,
            $topic
        );
        
        $prompt .= ' ' . $language_instructions;
        $prompt .= ' ' . __('Each idea should be:', MIAPG_TEXT_DOMAIN);
        $prompt .= ' 1. ' . __('Specific and attractive', MIAPG_TEXT_DOMAIN);
        $prompt .= ' 2. ' . __('SEO optimized', MIAPG_TEXT_DOMAIN);
        $prompt .= ' 3. ' . __('Useful for the audience', MIAPG_TEXT_DOMAIN);
        $prompt .= ' 4. ' . __('Feasible to write', MIAPG_TEXT_DOMAIN);
        $prompt .= ' ' . __('Present each idea as an attractive title on a separate line, without numbering.', MIAPG_TEXT_DOMAIN);
        
        return $prompt;
    }
    
    /**
     * Build prompt for ideas generation from article
     */
    private static function build_article_ideas_prompt($article, $count, $approach) {
        $approach_instructions = array(
            'related' => __('related and complementary topic ideas', MIAPG_TEXT_DOMAIN),
            'expanded' => __('ideas that expand and deepen the mentioned concepts', MIAPG_TEXT_DOMAIN),
            'alternative' => __('ideas with alternative approaches and different perspectives', MIAPG_TEXT_DOMAIN),
            'practical' => __('practical application and use case ideas', MIAPG_TEXT_DOMAIN),
        );
        
        $instruction = isset($approach_instructions[$approach]) ? $approach_instructions[$approach] : $approach_instructions['related'];
        $language_instructions = Miapg_Settings::get_language_instructions();
        
        $prompt = sprintf(
            __('Analyze the following article and generate %d %s for new blog posts based on its content.', MIAPG_TEXT_DOMAIN),
            $count,
            $instruction
        );
        
        $prompt .= ' ' . $language_instructions;
        $prompt .= "\n\n" . __('Reference article:', MIAPG_TEXT_DOMAIN) . "\n{$article}\n\n";
        
        $prompt .= __('Each idea should be:', MIAPG_TEXT_DOMAIN) . "\n";
        $prompt .= '1. ' . __('Specific and attractive', MIAPG_TEXT_DOMAIN) . "\n";
        $prompt .= '2. ' . __('Unique and different from the original article', MIAPG_TEXT_DOMAIN) . "\n";
        $prompt .= '3. ' . __('SEO optimized', MIAPG_TEXT_DOMAIN) . "\n";
        $prompt .= '4. ' . __('Feasible to write as an independent post', MIAPG_TEXT_DOMAIN) . "\n";
        $prompt .= __('Present each idea as an attractive title on a separate line, without numbering.', MIAPG_TEXT_DOMAIN);
        
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
                    'content' => __('You are a content marketing expert who generates creative ideas for blog posts.', MIAPG_TEXT_DOMAIN) . ' ' . $language_instructions
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
            return new WP_Error('no_content', __('No content generated', MIAPG_TEXT_DOMAIN));
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
            
            // Create idea post
            $post_data = array(
                'post_title' => $idea,
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'miapg_post_idea',
                'post_author' => get_current_user_id(),
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Save meta data
                update_post_meta($post_id, '_miapg_idea_topic', $topic);
                update_post_meta($post_id, '_miapg_idea_content_type', $content_type);
                update_post_meta($post_id, '_miapg_idea_generated_date', $current_time);
                
                $saved_ideas[] = $post_id;
            }
        }
        
        if (!empty($saved_ideas)) {
            $message = sprintf(
                __('%d post ideas have been generated and saved.', MIAPG_TEXT_DOMAIN),
                count($saved_ideas)
            );
            $message .= ' <a href="' . admin_url('edit.php?post_type=miapg_post_idea') . '" class="button button-secondary">' . __('View Saved Ideas', MIAPG_TEXT_DOMAIN) . '</a>';
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
            
            // Create idea post
            $post_data = array(
                'post_title' => $idea,
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'miapg_post_idea',
                'post_author' => get_current_user_id(),
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Save meta data
                update_post_meta($post_id, '_miapg_idea_topic', __('Based on reference article', MIAPG_TEXT_DOMAIN));
                update_post_meta($post_id, '_miapg_idea_content_type', $approach);
                update_post_meta($post_id, '_miapg_idea_generated_date', $current_time);
                update_post_meta($post_id, '_miapg_idea_source_article', wp_trim_words($article, 50));
                
                $saved_ideas[] = $post_id;
            }
        }
        
        if (!empty($saved_ideas)) {
            $message = sprintf(
                __('%d ideas have been generated and saved based on the reference article.', MIAPG_TEXT_DOMAIN),
                count($saved_ideas)
            );
            $message .= ' <a href="' . admin_url('edit.php?post_type=miapg_post_idea') . '" class="button button-secondary">' . __('View Saved Ideas', MIAPG_TEXT_DOMAIN) . '</a>';
            return $message;
        }
        
        return false;
    }
    
    /**
     * Get post ideas statistics
     */
    public static function get_post_ideas_stats() {
        $total_ideas = wp_count_posts('miapg_post_idea');
        $ideas_with_keywords = get_posts(array(
            'post_type' => 'miapg_post_idea',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_miapg_idea_keyword',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'fields' => 'ids'
        ));
        
        return array(
            'total' => $total_ideas->publish,
            'with_keywords' => count($ideas_with_keywords),
            'without_keywords' => $total_ideas->publish - count($ideas_with_keywords)
        );
    }
}