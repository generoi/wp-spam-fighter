<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2><?php echo esc_html(sprintf(__('%s Settings', 'wpsf_domain'), WPSF_NAME)); ?></h2>

    <form method="post" action="<?php echo $network_activated ? 'edit.php?action=wpsf_settings' : 'options.php'; ?>">
        <?php settings_fields('wpsf_settings'); ?>
        <?php do_settings_sections('wpsf_settings'); ?>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary"
                   value="<?php esc_attr_e('Save Changes', 'wpsf_domain'); ?>"/>
        </p>
    </form>
</div> <!-- .wrap -->
