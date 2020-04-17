<?php
namespace WooCommerce\Jadlog\Classes;

class PudoInfo {

    public function __construct($pudo_data) {
        $this->_data    = is_array($pudo_data) ? $pudo_data : array();
        $this->_address = $this->_data['pudoEnderecoList'][0];
    }

    public function nao_esta_ativo() {
        return $this->_data['ativo'] != 'S';
    }

    public function get_id() {
        return $this->_data['pudoId'];
    }

    public function get_name() {
        return $this->_data['razao'];
    }

    public function get_latitude() {
        return $this->_address['latitude'];
    }

    public function get_longitude() {
        return $this->_address['longitude'];
    }

    public function get_postcode() {
        return $this->_address['cep'];
    }

    public function get_openning_hours() {
        return $this->_data['pudoHorario'];
    }

    public function get_formatted_address() {
        $address = $this->_address['endereco'];
        if (!empty($this->_address['numero']))
            $address .= ', '.$this->_address['numero'];
        if (!empty($this->_address['compl2']))
            $address .= ' - '.$this->_address['compl2'];
        if (!empty($this->_address['bairro']) && $this->_address['bairro'] != $this->_address['compl2'])
            $address .= ' - '.$this->_address['bairro'];
        if (!empty($this->_address['cidade']))
            $address .= ' - '.$this->_address['cidade'];
        if (!empty($this->_address['cep']))
            $address .= ' - CEP '.$this->format_postcode($this->_address['cep']);
        return $address;
    }

    private function format_postcode($postcode) {
        $postcode = str_pad($postcode, 8, "0", STR_PAD_LEFT);
        return strlen($postcode) == 8 ? substr($postcode, 0, 5).'-'.substr($postcode, -3) : $postcode;
    }

    public function get_formatted_name_and_address() {
        return $this->get_name().' ('.$this->get_formatted_address().')';
    }
}
