<?php
class TipoFrete {
    const COD_NORMAL = 0;
    const COD_SUBCONTRATACAO = 1;
    const COD_REDESPACHO = 2;
    const COD_REDESPACHO_INTERMEDIARIO = 3;
    const TODOS = array(
        self::COD_NORMAL                   => self::COD_NORMAL.' - Normal',
        self::COD_SUBCONTRATACAO           => self::COD_SUBCONTRATACAO.' - Subcontratação',
        self::COD_REDESPACHO               => self::COD_REDESPACHO.' - Redespacho',
        self::COD_REDESPACHO_INTERMEDIARIO => self::COD_REDESPACHO_INTERMEDIARIO.' - Redespacho Intermediário',
    );
}
