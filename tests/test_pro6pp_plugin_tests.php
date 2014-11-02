<?php
// Include the plugin class.
require_once dirname(__DIR__) . '/pro6pp_postcode_autocomplete.php';

/**
 * Tests to test that that testing framework is testing tests.
 * Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_Pro6pp_Plugin_Tests extends WP_UnitTestCase
{
    /**
     * (non-PHPdoc)
     * @see WP_UnitTestCase::setUp()
     */
    function setUp ()
    {
        parent::setUp();
        $this->pro6pp = new Pro6pp();
        // Suppress warnings from "Cannot modify header information - headers already sent by"
        $this->_error_level = error_reporting();
        error_reporting( $this->_error_level & ~E_WARNING );
    }

    /**
     * If these tests are being run on Travis CI, verify that the version of
     * WordPress installed is the version that we requested.
     */
    function test_wp_version ()
    {
        if (! getenv('TRAVIS'))
            $this->markTestSkipped(
                'Test skipped since Travis CI was not detected.');

        $requestedVersion = getenv('WP_VERSION') . '-src';

        // The "master" version requires special handling.
        if ($requestedVersion == 'master-src') {
            $file = file_get_contents(
                'https://raw.github.com/tierra/wordpress/master/src/wp-includes/version.php'
            );
            preg_match('#\$wp_version = \'([^\']+)\';#', $file, $matches);
            $requestedVersion = $matches[1];
        }

        $this->assertEquals(get_bloginfo('version'), $requestedVersion);
    }

    /**
     * Ensure that the plugin has been installed and activated.
     */
    function test_pro6pp_activated ()
    {
        $this->assertTrue(is_plugin_active('pro6pp/pro6pp_autocomplete.php'));
    }

    /**
     * Ensure that the plugin has been installed and activated.
     */
    function test_woocommerce_activated ()
    {
        $this->assertTrue(is_plugin_active('woocommerce/woocommerce.php'));
    }

    /**
     * Ensure the settings button function is correct.
     */
    function test_pro6pp_settings_link ()
    {
        $parameter = array(
                        'That is' => 'The expected format'
        );
        $expected = array(
                        'Settings' => '<a href="' .
                                 admin_url(
                                     'admin.php?page=pro6pp_autocomplete'
                                 ) .
                                 '">Settings</a>',
                                 'That is' => 'The expected format'
        );
        $this->assertEquals(
            $expected,
            $this->pro6pp->pro6pp_settings_link($parameter)
        );
    }
    /**
     * Mock the WP_Http class that calls the service and stub the returned value.
     *
     * @expectedException WPDieException
     * @see WP_Test_Pro6pp_Plugin_Tests::test_pro6pp_request_handler_with_wrong_postcode()
     */
    function test_pro6pp_request_handler_with_correct_postcode()
    {
        $this->markTestIncomplete('Needs a mock for the WP_Http class.');

        $http = $this->getMock('WP_Http');
        $http->expects($this->any())
        ->method('request')
        ->will(
                $this->returnValue(
                        array(
                                'response' => array(
                                        'code' => 200
                                ),
                                'body' => json_encode(
                                        '{
                    "status": "ok",
                    "results": [
                        {
                            "nl_sixpp": "5408XB",
                            "street": "Reestraat",
                            "city": "Volkel",
                            "municipality": "Uden",
                            "province": "Noord-Brabant",
                            "streetnumbers": "10-56",
                            "lat": 51.64487,
                            "lng": 5.65168,
                            "areacode": "0413"
                        }
                    ]
                }')
                        )));
        $valid_postcodes = array('5408xb','5408XB', '5408 xb', '5408 XB',);
        $_GET['nl_sixpp'] = "";
        $_GET['streetnumber'] = "1";
        $expected = '{status:"ok"}';
        foreach ($valid_postcodes as $zip){
            $_GET['nl_sixpp'] = $zip;
            //$this->assertEquals($expected, $this->pro6pp->pro6pp_handle_request());

        }
    }

    /**
     * Mock the WP_Http class that calls the service and stub the returned value.
     *
     * @expectedException WPDieException
     * @see WP_Test_Pro6pp_Plugin_Tests::test_pro6pp_request_handler_with_wrong_postcode()
     */
    function test_pro6pp_request_handler_with_correct_streetNr()
    {
        $this->markTestIncomplete('Needs a mock for the WP_Http class.');

        $valid_streetNums = array('1', '1a', '1 ab', '1-a');
        $_GET['nl_sixpp'] = "abcdas";
        $_GET['streetnumber'] = "asdf";
    }

    /**
     * Wordpress throws an exception because of the wp_die() call at the end of the function.
     * As far as the test expects the error, we know that the call is actually happening.
     * Thus I leave it as is for now, instead of pocking around for a solution.
     *
     * @expectedException WPDieException
     */
    function test_pro6pp_request_handler_with_wrong_postcode()
    {
        $_GET['nl_sixpp'] = "abcdas";
        $_GET['streetnumber'] = 1;
        $expected = '{"status":"error","error":{"message":"Invalid postcode or Streetnumber"}}';
        $this->assertEquals($expected, (string)$this->pro6pp->pro6pp_handle_request());
        $this->expectOutputString($expected);
    }

    /**
     * @expectedException WPDieException
     * @see WP_Test_Pro6pp_Plugin_Tests::test_pro6pp_request_handler_with_wrong_postcode()
     */
    function test_pro6pp_request_handler_with_wrong_streetNr()
    {
        $_GET['nl_sixpp'] = "5408xb";
        $_GET['streetnumber'] = "asdf";
        $expected = '{"status":"error","error":{"message":"Invalid postcode or Streetnumber"}}';
        $this->assertEquals($expected, (string)$this->pro6pp->pro6pp_handle_request());
        $this->expectOutputString($expected);

    }
}

