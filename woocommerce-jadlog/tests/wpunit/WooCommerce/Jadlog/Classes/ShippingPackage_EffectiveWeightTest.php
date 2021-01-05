<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPackage_EffectiveWeightTest extends \Codeception\TestCase\WPTestCase
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

    public function test_effective_weight_when_weight_is_greater() {
        $this->product->set_props(
            array('weight' => 75.1, 'width' => 50, 'length' => 50, 'height' => 100));
        $this->product->save();

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', Modalidade::MODAL_RODOVIARIO);
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(225.3, $this->subject->get_effective_weight());
    }

    public function test_effective_weight_when_volumetric_weight_is_greater() {
        $this->product->set_props(
            array('weight' => 74.9, 'width' => 50, 'length' => 50, 'height' => 100));
        $this->product->save();

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', Modalidade::MODAL_RODOVIARIO);
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(225, $this->subject->get_effective_weight());
    }

    public function test_effective_weight_when_volumetric_weight_is_empty() {
        $this->product->set_weight(1.11);
        $this->product->save();

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', Modalidade::MODAL_RODOVIARIO);
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(3.33, $this->subject->get_effective_weight());
    }

    public function test_effective_weight_when_weight_is_zero() {
        $this->product->set_weight(null);
        $this->product->save();

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', Modalidade::MODAL_RODOVIARIO);
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(0, $this->subject->get_effective_weight());
    }
}
