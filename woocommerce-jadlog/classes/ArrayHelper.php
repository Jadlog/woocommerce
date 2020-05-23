<?php
namespace WooCommerce\Jadlog\Classes;

class ArrayHelper {

    public static function safe_get($array, $key, $default = null) {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

}
