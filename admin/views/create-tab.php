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
?>

<div class="create-post-tab">
    <h2><?php _e('Create New Post', MIAPG_TEXT_DOMAIN); ?></h2>
    
    <form method="post" id="create-post-form">
        <?php wp_nonce_field('create_post_now', 'create_post_nonce'); ?>
        
        <h3><?php _e('Post Details', MIAPG_TEXT_DOMAIN); ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Topic or Idea', MIAPG_TEXT_DOMAIN); ?></th>
                <td>
                    <input type="text" name="post_topic" required style="width: 100%;" 
                           placeholder="<?php _e('e.g. How to optimize WordPress for SEO', MIAPG_TEXT_DOMAIN); ?>"
                           value="<?php echo isset($_GET['idea_id']) ? esc_attr(get_the_title($_GET['idea_id'])) : ''; ?>" />
                    <?php if (isset($_GET['idea_id'])): ?>
                        <input type="hidden" name="idea_id" value="<?php echo esc_attr($_GET['idea_id']); ?>" />
                    <?php endif; ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Main Keyword', MIAPG_TEXT_DOMAIN); ?></th>
                <td>
                    <input type="text" name="post_keyword" style="width: 100%;" 
                           placeholder="<?php _e('e.g. SEO optimization, WordPress', MIAPG_TEXT_DOMAIN); ?>"
                           value="<?php echo isset($_GET['idea_id']) ? esc_attr(get_post_meta($_GET['idea_id'], '_miapg_idea_keyword', true)) : ''; ?>" />
                    <p class="description"><?php _e('Main keyword to focus the content on', MIAPG_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Category', MIAPG_TEXT_DOMAIN); ?></th>
                <td>
                    <select name="post_category">
                        <option value=""><?php _e('Select Category', MIAPG_TEXT_DOMAIN); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>" 
                                    <?php selected(Miapg_Settings::get_setting('auto_post_category'), $category->term_id); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Tags', MIAPG_TEXT_DOMAIN); ?></th>
                <td>
                    <input type="text" name="post_tags" style="width: 100%;" 
                           placeholder="<?php _e('tag1, tag2, tag3', MIAPG_TEXT_DOMAIN); ?>"
                           value="<?php echo esc_attr(Miapg_Settings::get_setting('auto_post_tags')); ?>" />
                    <p class="description"><?php _e('Separate tags with commas', MIAPG_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Post Status', MIAPG_TEXT_DOMAIN); ?></th>
                <td>
                    <select name="post_status">
                        <option value="publish" <?php selected(Miapg_Settings::get_setting('auto_post_status'), 'publish'); ?>>
                            <?php _e('Publish', MIAPG_TEXT_DOMAIN); ?>
                        </option>
                        <option value="draft" <?php selected(Miapg_Settings::get_setting('auto_post_status'), 'draft'); ?>>
                            <?php _e('Draft', MIAPG_TEXT_DOMAIN); ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Schedule Date', MIAPG_TEXT_DOMAIN); ?></th>
                <td>
                    <input type="datetime-local" name="schedule_date" />
                    <p class="description"><?php _e('Leave empty to publish immediately', MIAPG_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
        </table>
        
        <h3><?php _e('Content Based on Article', MIAPG_TEXT_DOMAIN); ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Reference Article', MIAPG_TEXT_DOMAIN); ?></th>
                <td>
                    <textarea name="reference_article" rows="8" cols="50" 
                              placeholder="<?php _e('Paste an article to base the content on (optional)', MIAPG_TEXT_DOMAIN); ?>" 
                              style="width: 100%;"></textarea>
                    <p class="description"><?php _e('If provided, the post will be based on this article while being unique', MIAPG_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="create_now" class="button button-primary button-large">
                ✨ <?php _e('Create Post Now', MIAPG_TEXT_DOMAIN); ?>
            </button>
        </p>
    </form>
</div>