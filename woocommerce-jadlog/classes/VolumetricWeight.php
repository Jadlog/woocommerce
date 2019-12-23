<?php

class VolumetricWeight {

    public function __construct($modalidade, $volume) {
        include_once("Modalidade.php");

        $this->fator_cubagem = Modalidade::fator_cubagem(Modalidade::modal($modalidade));
        $this->volume = $volume;
    }

    public function calculate() {
        return $this->volume * $this->fator_cubagem;
    }
}
