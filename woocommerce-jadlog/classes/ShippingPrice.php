<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPrice {

    public function __construct($postcode, $shipping_package) {
        $this->_shipping_package = $shipping_package;
        $this->_postcode         = $postcode;
    }

    public function get_estimated_value() {
        $values = $this->get_estimated_values();
        return $values['estimated_value'];
    }

    public function get_formatted_estimated_time() {
        $values = $this->get_estimated_values();
        return isset($values['estimated_time']) ?
            $values['estimated_time'].__(' dias úteis', 'jadlog') :
            '';
    }

    public function get_estimated_time() {
        $values = $this->get_estimated_values();
        return $values['estimated_time'];
    }

    private function get_estimated_values() {
        if (!isset($this->_estimated_values)) {
            include_once('ShippingPriceService.php');
            $service = new ShippingPriceService($this->_shipping_package->modalidade);
            $this->_estimated_values = $service->estimate(
                $this->_shipping_package->get_price(),
                $this->_postcode,
                $this->_shipping_package->get_effective_weight());
        }

        return $this->_estimated_values;
    }
}
