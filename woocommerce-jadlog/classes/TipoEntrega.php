<?php
class TipoEntrega {
    const COD_DOMICILIO = 'D';
    const COD_RETIRA    = 'R';
    const TODOS = array(
        self::COD_DOMICILIO => self::COD_DOMICILIO.' - Domicílio',
        self::COD_RETIRA    => self::COD_RETIRA.' - Retira'
    );
}
