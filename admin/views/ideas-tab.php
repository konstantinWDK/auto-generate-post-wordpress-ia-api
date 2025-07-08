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
    'post_type' => 'post_idea',
    'numberposts' => 20,
    'post_status' => 'publish'
));
?>

<div class="ideas-management">
    <h2><?php _e('Generate New Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></h2>
    
    <div class="ideas-generator">
        <form method="post" id="ideas-generator-form">
            <?php wp_nonce_field('generate_ideas', 'generate_ideas_nonce'); ?>
            
            <h3><?php _e('Generate Ideas from Topic', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Main Topic', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    <td>
                        <input type="text" name="main_topic" required style="width: 100%;" 
                               placeholder="<?php _e('e.g. Digital Marketing, WordPress, SEO', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Number of Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    <td>
                        <input type="number" name="num_ideas" value="5" min="1" max="20" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Content Type', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    <td>
                        <select name="content_type">
                            <option value="general"><?php _e('General post ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                            <option value="tutorial"><?php _e('Step-by-step tutorials', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                            <option value="list"><?php _e('Lists and compilations', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                            <option value="comparison"><?php _e('Comparisons and reviews', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                            <option value="news"><?php _e('News and updates', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="generate_ideas" id="generate-ideas-btn" class="button button-primary">
                    🚀 <?php _e('Generate and Save Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                </button>
            </p>
        </form>
    </div>
    
    <div class="ideas-from-article">
        <h3><?php _e('Generate Ideas from Article', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></h3>
        <form method="post" id="ideas-from-article-form">
            <?php wp_nonce_field('generate_ideas_from_article', 'generate_ideas_from_article_nonce'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Reference Article', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    <td>
                        <textarea name="reference_article" rows="8" cols="50" required 
                                  placeholder="<?php _e('Paste the article content here...', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>" 
                                  style="width: 100%;"></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Number of Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    <td>
                        <input type="number" name="num_ideas_article" value="5" min="1" max="20" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Generation Type', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    <td>
                        <select name="generation_type">
                            <option value="related"><?php _e('Related and complementary topics', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                            <option value="expanded"><?php _e('Expanded and deeper concepts', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                            <option value="alternative"><?php _e('Alternative approaches and perspectives', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                            <option value="practical"><?php _e('Practical applications and use cases', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="generate_ideas_from_article" class="button button-primary">
                    📝 <?php _e('Generate Ideas from Article', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                </button>
            </p>
        </form>
    </div>
    
    <hr />
    
    <h2><?php _e('Saved Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?> (<?php echo count($ideas); ?>)</h2>
    
    <?php if (!empty($ideas)): ?>
        <div class="ideas-list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Idea Title', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Original Topic', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Keyword', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Generated Date', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Actions', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ideas as $idea): ?>
                        <tr>
                            <td><strong><?php echo esc_html($idea->post_title); ?></strong></td>
                            <td><?php echo esc_html(get_post_meta($idea->ID, '_post_idea_topic', true) ?: __('Not defined', AUTO_POST_GENERATOR_TEXT_DOMAIN)); ?></td>
                            <td><?php echo esc_html(get_post_meta($idea->ID, '_post_idea_keyword', true) ?: __('Not defined', AUTO_POST_GENERATOR_TEXT_DOMAIN)); ?></td>
                            <td><?php echo esc_html(get_the_date('', $idea->ID)); ?></td>
                            <td>
                                <a href="<?php echo admin_url('post.php?post=' . $idea->ID . '&action=edit'); ?>" 
                                   class="button button-small">
                                    <?php _e('Edit', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=auto-post-generator&tab=create&idea_id=' . $idea->ID); ?>" 
                                   class="button button-small button-primary">
                                    <?php _e('Generate Post', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=auto-post-generator&tab=ideas&action=delete&idea_id=' . $idea->ID), 'delete_idea_' . $idea->ID); ?>" 
                                   class="button button-small delete-idea"
                                   onclick="return confirm('<?php _e('Are you sure you want to delete this idea?', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>');">
                                    <?php _e('Delete', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('No ideas found. Generate some ideas using the form above.', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></p>
        </div>
    <?php endif; ?>
    
    <p>
        <a href="<?php echo admin_url('edit.php?post_type=post_idea'); ?>" class="button">
            <?php _e('View All Ideas', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?>
        </a>
    </p>
</div>