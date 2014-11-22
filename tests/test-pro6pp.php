<?php
require_once (dirname(__DIR__) . '/pro6pp.php');

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
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        $reflectionMethod->setAccessible(true);

        $params = array_slice(func_get_args(), 2); // get all the
                                                   // parameters after
                                                   // $methodName
        return $reflectionMethod->invokeArgs($object, $params);
    }

    function setUp ()
    {
        parent::setUp();
        $this->pro6pp = new Pro6pp();
        // Suppress warnings from "Cannot modify header information - headers
        // already sent by"
        $this->_error_level = error_reporting();
        error_reporting($this->_error_level & ~ E_WARNING);
    }

    function testSettingsLink ()
    {
        $links = array(
                'dummy' => 'string'
        );
        $expected = array(
                'dummy' => 'string',
                'Settings' => '<a href="' . admin_url() .
                         'admin.php?page=pro6pp_autocomplete">Settings</a>'
        );
        $this->assertArrayHasKey('Settings',
                $this->pro6pp->pro6pp_settings_link($links));
        $this->assertArrayHasKey('dummy',
                $this->pro6pp->pro6pp_settings_link($links));

        $this->assertEqualSets($expected,
                $this->pro6pp->pro6pp_settings_link($links));
    }

    function testWoocommerceStates ()
    {
        // Dummy values with their equivelant states.
        $countries = $expected = array(
                'GB' => array(
                        'dummy',
                        'value'
                ),
                'NL' => array(
                        'foo' => 'bar'
                ),
                'BE' => array(
                        'bar' => 'foo'
                ),
                'US' => array(
                        'dummy' => 'data'
                )
        );
        $actual = $this->pro6pp->pro6pp_woocommerce_states($countries);

        $this->assertArrayHasKey('GB', $actual);
        $this->assertArrayHasKey('NL', $actual);
        $this->assertArrayHasKey('BE', $actual);
        $this->assertArrayHasKey('bar', $actual['BE']);
        $this->assertArrayHasKey('US', $actual);

        $this->assertTrue(is_array($actual['NL']));

        $this->assertArrayNotHasKey('foo', $actual['NL']);

        $this->assertEquals(12, count($actual['NL']));
    }

    function testOverrideAddressFields ()
    {
        $address_fields = $expected = array(
                'dummy' => array(
                        'foo' => 'bar'
                ),
                'address_2' => array(
                        'label' => 'LABEL',
                        'class' => 'CSS_CLASS'
                )
        );
        $expectedAdress2Class = array(
                'form-row',
                'form-row-wide'
        );

        $actual = $this->pro6pp->pro6pp_override_default_address_fields(
                $address_fields);

        $this->assertEquals('Streetnumber', $actual['address_2']['label']);
        $this->assertTrue(is_array($actual['address_2']['class']));
        $this->assertEquals($expectedAdress2Class,
                $actual['address_2']['class']);
    }

    function test_Empty_OverrideAddressFields ()
    {
        $address_fields = $expected = array(
                'dummy' => array(
                        'foo' => 'bar'
                ),
                'dumm-dummy' => array(
                        'foo' => 'bar',
                        'bar' => 'foo'
                )
        );
        $actual = $this->pro6pp->pro6pp_override_default_address_fields(
                $address_fields);

        // We set the key explicitly if not present. Is that OK or refactor?
        $this->arrayHasKey('address_2', $actual);
    }

    function testOverrideDefaultCheckoutFields ()
    {
        $this->markTestSkipped('Not implemented.');
    }

    function testAddressValidation ()
    {
        $this->markTestSkipped('Not implemented.');
    }

    function testHandleRequest ()
    {
        $this->markTestSkipped('Not implemented.');
    }

    function testErrorOccured ()
    {
        $this->markTestSkipped('Not implemented.');
    }

    /**
     * @expectedException WPDieException
     */
    function testInvalidRefererWithValidateReferer ()
    {
        $_SERVER['HTTP_REFERER'] = 'Possible_jiberish_example_from_another_domain..';
        $this->assertNotEmpty(
                $this->callPrivateMethod($this->pro6pp, 'validate_referer'));
    }

    function testValidRefererWithValidateReferer ()
    {
        $_SERVER['HTTP_REFERER'] = site_url() . '?foo=bar&bar=foo';
        $this->assertEmpty(
                $this->callPrivateMethod($this->pro6pp, 'validate_referer'));
        $_SERVER['HTTP_REFERER'] = admin_url() . '/some/stuff/here';
        $this->assertEmpty(
                $this->callPrivateMethod($this->pro6pp, 'validate_referer'));
    }
}
