<?php

if (!class_exists('WPSF_Settings')) {

    /**
     * Handles plugin settings and user profile meta fields
     */
    class WPSF_Settings extends WPSF_Module
    {
        /**
         * @var
         */
        protected $settings;
        /**
         * Whether this plugin was network activated.
         *
         * @var
         */
        private $network_activated;
        /**
         * Default values for the settings. Used to add missing (new) settings.
         *
         * @var
         */
        protected static $default_settings;
        /**
         * @var array
         */
        protected static $readable_properties = array('settings');
        /**
         * @var array
         */
        protected static $writeable_properties = array('settings');

        /**
         * Capability required to edit settings when the plugin isn't network-activated.
         */
        const REQUIRED_CAPABILITY = 'manage_options';

        /**
         * Capability required to edit settings when the plugin is network-activated.
         */
        const REQUIRED_CAPABILITY_MU = 'manage_network_plugins';

        /*
         * General methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct()
        {
            $this->network_activated = (is_multisite() && array_key_exists(plugin_basename(dirname(__DIR__)) . '/bootstrap.php', get_site_option('active_sitewide_plugins')));
            $this->register_hook_callbacks();
        }

        /**
         * Public setter for protected variables
         *
         * Updates settings outside of the Settings API or other subsystems
         *
         * @mvc Controller
         *
         * @param string $variable
         * @param array $value This will be merged with WPSF_Settings->settings, so it should mimic the structure of the WPSF_Settings::$default_settings. It only needs the contain the values that will change, though. See WordPress_Spam_Fighter->upgrade() for an example.
         */
        public function __set($variable, $value)
        {
            // Note: WPSF_Module::__set() is automatically called before this

            if ($variable != 'settings') {
                return;
            }

            $this->settings = self::validate_settings($value);
            update_option('wpsf_settings', $this->settings);
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks()
        {
            if ($this->network_activated) {
                add_action('network_admin_menu', array($this, 'register_settings_pages'));
                add_action('network_admin_edit_wpsf_settings', array($this, 'update_network_setting'));
            } else {
                add_action('admin_menu', array($this, 'register_settings_pages'));
            }
            add_action('init', array($this, 'init'));
            add_action('admin_init', array($this, 'register_settings'));

            if ($this->network_activated) {
                add_filter(
                    'network_admin_plugin_action_links_' . plugin_basename(dirname(__DIR__)) . '/bootstrap.php',
                    array($this, 'add_plugin_action_links')
                );
            } else {
                add_filter(
                    'plugin_action_links_' . plugin_basename(dirname(__DIR__)) . '/bootstrap.php',
                    array($this, 'add_plugin_action_links')
                );
            }
        }

        /**
         * This hook is called went the network-wide plugin settings are saved.
         * It saves the settings and redirects to the network-wide plugin settings page.
         * An exit is required otherwise WordPress would redirect to network_admin_url().
         */
        function update_network_setting()
        {
            update_site_option('wpsf_settings', $_POST['wpsf_settings']);
            wp_redirect(add_query_arg(array('page' => wpsf_settings, 'updated' => 'true'), network_admin_url('settings.php')));
            exit;
        }

        /**
         * Prepares site to use the plugin during activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public
        function activate($network_wide)
        {
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public
        function deactivate()
        {
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public
        function init()
        {
            self::$default_settings = self::get_default_settings();
            $this->settings = self::get_settings();
        }

        /**
         * Executes the logic of upgrading from specific older versions of the plugin to the current version
         *
         * @mvc Model
         *
         * @param string $db_version
         */
        public
        function upgrade($db_version = 0)
        {
            /*
            if( version_compare( $db_version, 'x.y.z', '<' ) )
            {
                // Do stuff
            }
            */
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected
        function is_valid($property = 'all')
        {
            // Note: __set() calls validate_settings(), so settings are never invalid

            return true;
        }


        /*
         * Plugin Settings
         */

        /**
         * Establishes initial values for all settings
         *
         * @mvc Model
         *
         * @return array
         */
        protected
        static function get_default_settings()
        {
            $timestamp = array(
                "timestamp" => true,
                "threshold" => 10,
                "client_message" => 'This site is protected by an anti-spam feature that requires {0} seconds to have elapsed between the page load and the form submission. \n\n Please close this alert window.  The form may be resubmitted successfully in {1} seconds.',
                "server_message" => 'Error: this site uses JavaScript validation to reduce comment spam by rejecting comments that appear to be submitted by an automated method.  Either your browser has JavaScript disabled or the comment appeared to be submitted by a bot.',
            );

            $honeypot = array(
                "honeypot" => true,
                "element_name" => "more_comment",
                "honeypot_type" => "textarea",
            );

            $others = array(
                "avatar" => false,
                "not_a_spammer" => false,
                "logged_in_users" => false,
                "javascript" => true,
                "trackbacks" => false,
                "registration" => true,
                "delete" => false,
            );

            return array(
                'db-version' => '0',
                'timestamp' => $timestamp,
                'honeypot' => $honeypot,
                'others' => $others,
            );
        }

        /**
         * Retrieves all of the settings from the database
         *
         * @mvc Model
         *
         * @return array
         */
        protected function get_settings()
        {
            $settings = shortcode_atts(
                self::$default_settings,
                $this->network_activated ? get_site_option('wpsf_settings', array()) : get_option('wpsf_settings', array())
            );

            return $settings;
        }

        /**
         * Adds links to the plugin's action link section on the Plugins page
         *
         * @mvc Model
         *
         * @param array $links The links currently mapped to the plugin
         * @return array
         */
        public function add_plugin_action_links($links)
        {
            array_unshift($links, '<a href="http://wordpress.org/extend/plugins/wp-spam-fighter/faq/">' . __('Help', 'wpsf_domain') . '</a>');
            if ($this->network_activated) {
                array_unshift($links, '<a href="settings.php?page=wpsf_settings">Settings</a>');
            } else {
                array_unshift($links, '<a href="options-general.php?page=wpsf_settings">Settings</a>');
            }
            return $links;
        }

        /**
         * Adds pages to the Admin Panel menu
         *
         * @mvc Controller
         */
        public function register_settings_pages()
        {
            if ($this->network_activated) {
                add_submenu_page(
                    'settings.php',
                    WPSF_NAME . ' Settings',
                    WPSF_NAME,
                    self::REQUIRED_CAPABILITY,
                    'wpsf_settings',
                    array($this, 'markup_settings_page')
                );
            } else {
                add_submenu_page(
                    'options-general.php',
                    sprintf(__('%s Settings', 'wpsf_domain'), WPSF_NAME),
                    WPSF_NAME,
                    self::REQUIRED_CAPABILITY,
                    'wpsf_settings',
                    array($this, 'markup_settings_page')
                );
            }
        }

        /**
         * Creates the markup for the Settings page
         *
         * @mvc Controller
         */
        public function markup_settings_page()
        {
            if (($this->network_activated && current_user_can(self::REQUIRED_CAPABILITY_MU)) ||
                (!$this->network_activated && current_user_can(self::REQUIRED_CAPABILITY))
            ) {
                echo self::render_template('wpsf-settings/page-settings.php', array('network_activated' => $this->network_activated), 'always');
            } else {
                wp_die('Access denied.');
            }
        }

        /**
         * Registers a field in the settings page
         *
         * @param $id
         * @param $title
         * @param $section
         */
        private function add_settings_field($id, $title, $section)
        {
            add_settings_field(
                $id,
                $title,
                array($this, 'markup_fields'),
                'wpsf_settings',
                $section,
                array('label_for' => $id)
            );
        }

        /**
         * Registers a field in the timestamp settings page
         *
         * @param $id
         * @param $title
         */
        private function add_settings_field_timestamp($id, $title)
        {
            $this->add_settings_field($id, $title, 'wpsf_section-timestamp');
        }

        /**
         * Registers a field in the honeypot settings page
         *
         * @param $id
         * @param $title
         */
        private function add_settings_field_honeypot($id, $title)
        {
            $this->add_settings_field($id, $title, 'wpsf_section-honeypot');
        }

        /**
         * Registers a field in the "others" settings page
         *
         * @param $id
         * @param $title
         */
        private function add_settings_field_others($id, $title)
        {
            $this->add_settings_field($id, $title, 'wpsf_section-others');
        }

        /**
         * Registers a settings section
         *
         * @param $id
         * @param $title
         */
        private function add_settings_section($id, $title)
        {
            add_settings_section(
                $id,
                $title,
                array($this, 'markup_section_headers'),
                'wpsf_settings'
            );
        }

        /**
         * Registers settings sections, fields and settings
         *
         * @mvc Controller
         */
        public function register_settings()
        {
            /*
             * Timestamp Section
             */
            $this->add_settings_section('wpsf_section-timestamp', 'Timestamp');

            $this->add_settings_field_timestamp('wpsf_timestamp', 'Timestamp protection');
            $this->add_settings_field_timestamp('wpsf_threshold', 'Threshold in seconds');
            $this->add_settings_field_timestamp('wpsf_client_message', 'Message for users');
            $this->add_settings_field_timestamp('wpsf_server_message', 'Message for bots');

            /*
             * Honeypot Section
             */
            $this->add_settings_section('wpsf_section-honeypot', 'Honeypot');

            $this->add_settings_field_honeypot('wpsf_honeypot', 'Honeypot protection');
            $this->add_settings_field_honeypot('wpsf_elementname', 'Honeypot HTML form element name');
            $this->add_settings_field_honeypot('wpsf_honeypot_type', 'Honeypot type');

            /*
             * Others Section
             */
            $this->add_settings_section('wpsf_section-others', 'Others');

            $this->add_settings_field_others('wpsf_avatar', 'Only commenters with avatars');
            $this->add_settings_field_others('wpsf_not_a_spammer', '"Not a spammer" checkbox');
            $this->add_settings_field_others('wpsf_logged_in_users', 'Check spam from logged in users');
            $this->add_settings_field_others('wpsf_trackbacks', 'Do not check trackbacks/pingbacks');
            $this->add_settings_field_others('wpsf_javascript', 'JavaScript human check');
            $this->add_settings_field_others('wpsf_registration', 'Also protect from spammer registration');
            $this->add_settings_field_others('wpsf_delete', 'Move Spam to Trash');

            // The settings container
            register_setting('wpsf_settings', 'wpsf_settings', array($this, 'validate_settings'));
        }

        /**
         * Adds the section introduction text to the Settings page
         *
         * @mvc Controller
         *
         * @param array $section
         */
        public function markup_section_headers($section)
        {
            echo self::render_template('wpsf-settings/page-settings-section-headers.php', array('section' => $section), 'always');
        }

        /**
         * Delivers the markup for settings fields
         *
         * @mvc Controller
         *
         * @param array $field
         */
        public function markup_fields($field)
        {
            echo self::render_template('wpsf-settings/page-settings-fields.php', array('settings' => $this->settings, 'field' => $field), 'always');
        }

        /**
         * Sets a default walue for a settings field if not set
         *
         * @param $new_settings
         * @param $section
         * @param $id
         * @param $value
         * @return mixed
         */
        private function setting_default_if_not_set($new_settings, $section, $id, $value)
        {
            if (!isset($new_settings[$section][$id])) {
                $new_settings[$section][$id] = $value;
            }
            return $new_settings;
        }

        /**
         * Sets an empty string for a settings field if not set
         *
         * @param $new_settings
         * @param $section
         * @param $id
         * @return mixed
         */
        private function setting_empty_string_if_not_set($new_settings, $section, $id)
        {
            return $this->setting_default_if_not_set($new_settings, $section, $id, '');
        }

        /**
         * Sets an empty array for a settings field if not set
         *
         * @param $new_settings
         * @param $section
         * @param $id
         * @return mixed
         */
        private function setting_empty_array_if_not_set($new_settings, $section, $id)
        {
            return $this->setting_default_if_not_set($new_settings, $section, $id, array());
        }

        /**
         * Sets a 0 walue for a settings field if not set
         *
         * @param $new_settings
         * @param $section
         * @param $id
         * @return mixed
         */
        private function setting_zero_if_not_set($new_settings, $section, $id)
        {
            return $this->setting_default_if_not_set($new_settings, $section, $id, '0');
        }

        /**
         * Validates submitted setting values before they get saved to the database. Invalid data will be overwritten with defaults.
         *
         * @mvc Model
         *
         * @param array $new_settings
         * @return array
         */
        public function validate_settings($new_settings)
        {
            $new_settings = shortcode_atts($this->settings, $new_settings);

            /*
             * Timestamp Settings
             */

            if (!isset($new_settings['timestamp'])) {
                $new_settings['timestamp'] = array();
            }

            if (isset($new_settings['timestamp']['client_message']) && empty($new_settings['timestamp']['client_message'])) {
                unset($new_settings['timestamp']['client_message']);
            }
            if (isset($new_settings['timestamp']['server_message']) && empty($new_settings['timestamp']['server_message'])) {
                unset($new_settings['timestamp']['server_message']);
            }

            $new_settings = $this->setting_default_if_not_set($new_settings, 'timestamp', 'timestamp', true);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'timestamp', 'threshold', 10);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'timestamp', 'client_message', 'This site is protected by an anti-spam feature that requires {0} seconds to have elapsed between the page load and the form submission. \n\n Please close this alert window.  The form may be resubmitted successfully in {1} seconds.');
            $new_settings = $this->setting_default_if_not_set($new_settings, 'timestamp', 'server_message', 'Error: this site uses JavaScript validation to reduce comment spam by rejecting comments that appear to be submitted by an automated method.  Either your browser has JavaScript disabled or the comment appeared to be submitted by a bot.');

            /*
             * Honeypot Settings
             */

            if (!isset($new_settings['honeypot'])) {
                $new_settings['honeypot'] = array();
            }

            if (isset($new_settings['honeypot']['element_name']) && empty($new_settings['honeypot']['element_name'])) {
                unset($new_settings['honeypot']['element_name']);
            }

            $new_settings = $this->setting_default_if_not_set($new_settings, 'honeypot', 'honeypot', true);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'honeypot', 'element_name', 'more_comment');
            $new_settings = $this->setting_default_if_not_set($new_settings, 'honeypot', 'honeypot_type', 'textarea');

            /*
             * Others Settings
             */

            if (!isset($new_settings['others'])) {
                $new_settings['others'] = array();
            }

            $new_settings = $this->setting_default_if_not_set($new_settings, 'others', 'avatar', false);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'others', 'not_a_spammer', false);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'others', 'logged_in_users', false);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'others', 'trackbacks', false);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'others', 'javascript', true);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'others', 'registration', true);
            $new_settings = $this->setting_default_if_not_set($new_settings, 'others', 'delete', false);

            return $new_settings;
        }
    } // end WPSF_Settings
}
