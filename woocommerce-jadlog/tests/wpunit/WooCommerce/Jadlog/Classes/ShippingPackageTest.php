<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPackageTest extends \Codeception\TestCase\WPTestCase
{
    protected $tester;

    private $subject, $product;

    public function setUp(): void {
        parent::setUp();

        $this->product = $this->tester->create_simple_product();
        $this->package = array('contents' => array(array(
            'quantity'   => 3,
            'product_id' => $this->product->get_id())));
    }

    public function test_get_price() {
        $this->product->set_regular_price(23.99);
        $this->product->save();
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM);
        $this->assertEquals(71.97, $this->subject->get_price());
    }

    public function test_get_empty_price() {
        $this->product->set_props(
            array('sale_price' => '', 'regular_price' => '', 'price' => ''));
        $this->product->save();
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM);
        $this->assertEquals(0, $this->subject->get_price());
    }

    public function test_get_volume_in_m3() {
        $this->product->set_props(
            array('width' => 10, 'length' => 20, 'height' => 30));
        $this->product->save();
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM);
        $this->assertEquals(0.018, $this->subject->get_volume());

        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM,
            array('dimension_converter' => new DimensionConverter('m')));
        $this->assertEquals(18000, $this->subject->get_volume());
    }

    public function test_get_empty_volume() {
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM);
        $this->assertEquals(0, $this->subject->get_volume());
    }

    public function test_get_weight_in_kg() {
        $this->product->set_weight(5.1);
        $this->product->save();
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM);
        $this->assertEquals(15.3, $this->subject->get_weight());

        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM,
            array('weight_converter' => new WeightConverter('g')));
        $this->assertEquals(0.0153, $this->subject->get_weight());
    }

    public function test_get_empty_weight() {
        $this->product->set_weight(null);
        $this->product->save();
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_COM);
        $this->assertEquals(0, $this->subject->get_weight());
    }
}

