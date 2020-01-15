<?php

class LocalizedNumber {

    public static function to_float($localized_number) {
        $english_number = self::to_english_format(trim($localized_number));
        return floatval($english_number);
    }

    public static function to_english_format($localized_number) {
        return str_replace(',', '.', str_replace('.', '', trim($localized_number)));
    }

    public static function to_pt_br_format($number) {
        return str_replace('.', ',', str_replace(',', '', strval($number)));
    }
}
