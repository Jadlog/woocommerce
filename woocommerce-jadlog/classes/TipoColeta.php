<?php
namespace WooCommerce\Jadlog\Classes;

class TipoColeta {
    const COD_ELETRONICA = 'S';
    const COD_REMETENTE  = 'K';
    const TODOS = array(
        self::COD_ELETRONICA => self::COD_ELETRONICA.' - Solicitação eletrônica (aguardando remessa física)',
        self::COD_REMETENTE  => self::COD_REMETENTE.' - Solicitação de coleta no remetente'
    );
}
