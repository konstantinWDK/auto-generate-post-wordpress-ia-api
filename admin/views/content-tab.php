<?php
/**
 * Content Settings Tab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<form method="post" action="options.php">
    <?php settings_fields('my_plugin_content_settings_group'); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Writing Style', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <select name="writing_style">
                    <option value="informativo" <?php selected(Auto_Post_Generator_Settings::get_setting('writing_style'), 'informativo'); ?>>Informativo</option>
                    <option value="persuasivo" <?php selected(Auto_Post_Generator_Settings::get_setting('writing_style'), 'persuasivo'); ?>>Persuasivo</option>
                    <option value="narrativo" <?php selected(Auto_Post_Generator_Settings::get_setting('writing_style'), 'narrativo'); ?>>Narrativo</option>
                    <option value="tutorial" <?php selected(Auto_Post_Generator_Settings::get_setting('writing_style'), 'tutorial'); ?>>Tutorial</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Target Audience', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <select name="target_audience">
                    <option value="general" <?php selected(Auto_Post_Generator_Settings::get_setting('target_audience'), 'general'); ?>>General</option>
                    <option value="principiantes" <?php selected(Auto_Post_Generator_Settings::get_setting('target_audience'), 'principiantes'); ?>>Principiantes</option>
                    <option value="intermedios" <?php selected(Auto_Post_Generator_Settings::get_setting('target_audience'), 'intermedios'); ?>>Intermedios</option>
                    <option value="expertos" <?php selected(Auto_Post_Generator_Settings::get_setting('target_audience'), 'expertos'); ?>>Expertos</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Tone', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <select name="tone">
                    <option value="profesional" <?php selected(Auto_Post_Generator_Settings::get_setting('tone'), 'profesional'); ?>>Profesional</option>
                    <option value="amigable" <?php selected(Auto_Post_Generator_Settings::get_setting('tone'), 'amigable'); ?>>Amigable</option>
                    <option value="formal" <?php selected(Auto_Post_Generator_Settings::get_setting('tone'), 'formal'); ?>>Formal</option>
                    <option value="casual" <?php selected(Auto_Post_Generator_Settings::get_setting('tone'), 'casual'); ?>>Casual</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Word Count', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <input type="number" name="auto_post_word_count" value="<?php echo esc_attr(Auto_Post_Generator_Settings::get_setting('auto_post_word_count', '500')); ?>" min="100" max="2000" />
                <p class="description"><?php _e('Number of words for generated posts', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Include FAQ', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <select name="include_faq">
                    <option value="yes" <?php selected(Auto_Post_Generator_Settings::get_setting('include_faq'), 'yes'); ?>>Yes</option>
                    <option value="no" <?php selected(Auto_Post_Generator_Settings::get_setting('include_faq'), 'no'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Include Lists', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <select name="include_lists">
                    <option value="yes" <?php selected(Auto_Post_Generator_Settings::get_setting('include_lists'), 'yes'); ?>>Yes</option>
                    <option value="no" <?php selected(Auto_Post_Generator_Settings::get_setting('include_lists'), 'no'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('SEO Focus', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <select name="seo_focus">
                    <option value="low" <?php selected(Auto_Post_Generator_Settings::get_setting('seo_focus'), 'low'); ?>>Low</option>
                    <option value="medium" <?php selected(Auto_Post_Generator_Settings::get_setting('seo_focus'), 'medium'); ?>>Medium</option>
                    <option value="high" <?php selected(Auto_Post_Generator_Settings::get_setting('seo_focus'), 'high'); ?>>High</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Custom Instructions', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></th>
            <td>
                <textarea name="custom_instructions" rows="4" cols="50"><?php echo esc_textarea(Auto_Post_Generator_Settings::get_setting('custom_instructions')); ?></textarea>
                <p class="description"><?php _e('Additional instructions for content generation', AUTO_POST_GENERATOR_TEXT_DOMAIN); ?></p>
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>