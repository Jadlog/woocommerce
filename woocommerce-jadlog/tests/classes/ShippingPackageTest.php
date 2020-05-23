<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPackgeTest extends \WP_UnitTestCase {

    private $subject, $product;

    public function setUp() {
        parent::setUp();

        include_once("Modalidade.php");
        include_once("ShippingPackage.php");
        $this->product = \WC_Helper_Product::create_simple_product();
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

