<?php
namespace WooCommerce\Jadlog\Classes;

class VolumetricWeight {

    public function __construct($modal, $volume) {
        include_once("Modalidade.php");

        $this->fator_cubagem = Modalidade::fator_cubagem($modal);
        $this->volume = $volume;
    }

    public function get_value() {
        return $this->volume * $this->fator_cubagem;
    }
}
