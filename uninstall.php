<?php
/**
 * Uninstall script for Auto Post Generator
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
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

// Delete all post ideas
global $wpdb;

$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'post_idea'");

// Delete related meta data
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})");

// Clear scheduled events
wp_clear_scheduled_hook('auto_generate_post_hook');

// Clear any cached data
wp_cache_flush();