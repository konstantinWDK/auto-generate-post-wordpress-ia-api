<?php
/**
 * Ideas Management Tab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get recent ideas
$ideas = get_posts(array(
    'post_type' => 'miapg_post_idea',
    'numberposts' => 20,
    'post_status' => 'publish'
));
?>

<div class="ideas-management">
    <h2><?php _e('Generate New Ideas', MIAPG_TEXT_DOMAIN); ?></h2>
    
    <div class="ideas-generator">
        <form method="post" id="ideas-generator-form">
            <?php wp_nonce_field('generate_ideas', 'generate_ideas_nonce'); ?>
            
            <h3><?php _e('Generate Ideas from Topic', MIAPG_TEXT_DOMAIN); ?></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Main Topic', MIAPG_TEXT_DOMAIN); ?></th>
                    <td>
                        <input type="text" name="main_topic" required style="width: 100%;" 
                               placeholder="<?php _e('e.g. Digital Marketing, WordPress, SEO', MIAPG_TEXT_DOMAIN); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Number of Ideas', MIAPG_TEXT_DOMAIN); ?></th>
                    <td>
                        <input type="number" name="num_ideas" value="5" min="1" max="20" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Content Type', MIAPG_TEXT_DOMAIN); ?></th>
                    <td>
                        <select name="content_type">
                            <option value="general"><?php _e('General post ideas', MIAPG_TEXT_DOMAIN); ?></option>
                            <option value="tutorial"><?php _e('Step-by-step tutorials', MIAPG_TEXT_DOMAIN); ?></option>
                            <option value="list"><?php _e('Lists and compilations', MIAPG_TEXT_DOMAIN); ?></option>
                            <option value="comparison"><?php _e('Comparisons and reviews', MIAPG_TEXT_DOMAIN); ?></option>
                            <option value="news"><?php _e('News and updates', MIAPG_TEXT_DOMAIN); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="generate_ideas" id="generate-ideas-btn" class="button button-primary">
                    🚀 <?php _e('Generate and Save Ideas', MIAPG_TEXT_DOMAIN); ?>
                </button>
            </p>
        </form>
    </div>
    
    <div class="ideas-from-article">
        <h3><?php _e('Generate Ideas from Article', MIAPG_TEXT_DOMAIN); ?></h3>
        <form method="post" id="ideas-from-article-form">
            <?php wp_nonce_field('generate_ideas_from_article', 'generate_ideas_from_article_nonce'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Reference Article', MIAPG_TEXT_DOMAIN); ?></th>
                    <td>
                        <textarea name="reference_article" rows="8" cols="50" required 
                                  placeholder="<?php _e('Paste the article content here...', MIAPG_TEXT_DOMAIN); ?>" 
                                  style="width: 100%;"></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Number of Ideas', MIAPG_TEXT_DOMAIN); ?></th>
                    <td>
                        <input type="number" name="num_ideas_article" value="5" min="1" max="20" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Generation Type', MIAPG_TEXT_DOMAIN); ?></th>
                    <td>
                        <select name="generation_type">
                            <option value="related"><?php _e('Related and complementary topics', MIAPG_TEXT_DOMAIN); ?></option>
                            <option value="expanded"><?php _e('Expanded and deeper concepts', MIAPG_TEXT_DOMAIN); ?></option>
                            <option value="alternative"><?php _e('Alternative approaches and perspectives', MIAPG_TEXT_DOMAIN); ?></option>
                            <option value="practical"><?php _e('Practical applications and use cases', MIAPG_TEXT_DOMAIN); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="generate_ideas_from_article" class="button button-primary">
                    📝 <?php _e('Generate Ideas from Article', MIAPG_TEXT_DOMAIN); ?>
                </button>
            </p>
        </form>
    </div>
    
    <hr />
    
    <h2><?php _e('Saved Ideas', MIAPG_TEXT_DOMAIN); ?> (<span class="ideas-count"><?php echo count($ideas); ?></span>)</h2>
    
    <?php if (!empty($ideas)): ?>
        <div class="ideas-list">
            <!-- Bulk Actions -->
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e('Select bulk action', MIAPG_TEXT_DOMAIN); ?></label>
                    <select name="action" id="bulk-action-selector-top">
                        <option value="-1"><?php _e('Bulk Actions', MIAPG_TEXT_DOMAIN); ?></option>
                        <option value="bulk_delete_selected"><?php _e('Delete Selected', MIAPG_TEXT_DOMAIN); ?></option>
                        <option value="bulk_generate_posts"><?php _e('Generate Posts', MIAPG_TEXT_DOMAIN); ?></option>
                        <option value="bulk_add_keyword"><?php _e('Add Keyword', MIAPG_TEXT_DOMAIN); ?></option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="<?php _e('Apply', MIAPG_TEXT_DOMAIN); ?>">
                </div>
                <div class="alignright">
                    <span class="displaying-num"><?php printf(_n('%s item', '%s items', count($ideas), MIAPG_TEXT_DOMAIN), count($ideas)); ?></span>
                </div>
            </div>
            
            <form id="ideas-bulk-form" method="post">
                <?php wp_nonce_field('bulk_ideas_action', 'bulk_ideas_nonce'); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All', MIAPG_TEXT_DOMAIN); ?></label>
                                <input id="cb-select-all-1" type="checkbox" />
                            </td>
                            <th><?php _e('Idea Title', MIAPG_TEXT_DOMAIN); ?></th>
                            <th><?php _e('Original Topic', MIAPG_TEXT_DOMAIN); ?></th>
                            <th><?php _e('Keyword', MIAPG_TEXT_DOMAIN); ?></th>
                            <th><?php _e('Generated Date', MIAPG_TEXT_DOMAIN); ?></th>
                            <th><?php _e('Actions', MIAPG_TEXT_DOMAIN); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ideas as $idea): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <label class="screen-reader-text" for="cb-select-<?php echo $idea->ID; ?>"><?php printf(__('Select %s', MIAPG_TEXT_DOMAIN), $idea->post_title); ?></label>
                                    <input id="cb-select-<?php echo $idea->ID; ?>" type="checkbox" name="idea_ids[]" value="<?php echo $idea->ID; ?>" />
                                </th>
                                <td><strong><?php echo esc_html($idea->post_title); ?></strong></td>
                            <td><?php echo esc_html(get_post_meta($idea->ID, '_miapg_idea_topic', true) ?: __('Not defined', MIAPG_TEXT_DOMAIN)); ?></td>
                            <td><?php echo esc_html(get_post_meta($idea->ID, '_miapg_idea_keyword', true) ?: __('Not defined', MIAPG_TEXT_DOMAIN)); ?></td>
                            <td><?php echo esc_html(get_the_date('', $idea->ID)); ?></td>
                            <td>
                                <div class="idea-actions">
                                    <a href="<?php echo admin_url('post.php?post=' . $idea->ID . '&action=edit'); ?>" 
                                       class="button button-small idea-edit-btn" 
                                       title="<?php _e('Edit this idea', MIAPG_TEXT_DOMAIN); ?>">
                                        📝 <?php _e('Edit', MIAPG_TEXT_DOMAIN); ?>
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $idea->ID); ?>" 
                                       class="button button-small button-primary idea-generate-btn"
                                       title="<?php _e('Generate a post from this idea', MIAPG_TEXT_DOMAIN); ?>">
                                        🚀 <?php _e('Generate Post', MIAPG_TEXT_DOMAIN); ?>
                                    </a>
                                    <button class="button button-small button-secondary idea-delete-btn" 
                                            data-idea-id="<?php echo $idea->ID; ?>" 
                                            data-idea-title="<?php echo esc_attr($idea->post_title); ?>"
                                            title="<?php _e('Delete this idea', MIAPG_TEXT_DOMAIN); ?>">
                                        🗑️ <?php _e('Delete', MIAPG_TEXT_DOMAIN); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('No ideas found. Generate some ideas using the form above.', MIAPG_TEXT_DOMAIN); ?></p>
        </div>
    <?php endif; ?>
    
    <p>
        <a href="<?php echo admin_url('edit.php?post_type=miapg_post_idea'); ?>" class="button">
            <?php _e('View All Ideas', MIAPG_TEXT_DOMAIN); ?>
        </a>
    </p>
</div>