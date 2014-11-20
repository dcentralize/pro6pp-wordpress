<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly

/**
 * The main plugin class.
 * Responsible for the pro6pp communication to the service and the output of
 * the response to the client side.
 *
 * @name Pro6ppAutocomplete
 * @author dcentralize
 */
class Pro6pp
{

    /**
     * The action name used to call the pro6pp asynchronously.
     *
     * @var string $_ajaxAction
     */
    private $_ajaxAction = 'pro6pp';

    /**
     * The countries supported by Pro6PP service.
     * Values have to be in 2code country code.
     *
     * @var {array} $_countries Holds an array of 2 character country
     *      identifiers.
     */
    private static $_countries = array(
            'NL' => array(
                    'Drenthe',
                    'Flevoland',
                    'Friesland',
                    'Gelderland',
                    'Groningen',
                    'Limburg',
                    'Noord-Brabant',
                    'Noord-Holland',
                    'Overijssel',
                    'Utrecht',
                    'Zeeland',
                    'Zuid-Holland'
            ),
            'BE'
    );

    /**
     * Billing form variables, used in the JavaScript part.
     *
     * @var {array} $_billing
     */
    private static $_billing = array(
            'scope' => '#main',
            'prefix' => 'billing_',
            'company' => '#billing_company',
            'country' => '#billing_country',
            'postcode' => '#billing_postcode',
            'streetnumber' => '#billing_address_2',
            'street' => '#billing_address_1',
            'city' => '#billing_city',
            'province' => '#billing_state',
            'actualPostcodeField' => '#billing_postcode'
    );

    /**
     * Shipping form variables, used in the JavaScript part.
     *
     * @var {array} $_shipping
     */
    private static $_shipping = array(
            'scope' => '#main',
            'prefix' => 'shipping_',
            'company' => '#shipping_company',
            'country' => '#shipping_country',
            'postcode' => '#shipping_postcode',
            'streetnumber' => '#shipping_address_2',
            'street' => '#shipping_address_1',
            'city' => '#shipping_city',
            'province' => '#shipping_state',
            'actualPostcodeField' => '#shipping_postcode'
    );

    /**
     * A template array representing an error response from the service.
     * Is used to manipulate error messages and respond them as a JSON
     * response.
     *
     * @var {array} $_returnError
     */
    private $_returnError = array(
            'status' => 'error',
            'error' => array(
                    'message' => 'Error'
            )
    );

    /**
     * General purpose variables, used in the JavaScript part.
     *
     * @var {array} $_pro6pp
     */
    private $_pro6pp;

    /**
     * Construct the plugin object
     */
    public function __construct ()
    {
        // Load translations.
        add_action('init',
                array(
                        $this,
                        'load_translation'
                ));
        // Get the settings page.
        require_once (sprintf("%s/settings.php", dirname(__FILE__)));
        // Installation and uninstallation hooks
        register_activation_hook(__FILE__,
                array(
                        'Pro6pp',
                        'activate'
                ));
        register_deactivation_hook(__FILE__,
                array(
                        'Pro6ppAutocomplete',
                        'deactivate'
                ));
        $this->_settings = new Pro6ppSettings();
        // Add the settings link in the plugins page.
        add_filter("plugin_action_links_" . plugin_basename(__FILE__),
                array(
                        $this,
                        'pro6pp_settings_link'
                ));

        // Add scripts
        add_filter('pre_get_posts',
                array(
                        $this,
                        'add_client_script'
                ));
        // Add custom validation for the address input.
        add_action('woocommerce_checkout_process',
                array(
                        $this,
                        'pro6pp_address_validation'
                ));

        // Create custom postcode and streetnumber fields.
        add_filter('woocommerce_checkout_fields',
                array(
                        $this,
                        'custom_override_checkout_fields'
                ));

        // Manipulate the default address fields.
        add_filter('woocommerce_default_address_fields',
                array(
                        $this,
                        'custom_override_default_address_fields'
                ));

        // Add provinces for the supported countries.
        add_filter('woocommerce_states',
                array(
                        $this,
                        'custom_woocommerce_states'
                ));

        // add_filter();

        // Ajax - Logged in users.
        add_action("wp_ajax_$this->_ajaxAction",
                array(
                        $this,
                        'pro6pp_handle_request'
                ));
        // Ajax - Guest users.
        add_action("wp_ajax_nopriv_$this->_ajaxAction",
                array(
                        $this,
                        'pro6pp_handle_request'
                ));

        $countryCodes = array();
        foreach (self::$_countries as $cc => $value)
            $countryCodes[] = is_numeric($cc) ? $value : $cc;

            // Generic variables for use in JavaScript.
        $this->_pro6pp = array(
                'url' => admin_url('admin-ajax.php'),
                'action' => $this->_ajaxAction,
                'timeout' => get_option('pro6pp_timeout', 1),
                'spinnerSrc' => plugins_url('assets/ajax-loader.gif', __FILE__),
                'countries' => $countryCodes,
                'streetnumber' => __('Street Number', 'pro6pp_autocomplete'),
                // Localised error messages.
                'invalidPostcode' => __('Invalid postcode format.',
                        'pro6pp_autocomplete'),
                'invalidStreetnumber' => __('Invalid Streetnumber format.',
                        'pro6pp_autocomplete'),
                'serviceDown' => __(
                        'The Pro6PP service is currently unavailable.',
                        'pro6pp_autocomplete')
        );
    }

    /**
     * Manipulates the supported countries known to WC by adding their
     * provinces.
     *
     * @param {array} $states
     *            An array holding the default states known to WC.
     * @return {array} The states after manipulation.
     */
    function custom_woocommerce_states ($states)
    {
        $provinces;
        foreach (self::$_countries as $cc => $province) {
            $provinces = array();
            for ($i = 1, $max = count($province); $i <= $max; $i ++) {
                $provinces[$cc . $i] = $province[$i - 1];
            }
            $states[$cc] = $provinces;
        }
        unset($provinces);
        return $states;
    }

    /**
     * Changes the default options of some address fields to satisfy the
     * service's usability.
     *
     * @param {array} $address_fields
     *            The default options for the address fields
     * @return {array} The input array manipulated
     */
    function custom_override_default_address_fields ($address_fields)
    {
        $address_fields['address_2']['label'] = __('Streetnumber',
                'woocommerce');
        $address_fields['address_2']['class'] = array(
                'form-row',
                'form-row-wide'
        );
        return $address_fields;
    }

    /**
     * Change the order of the address fields to increase the service's
     * usability.
     *
     * @param {array} $fields
     *            The checkout fields to render.
     * @return {array} The input array manipulated.
     */
    function custom_override_checkout_fields ($groups)
    {
        $add_after_this = '_company';
        $knownFields = array(
                'country' => '_country',
                'postcode' => '_postcode',
                'address2' => '_address_2'
        );
        foreach ($groups as $category => $fields) {
            $idOfField = $knownFields;
            array_walk($idOfField,
                    function  (&$value, $key, $prepend)
                    {
                        $value = "$prepend$value";
                    }, $category);

            if (! array_key_exists($idOfField['country'], $fields))
                continue;

            $country = $fields[$idOfField['country']];
            $postcode = $fields[$idOfField['postcode']];
            $address2 = $fields[$idOfField['address2']];

            $pro6pp_ordered = array(
                    $idOfField['country'] => $country,
                    $idOfField['postcode'] => $postcode,
                    $idOfField['address2'] => $address2
            );

            $pos = $category . $add_after_this;
            $i = 0;
            foreach ($fields as $key => $value) {
                if ($key == $pos) {
                    $pos = $i;
                    break;
                } else
                    $i ++;
            }

            unset($fields[$idOfField['country']],
                    $fields[$idOfField['postcode']],
                    $fields[$idOfField['address2']]);

            array_insert($fields, $pos, $pro6pp_ordered);

            $groups[$category] = $fields;
        }

        return $groups;
    }

    /**
     * Process the adress validation on submissionif it's supported
     */
    public function pro6pp_address_validation ()
    {
        global $wp_http;
        // Check that fields were sent.
        if (empty($_REQUEST['billing_country']) || empty(
                $_REQUEST['billing_postcode'])) {
            return;
        }

        $api = 'http://api.pro6pp.nl/v1/autocomplete?';
        $data = http_build_query(
                array(
                        'auth_key' => get_option('pro6pp_auth_key', 'AUTH_KEY'),
                        'nl_sixpp' => wc_clean($_REQUEST['billing_postcode']),
                        'streetnumber' => wc_clean(
                                $_REQUEST['billing_address_2'])
                ), '', '&');
        $url = $api . $data;
        $http = new WP_Http();
        $result = $http->request($url,
                array( // Convert seconds to miliseconds.
                        'timeout' => (get_option('pro6pp_timeout', 5) * 1000)
                ));

        if ($result['response']['code'] == 200) {
            $values = json_decode($result['body']);
            if ($values->error !== 'ok') {
                return;
            }
        } elseif ($result['body']['nl_sixpp'] !== $postcode) {
            wc_add_notice(__("The <b>postcode</b> is not valid."), 'error');
        } else {
            wc_add_notice(__("The <b>postcode</b> is valid."), 'success');
        }
    }

    /**
     * Handle localization.
     *
     * @since 0.3
     */
    public function load_translation ()
    {
        load_plugin_textdomain('pro6pp_autocomplete', false,
                dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Activate the plugin
     */
    public static function activate ()
    {
        // Do nothing
    }

    /**
     * Deactivate the plugin
     */
    public static function deactivate ()
    {
        // Do nothing
    }

    /**
     * Add the "settings" link to the plugins page.
     *
     * @param Array $links
     * @return Array:
     */
    public function pro6pp_settings_link ($links)
    {
        $settings = array(
                'Settings' => sprintf('<a href="%s">%s</a>',
                        admin_url('admin.php?page=pro6pp_autocomplete'),
                        'Settings')
        );
        return array_merge($settings, $links);
    }

    /**
     * Accepts the frontend ajax request
     */
    public function pro6pp_handle_request ()
    {
        if (get_option('pro6pp_security', false))
            $this->validate_referer();

        if (empty($_GET['nl_sixpp']) || empty($_GET['streetnumber'])) {
            $this->error_occured('Empty postcode or streetnumber');
        }

        $key = get_option('pro6pp_auth_key', 'EMPTY');
        $postcode = rawUrlDecode($_GET['nl_sixpp']);
        $streetNr = preg_replace('/\s/', '',
                rawUrlDecode($_GET['streetnumber']));
        $postcode = preg_match('/(^[0-9]{4})(\s?)([a-zA-Z]{2})$/i', $postcode,
                $pMatches);
        $streetNr = preg_match('/^([0-9]+)(.?)([a-zA-Z]{0,2})$/', $streetNr,
                $sMatches);

        // Define the response type. Suppress possible errors from showing.
        if (! headers_sent()) {
            header("Content-Type: application/json");
        }

        // If correct input was given, the first match is used.
        if (! $postcode || ! $streetNr) {
            $this->error_occured('Invalid postcode or Streetnumber');
        }
        $api = 'http://api.pro6pp.nl/v1/autocomplete?';
        $data = http_build_query(
                array(
                        'auth_key' => $key,
                        'nl_sixpp' => $pMatches[0],
                        'streetnumber' => $sMatches[0]
                ), '', '&');
        unset($pMatches, $sMatches);

        // Initiate the request to the pro6pp service.
        $url = $api . $data;
        $pro6ppService = new WP_Http();
        $result = $pro6ppService->request($url,
                array( // Convert seconds to miliseconds.
                        'timeout' => (get_option('pro6pp_timeout', 5) * 1000)
                ));

        if (is_a($result, 'WP_Error') || $result['response']['code'] !== 200) {
            $this->error_occured('Service is currently unavailable.');
        } else {
            echo $result['body'];
        }

        wp_die();
    }

    /**
     * Responds an error in JSON format and exits the script.
     *
     * @param {string} $msg
     *            The message describing the error.
     */
    private function error_occured ($msg)
    {
        if (! headers_sent()) {
            header("Content-Type: application/json");
        }
        $this->_returnError['error']['message'] = __($msg,
                'pro6pp_autocomplete');
        echo json_encode($this->_returnError);
        wp_die();
    }

    /**
     * Tries to authenticate the origin of the request.
     * If the referer doesn't exist, or the referer was from a different
     * domain,
     * the function will stop execution and respond an error status.
     * The error status will be in JSON format.
     */
    private function validate_referer ()
    {
        $errorMsg = "An error occured, please contact the site's administrator.";
        // Match the referer contains this site's url.
        if (! wp_get_referer()) {
            $this->error_occured($errorMsg);
        }
    }

    /**
     * Injects the required scripts and variables according to the form
     * the user requested.
     * Returns if not in scope.
     *
     * @param unknown $query
     */
    public function add_client_script ($query)
    {
        $editPage = false;
        // Compatibility with WooCommerce < 2.1
        if (function_exists('is_wc_endpoint_url')) {
            $editPage = is_wc_endpoint_url('edit-address');
        } else {
            $editPage = $query->get('page_id') == wc_get_page_id('edit_address');
        }

        // Include the scripts only when relevant pages are requested.
        if ($editPage || $query->get('page_id') == wc_get_page_id('checkout')) {
            wp_register_script('pro6pp_reorder',
                    plugins_url('/js/pro6pp_reorder.js', __FILE__),
                    array(
                            'jquery',
                            'woocommerce'
                    ));
            wp_register_script('pro6pp_autocomplete',
                    plugins_url('/js/pro6pp_autocomplete.js', __FILE__),
                    array(
                            'jquery',
                            'woocommerce',
                            'pro6pp_reorder'
                    ));

            // Add the scripts to the page.
            wp_enqueue_script('pro6pp_reorder');
            wp_enqueue_script('pro6pp_autocomplete');

            // Distinguish the type of form.
            $form = 'both';
            if (isset($_GET['address'])) {
                if ($_GET['address'] == 'billing' ||
                         $_GET['address'] == 'shipping')
                    $form = esc_attr($_GET['address']);
            }

            // Inject the generic variables for both scripts.
            wp_localize_script('pro6pp_autocomplete', 'pro6pp', $this->_pro6pp);
            wp_localize_script('pro6pp_reorder', 'pro6pp', $this->_pro6pp);

            // Inject the form variables.
            $this->localizeFormVariables($form);
        } else
            return;
    }

    /**
     * Passes the initializing variables for the JavaScript scripts,
     * according to the rendered form.
     * When called, the autocomplete script needs to be already
     * registered and enqued with the respective WP functions.
     *
     * @param {string} $formType
     *            Is one of the two: "shipping", "billing".
     *            Otherwise it defaults to output both scripts.
     */
    public function localizeFormVariables ($formType)
    {
        switch ($formType) {
            case 'billing':
                wp_localize_script('pro6pp_autocomplete', 'billing_fields',
                        self::$_billing);
            break;
            case 'shipping':
                wp_localize_script('pro6pp_autocomplete', 'shipping_fields',
                        self::$_shipping);
            break;
            default: // Both
                wp_localize_script('pro6pp_autocomplete', 'billing_fields',
                        self::$_billing);
                wp_localize_script('pro6pp_autocomplete', 'shipping_fields',
                        self::$_shipping);
            break;
        }
    }
}
