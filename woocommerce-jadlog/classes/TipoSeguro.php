<?php
namespace WooCommerce\Jadlog\Classes;

class TipoSeguro {
    const COD_NORMAL = 'N';
    const COD_APOLICE = 'A';
    const TODOS = array(
        self::COD_NORMAL  => self::COD_NORMAL.' - Normal',
        self::COD_APOLICE => self::COD_APOLICE.' - Apólice'
    );
}
