<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPackage_VolumetricWeightTest extends \Codeception\TestCase\WPTestCase
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

    public function test_volumetric_weight_with_invalid_option() {
        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', 'INVALID');
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertNull($this->subject->get_volumetric_weight());
    }

    public function test_volumetric_weight_with_dont_calculate_option() {
        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', 'NAO_CALCULAR');
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertNull($this->subject->get_volumetric_weight());
    }

    public function test_volumetric_weigth_with_empty_volume() {
        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', 'PADRAO');
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(0, $this->subject->get_volumetric_weight());
    }

    public function test_volumetric_weight_with_standard_option() {
        $this->product->set_props(array('width' => 50, 'length' => 50, 'height' => 100));
        $this->product->save();

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', 'PADRAO');
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(0.75 * 166.667, $this->subject->get_volumetric_weight());

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_rodoviario', 'PADRAO');
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_RODOVIARIO);
        $this->assertEquals(0.75 * 300, $this->subject->get_volumetric_weight());
    }

    public function test_volumetric_weight_with_rodo_option() {
        $this->product->set_props(array('width' => 50, 'length' => 50, 'height' => 100));
        $this->product->save();

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', Modalidade::MODAL_RODOVIARIO);
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(0.75 * 300, $this->subject->get_volumetric_weight());
    }

    public function test_volumetric_weight_with_aereo_option() {
        $this->product->set_props(array('width' => 50, 'length' => 50, 'height' => 100));
        $this->product->save();

        update_option('wc_settings_tab_jadlog_calcular_pesos_cubados_pickup', Modalidade::MODAL_AEREO);
        $this->subject = new ShippingPackage($this->package, Modalidade::COD_PICKUP);
        $this->assertEquals(0.75 * 166.667, $this->subject->get_volumetric_weight());
    }
}
