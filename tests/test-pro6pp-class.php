<?php
require_once (dirname(__DIR__) . '/pro6pp.php');

class WP_Test_Pro6pp_Class extends WP_UnitTestCase
{

    function setUp ()
    {
        parent::setUp();
        $this->pro6pp = new Pro6pp();
        // Suppress warnings from "Cannot modify header information - headers
        // already sent by"
        $this->_error_level = error_reporting();
        error_reporting($this->_error_level & ~ E_WARNING);
    }

    function testPro6pp ()
    {
        // Assume success.
        $this->assertTrue(true);
    }

    function testWoocommerceInstalled ()
    {
        $this->markTestSkipped(
                'pro6pp tests are pending creation.'
        );
    }
}

