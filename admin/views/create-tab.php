<?php
/**
 * Create Post Tab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get categories for dropdown
$categories = get_categories();

// Safely get idea_id from URL if present (WordPress admin standard pattern)
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$idea_id = isset($_GET['idea_id']) ? absint(wp_unslash($_GET['idea_id'])) : 0;
$idea_title = '';
$idea_keyword = '';

if ($idea_id > 0) {
    $idea_post = get_post($idea_id);
    if ($idea_post && $idea_post->post_type === 'miapg_post_idea') {
        $idea_title = $idea_post->post_title;
        $idea_keyword = get_post_meta($idea_id, '_miapg_idea_keyword', true);
    } else {
        $idea_id = 0; // Reset if invalid post
    }
}
?>

<div class="create-post-tab">
    <h2><?php esc_html_e('Create New Post', 'miapg-post-generator'); ?></h2>
    
    <form method="post" id="create-post-form">
        <?php wp_nonce_field('create_post_now', 'create_post_nonce'); ?>
        
        <h3><?php esc_html_e('Post Details', 'miapg-post-generator'); ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Topic or Idea', 'miapg-post-generator'); ?></th>
                <td>
                    <input type="text" name="custom_prompt" required style="width: 100%;" 
                           placeholder="<?php esc_attr_e('e.g. How to optimize WordPress for SEO', 'miapg-post-generator'); ?>"
                           value="<?php echo esc_attr($idea_title); ?>" />
                    <?php if ($idea_id > 0): ?>
                        <input type="hidden" name="idea_id" value="<?php echo esc_attr($idea_id); ?>" />
                    <?php endif; ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Main Keyword', 'miapg-post-generator'); ?></th>
                <td>
                    <input type="text" name="keyword" style="width: 100%;" 
                           placeholder="<?php esc_attr_e('e.g. SEO optimization, WordPress', 'miapg-post-generator'); ?>"
                           value="<?php echo esc_attr($idea_keyword); ?>" />
                    <p class="description"><?php esc_html_e('Main keyword to focus the content on', 'miapg-post-generator'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Category', 'miapg-post-generator'); ?></th>
                <td>
                    <select name="category_custom">
                        <option value=""><?php esc_html_e('Select Category', 'miapg-post-generator'); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>" 
                                    <?php selected(Miapg_Settings::get_setting('miapg_post_category'), $category->term_id); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Tags', 'miapg-post-generator'); ?></th>
                <td>
                    <input type="text" name="post_tags" style="width: 100%;" 
                           placeholder="<?php esc_attr_e('tag1, tag2, tag3', 'miapg-post-generator'); ?>"
                           value="<?php echo esc_attr(Miapg_Settings::get_setting('miapg_post_tags')); ?>" />
                    <p class="description"><?php esc_html_e('Separate tags with commas', 'miapg-post-generator'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Post Status', 'miapg-post-generator'); ?></th>
                <td>
                    <select name="post_status_custom">
                        <option value="publish" <?php selected(Miapg_Settings::get_setting('miapg_post_status'), 'publish'); ?>>
                            <?php esc_html_e('Publish', 'miapg-post-generator'); ?>
                        </option>
                        <option value="draft" <?php selected(Miapg_Settings::get_setting('miapg_post_status'), 'draft'); ?>>
                            <?php esc_html_e('Draft', 'miapg-post-generator'); ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Schedule Date', 'miapg-post-generator'); ?></th>
                <td>
                    <input type="datetime-local" name="post_date" />
                    <p class="description"><?php esc_html_e('Leave empty to publish immediately', 'miapg-post-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <h3><?php esc_html_e('Content Based on Article', 'miapg-post-generator'); ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Reference Article', 'miapg-post-generator'); ?></th>
                <td>
                    <textarea name="source_article" rows="8" cols="50" 
                              placeholder="<?php esc_attr_e('Paste an article to base the content on (optional)', 'miapg-post-generator'); ?>" 
                              style="width: 100%;"></textarea>
                    <p class="description"><?php esc_html_e('If provided, the post will be based on this article while being unique', 'miapg-post-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="create_now" class="button button-primary button-large">
                ✨ <?php esc_html_e('Create Post Now', 'miapg-post-generator'); ?>
            </button>
        </p>
    </form>
</div>