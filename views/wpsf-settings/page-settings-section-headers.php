<?php if ('wpsf_section-timestamp' == $section['id']) { ?>
    <p><?php esc_html_e('Set options for the time based spam blocker.', 'wpsf_domain'); ?></p>
    <input type="hidden" name="wpsf_settings[timestamp][timestamp]" value="0">
<?php
} elseif ('wpsf_section-honeypot' == $section['id']) {
    ?>
    <p><?php esc_html_e('Set options for the honeypot spam blocker.', 'wpsf_domain'); ?></p>
    <input type="hidden" name="wpsf_settings[honeypot][honeypot]" value="0">
<?php
} elseif ('wpsf_section-others' == $section['id']) {
    ?>
    <p><?php esc_html_e('Set options for other spam blocking methods.', 'wpsf_domain'); ?></p>
    <input type="hidden" name="wpsf_settings[others][avatar]" value="0">
<?php } ?>