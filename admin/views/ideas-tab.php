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
    <h2><?php esc_html_e('Generate New Ideas', 'miapg-post-generator'); ?></h2>
    
    <div class="ideas-generator">
        <form method="post" id="ideas-generator-form">
            <?php wp_nonce_field('generate_ideas', 'generate_ideas_nonce'); ?>
            
            <h3><?php esc_html_e('Generate Ideas from Topic', 'miapg-post-generator'); ?></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Main Topic', 'miapg-post-generator'); ?></th>
                    <td>
                        <input type="text" name="main_topic" required style="width: 100%;" 
                               placeholder="<?php esc_attr_e('e.g. Digital Marketing, WordPress, SEO', 'miapg-post-generator'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Number of Ideas', 'miapg-post-generator'); ?></th>
                    <td>
                        <input type="number" name="num_ideas" value="5" min="1" max="20" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Content Type', 'miapg-post-generator'); ?></th>
                    <td>
                        <select name="content_type">
                            <option value="general"><?php esc_html_e('General post ideas', 'miapg-post-generator'); ?></option>
                            <option value="tutorial"><?php esc_html_e('Step-by-step tutorials', 'miapg-post-generator'); ?></option>
                            <option value="list"><?php esc_html_e('Lists and compilations', 'miapg-post-generator'); ?></option>
                            <option value="comparison"><?php esc_html_e('Comparisons and reviews', 'miapg-post-generator'); ?></option>
                            <option value="news"><?php esc_html_e('News and updates', 'miapg-post-generator'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="generate_ideas" id="generate-ideas-btn" class="button button-primary">
                    🚀 <?php esc_html_e('Generate and Save Ideas', 'miapg-post-generator'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <div class="ideas-from-article">
        <h3><?php esc_html_e('Generate Ideas from Article', 'miapg-post-generator'); ?></h3>
        <form method="post" id="ideas-from-article-form">
            <?php wp_nonce_field('generate_ideas_from_article', 'generate_ideas_from_article_nonce'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Reference Article', 'miapg-post-generator'); ?></th>
                    <td>
                        <textarea name="reference_article" rows="8" cols="50" required 
                                  placeholder="<?php esc_attr_e('Paste the article content here...', 'miapg-post-generator'); ?>" 
                                  style="width: 100%;"></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Number of Ideas', 'miapg-post-generator'); ?></th>
                    <td>
                        <input type="number" name="num_ideas_article" value="5" min="1" max="20" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Generation Type', 'miapg-post-generator'); ?></th>
                    <td>
                        <select name="generation_type">
                            <option value="related"><?php esc_html_e('Related and complementary topics', 'miapg-post-generator'); ?></option>
                            <option value="expanded"><?php esc_html_e('Expanded and deeper concepts', 'miapg-post-generator'); ?></option>
                            <option value="alternative"><?php esc_html_e('Alternative approaches and perspectives', 'miapg-post-generator'); ?></option>
                            <option value="practical"><?php esc_html_e('Practical applications and use cases', 'miapg-post-generator'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="generate_ideas_from_article" class="button button-primary">
                    📝 <?php esc_html_e('Generate Ideas from Article', 'miapg-post-generator'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <hr />
    
    <h2><?php esc_html_e('Saved Ideas', 'miapg-post-generator'); ?> (<span class="ideas-count"><?php echo esc_html(count($ideas)); ?></span>)</h2>
    
    <?php if (!empty($ideas)): ?>
        <div class="ideas-list">
            <!-- Bulk Actions -->
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e('Select bulk action', 'miapg-post-generator'); ?></label>
                    <select name="action" id="bulk-action-selector-top">
                        <option value="-1"><?php esc_html_e('Bulk Actions', 'miapg-post-generator'); ?></option>
                        <option value="bulk_delete_selected"><?php esc_html_e('Delete Selected', 'miapg-post-generator'); ?></option>
                        <option value="bulk_generate_posts"><?php esc_html_e('Generate Posts', 'miapg-post-generator'); ?></option>
                        <option value="bulk_add_keyword"><?php esc_html_e('Add Keyword', 'miapg-post-generator'); ?></option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="<?php esc_attr_e('Apply', 'miapg-post-generator'); ?>">
                </div>
                <div class="alignright">
                    <span class="displaying-num"><?php 
                    // translators: %s is the number of items
                    printf(esc_html(_n('%s item', '%s items', count($ideas), 'miapg-post-generator')), count($ideas)); ?></span>
                </div>
            </div>
            
            <form id="ideas-bulk-form" method="post">
                <?php wp_nonce_field('bulk_ideas_action', 'bulk_ideas_nonce'); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e('Select All', 'miapg-post-generator'); ?></label>
                                <input id="cb-select-all-1" type="checkbox" />
                            </td>
                            <th><?php esc_html_e('Idea Title', 'miapg-post-generator'); ?></th>
                            <th><?php esc_html_e('Original Topic', 'miapg-post-generator'); ?></th>
                            <th><?php esc_html_e('Keyword', 'miapg-post-generator'); ?></th>
                            <th><?php esc_html_e('Generated Date', 'miapg-post-generator'); ?></th>
                            <th><?php esc_html_e('Actions', 'miapg-post-generator'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ideas as $idea): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <label class="screen-reader-text" for="cb-select-<?php echo esc_attr($idea->ID); ?>"><?php 
                                    // translators: %s is the idea title
                                    printf(esc_html__('Select %s', 'miapg-post-generator'), esc_html($idea->post_title)); ?></label>
                                    <input id="cb-select-<?php echo esc_attr($idea->ID); ?>" type="checkbox" name="idea_ids[]" value="<?php echo esc_attr($idea->ID); ?>" />
                                </th>
                                <td><strong><?php echo esc_html($idea->post_title); ?></strong></td>
                                <td><?php 
                                    $topic = get_post_meta($idea->ID, '_miapg_idea_topic', true);
                                    if ($topic) {
                                        echo esc_html(Miapg_Ideas_Generator::format_topic_text($topic));
                                    } else {
                                        echo esc_html__('Not defined', 'miapg-post-generator');
                                    }
                                ?></td>
                                <td><?php echo esc_html(get_post_meta($idea->ID, '_miapg_idea_keyword', true) ?: esc_html__('Not defined', 'miapg-post-generator')); ?></td>
                                <td><?php echo esc_html(get_the_date('', $idea->ID)); ?></td>
                                <td>
                                    <div class="idea-actions">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $idea->ID . '&action=edit')); ?>" 
                                           class="button button-small idea-edit-btn" 
                                           title="<?php esc_attr_e('Edit this idea', 'miapg-post-generator'); ?>"
                                           target="_blank">
                                            📝 <?php esc_html_e('Edit', 'miapg-post-generator'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=miapg-post-generator&tab=create&idea_id=' . $idea->ID)); ?>" 
                                           class="button button-small button-primary idea-generate-btn"
                                           title="<?php esc_attr_e('Generate a post from this idea', 'miapg-post-generator'); ?>">
                                            🚀 <?php esc_html_e('Generate Post', 'miapg-post-generator'); ?>
                                        </a>
                                        <button type="button" class="button button-small button-secondary idea-delete-btn" 
                                               onclick="deleteIdea(<?php echo esc_js($idea->ID); ?>, '<?php echo esc_js($idea->post_title); ?>')"
                                               title="<?php esc_attr_e('Delete this idea', 'miapg-post-generator'); ?>">
                                            🗑️ <?php esc_html_e('Delete', 'miapg-post-generator'); ?>
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
            <p><?php esc_html_e('No ideas found. Generate some ideas using the form above.', 'miapg-post-generator'); ?></p>
        </div>
    <?php endif; ?>
    
    <p>
        <a href="<?php echo esc_url(admin_url('edit.php?post_type=miapg_post_idea')); ?>" class="button">
            <?php esc_html_e('View All Ideas', 'miapg-post-generator'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=miapg-ideas-manager')); ?>" class="button button-primary">
            <?php esc_html_e('Advanced Ideas Manager', 'miapg-post-generator'); ?>
        </a>
    </p>
</div>

<script>
function deleteIdea(ideaId, ideaTitle) {
    if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this idea?', 'miapg-post-generator')); ?> "' + ideaTitle + '"')) {
        return;
    }
    
    jQuery.ajax({
        url: miapgAdmin.ajaxurl,
        type: 'POST',
        data: {
            action: 'delete_idea',
            idea_id: ideaId,
            nonce: miapgAdmin.nonce
        },
        success: function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload(); // Refresh the page to show updated list
            } else {
                alert('Error: ' + response.data.message);
            }
        },
        error: function() {
            alert('<?php echo esc_js(__('Error deleting idea. Please try again.', 'miapg-post-generator')); ?>');
        }
    });
}

// Handle bulk actions
jQuery(document).ready(function($) {
    $('#doaction').click(function(e) {
        e.preventDefault();
        
        var action = $('#bulk-action-selector-top').val();
        var selectedIds = [];
        
        $('input[name="idea_ids[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (action === '-1') {
            alert('<?php echo esc_js(__('Please select an action.', 'miapg-post-generator')); ?>');
            return;
        }
        
        if (selectedIds.length === 0) {
            alert('<?php echo esc_js(__('Please select at least one idea.', 'miapg-post-generator')); ?>');
            return;
        }
        
        var keyword = '';
        if (action === 'bulk_add_keyword') {
            keyword = prompt('<?php echo esc_js(__('Enter keyword to add to selected ideas:', 'miapg-post-generator')); ?>');
            if (!keyword) {
                return;
            }
        }
        
        if (action === 'bulk_delete_selected' && !confirm('<?php echo esc_js(__('Are you sure you want to delete the selected ideas?', 'miapg-post-generator')); ?>')) {
            return;
        }
        
        $.ajax({
            url: miapgAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'bulk_ideas_action',
                bulk_action: action,
                idea_ids: selectedIds,
                keyword: keyword,
                nonce: miapgAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Error processing bulk action. Please try again.', 'miapg-post-generator')); ?>');
            }
        });
    });
    
    // Select all checkbox
    $('#cb-select-all-1').change(function() {
        $('input[name="idea_ids[]"]').prop('checked', this.checked);
    });
});
</script>
