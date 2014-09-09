<div class="error">
    <p><?php echo esc_html(sprintf(__("%s error: Your environment doesn't meet all of the system requirements listed below.", 'wpsf_domain'), WPSF_NAME)); ?></p>

    <ul class="ul-disc">
        <li>
            <strong><?php echo esc_html(sprintf(__("PHP %s or higher", 'wpsf_domain'), WPSF_REQUIRED_PHP_VERSION)); ?></strong>
            <em><?php echo esc_html(sprintf(__("(You're running version %s)", 'wpsf_domain'), PHP_VERSION)); ?><</em>
        </li>

        <li>
            <strong><?php echo esc_html(sprintf(__("WordPress %s or higher", 'wpsf_domain'), WPSF_REQUIRED_WP_VERSION)); ?></strong>
            <em><?php echo esc_html(sprintf(__("(You're running version %s)", 'wpsf_domain'), $wp_version)); ?><</em>
        </li>

        <?php //<li><strong>Plugin XYZ</strong> activated</em></li> ?>
    </ul>

    <p>
        <?php
        $url = 'http://codex.wordpress.org/Upgrading_WordPress';
        $link = sprintf(__('If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="%s">the Codex</a>.', 'wpsf_domain'), esc_url($url));
        echo $link;
        ?>
    </p>
</div>
