<?php
/*
Plugin Name: WP Spam Fighter
Plugin URI:  https://wordpress.org/plugins/wp-spam-fighter/
Description: Comment spam prevention without moderation, captchas or questions
Version:     0.4
Author:      Henri Benoit
Author URI:  http://benohead.com
*/

/*
 * This plugin was built on top of WordPress-Plugin-Skeleton by Ian Dunn.
 * See https://github.com/iandunn/WordPress-Plugin-Skeleton for details.
 */

if (!defined('ABSPATH')) {
    die('Access denied.');
}

/**
 *
 */
define('WPSF_NAME', 'WP Spam Fighter');
/**
 *
 */
define('WPSF_REQUIRED_PHP_VERSION', '5.3'); // because of get_called_class()
/**
 *
 */
define('WPSF_REQUIRED_WP_VERSION', '3.1'); // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function wpsf_requirements_met()
{
    global $wp_version;

    if (version_compare(PHP_VERSION, WPSF_REQUIRED_PHP_VERSION, '<')) {
        return false;
    }

    if (version_compare($wp_version, WPSF_REQUIRED_WP_VERSION, '<')) {
        return false;
    }

    return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function wpsf_requirements_error()
{
    global $wp_version;

    require_once(dirname(__FILE__) . '/views/requirements-error.php');
}

/**
 *
 */
function load_wpsf_textdomain()
{
    load_plugin_textdomain('wpsf_domain', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if (wpsf_requirements_met()) {
    require_once(__DIR__ . '/classes/wpsf-module.php');
    require_once(__DIR__ . '/classes/wp-spam-fighter.php');
    require_once(__DIR__ . '/classes/wpsf-settings.php');

    add_action('init', 'load_wpsf_textdomain');

    if (class_exists('WordPress_Spam_Fighter')) {
        $GLOBALS['wpsf'] = WordPress_Spam_Fighter::get_instance();
        register_activation_hook(__FILE__, array($GLOBALS['wpsf'], 'activate'));
        register_deactivation_hook(__FILE__, array($GLOBALS['wpsf'], 'deactivate'));
    }
} else {
    add_action('admin_notices', 'wpsf_requirements_error');
}
