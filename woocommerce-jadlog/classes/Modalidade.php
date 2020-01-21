<?php
namespace WooCommerce\Jadlog\Classes;

class Modalidade {

    const COD_EXPRESSO      = 0;
    const COD_PACKAGE       = 3;
    const COD_RODOVIARIO    = 4;
    const COD_ECONOMICO     = 5;
    const COD_DOC           = 6;
    const COD_CORPORATE     = 7;
    const COD_COM           = 9;
    const COD_INTERNACIONAL = 10;
    const COD_CARGO         = 12;
    const COD_EMERGENCIAL   = 14;
    const COD_PICKUP        = 40;

    const LABEL_EXPRESSO      = 'EXPRESSO';
    const LABEL_PACKAGE       = '.PACKAGE';
    const LABEL_RODOVIARIO    = 'RODOVIÁRIO';
    const LABEL_ECONOMICO     = 'ECONÔMICO';
    const LABEL_DOC           = 'DOC';
    const LABEL_CORPORATE     = 'CORPORATE';
    const LABEL_COM           = 'COM';
    const LABEL_INTERNACIONAL = 'INTERNACIONAL';
    const LABEL_CARGO         = 'CARGO';
    const LABEL_EMERGENCIAL   = 'EMERGENCIAL';
    const LABEL_PICKUP        = 'PICKUP';

    const TODOS = array(
        self::COD_EXPRESSO      => self::LABEL_EXPRESSO,
        self::COD_PACKAGE       => self::LABEL_PACKAGE,
        self::COD_RODOVIARIO    => self::LABEL_RODOVIARIO,
        self::COD_ECONOMICO     => self::LABEL_ECONOMICO,
        self::COD_DOC           => self::LABEL_DOC,
        self::COD_CORPORATE     => self::LABEL_CORPORATE,
        self::COD_COM           => self::LABEL_COM,
        self::COD_INTERNACIONAL => self::LABEL_INTERNACIONAL,
        self::COD_CARGO         => self::LABEL_CARGO,
        self::COD_EMERGENCIAL   => self::LABEL_EMERGENCIAL,
        self::COD_PICKUP        => self::LABEL_PICKUP
    );

    const MODAL_AEREO      = 'AÉREO';
    const MODAL_RODOVIARIO = 'RODOVIÁRIO';

    const MODAL = array(
        self::COD_EXPRESSO      => self::MODAL_AEREO,
        self::COD_PACKAGE       => self::MODAL_RODOVIARIO,
        self::COD_RODOVIARIO    => self::MODAL_RODOVIARIO,
        self::COD_ECONOMICO     => self::MODAL_RODOVIARIO,
        self::COD_DOC           => self::MODAL_RODOVIARIO,
        self::COD_CORPORATE     => self::MODAL_AEREO,
        self::COD_COM           => self::MODAL_AEREO,
        self::COD_INTERNACIONAL => self::MODAL_AEREO,
        self::COD_CARGO         => self::MODAL_AEREO,
        self::COD_EMERGENCIAL   => self::MODAL_RODOVIARIO,
        self::COD_PICKUP        => self::MODAL_AEREO
    );

    public static function modal($modalidade) {
        return self::MODAL[$modalidade];
    }

    public static function codigo_modalidade($label) {
        return array_search($label, self::TODOS);
    }

    const FATOR_CUBAGEM_AEREO      = 166.667;
    const FATOR_CUBAGEM_RODOVIARIO = 300.0;

    public static function fator_cubagem($modal) {
        if ($modal == self::MODAL_RODOVIARIO)
            return self::FATOR_CUBAGEM_RODOVIARIO;
        elseif ($modal == self::MODAL_AEREO)
            return self::FATOR_CUBAGEM_AEREO;
        else
            return self::FATOR_CUBAGEM_AEREO; //Aereo in last case
    }
}
