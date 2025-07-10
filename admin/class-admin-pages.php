<?php
/**
 * Admin pages class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Auto_Post_Generator_Admin_Pages {
    
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
            wp_die(__('You do not have sufficient permissions to access this page.', AUTO_POST_GENERATOR_TEXT_DOMAIN));
        }
        
        // Check if settings were updated
        if (isset($_GET['settings-updated'])) {
            add_settings_error('auto_post_generator_messages', 'auto_post_generator_message', apg_translate('Settings saved'), 'updated');
        }
        
        // Show error/update messages
        settings_errors('auto_post_generator_messages');
        
        // Get active tab
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        
        // Handle form submissions
        $this->handle_form_submissions($active_tab);
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <!-- Navigation tabs -->
            <nav class="nav-tab-wrapper">
                <a href="?page=auto-post-generator&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(apg_translate('General Settings')); ?>
                </a>
                <a href="?page=auto-post-generator&tab=ai" class="nav-tab <?php echo $active_tab == 'ai' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(apg_translate('AI Settings')); ?>
                </a>
                <a href="?page=auto-post-generator&tab=content" class="nav-tab <?php echo $active_tab == 'content' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(apg_translate('Content Settings')); ?>
                </a>
                <a href="?page=auto-post-generator&tab=scheduling" class="nav-tab <?php echo $active_tab == 'scheduling' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(apg_translate('Scheduling')); ?>
                </a>
                <a href="?page=auto-post-generator&tab=ideas" class="nav-tab <?php echo $active_tab == 'ideas' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(apg_translate('Post Ideas')); ?>
                </a>
                <a href="?page=auto-post-generator&tab=create" class="nav-tab <?php echo $active_tab == 'create' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html(apg_translate('Create Post')); ?>
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
        if (isset($_POST['generate_ideas']) && wp_verify_nonce($_POST['generate_ideas_nonce'], 'generate_ideas')) {
            $this->handle_generate_ideas();
        }
        
        // Handle article-based idea generation
        if (isset($_POST['generate_ideas_from_article']) && wp_verify_nonce($_POST['generate_ideas_from_article_nonce'], 'generate_ideas_from_article')) {
            $this->handle_generate_ideas_from_article();
        }
        
        // Handle post creation
        if (isset($_POST['create_now']) && wp_verify_nonce($_POST['create_post_nonce'], 'create_post_now')) {
            $this->handle_create_post();
        }
        
        // Handle idea deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['idea_id']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_idea_' . $_GET['idea_id'])) {
            $this->handle_delete_idea();
        }
    }
    
    /**
     * Handle idea generation
     */
    private function handle_generate_ideas() {
        $main_topic = sanitize_text_field($_POST['main_topic']);
        $ideas_count = absint($_POST['num_ideas']);
        $content_type = sanitize_text_field($_POST['content_type']);
        
        if ($main_topic) {
            $result = Auto_Post_Generator_Ideas_Generator::generate_post_ideas($main_topic, $ideas_count, $content_type);
            if ($result) {
                echo '<div class="notice notice-success"><p>' . $result . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html(apg_translate('Error generating ideas. Please check your API configuration.')) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-warning"><p>' . esc_html(apg_translate('Please enter a main topic.')) . '</p></div>';
        }
    }
    
    /**
     * Handle article-based idea generation
     */
    private function handle_generate_ideas_from_article() {
        $reference_article = sanitize_textarea_field($_POST['reference_article']);
        $ideas_count = absint($_POST['num_ideas_article']);
        $approach = sanitize_text_field($_POST['generation_type']);
        
        if ($reference_article) {
            $result = Auto_Post_Generator_Ideas_Generator::generate_ideas_from_article($reference_article, $ideas_count, $approach);
            if ($result) {
                echo '<div class="notice notice-success"><p>' . $result . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html(apg_translate('Error generating ideas from article. Please check your API configuration.')) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-warning"><p>' . esc_html(apg_translate('Please enter a reference article.')) . '</p></div>';
        }
    }
    
    /**
     * Handle post creation
     */
    private function handle_create_post() {
        // Handle idea-based creation
        $idea_id = isset($_GET['idea_id']) ? absint($_GET['idea_id']) : 0;
        $idea_post = null;
        $idea_keyword = '';
        
        if ($idea_id) {
            $idea_post = get_post($idea_id);
            if ($idea_post && $idea_post->post_type === 'post_idea') {
                $idea_keyword = get_post_meta($idea_id, '_post_idea_keyword', true);
            }
        }
        
        // Get form data
        $prompt = !empty($_POST['custom_prompt']) ? sanitize_textarea_field($_POST['custom_prompt']) : Auto_Post_Generator_Settings::get_setting('auto_post_prompt', 'Write a post about a relevant topic.');
        $category_id = !empty($_POST['category_custom']) ? absint($_POST['category_custom']) : Auto_Post_Generator_Settings::get_setting('auto_post_category', 1);
        $tags = explode(',', Auto_Post_Generator_Settings::get_setting('auto_post_tags', ''));
        $post_status = !empty($_POST['post_status_custom']) ? sanitize_text_field($_POST['post_status_custom']) : Auto_Post_Generator_Settings::get_setting('auto_post_status', 'publish');
        $word_count = Auto_Post_Generator_Settings::get_setting('auto_post_word_count', '500');
        $post_date = isset($_POST['post_date']) ? sanitize_text_field($_POST['post_date']) : current_time('mysql');
        $ai_provider = !empty($_POST['ai_provider_custom']) ? sanitize_text_field($_POST['ai_provider_custom']) : Auto_Post_Generator_Settings::get_setting('ai_provider', 'openai');
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $source_article = isset($_POST['source_article']) ? sanitize_textarea_field($_POST['source_article']) : '';
        
        // If generating from idea, use idea's title and keyword
        if ($idea_id && $idea_post) {
            $prompt = $idea_post->post_title;
            if (!$keyword && $idea_keyword) {
                $keyword = $idea_keyword;
            }
        }
        
        // Generate post
        $message = Auto_Post_Generator_Post_Generator::generate_and_publish_post(
            $prompt,
            $category_id,
            $tags,
            $post_status,
            $post_date,
            $word_count,
            $ai_provider,
            array(),
            $keyword,
            $source_article
        );
        
        echo '<div class="notice notice-info"><p>' . esc_html($message) . '</p></div>';
        
        // Show option to delete used idea
        if ($idea_id && $idea_post) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . esc_html(apg_translate('Post generated from idea. '));
            $delete_url = wp_nonce_url(admin_url('admin.php?page=auto-post-generator&tab=create&delete_idea=' . $idea_id), 'delete_idea_' . $idea_id);
            echo '<a href="' . $delete_url . '" onclick="return confirm(\'' . esc_js(apg_translate('Do you want to delete this idea since it has been used to generate the post?')) . '\')" class="button button-small">' . esc_html(apg_translate('Delete Used Idea')) . '</a>';
            echo '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Handle idea deletion
     */
    private function handle_delete_idea() {
        $delete_id = absint($_GET['idea_id']);
        
        if (wp_delete_post($delete_id, true)) {
            echo '<div class="notice notice-success"><p>' . esc_html(apg_translate('Idea deleted successfully.')) . '</p></div>';
            echo '<script>window.history.replaceState({}, document.title, "' . admin_url('admin.php?page=auto-post-generator&tab=ideas') . '");</script>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html(apg_translate('Error deleting idea.')) . '</p></div>';
        }
    }
    
    /**
     * Render general tab
     */
    private function render_general_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_plugin_settings_group');
            do_settings_sections('my_plugin_settings_group');
            ?>
            <h2><?php echo esc_html(apg_translate('General Settings')); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('Interface Language')); ?></th>
                    <td>
                        <select name="interface_language">
                            <?php 
                            $languages = Auto_Post_Generator_Settings::get_available_languages();
                            $current_language = Auto_Post_Generator_Settings::get_interface_language();
                            foreach ($languages as $code => $name): ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($current_language, $code); ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo esc_html(apg_translate('Select the language for the interface and content generation')); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('AI Provider')); ?></th>
                    <td>
                        <select name="ai_provider">
                            <option value="openai" <?php selected(Auto_Post_Generator_Settings::get_setting('ai_provider'), 'openai'); ?>>OpenAI</option>
                            <option value="deepseek" <?php selected(Auto_Post_Generator_Settings::get_setting('ai_provider'), 'deepseek'); ?>>DeepSeek</option>
                        </select>
                        <p class="description"><?php echo esc_html(apg_translate('Select the AI provider you want to use')); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('OpenAI API Key')); ?></th>
                    <td>
                        <input type="password" name="openai_api_key" value="<?php echo esc_attr(Auto_Post_Generator_Settings::get_setting('openai_api_key')); ?>" style="width: 400px;" />
                        <p class="description"><?php echo esc_html(apg_translate('Get your API key from')); ?> <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('DeepSeek API Key')); ?></th>
                    <td>
                        <input type="password" name="deepseek_api_key" value="<?php echo esc_attr(Auto_Post_Generator_Settings::get_setting('deepseek_api_key')); ?>" style="width: 400px;" />
                        <p class="description"><?php echo esc_html(apg_translate('Get your API key from')); ?> <a href="https://platform.deepseek.com/api_keys" target="_blank">DeepSeek</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('Default Category')); ?></th>
                    <td>
                        <?php
                        $categories = get_categories(array('hide_empty' => false));
                        $selected_category = Auto_Post_Generator_Settings::get_setting('auto_post_category', 1);
                        ?>
                        <select name="auto_post_category">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($selected_category, $category->term_id); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('Tags (comma separated)')); ?></th>
                    <td><input type="text" name="auto_post_tags" value="<?php echo esc_attr(Auto_Post_Generator_Settings::get_setting('auto_post_tags', '')); ?>" style="width: 400px;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('Post Status')); ?></th>
                    <td>
                        <select name="auto_post_status">
                            <option value="publish" <?php selected(Auto_Post_Generator_Settings::get_setting('auto_post_status'), 'publish'); ?>><?php echo esc_html(apg_translate('Publish')); ?></option>
                            <option value="draft" <?php selected(Auto_Post_Generator_Settings::get_setting('auto_post_status'), 'draft'); ?>><?php echo esc_html(apg_translate('Draft')); ?></option>
                            <option value="future" <?php selected(Auto_Post_Generator_Settings::get_setting('auto_post_status'), 'future'); ?>><?php echo esc_html(apg_translate('Scheduled')); ?></option>
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
            <?php
            settings_fields('my_plugin_ai_settings_group');
            do_settings_sections('my_plugin_ai_settings_group');
            ?>
            <h2><?php echo esc_html(apg_translate('AI Settings')); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('OpenAI Model')); ?></th>
                    <td>
                        <select name="openai_model">
                            <option value="gpt-4" <?php selected(Auto_Post_Generator_Settings::get_setting('openai_model'), 'gpt-4'); ?>>GPT-4</option>
                            <option value="gpt-4-turbo" <?php selected(Auto_Post_Generator_Settings::get_setting('openai_model'), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                            <option value="gpt-3.5-turbo" <?php selected(Auto_Post_Generator_Settings::get_setting('openai_model'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('DeepSeek Model')); ?></th>
                    <td>
                        <select name="deepseek_model">
                            <option value="deepseek-chat" <?php selected(Auto_Post_Generator_Settings::get_setting('deepseek_model'), 'deepseek-chat'); ?>>DeepSeek Chat</option>
                            <option value="deepseek-coder" <?php selected(Auto_Post_Generator_Settings::get_setting('deepseek_model'), 'deepseek-coder'); ?>>DeepSeek Coder</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('Temperature (0.0 - 2.0)')); ?></th>
                    <td>
                        <input type="number" name="ai_temperature" value="<?php echo esc_attr(Auto_Post_Generator_Settings::get_setting('ai_temperature', '0.7')); ?>" min="0" max="2" step="0.1" />
                        <p class="description"><?php echo esc_html(apg_translate('Controls creativity. Higher values = more creativity')); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html(apg_translate('Max Tokens')); ?></th>
                    <td>
                        <input type="number" name="ai_max_tokens" value="<?php echo esc_attr(Auto_Post_Generator_Settings::get_setting('ai_max_tokens', '2000')); ?>" min="100" max="4000" />
                        <p class="description"><?php echo esc_html(apg_translate('Maximum number of tokens in response')); ?></p>
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