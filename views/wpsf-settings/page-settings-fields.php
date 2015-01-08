<?php
/*
 * Timestamp Section
 */
?>

<?php
if ('wpsf_timestamp' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[timestamp][timestamp]"
           id="wpsf_settings[timestamp][timestamp]"
           value="1" <?php checked(1, $settings['timestamp']['timestamp']) ?>>
<?php
elseif ('wpsf_threshold' == $field['label_for']) : ?>
    <input type="text" name="wpsf_settings[timestamp][threshold]"
           id="wpsf_settings[timestamp][threshold]"
           value="<?php echo $settings['timestamp']['threshold']; ?>"
           placeholder="e.g. 10">
<?php
elseif ('wpsf_client_message' == $field['label_for']) : ?>
    <textarea name="wpsf_settings[timestamp][client_message]"
              id="wpsf_settings[timestamp][client_message]"><?php echo $settings['timestamp']['client_message']; ?></textarea>
<?php
elseif ('wpsf_server_message' == $field['label_for']) : ?>
    <textarea name="wpsf_settings[timestamp][server_message]"
              id="wpsf_settings[timestamp][server_message]"><?php echo $settings['timestamp']['server_message']; ?></textarea>
<?php
elseif ('wpsf_honeypot' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[honeypot][honeypot]"
           id="wpsf_settings[honeypot][honeypot]"
           value="1" <?php checked(1, $settings['honeypot']['honeypot']) ?>>
<?php
elseif ('wpsf_elementname' == $field['label_for']) : ?>
    <input type="text" name="wpsf_settings[honeypot][element_name]"
           id="wpsf_settings[honeypot][element_name]"
           value="<?php echo $settings['honeypot']['element_name']; ?>">
<?php
elseif ('wpsf_honeypot_type' == $field['label_for']) : ?>
    <select id="wpsf_settings[honeypot][honeypot_type]" name="wpsf_settings[honeypot][honeypot_type]">
        <option
            value="textarea" <?php echo($settings['honeypot']['honeypot_type'] === "textarea" ? 'selected="selected"' : ''); ?>><?php _e("Text area"); ?></option>
        <option
            value="text" <?php echo($settings['honeypot']['honeypot_type'] === "text" ? 'selected="selected"' : ''); ?>><?php _e("Text input field"); ?></option>
    </select>
<?php
elseif ('wpsf_avatar' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][avatar]"
           id="wpsf_settings[others][avatar]"
           value="1" <?php checked(1, $settings['others']['avatar']) ?>>
<?php
elseif ('wpsf_not_a_spammer' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][not_a_spammer]"
           id="wpsf_settings[others][not_a_spammer]"
           value="1" <?php checked(1, $settings['others']['not_a_spammer']) ?>>
<?php
elseif ('wpsf_logged_in_users' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][logged_in_users]"
           id="wpsf_settings[others][logged_in_users]"
           value="1" <?php checked(1, $settings['others']['logged_in_users']) ?>>
<?php
elseif ('wpsf_trackbacks' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][trackbacks]"
           id="wpsf_settings[others][trackbacks]"
           value="1" <?php checked(1, $settings['others']['trackbacks']) ?>>
<?php
elseif ('wpsf_javascript' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][javascript]"
           id="wpsf_settings[others][javascript]"
           value="1" <?php checked(1, $settings['others']['javascript']) ?>>
<?php
elseif ('wpsf_registration' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][registration]"
           id="wpsf_settings[others][registration]"
           value="1" <?php checked(1, $settings['others']['registration']) ?>>
<?php
elseif ('wpsf_delete' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][delete]"
           id="wpsf_settings[others][delete]"
           value="1" <?php if (isset($settings['others']['delete'])) checked(1, $settings['others']['delete']) ?>>
<?php
elseif ('wpsf_discard' == $field['label_for']) : ?>
    <input type="checkbox" name="wpsf_settings[others][discard]"
           id="wpsf_settings[others][discard]"
           value="1" <?php if (isset($settings['others']['discard'])) checked(1, $settings['others']['discard']) ?>>
<?php endif; ?>