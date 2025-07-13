<?php
/**
 * Scheduling Settings Tab
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<form method="post" action="options.php">
    <?php wp_nonce_field('miapg_scheduling_settings_action', 'miapg_scheduling_nonce'); ?>
    <input type="hidden" name="option_page" value="miapg_scheduling_settings_group" />
    <input type="hidden" name="action" value="update" />
    <?php settings_fields('miapg_scheduling_settings_group'); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Auto Scheduling', 'miapg-post-generator'); ?></th>
            <td>
                <select name="auto_scheduling_enabled">
                    <option value="no" <?php selected(Miapg_Settings::get_setting('auto_scheduling_enabled'), 'no'); ?>>No</option>
                    <option value="yes" <?php selected(Miapg_Settings::get_setting('auto_scheduling_enabled'), 'yes'); ?>>Yes</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Posting Frequency', 'miapg-post-generator'); ?></th>
            <td>
                <select name="posting_frequency">
                    <option value="daily" <?php selected(Miapg_Settings::get_setting('posting_frequency'), 'daily'); ?>>Daily</option>
                    <option value="weekly" <?php selected(Miapg_Settings::get_setting('posting_frequency'), 'weekly'); ?>>Weekly</option>
                    <option value="biweekly" <?php selected(Miapg_Settings::get_setting('posting_frequency'), 'biweekly'); ?>>Biweekly</option>
                    <option value="monthly" <?php selected(Miapg_Settings::get_setting('posting_frequency'), 'monthly'); ?>>Monthly</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Posting Time', 'miapg-post-generator'); ?></th>
            <td>
                <input type="time" name="posting_time" value="<?php echo esc_attr(Miapg_Settings::get_setting('posting_time', '09:00')); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Posting Day', 'miapg-post-generator'); ?></th>
            <td>
                <select name="posting_day">
                    <option value="monday" <?php selected(Miapg_Settings::get_setting('posting_day'), 'monday'); ?>>Monday</option>
                    <option value="tuesday" <?php selected(Miapg_Settings::get_setting('posting_day'), 'tuesday'); ?>>Tuesday</option>
                    <option value="wednesday" <?php selected(Miapg_Settings::get_setting('posting_day'), 'wednesday'); ?>>Wednesday</option>
                    <option value="thursday" <?php selected(Miapg_Settings::get_setting('posting_day'), 'thursday'); ?>>Thursday</option>
                    <option value="friday" <?php selected(Miapg_Settings::get_setting('posting_day'), 'friday'); ?>>Friday</option>
                    <option value="saturday" <?php selected(Miapg_Settings::get_setting('posting_day'), 'saturday'); ?>>Saturday</option>
                    <option value="sunday" <?php selected(Miapg_Settings::get_setting('posting_day'), 'sunday'); ?>>Sunday</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Topics List', 'miapg-post-generator'); ?></th>
            <td>
                <textarea name="auto_topics_list" rows="8" cols="50"><?php echo esc_textarea(Miapg_Settings::get_setting('auto_topics_list')); ?></textarea>
                <p class="description"><?php esc_html_e('Enter topics one per line for automatic post generation', 'miapg-post-generator'); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Auto Delete Used Ideas', 'miapg-post-generator'); ?></th>
            <td>
                <select name="auto_delete_used_ideas">
                    <option value="no" <?php selected(Miapg_Settings::get_setting('auto_delete_used_ideas'), 'no'); ?>>No</option>
                    <option value="yes" <?php selected(Miapg_Settings::get_setting('auto_delete_used_ideas'), 'yes'); ?>>Yes</option>
                </select>
                <p class="description"><?php esc_html_e('Delete ideas after they are used to generate posts', 'miapg-post-generator'); ?></p>
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
