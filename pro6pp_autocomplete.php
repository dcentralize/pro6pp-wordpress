<?php
/**
 * Plugin Name: Pro6PP postcode autocomlpete.
 * Plugin URI: http://www.d-centralize.nl
 * Description: Autocomplete the address forms by adding only your postcode and
 * streetnumber.
 * Version: 0.4
 * Author: d-centralize
 * Author URI: http://www.d-centralize.com
 * License: GPL2
 */
/**
 * According to WordPress,
 * License Goes HERE..
 */
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly

// Check if WooCommerce is active.
if (! in_array('woocommerce/woocommerce.php',
        apply_filters('active_plugins', get_option('active_plugins'))))
    return;

if (! function_exists('array_insert')) {

    /**
     * Insert an array with [key => value] pairs into another array.
     *
     * @param {&array} $array
     * @param {integer} $position
     * @param {array} $insert_array
     */
    function array_insert (&$array, $position, $insert_array)
    {
        $first_array = array_splice($array, 0, $position);
        $array = array_merge($first_array, $insert_array, $array);
    }
}

if (! class_exists('Pro6pp')) {
    require_once (sprintf("%s/pro6pp.php", dirname(__FILE__)));
}

// Enable the class
$pro6ppAutocomplete = new Pro6pp();
