<?php
/**
 * Admin pages class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Admin_Pages {
    
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
     * Render main admin page
     */
    public function render_main_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(__('You do not have sufficient permissions to access this page.', 'miapg-post-generator')));
        }
        
        // Check if settings were updated (WordPress admin standard pattern)
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['settings-updated'])) {
            add_settings_error('miapg_messages', 'miapg_message', miapg_translate('Settings saved'), 'updated');
        }
        
        // Show error/update messages
        settings_errors('miapg_messages');
        
        // Get active tab (WordPress admin standard pattern)
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $requested_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general';
        
        // Validate tab against allowed tabs (security enhancement)
        $allowed_tabs = array('general', 'ai', 'content', 'scheduling', 'ideas', 'create');
        $active_tab = in_array($requested_tab, $allowed_tabs, true) ? $requested_tab : 'general';
        
        // Handle form submissions
        $this->handle_form_submissions($active_tab);
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <!-- Navigation tabs -->
            <nav class="nav-tab-wrapper">
                <a href="?page=miapg-post-generator&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(miapg_translate('General Settings')); ?>
                </a>
                <a href="?page=miapg-post-generator&tab=ai" class="nav-tab <?php echo $active_tab == 'ai' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(miapg_translate('AI Settings')); ?>
                </a>
                <a href="?page=miapg-post-generator&tab=content" class="nav-tab <?php echo $active_tab == 'content' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(miapg_translate('Content Settings')); ?>
                </a>
                <a href="?page=miapg-post-generator&tab=scheduling" class="nav-tab <?php echo $active_tab == 'scheduling' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(miapg_translate('Scheduling')); ?>
                </a>
                <a href="?page=miapg-post-generator&tab=ideas" class="nav-tab <?php echo $active_tab == 'ideas' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(miapg_translate('Post Ideas')); ?>
                </a>
                <a href="?page=miapg-post-generator&tab=create" class="nav-tab <?php echo $active_tab == 'create' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(miapg_translate('Create Post')); ?>
                </a>
            </nav>
            
            <!-- Tab content -->
            <div class="apg-tab-content <?php echo $active_tab == 'general' ? 'active' : ''; ?>" data-tab="general">
                <?php $this->render_general_tab(); ?>
            </div>
            
            <div class="apg-tab-content <?php echo $active_tab == 'ai' ? 'active' : ''; ?>" data-tab="ai">
                <?php $this->render_ai_tab(); ?>
            </div>
            
            <div class="apg-tab-content <?php echo $active_tab == 'content' ? 'active' : ''; ?>" data-tab="content">
                <?php $this->render_content_tab(); ?>
            </div>
            
            <div class="apg-tab-content <?php echo $active_tab == 'scheduling' ? 'active' : ''; ?>" data-tab="scheduling">
                <?php $this->render_scheduling_tab(); ?>
            </div>
            
            <div class="apg-tab-content <?php echo $active_tab == 'ideas' ? 'active' : ''; ?>" data-tab="ideas">
                <?php $this->render_ideas_tab(); ?>
            </div>
            
            <div class="apg-tab-content <?php echo $active_tab == 'create' ? 'active' : ''; ?>" data-tab="create">
                <?php $this->render_create_tab(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle form submissions
     */
    private function handle_form_submissions($active_tab) {
        // Handle idea generation
        if (isset($_POST['generate_ideas']) && isset($_POST['generate_ideas_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['generate_ideas_nonce'])), 'generate_ideas')) {
            // Form data is safe to access after nonce verification
            $main_topic = isset($_POST['main_topic']) ? sanitize_text_field(wp_unslash($_POST['main_topic'])) : '';
            $ideas_count = isset($_POST['num_ideas']) ? absint($_POST['num_ideas']) : 5;
            $content_type = isset($_POST['content_type']) ? sanitize_text_field(wp_unslash($_POST['content_type'])) : 'general';
            
            if ($main_topic) {
                $result = Miapg_Ideas_Generator::generate_post_ideas($main_topic, $ideas_count, $content_type);
                if ($result) {
                    echo '<div class="notice notice-success"><p>' . wp_kses_post($result) . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>' . esc_html(miapg_translate('Error generating ideas. Please check your API configuration.')) . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-warning"><p>' . esc_html(miapg_translate('Please enter a main topic.')) . '</p></div>';
            }
        }
        
        // Handle article-based idea generation
        if (isset($_POST['generate_ideas_from_article']) && isset($_POST['generate_ideas_from_article_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['generate_ideas_from_article_nonce'])), 'generate_ideas_from_article')) {
            // Form data is safe to access after nonce verification
            $reference_article = isset($_POST['reference_article']) ? sanitize_textarea_field(wp_unslash($_POST['reference_article'])) : '';
            $ideas_count = isset($_POST['num_ideas_article']) ? absint($_POST['num_ideas_article']) : 5;
            $approach = isset($_POST['generation_type']) ? sanitize_text_field(wp_unslash($_POST['generation_type'])) : 'related';
            
            if ($reference_article) {
                $result = Miapg_Ideas_Generator::generate_ideas_from_article($reference_article, $ideas_count, $approach);
                if ($result) {
                    echo '<div class="notice notice-success"><p>' . wp_kses_post($result) . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>' . esc_html(miapg_translate('Error generating ideas from article. Please check your API configuration.')) . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-warning"><p>' . esc_html(miapg_translate('Please enter reference article content.')) . '</p></div>';
            }
        }
        
        // Handle post creation
        if (isset($_POST['create_now']) && isset($_POST['create_post_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['create_post_nonce'])), 'create_post_now')) {
            // Form data is safe to access after nonce verification
            // Handle idea-based creation (nonce already verified above for form submission)
            $idea_id = isset($_GET['idea_id']) ? absint(wp_unslash($_GET['idea_id'])) : 0;
            $idea_post = null;
            $idea_keyword = '';
            
            if ($idea_id) {
                // Additional security check: verify nonce for idea access
                if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'create_from_idea_' . $idea_id)) {
                    echo '<div class="notice notice-error"><p>' . esc_html(miapg_translate('Security check failed when accessing idea.')) . '</p></div>';
                    $idea_id = 0;
                } else {
                    $idea_post = get_post($idea_id);
                    if ($idea_post && $idea_post->post_type === 'miapg_post_idea') {
                        $idea_keyword = get_post_meta($idea_id, '_miapg_idea_keyword', true);
                    } else {
                        $idea_id = 0; // Reset if invalid post
                    }
                }
            }
            
            // Get form data (nonce already verified above)
            $prompt = !empty($_POST['custom_prompt']) ? sanitize_textarea_field(wp_unslash($_POST['custom_prompt'])) : Miapg_Settings::get_setting('miapg_post_prompt', 'Write a post about a relevant topic.');
            $category_id = !empty($_POST['category_custom']) ? absint(wp_unslash($_POST['category_custom'])) : Miapg_Settings::get_setting('miapg_post_category', 1);
            $tags = explode(',', Miapg_Settings::get_setting('miapg_post_tags', ''));
            $post_status = !empty($_POST['post_status_custom']) ? sanitize_text_field(wp_unslash($_POST['post_status_custom'])) : Miapg_Settings::get_setting('miapg_post_status', 'publish');
            $word_count = Miapg_Settings::get_setting('miapg_post_word_count', '500');
            $post_date = isset($_POST['post_date']) ? sanitize_text_field(wp_unslash($_POST['post_date'])) : current_time('mysql');
            $ai_provider = !empty($_POST['ai_provider_custom']) ? sanitize_text_field(wp_unslash($_POST['ai_provider_custom'])) : Miapg_Settings::get_setting('miapg_ai_provider', 'openai');
            $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
            $source_article = isset($_POST['source_article']) ? sanitize_textarea_field(wp_unslash($_POST['source_article'])) : '';
            
            // If generating from idea, use idea's title and keyword
            if ($idea_id && $idea_post) {
                $prompt = $idea_post->post_title;
                if (!$keyword && $idea_keyword) {
                    $keyword = $idea_keyword;
                }
            }
            
            // Generate post
            $message = Miapg_Post_Generator::generate_and_publish_post(
                $prompt,
                $category_id,
                $tags,
                $post_status,
                $post_date,
                $word_count,
                $ai_provider,
                array(),
                $keyword,
                $source_article,
                ($idea_id && $idea_post) // Pass true if generating from idea
            );
            
            echo '<div class="notice notice-info"><p>' . esc_html($message) . '</p></div>';
            
            // Show option to delete used idea
            if ($idea_id && $idea_post) {
                echo '<div class="notice notice-warning">';
                echo '<p>' . esc_html(miapg_translate('Post generated from idea. '));
                $delete_url = wp_nonce_url(admin_url('admin.php?page=miapg-post-generator&tab=create&delete_idea=' . $idea_id), 'delete_idea_' . $idea_id);
                echo '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'' . esc_js(miapg_translate('Do you want to delete this idea since it has been used to generate the post?')) . '\')" class="button button-small">' . esc_html(miapg_translate('Delete Used Idea')) . '</a>';
                echo '</p>';
                echo '</div>';
            }
        }
        
        // Handle idea deletion with proper security checks
        if (isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'delete') {
            // First verify we have all required parameters
            if (!isset($_GET['idea_id']) || !isset($_GET['_wpnonce'])) {
                wp_die(esc_html__('Missing required parameters for deletion.', 'miapg-post-generator'));
            }
            
            $delete_id = absint(wp_unslash($_GET['idea_id']));
            
            // Verify nonce separately to avoid bypass
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'delete_idea_' . $delete_id)) {
                wp_die(esc_html__('Security check failed.', 'miapg-post-generator'));
            }
            
            // Verify user permissions
            if (!current_user_can('delete_miapg_post_ideas')) {
                wp_die(esc_html__('You do not have permission to delete ideas.', 'miapg-post-generator'));
            }
            
            if (wp_delete_post($delete_id, true)) {
                echo '<div class="notice notice-success"><p>' . esc_html(miapg_translate('Idea deleted successfully.')) . '</p></div>';
                
                // Enqueue page redirecter script
                wp_enqueue_script(
                    'miapg-page-redirecter',
                    MIAPG_PLUGIN_URL . 'assets/js/page-redirecter.js',
                    array(),
                    MIAPG_VERSION,
                    true
                );
                
                // Localize script with redirect URL
                wp_localize_script(
                    'miapg-page-redirecter',
                    'miapgPageRedirect',
                    array(
                        'redirectUrl' => admin_url('admin.php?page=miapg-post-generator&tab=ideas')
                    )
                );
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html(miapg_translate('Error deleting idea.')) . '</p></div>';
            }
        }
    }
    
    
    
    
    /**
     * Render general tab
     */
    private function render_general_tab() {
        ?>
        <form method="post" action="options.php">
            <?php wp_nonce_field('miapg_general_settings_action', 'miapg_general_nonce'); ?>
            <input type="hidden" name="option_page" value="miapg_settings_group" />
            <input type="hidden" name="action" value="update" />
            <?php
            settings_fields('miapg_settings_group');
            do_settings_sections('miapg_settings_group');
            ?>
            <h2><?php echo esc_html(miapg_translate('General Settings')); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('Interface Language')); ?></th>
                    <td>
                        <select name="miapg_interface_language">
                            <?php 
                            $languages = Miapg_Settings::get_available_languages();
                            $current_language = Miapg_Settings::get_interface_language();
                            foreach ($languages as $code => $name): ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($current_language, $code); ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo esc_html(miapg_translate('Select the language for the interface and content generation')); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('AI Provider')); ?></th>
                    <td>
                        <select name="miapg_ai_provider">
                            <option value="openai" <?php selected(Miapg_Settings::get_setting('miapg_ai_provider'), 'openai'); ?>>OpenAI</option>
                            <option value="deepseek" <?php selected(Miapg_Settings::get_setting('miapg_ai_provider'), 'deepseek'); ?>>DeepSeek</option>
                        </select>
                        <p class="description"><?php echo esc_html(miapg_translate('Select the AI provider you want to use')); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('OpenAI API Key')); ?></th>
                    <td>
                        <input type="password" name="miapg_openai_api_key" value="<?php echo esc_attr(Miapg_Settings::get_setting('miapg_openai_api_key')); ?>" style="width: 400px;" />
                        <p class="description"><?php echo esc_html(miapg_translate('Get your API key from')); ?> <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('DeepSeek API Key')); ?></th>
                    <td>
                        <input type="password" name="miapg_deepseek_api_key" value="<?php echo esc_attr(Miapg_Settings::get_setting('miapg_deepseek_api_key')); ?>" style="width: 400px;" />
                        <p class="description"><?php echo esc_html(miapg_translate('Get your API key from')); ?> <a href="https://platform.deepseek.com/api_keys" target="_blank">DeepSeek</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('Default Category')); ?></th>
                    <td>
                        <?php
                        $categories = get_categories(array('hide_empty' => false));
                        $selected_category = Miapg_Settings::get_setting('miapg_post_category', 1);
                        ?>
                        <select name="miapg_post_category">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($selected_category, $category->term_id); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('Tags (comma separated)')); ?></th>
                    <td><input type="text" name="miapg_post_tags" value="<?php echo esc_attr(Miapg_Settings::get_setting('miapg_post_tags', '')); ?>" style="width: 400px;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('Post Status')); ?></th>
                    <td>
                        <select name="miapg_post_status">
                            <option value="publish" <?php selected(Miapg_Settings::get_setting('miapg_post_status'), 'publish'); ?>><?php echo esc_html(miapg_translate('Publish')); ?></option>
                            <option value="draft" <?php selected(Miapg_Settings::get_setting('miapg_post_status'), 'draft'); ?>><?php echo esc_html(miapg_translate('Draft')); ?></option>
                            <option value="future" <?php selected(Miapg_Settings::get_setting('miapg_post_status'), 'future'); ?>><?php echo esc_html(miapg_translate('Scheduled')); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Render AI tab
     */
    private function render_ai_tab() {
        ?>
        <form method="post" action="options.php">
            <?php wp_nonce_field('miapg_ai_settings_action', 'miapg_ai_nonce'); ?>
            <input type="hidden" name="option_page" value="miapg_ai_settings_group" />
            <input type="hidden" name="action" value="update" />
            <?php
            settings_fields('miapg_ai_settings_group');
            do_settings_sections('miapg_ai_settings_group');
            ?>
            <h2><?php echo esc_html(miapg_translate('AI Settings')); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('OpenAI Model')); ?></th>
                    <td>
                        <select name="miapg_openai_model">
                            <option value="gpt-4" <?php selected(Miapg_Settings::get_setting('miapg_openai_model'), 'gpt-4'); ?>>GPT-4</option>
                            <option value="gpt-4-turbo" <?php selected(Miapg_Settings::get_setting('miapg_openai_model'), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                            <option value="gpt-3.5-turbo" <?php selected(Miapg_Settings::get_setting('miapg_openai_model'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('DeepSeek Model')); ?></th>
                    <td>
                        <select name="miapg_deepseek_model">
                            <option value="deepseek-chat" <?php selected(Miapg_Settings::get_setting('miapg_deepseek_model'), 'deepseek-chat'); ?>>DeepSeek Chat</option>
                            <option value="deepseek-coder" <?php selected(Miapg_Settings::get_setting('miapg_deepseek_model'), 'deepseek-coder'); ?>>DeepSeek Coder</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('Temperature (0.0 - 2.0)')); ?></th>
                    <td>
                        <input type="number" name="miapg_ai_temperature" value="<?php echo esc_attr(Miapg_Settings::get_setting('miapg_ai_temperature', '0.7')); ?>" min="0" max="2" step="0.1" />
                        <p class="description"><?php echo esc_html(miapg_translate('Controls creativity. Higher values = more creativity')); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(miapg_translate('Max Tokens')); ?></th>
                    <td>
                        <input type="number" name="miapg_ai_max_tokens" value="<?php echo esc_attr(Miapg_Settings::get_setting('miapg_ai_max_tokens', '2000')); ?>" min="100" max="4000" />
                        <p class="description"><?php echo esc_html(miapg_translate('Maximum number of tokens in response')); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Render content tab
     */
    private function render_content_tab() {
        include dirname(__FILE__) . '/views/content-tab.php';
    }
    
    /**
     * Render scheduling tab
     */
    private function render_scheduling_tab() {
        include dirname(__FILE__) . '/views/scheduling-tab.php';
    }
    
    /**
     * Render ideas tab
     */
    private function render_ideas_tab() {
        include dirname(__FILE__) . '/views/ideas-tab.php';
    }
    
    /**
     * Render create tab
     */
    private function render_create_tab() {
        include dirname(__FILE__) . '/views/create-tab.php';
    }
}
