<?php
namespace WooCommerce\Jadlog\Classes;

class DimensionConverter {

    const TO_METER = array(
        'm'  => 1.0,
        'cm' => 0.01,
        'mm' => 0.001,
        'in' => 0.0254,
        'yd' => 0.9144
    );

    public function __construct($source_unit = null) {
        $this->source_unit = isset($source_unit) ? $source_unit : get_option('woocommerce_dimension_unit');
    }

    public function to_meter($value) {
        if (array_key_exists($this->source_unit, self::TO_METER))
            return $value * self::TO_METER[$this->source_unit];
        else
            error_log('In '.__FUNCTION__.'(): '.$this->source_unit.' is an invalid dimension unit. Available units are '.join(', ', array_keys(self::TO_METER)));
    }
}
