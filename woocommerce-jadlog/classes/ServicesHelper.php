<?php
namespace WooCommerce\Jadlog\Classes;

class ServicesHelper {

    public static function only_digits($string) {
        return preg_replace('/[^0-9]/', '', $string);
    }
}
