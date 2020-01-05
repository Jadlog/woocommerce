<?php
class TipoServico {
    const COD_SEM_PIN = 0;
    const COD_COM_PIN = 1;
    const COD_DROPOFF = 2;
    const TODOS = array(
        self::COD_SEM_PIN => self::COD_SEM_PIN.' - Sem PIN',
        self::COD_COM_PIN => self::COD_COM_PIN.' - Com PIN',
        self::COD_DROPOFF => self::COD_DROPOFF.' - Dropoff'
    );
}
