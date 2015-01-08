<?php

if (!class_exists('WordPress_Spam_Fighter')) {

    /**
     * Main / front controller class
     */
    class WordPress_Spam_Fighter extends WPSF_Module
    {
        /**
         * @var array
         */
        protected static $readable_properties = array(); // These should really be constants, but PHP doesn't allow class constants to be arrays

        /**
         * @var array
         */
        protected static $writeable_properties = array();

        /**
         * Modules of this plugin. Currently only WPSF_Settings.
         *
         * @var array
         */
        protected $modules;

        /**
         * Plugin version number
         */
        const VERSION = '0.4';

        /**
         * prefix for this plugin, used for enqueued styles and scripts.
         */
        const PREFIX = 'wpsf_';

        /*
         * Magic methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct()
        {
            $this->register_hook_callbacks();

            $this->modules = array(
                'WPSF_Settings' => WPSF_Settings::get_instance()
            );
        }


        /*
         * Static methods
         */

        /**
         * Enqueues CSS, JavaScript, etc
         *
         * @mvc Controller
         */
        public static function load_resources()
        {
            wp_register_script(
                self::PREFIX . 'wp-spam-fighter-admin',
                plugins_url('javascript/wp-spamfighter-admin.js', dirname(__FILE__)),
                array('jquery'),
                self::VERSION,
                true
            );

            wp_register_script(
                self::PREFIX . 'wp-spam-fighter',
                plugins_url('javascript/wp-spamfighter.js', dirname(__FILE__)),
                array('jquery'),
                self::VERSION,
                true
            );

            wp_register_style(
                self::PREFIX . 'admin',
                plugins_url('css/admin.css', dirname(__FILE__)),
                array(),
                self::VERSION,
                'all'
            );

            wp_register_style(
                self::PREFIX . 'wpsf',
                plugins_url('css/wpsf.css', dirname(__FILE__)),
                array(),
                self::VERSION,
                'all'
            );

            if (is_admin()) {
                wp_enqueue_style(self::PREFIX . 'admin');
                wp_enqueue_script(self::PREFIX . 'wp-spam-fighter-admin');
            } else {
                wp_enqueue_style(self::PREFIX . 'wpsf');
                wp_enqueue_script(self::PREFIX . 'wp-spam-fighter');
            }
        }

        /**
         * Clears caches of content generated by caching plugins like WP Super Cache
         *
         * @mvc Model
         */
        protected static function clear_caching_plugins()
        {
            // WP Super Cache
            if (function_exists('wp_cache_clear_cache')) {
                wp_cache_clear_cache();
            }

            // W3 Total Cache
            if (class_exists('W3_Plugin_TotalCacheAdmin')) {
                $w3_total_cache = w3_instance('W3_Plugin_TotalCacheAdmin');

                if (method_exists($w3_total_cache, 'flush_all')) {
                    $w3_total_cache->flush_all();
                }
            }
        }


        /*
         * Instance methods
         */

        /**
         * Prepares sites to use the plugin during single or network-wide activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate($network_wide)
        {
            global $wpdb;

            if (function_exists('is_multisite') && is_multisite()) {
                if ($network_wide) {
                    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                    foreach ($blogs as $blog) {
                        switch_to_blog($blog);
                        $this->single_activate($network_wide);
                    }

                    restore_current_blog();
                } else {
                    $this->single_activate($network_wide);
                }
            } else {
                $this->single_activate($network_wide);
            }
        }

        /**
         * Runs activation code on a new WPMS site when it's created
         *
         * @mvc Controller
         *
         * @param int $blog_id
         */
        public function activate_new_site($blog_id)
        {
            switch_to_blog($blog_id);
            $this->single_activate(true);
            restore_current_blog();
        }

        /**
         * Prepares a single blog to use the plugin
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        protected function single_activate($network_wide)
        {
            foreach ($this->modules as $module) {
                $module->activate($network_wide);
            }
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate()
        {
            foreach ($this->modules as $module) {
                $module->deactivate();
            }
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks()
        {
            add_action('wpmu_new_blog', __CLASS__ . '::activate_new_site');
            add_action('wp_enqueue_scripts', __CLASS__ . '::load_resources');
            add_action('admin_enqueue_scripts', __CLASS__ . '::load_resources');

            add_action('comment_form_before', array($this, 'comment_form_before'));
            add_action('comment_form_after_fields', array($this, 'comment_form_after_fields'), 1);
            add_action('comment_form_logged_in_after', array($this, 'comment_form_after_fields'), 1);
            add_action('pre_comment_on_post', array($this, 'pre_comment_on_post'));
            add_filter('pre_comment_approved', array($this, 'pre_comment_approved'), 10, 2);
            add_action('comment_post', array($this, 'comment_post'), 10, 2);


            add_action('register_form', array($this, 'register_form'));
            add_action('signup_extra_fields', array($this, 'register_form'));
            add_filter('registration_errors', array($this, 'registration_errors'), 10, 3);
            add_filter('wpmu_validate_user_signup', array($this, 'wpmu_validate_user_signup'), 10, 1);

            add_action('init', 'session_start');

            add_action('init', array($this, 'init'));
            add_action('init', array($this, 'upgrade'), 11);
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init()
        {
            try {
            } catch (Exception $exception) {
                add_notice(__METHOD__ . ' error: ' . $exception->getMessage(), 'error');
            }
        }

        /**
         * Checks if the plugin was recently updated and upgrades if necessary
         *
         * @mvc Controller
         *
         * @param int|string $db_version
         */
        public function upgrade($db_version = 0)
        {
            if (version_compare($this->modules['WPSF_Settings']->settings['db-version'], self::VERSION, '==')) {
                return;
            }

            foreach ($this->modules as $module) {
                $module->upgrade($this->modules['WPSF_Settings']->settings['db-version']);
            }

            $this->modules['WPSF_Settings']->settings = array('db-version' => self::VERSION);
            self::clear_caching_plugins();
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected function is_valid($property = 'all')
        {
            return true;
        }

        /**
         * Fired before the comment form.
         * Adds JavaScript for the timestamp spam protection.
         */
        public function comment_form_before()
        {
            $timestamp = 0;
            if ($this->modules['WPSF_Settings']->settings['timestamp']['timestamp']) {
                $timestamp = $this->modules['WPSF_Settings']->settings['timestamp']['threshold'] * 1000;
            }

            $client_message = $this->modules['WPSF_Settings']->settings['timestamp']['client_message'];

            ?>

            <script type="text/javascript">
                window.wpsf_timestamp_enabled = true;
                window.wpsf_threshold = <?php echo $timestamp; ?>;
                window.wpsf_message = '<?php echo $client_message; ?>';

                String.prototype.format = function (args) {
                    var str = this;
                    return str.replace(String.prototype.format.regex, function (item) {
                        var intVal = parseInt(item.substring(1, item.length - 1));
                        var replace;
                        if (intVal >= 0) {
                            replace = args[intVal];
                        } else if (intVal === -1) {
                            replace = "{";
                        } else if (intVal === -2) {
                            replace = "}";
                        } else {
                            replace = "";
                        }
                        return replace;
                    });
                };
                String.prototype.format.regex = new RegExp("{-?[0-9]+}", "g");
            </script>
        <?php
        }

        /**
         * Fired before a comment is posted.
         * Checks the timestamps in case the timestamp spam protection is on.
         */
        public function pre_comment_on_post()
        {
            if ($this->modules['WPSF_Settings']->settings['timestamp']['timestamp']) {
                $timestamp = $this->modules['WPSF_Settings']->settings['timestamp']['threshold'] * 1000;
                $wpsfTS1 = (isset($_POST['wpsfTS1'])) ? trim($_POST['wpsfTS1']) : 1;
                $wpsfTS2 = (isset($_POST['wpsfTS2'])) ? trim($_POST['wpsfTS2']) : 2;
                if (($wpsfTS2 - $wpsfTS1) < $timestamp['threshold']) {
                    wp_die($this->modules['WPSF_Settings']->settings['timestamp']['server_message']);
                }
            }
        }

        /**
         * Fired after the comment fields in the comment form.
         * Adds JavaScript to switch on spam protectors and HTML element required for some spam protectors.
         *
         * @param $postID
         */
        function comment_form_after_fields($postID)
        {
            global $wpsf_not_a_spammer_enabled, $wpsf_timestamp_enabled, $wpsf_honeypot_enabled, $wpsf_javascript_enabled;

            if ($this->modules['WPSF_Settings']->settings['others']['logged_in_users'] || !is_user_logged_in()) {
                if ($this->modules['WPSF_Settings']->settings['others']['javascript']) {
                    if (!$wpsf_javascript_enabled) {
                        ?>
                        <script type="text/javascript">
                            window.wpsf_javascript_enabled = true;
                        </script>
                        <?php
                        $wpsf_javascript_enabled = true;
                    }
                }

                if ($this->modules['WPSF_Settings']->settings['timestamp']['timestamp']) {
                    if (!$wpsf_timestamp_enabled) {
                        ?>
                        <script type="text/javascript">
                            window.wpsf_timestamp_enabled = true;
                        </script>
                        <?php
                        $wpsf_timestamp_enabled = true;
                    }
                }

                if ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot']) {
                    if (!$wpsf_honeypot_enabled) {
                        if ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot_type'] === "textarea") {
                            ?>
                            <textarea
                                id="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                aria-required="true" rows="8" cols="45"
                                name="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                style="display: none;"></textarea>
                        <?php
                        } elseif ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot_type'] === "hidden") {
                            ?>
                            <input type="hidden"
                                   id="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                   aria-required="true"
                                   name="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>">
                        <?php
                        } elseif ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot_type'] === "text") {
                            ?>
                            <input type="text"
                                   id="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                   aria-required="true" size="30" value=""
                                   name="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                   style="display: none;">
                        <?php
                        }
                        $wpsf_honeypot_enabled = true;
                    }
                }

                if ($this->modules['WPSF_Settings']->settings['others']['not_a_spammer']) {
                    if (!$wpsf_not_a_spammer_enabled) {
                        ?>
                        <p id="wpsf_p" style="clear:both;"></p>
                        <script type="text/javascript">
                            window.wpsf_not_a_spammer_enabled = true;
                            window.not_a_spammer_label = "<?php echo __('I am not a spammer', 'wpsf_domain'); ?>";
                            window.not_a_spammer_user_message = "<?php echo __('Please confirm that you are not a spammer', 'wpsf_domain'); ?>";
                        </script>
                        <noscript><?php echo __('Please enable javascript in order to be allowed to comment', 'wpsf_domain'); ?></noscript>
                        <?php
                        $wpsf_not_a_spammer_enabled = true;
                    }
                }
            }
        }

        function comment_post( $comment_ID, $approved ) {
        }

        /**
         * Filter a comment’s approval status before it is set.
         * Checks the comment data for spam.
         *
         * @param $approved
         * @param $comment_data
         * @return string
         */
        function pre_comment_approved($approved, $comment_data)
        {
            if (!$this->modules['WPSF_Settings']->settings['others']['trackbacks'] || $comment_data['comment_type'] == 'pingback' || $comment_data['comment_type'] == 'trackback') {
                if ($this->modules['WPSF_Settings']->settings['others']['logged_in_users'] || !is_user_logged_in()) {
                    if (($this->modules['WPSF_Settings']->settings['honeypot']['honeypot'])
                        && (!empty($_POST[$this->modules['WPSF_Settings']->settings['honeypot']['element_name']]))
                    ) {
                        $approved = 'spam';
                    } elseif (($this->modules['WPSF_Settings']->settings['others']['avatar'])
                        && !$this->check_avatar($comment_data['comment_author_email'])
                    ) {
                        $approved = 'spam';
                    } elseif (($this->modules['WPSF_Settings']->settings['others']['not_a_spammer'])
                        && (empty($_POST["wpsf_not_a_spammer"]))
                    ) {
                        $approved = 'spam';
                    } elseif (($this->modules['WPSF_Settings']->settings['others']['javascript'])
                        && (empty($_POST["wpsf_javascript"]) || $_POST["wpsf_javascript"] != "WPSF_JAVASCRIPT_TOKEN")
                    ) {
                        $approved = 'spam';
                    }
                }
            }
            if ($approved === 'spam') {
                if (isset($this->modules['WPSF_Settings']->settings['others']['delete']) && $this->modules['WPSF_Settings']->settings['others']['delete']) {
                    add_action('wp_insert_comment', array(&$this, 'handle_auto_trash'), 0, 2);
                }
                if (isset($this->modules['WPSF_Settings']->settings['others']['discard']) && $this->modules['WPSF_Settings']->settings['others']['discard']) {
                    add_action('wp_insert_comment', array(&$this, 'handle_auto_delete'), 0, 2);
                }
            }
            return $approved;
        }

        public function handle_auto_delete($id, $comment) {
            if(!$comment && !is_object($cmt = get_comment($comment))){
                return;
            }
            wp_delete_comment($id, true);
        }

        public function handle_auto_trash($id, $comment) {
            if(!$comment && !is_object($cmt = get_comment($comment))){
                return;
            }
            wp_trash_comment($id, false);
        }

        /**
         * Fired following the ‘E-mail’ field in the user registration form.
         * Called both in single site and multi site deployment.
         * Adds the JavaScript code and HTML elements required by the different spam protectors.
         */
        public function register_form()
        {
            global $wpsf_not_a_spammer_enabled, $wpsf_timestamp_enabled, $wpsf_honeypot_enabled, $wpsf_javascript_enabled;

            if ($this->modules['WPSF_Settings']->settings['others']['registration']) {
                if ($this->modules['WPSF_Settings']->settings['others']['javascript']) {
                    if (!$wpsf_javascript_enabled) {
                        ?>
                        <script type="text/javascript">
                            window.wpsf_javascript_enabled = true;
                        </script>
                        <?php
                        $wpsf_javascript_enabled = true;
                    }
                }

                if ($this->modules['WPSF_Settings']->settings['timestamp']['timestamp']) {
                    if (!$wpsf_timestamp_enabled) {
                        ?>
                        <script type="text/javascript">
                            window.wpsf_timestamp_enabled = true;
                        </script>
                        <?php
                        $wpsf_timestamp_enabled = true;
                    }
                }

                if ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot']) {
                    if (!$wpsf_honeypot_enabled) {
                        if ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot_type'] === "textarea") {
                            ?>
                            <textarea
                                id="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                aria-required="true" rows="8" cols="45"
                                name="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                style="display: none;"></textarea>
                        <?php
                        } elseif ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot_type'] === "hidden") {
                            ?>
                            <input type="hidden"
                                   id="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                   aria-required="true"
                                   name="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>">
                        <?php
                        } elseif ($this->modules['WPSF_Settings']->settings['honeypot']['honeypot_type'] === "text") {
                            ?>
                            <input type="text"
                                   id="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                   aria-required="true" size="30" value=""
                                   name="<?php echo $this->modules['WPSF_Settings']->settings['honeypot']['element_name']; ?>"
                                   style="display: none;">
                        <?php
                        }
                        $wpsf_honeypot_enabled = true;
                    }
                }

                if ($this->modules['WPSF_Settings']->settings['others']['not_a_spammer']) {
                    if (!$wpsf_not_a_spammer_enabled) {
                        ?>
                        <p id="wpsf_p" style="clear:both;"></p>
                        <script type="text/javascript">
                            window.wpsf_not_a_spammer_enabled = true;
                            window.not_a_spammer_label = "<?php echo __('I am not a spammer', 'wpsf_domain'); ?>";
                            window.not_a_spammer_user_message = "<?php echo __('Please confirm that you are not a spammer', 'wpsf_domain'); ?>";
                        </script>
                        <noscript><?php echo __('Please enable javascript in order to be allowed to comment', 'wpsf_domain'); ?></noscript>
                        <?php
                        $wpsf_not_a_spammer_enabled = true;
                    }
                }
            }
        }

        /**
         * Filter the validated user registration details.
         * This does not allow you to override the username or email of the user during registration. The values are solely used for validation and error handling.
         * It is called in multi site deployment and just forwards the call to registration_errors.
         *
         * @param $result
         * @return mixed
         */
        public function wpmu_validate_user_signup($result)
        {
            $result['errors'] = $this->registration_errors($result['errors'], $result['user_name'], $result['user_email']);
            return $result;
        }

        /**
         * Filter the errors encountered when a new user is being registered.
         * The filtered WP_Error object may, for example, contain errors for an invalid or existing username or email address. A WP_Error object should always returned, but may or may not contain errors.
         * If any errors are present in $errors, this will abort the user’s registration.
         * Implements the same logic as pre_comment_approved but for the registration form.
         *
         * @param $errors
         * @param $sanitized_user_login
         * @param $user_email
         * @return mixed
         */
        public function registration_errors($errors, $sanitized_user_login, $user_email)
        {
            if ($this->modules['WPSF_Settings']->settings['others']['registration']) {
                if (($this->modules['WPSF_Settings']->settings['honeypot']['honeypot'])
                    && (!empty($_POST[$this->modules['WPSF_Settings']->settings['honeypot']['element_name']]))
                ) {
                    $errors->add('spam_error', __('<strong>ERROR</strong>: There was a problem processing your registration.', 'wpsf_domain'));
                } elseif (($this->modules['WPSF_Settings']->settings['others']['avatar'])
                    && !$this->check_avatar($user_email)
                ) {
                    $errors->add('spam_error', __('<strong>ERROR</strong>: There was a problem processing your registration.', 'wpsf_domain'));
                } elseif (($this->modules['WPSF_Settings']->settings['others']['not_a_spammer'])
                    && (empty($_POST["wpsf_not_a_spammer"]))
                ) {
                    $errors->add('spam_error', __('<strong>ERROR</strong>: There was a problem processing your registration.', 'wpsf_domain'));
                } elseif (($this->modules['WPSF_Settings']->settings['others']['javascript'])
                    && (empty($_POST["wpsf_javascript"]) || $_POST["wpsf_javascript"] != "WPSF_JAVASCRIPT_TOKEN")
                ) {
                    $errors->add('spam_error', __('<strong>ERROR</strong>: There was a problem processing your registration.', 'wpsf_domain'));
                }
            }
            return $errors;
        }

        /**
         * Checks whether an avata is available on www.gravator.com for this email address.
         *
         * @param $email
         * @return bool
         */
        public function check_avatar($email)
        {
            $headers = get_headers("http://www.gravatar.com/avatar/" . md5(strtolower($email)) . "?d=404", 1);
            if (strpos($headers[0], '404')) {
                return false;
            }
            return true;
        }
    }

    ; // end WordPress_Spam_Fighter
}
