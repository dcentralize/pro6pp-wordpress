<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly

if (class_exists('Pro6ppSettings'))
    return;

class Pro6PPSettings
{

    /**
     * Construct the settings object
     */
    public function __construct ()
    {
        // Register actions and filters.
        add_action('admin_init',
                array(
                        &$this,
                        'admin_init'
                ));
        add_action('admin_menu',
                array(
                        &$this,
                        'add_menu'
                ));
        add_filter('woocommerce_screen_ids',
                array(
                        &$this,
                        'load_woocommerce_includes'
                ));
        add_action( 'admin_notices', array(&$this, 'pro6pp_admin_notices_action') );

    }

    /**
     * Callback to enable css and scripts of woocommerce to
     * be included in the settings page.
     *
     * @param Array $screenIds
     * @return Array
     */
    public function load_woocommerce_includes ($screenIds)
    {
        $screenIds[] = 'woocommerce_page_pro6pp_autocomplete';
        return $screenIds;
    }

    /**
     * Hook into WP's admin_init action hook.
     * Creates all necessary fields for the settings form.
     */
    public function admin_init ()
    {
        $settings = $this->get_custom_fields();
        // Add settings sections
        foreach ($settings as $section => $data) {

            foreach ($data['fields'] as $id => $attr) {
                // Register all fields per group.
                if (array_key_exists('rule', $attr))
                    register_setting($section, $id, $attr['rule']);
                else
                    register_setting($section, $id);

                // Add the section
                add_settings_section($section,
                __($data['args']['title'], 'pro6pp_autocomplete'),
                array(
                &$this,
                $data['args']['callback']
                ), $section);

                // Add the settings.
                add_settings_field($id,
                        __($attr['title'], 'pro6pp_autocomplete'),
                        array(
                                &$this,
                                $attr['callback']
                        ), $attr['page'], $section, $attr['args']);
            }
        }
    }

    /**
     * Takes predefined values and inserts them into the settings fields of
     * wordpress.
     * It is mainly used in order to unify and centralize the statically defined
     * form fields.
     *  It is the single point of reference for the templates and the settings class.
     */
    private function get_custom_fields ()
    {
        $pro6pp_settings_fields = array(
                'pro6pp_autocomplete-connection' => array(
                        'args' => array(
                                'title' => 'Connection Settings',
                                'callback' => 'settings_connection_pro6pp_autocomplete',
                                'page' => 'pro6pp_autocomplete-connection'
                        ),
                        'fields' => array(
                                'pro6pp_auth_key' => array(
                                        'title' => 'Authentication',
                                        'callback' => 'settings_field_input_text',
                                        'page' => 'pro6pp_autocomplete-connection',
                                        'section' => 'pro6pp_autocomplete-connection',
                                        'rule' => array(
                                                &$this,
                                                'validate_password_input'
                                        ),
                                        'args' => array(
                                                'fields' => array('authentication_key'=>"pro6pp_auth_key"),
                                                "description" => "Digits and characters.",
                                                'password' => true
                                        )
                                ),
                                'pro6pp_timeout' => array(
                                        'title' => 'Timeout Internal',
                                        'callback' => 'settings_field_input_text',
                                        'page' => 'pro6pp_autocomplete-connection',
                                        'section' => 'pro6pp_autocomplete-connection',
                                        'rule' => array(
                                                $this,
                                                'validate_timeout_input'
                                        ),
                                        'args' => array(
                                                'fields' => array('timeout'=>"pro6pp_timeout"),
                                                "description" => "Waiting time in seconds before " .
                                                         "giving up on communication with Pro6PP."
                                        )
                                )
                        )
                ),
                'pro6pp_autocomplete-configuration' => array(
                        'args' => array(
                                'title' => 'Configuration Settings',
                                'callback' => 'settings_configuration_pro6pp_autocomplete',
                                'page' => 'pro6pp_autocomplete-configuration'
                        ),
                        'fields' => array(
                                'pro6pp_autocomplete' => array(
                                        'title' => 'Configuration Settings',
                                        'callback' => 'settings_field_input_chkBox',
                                        'page' => 'pro6pp_autocomplete-configuration',
                                        'section' => 'pro6pp_autocomplete-configuration',
                                        'args' => array( // Args
                                                'fields' => array(
                                                        'autocomplete' => "pro6pp_autocomplete"
                                                ),
                                                'description' => __(
                                                        "If enabled it will force the form " .
                                                         "to autocomplete before submission.",
                                                        "pro6pp_autocomplete")
                                        )
                                ),
                                'pro6pp_validation' => array(
                                        'title' => 'Enforce Validation',
                                        'callback' => 'settings_field_input_chkBox',
                                        'page' => 'pro6pp_autocomplete-configuration',
                                        'section' => 'pro6pp_autocomplete-configuration',
                                        'args' => array(
                                                'fields' => array(
                                                        'validation' => "pro6pp_validation"
                                                ),
                                                'description' => __(
                                                        "When set to 'on', the script will required a valid " .
                                                         "address present in the Pro6PP database. Set to 'off'" .
                                                         " to allow entering a custom address.",
                                                        "pro6pp_autocomplete")
                                        )
                                ),
                                'pro6pp_degrade' => array(
                                        'title' => 'Gracefully Degrade',
                                        'callback' => 'settings_field_input_chkBox',
                                        'page' => 'pro6pp_autocomplete-configuration',
                                        'section' => 'pro6pp_autocomplete-configuration',
                                        'args' => array(
                                                'fields' => array(
                                                        'degrade' => "pro6pp_degrade"
                                                ),
                                                'description' => __(
                                                        "When set to 'on', the script will never block the " .
                                                         "process in case of trouble communicating with Pro6PP." .
                                                         "Set to 'off' to prevent users from entering a " .
                                                         "custom address.",
                                                        "pro6pp_autocomplete")
                                        )
                                ),
                                'pro6pp_security' => array(
                                        'title' => 'Secure calls via referer validation',
                                        'callback' => 'settings_field_input_chkBox',
                                        'page' => 'pro6pp_autocomplete-configuration',
                                        'section' => 'pro6pp_autocomplete-configuration',
                                        'args' => array(
                                                'fields' => array(
                                                        'security' => "pro6pp_security"
                                                ),
                                                'description' => __(
                                                        "When enabled, the user requests towards the pro6pp service will be validated against your sites domain. You need to configure the URL of your website in the Pro6PP CMS.",
                                                        "pro6pp_autocomplete")
                                        )
                                ),
                                'pro6pp_feedback' => array(
                                        'title' => 'Provide Feedback',
                                        'callback' => 'settings_field_input_chkBox',
                                        'page' => 'pro6pp_autocomplete-configuration',
                                        'section' => 'pro6pp_autocomplete-configuration',
                                        'args' => array(
                                                'fields' => array(
                                                        'feedback' => "pro6pp_feedback"
                                                ),
                                                'description' => __(
                                                        "Enable the use of the feedback call " .
                                                         "to improve the PRO6PP database by " .
                                                         "switching this setting to true." .
                                                         "For more information: " .
                                                         "http://d-centralize.nl/pro6pp/examples/feedback",
                                                        "pro6pp_autocomplete")
                                        )
                                )
                        )
                )
        );
        return $pro6pp_settings_fields;
    }

    /**
     * Displays all messages registered to 'pro6pp_settings_notice'
     * It shows save notifications to the user.
     */
    public function pro6pp_admin_notices_action() {
        settings_errors('pro6pp_settings_notice');
    }

    /**
     * Callback.
     * Validation rule when user tries to save the password settings value.
     */
    public function validate_password_input ($data)
    {
        $message = 'Password error';
        $type = 'error';
        if ($data == null || empty($data)) {
            $message = 'Authentication key cannot be empty.';
            $data = '';
        } else {
            $init = strlen($data);
            $data = $data = preg_replace('/[^A-Za-z0-9]+/i', '', trim($data));
            if (strlen($data) !== $init || strlen($data) < 4) {
                $message = 'The given authentication key is too short, or it contains invalid characters.';
                $data = '';
            } else {
                $message = 'Authentication key saved succesfully.';
                $type = 'updated';
            }
        }
        add_settings_error('pro6pp_settings_notice', esc_attr('settings_update'),
                __($message, 'pro6pp_autocomplete'), $type);
        return $data;
    }

    /**
     * Callback.
     * Validation rule when user tries to save the timeout settings value.
     */
    public function validate_timeout_input ($data)
    {
        $type = 'error';
        $message = 'Timeout error.';
        $default = 5;

        if ($data == null || empty($data)) {
            $message = 'Timeout cannot be empty, Default value is used instead.';
            $data = $default;
        } else {
            if ($data = intval($data, 10)) {
                if ($data > 15 || $data < 2) {
                    $message = 'Timeout value too large or too small, falling back to default.';
                    $data = $default;
                } else {
                    $type = 'updated';
                    $message = 'Timeout value updated succesfully.';
                }
            }
       }
        add_settings_error('pro6pp_settings_notice', esc_attr('settings_update'),
                __($message, 'pro6pp_autocomplete'), $type);
        return $data;
    }

    /**
     * Callback for "add_settings_section"
     * Echoes a description title for the connection settings, or nothing.
     */
    public function settings_connection_pro6pp_autocomplete ()
    {
        // Think of this as help text for that section.
        // The variables below are used inside the template.
        $cms = 'http://www.pro6pp.nl/cms';
        $more = 'http://www.d-centralize.nl/pro6pp';
        include (sprintf("%s/templates/service_info.php", dirname(__FILE__)));
    }

    /**
     * Callback for "add_settings_section"
     * Echoes a description title for the configuration settings, or
     * nothing.
     */
    public function settings_configuration_pro6pp_autocomplete ()
    {
        echo __('These setting are related to the user interaction.',
                'pro6pp_autocomplete');
        echo '<br>';
        echo __('Check the boxes next to the options you would like to enable.',
                'pro6pp_autocomplete');
    }

    /**
     * Callback for "add_settings_field"
     * Echoes paired radio buttons (true, false).
     *
     * @param Array $args
     * @see add_settings_field
     */
    public function settings_field_input_chkBox ($args)
    {

        // Print a description tooltip if given.
        if (isset($args['description']))
            echo sprintf(
                    '<img class="help_tip" data-tip="%s" src="%s" height="16"' .
                             ' width="16" />&nbsp;',
                            esc_attr($args['description']),
                            esc_url(
                                    $GLOBALS['woocommerce']->plugin_url() .
                                     '/assets/images/help.png'));
            // Keep only the "field" array.
        $args = $args['fields'];
        foreach ($args as $name => $id) {
            // The value of the checkbox should always be 1.
            $default = 1; // get_option($id, 1);
            // Display the radio button.
            echo sprintf(
                    '<input type="checkbox" name="%s" id="%s" value=%d %s/>',
                    $id,                     // name
                    $id,                     // id
                    $default, get_option($id, 0) ? 'checked="checked" ' : '');
        }
    }

    /**
     * Callback for "add_settings_field".
     * This function outputs text inputs for settings fields.
     * Accepts an array containing the field name.
     * Optionally: Accepts a description to show.
     * Uses tooltip image from woocommerce resources.
     * Optionally: If exists a key "password", the type will be of password.
     * If the value of that key is true, a checkbox will be included
     * to the form to allow switching between visible/hidden input.
     *
     * @param array $args.
     *            An array containing at least a 'field' key/value pair.
     * @uses woocommerce help image.
     */
    public function settings_field_input_text ($args)
    {
        // Get the field name/id from the $args array
        $field = $args['fields'][key($args['fields'])];
        // Get the stored value of this setting.
        $value = get_option($field, '');
        $type = isset($args['password']) ? 'password' : 'text';

        // Print a description tooltip if given.
        if (isset($args['description']))
            echo sprintf(
                    '<img class="help_tip" data-tip="%s" src="%s
                    " height="16" width="16" alt="i" />&nbsp;',
                    esc_attr($args['description']),
                    esc_url(
                            $GLOBALS['woocommerce']->plugin_url() .
                                     '/assets/images/help.png'));
            // Print the field.
        echo sprintf('<input type="%s" name="%s" id="%s" value="%s" />', $type,
                $field,                 // name
                $field,                 // id
                $value);
        // If defined, include a show/hide password checkbox, uses JavaScript.
        if (isset($args['password']) && $args['password']) {
            $script = "function change(id){";
            $script .= "this.checked = !this.checked;";
            $script .= "e = document.getElementById(id);";
            $script .= "this.checked ? e.type ='text' : ";
            $script .= "e.type ='password';}";
            echo sprintf('<script type="text/javascript">%s</script>', $script);
            $name = 'hiddenPass';
            echo sprintf(
                    '<br /><span>Show key:&nbsp</span><input type="checkbox" ' .
                             'name="%s" id="%s" onchange="%s" />', $name, $name,
                            "change('{$field}')");
        }
    }

    /**
     * Adds a menu option under woocommerce category.
     */
    public function add_menu ()
    {
        add_submenu_page('woocommerce',
                __('Pro6PP Autocomplete', 'pro6pp_autocomplete'),
                __('Pro6PP', 'pro6pp_autocomplete'), 'manage_woocommerce',
                'pro6pp_autocomplete',
                array(
                        &$this,
                        'pro6pp_settings_page'
                ));
    }

    /**
     * Defines the tabs this template supports.
     * Each tab can present sections of fields defined in init_settings().
     * Each section has fields that belog to a group.
     * The fields to be saved are defined by the group.
     * NOTE: The tabs should be defined in the same order as returned from
     * get_custom_fields().
     *
     * @return array : An array with the page tab as key and an array of
     *         section and group key=>values as the value.
     */
    function get_template_settings ()
    {
        $tabs = array(
                'connection' => array(
                        'section' => '',
                        'group' => ''
                ),
                'configuration' => array(
                        'section' => '',
                        'group' => ''
                )
        );

        $settings = $this->get_custom_fields();
        foreach ($settings as $section => $data) {
            $tabs[key($tabs)]['section'] = $section;
            $tabs[key($tabs)]['group'] = $section;
            next($tabs);
        }
        unset($settings);

        return $tabs;
    }

    /**
     * Menu Callback.
     * The settings page to display.
     */
    public function pro6pp_settings_page ()
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(
                    __(
                            'You do not have sufficient' .
                                     ' permissions to access this page.',
                                    'pro6pp_autocomplete'));
        }

        // This variable is used inside the template.
        $tabs = $this->get_template_settings();

        // Render the settings template
        include (sprintf("%s/templates/settings.php", dirname(__FILE__)));
    }

}
