<?php
/**
 * Uninstall script for MaestrIA post generator
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
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

// Delete all post ideas
global $wpdb;

$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'miapg_post_idea'");

// Delete related meta data
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})");

// Clear scheduled events
wp_clear_scheduled_hook('miapg_generate_post_hook');

// Clear any cached data
wp_cache_flush();