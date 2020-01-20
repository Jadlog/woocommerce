<?php
namespace WooCommerce\Jadlog\Classes;

class WeightConverter {

    const TO_KG = array(
        'kg'  => 1.0,
        'g'   => 0.001,
        'lbs' => 0.453592,
        'oz'  => 0.0283495
    );

    public function __construct($source_unit = null) {
        $this->source_unit = isset($source_unit) ? $source_unit : get_option('woocommerce_weight_unit');
    }

    public function to_kg($value) {
        if (array_key_exists($this->source_unit, self::TO_KG))
            return $value * self::TO_KG[$this->source_unit];
        else
            error_log('In '.__FUNCTION__.'(): '.$this->source_unit.' is an invalid weight unit. Available units are '.join(', ', array_keys(self::TO_KG)));
    }
}
