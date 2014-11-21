<?php
require_once (dirname(
        __DIR__) . '/pro6pp.php');

class Pro6ppTest extends WP_UnitTestCase
{

    /**
     * Fancy function to call private members of classes.
     * Uses reflection
     *
     * @uses php >= 5.3.4
     * @see http://stackoverflow.com/questions/5937845/mock-private-method-with-phpunit
     * @param Class $object
     * @param Function $methodName
     */
    public function callPrivateMethod ($object, $methodName)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionMethod = $reflectionClass->getMethod(
                $methodName);
        $reflectionMethod->setAccessible(
                true);

        $params = array_slice(
                func_get_args(),
                2); // get all the
                                                           // parameters after
                                                           // $methodName
        return $reflectionMethod->invokeArgs(
                $object,
                $params);
    }

    function setUp ()
    {
        parent::setUp();
        $this->pro6pp = new Pro6pp();
        // Suppress warnings from "Cannot modify header information - headers
        // already sent by"
        $this->_error_level = error_reporting();
        error_reporting(
                $this->_error_level & ~ E_WARNING);
    }

    function testPro6pp ()
    {
        // Assume success.
        /*   $this->assertTrue(
                true); */
    }

    /**
     * @expectedException WPDieException
     */
    function testInvalidRefererWithValidateReferer ()
    {
        $_SERVER['HTTP_REFERER'] = 'Possible_jiberish_example_from_another_domain..';
        $this->assertNotEmpty(
                $this->callPrivateMethod(
                        $this->pro6pp,
                        'validate_referer'));
    }

    function testValidRefererWithValidateReferer ()
    {
        $_SERVER['HTTP_REFERER'] = site_url() . '?foo=bar&bar=foo';
        $this->assertEmpty(
                $this->callPrivateMethod(
                        $this->pro6pp,
                        'validate_referer'));
        $_SERVER['HTTP_REFERER'] = admin_url() . '/some/stuff/here';
        $this->assertEmpty(
                $this->callPrivateMethod(
                        $this->pro6pp,
                        'validate_referer'));
    }

    function testSettingsLink ()
    {
        $links = array(
                'dummy' => 'string'
        );
        $adminUrl = admin_url();
        $expected = array(
                'dummy' => 'string',
                'Settings' => '<a href="' . $adminUrl .
                         'admin.php?page=pro6pp_autocomplete">Settings</a>'
        );
        $this->assertArrayHasKey(
                'Settings',
                $this->pro6pp->pro6pp_settings_link(
                        $links));
        $this->assertArrayHasKey(
                'dummy',
                $this->pro6pp->pro6pp_settings_link(
                        $links));
        $this->assertEqualSets(
                $expected,
                $this->pro6pp->pro6pp_settings_link(
                        $links));
    }

    function testClientScripts ()
    {
    }
}

