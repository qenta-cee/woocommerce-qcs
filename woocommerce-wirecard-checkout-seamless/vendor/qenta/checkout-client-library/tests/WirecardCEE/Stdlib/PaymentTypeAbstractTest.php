<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */


/**
 * Test class for WirecardCEE_Stdlib_PaymentTypeTest.
 * Generated by PHPUnit on 2011-06-24 at 13:17:01.
 */
use PHPUnit\Framework\TestCase;

class WirecardCEE_Stdlib_PaymentTypeTest extends TestCase
{

    /**
     * @var WirecardCEE_Stdlib_PaymentTypeTest
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {

    }

    public function testHasFinancialInstitutions()
    {
        $paymentType = WirecardCEE_Stdlib_PaymentTypeAbstract::EPS;
        $this->assertTrue(WirecardCEE_Stdlib_PaymentTypeAbstract::hasFinancialInstitutions($paymentType));
    }

    public function testHasNoFinancialInstitutions()
    {
        $paymentType = WirecardCEE_Stdlib_PaymentTypeAbstract::CCARD;
        $this->assertFalse(WirecardCEE_Stdlib_PaymentTypeAbstract::hasFinancialInstitutions($paymentType));
    }

    public function testGetIdealFinancialInstitutions()
    {
        $paymentType = WirecardCEE_Stdlib_PaymentTypeAbstract::IDL;
        $this->assertContains('ABN AMRO Bank',
            WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutions($paymentType));
        $this->assertArrayHasKey('REGIOBANK',
            WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutions($paymentType));
    }

    public function testGetEpsFinancialInstitutions()
    {
        $paymentType = WirecardCEE_Stdlib_PaymentTypeAbstract::EPS;
        $this->assertContains('BAWAG P.S.K. AG', WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutions($paymentType));
        $this->assertArrayHasKey('ARZ|VB',
            WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutions($paymentType));
    }

    public function testGetEmptyFinancialInstitutions()
    {
        $paymentType = WirecardCEE_Stdlib_PaymentTypeAbstract::CCARD;
        $this->assertEmpty(WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutions($paymentType));
    }

    public function testgetFinancialInstitutionFullName()
    {
        $this->assertEquals('Bank Austria',
            WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutionFullName('BA-CA'));
        $this->assertEquals('ABN AMRO Bank',
            WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutionFullName('ABNAMROBANK'));
        $this->assertEquals('', WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutionFullName(''));
    }
}

?>
