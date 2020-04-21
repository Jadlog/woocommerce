<?php
namespace WooCommerce\Jadlog\Classes;

use WooCommerce\Jadlog\Classes\Modalidade;
use WooCommerce\Jadlog\Classes\ShippingPackage;

class ShippingPackgeTest extends \WP_UnitTestCase {

    private $subject, $product;

    public function setUp() {
        parent::setUp();

        include_once("Modalidade.php");
        include_once("ShippingPackage.php");
        $this->product = \WC_Helper_Product::create_simple_product();
        $this->product->set_weight(5.5);
        $this->product->save();
        $package = array('contents' => array(array(
            'quantity'   => 3,
            'product_id' => $this->product->get_id())
        ));
        $this->subject = new ShippingPackage($package, Modalidade::COD_COM);
    }

    public function test_get_weight() {
        $this->assertEquals(16.5, $this->subject->get_weight());
    }
}

