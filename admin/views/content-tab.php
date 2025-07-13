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
    <?php wp_nonce_field('miapg_content_settings_action', 'miapg_content_nonce'); ?>
    <input type="hidden" name="option_page" value="miapg_content_settings_group" />
    <input type="hidden" name="action" value="update" />
    <?php settings_fields('miapg_content_settings_group'); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Writing Style', 'miapg-post-generator'); ?></th>
            <td>
                <select name="miapg_writing_style">
                    <option value="informativo" <?php selected(Miapg_Settings::get_setting('miapg_writing_style'), 'informativo'); ?>>Informativo</option>
                    <option value="persuasivo" <?php selected(Miapg_Settings::get_setting('miapg_writing_style'), 'persuasivo'); ?>>Persuasivo</option>
                    <option value="narrativo" <?php selected(Miapg_Settings::get_setting('miapg_writing_style'), 'narrativo'); ?>>Narrativo</option>
                    <option value="tutorial" <?php selected(Miapg_Settings::get_setting('miapg_writing_style'), 'tutorial'); ?>>Tutorial</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Target Audience', 'miapg-post-generator'); ?></th>
            <td>
                <select name="miapg_target_audience">
                    <option value="general" <?php selected(Miapg_Settings::get_setting('miapg_target_audience'), 'general'); ?>>General</option>
                    <option value="principiantes" <?php selected(Miapg_Settings::get_setting('miapg_target_audience'), 'principiantes'); ?>>Principiantes</option>
                    <option value="intermedios" <?php selected(Miapg_Settings::get_setting('miapg_target_audience'), 'intermedios'); ?>>Intermedios</option>
                    <option value="expertos" <?php selected(Miapg_Settings::get_setting('miapg_target_audience'), 'expertos'); ?>>Expertos</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Tone', 'miapg-post-generator'); ?></th>
            <td>
                <select name="miapg_tone">
                    <option value="profesional" <?php selected(Miapg_Settings::get_setting('miapg_tone'), 'profesional'); ?>>Profesional</option>
                    <option value="amigable" <?php selected(Miapg_Settings::get_setting('miapg_tone'), 'amigable'); ?>>Amigable</option>
                    <option value="formal" <?php selected(Miapg_Settings::get_setting('miapg_tone'), 'formal'); ?>>Formal</option>
                    <option value="casual" <?php selected(Miapg_Settings::get_setting('miapg_tone'), 'casual'); ?>>Casual</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Word Count', 'miapg-post-generator'); ?></th>
            <td>
                <input type="number" name="miapg_post_word_count" value="<?php echo esc_attr(Miapg_Settings::get_setting('miapg_post_word_count', '500')); ?>" min="100" max="2000" />
                <p class="description"><?php esc_html_e('Number of words for generated posts', 'miapg-post-generator'); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Include FAQ', 'miapg-post-generator'); ?></th>
            <td>
                <select name="miapg_include_faq">
                    <option value="yes" <?php selected(Miapg_Settings::get_setting('miapg_include_faq'), 'yes'); ?>>Yes</option>
                    <option value="no" <?php selected(Miapg_Settings::get_setting('miapg_include_faq'), 'no'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Include Lists', 'miapg-post-generator'); ?></th>
            <td>
                <select name="miapg_include_lists">
                    <option value="yes" <?php selected(Miapg_Settings::get_setting('miapg_include_lists'), 'yes'); ?>>Yes</option>
                    <option value="no" <?php selected(Miapg_Settings::get_setting('miapg_include_lists'), 'no'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('SEO Focus', 'miapg-post-generator'); ?></th>
            <td>
                <select name="miapg_seo_focus">
                    <option value="low" <?php selected(Miapg_Settings::get_setting('miapg_seo_focus'), 'low'); ?>>Low</option>
                    <option value="medium" <?php selected(Miapg_Settings::get_setting('miapg_seo_focus'), 'medium'); ?>>Medium</option>
                    <option value="high" <?php selected(Miapg_Settings::get_setting('miapg_seo_focus'), 'high'); ?>>High</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Custom Instructions', 'miapg-post-generator'); ?></th>
            <td>
                <textarea name="miapg_custom_instructions" rows="4" cols="50"><?php echo esc_textarea(Miapg_Settings::get_setting('miapg_custom_instructions')); ?></textarea>
                <p class="description"><?php esc_html_e('Additional instructions for content generation', 'miapg-post-generator'); ?></p>
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
