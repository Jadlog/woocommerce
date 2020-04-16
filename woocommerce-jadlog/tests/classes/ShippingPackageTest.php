<?php
namespace WooCommerce\Jadlog\Classes;

use WooCommerce\Jadlog\Classes\Modalidade;
use WooCommerce\Jadlog\Classes\ShippingPackage;

class ShippingPackgeTest extends \WP_UnitTestCase {

    public function setUp() {
        parent::setUp();

        include_once("Modalidade.php");
        include_once("ShippingPackage.php");
        $package = array('contents' => array(
            array('quantity' => 1, 'product_id' => 1)
        ));
        $this->subject = new ShippingPackage($package, Modalidade::COD_COM);
    }

    public function test_sample() {
        // Replace this with some actual testing code.
        $this->assertTrue( false );
    }

    public function wc_get_product() {
        return array();
    }
}

