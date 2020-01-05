<?php
class OrderHelper {

    public function __construct($order) {
        $this->order = is_numeric($order) ? wc_get_order($order) : $order;
    }

    public function get_order() {
        return $this->order;
    }

    public function get_formatted_address($shipping_or_billing) {
        $raw_address = $this->order->get_address($shipping_or_billing);
        $address = $raw_address['address_1'].", ".$raw_address['number'];
        foreach (array('address_2', 'neighborhood', 'city', 'state', 'postcode') as $key) {
            $field = $raw_address[$key];
            if (!empty($field))
                $address = $address.' - '.$field;
        }
        return $address;
    }

    public function get_formatted_date_created() {
        return date('d/m/Y H:i:s', strtotime($this->order->get_date_created()));
    }

    public function get_cpf_or_cnpj() {
        $property = $this->order->get_meta('_billing_persontype') == 1 ? '_billing_cpf' : '_billing_cnpj';
        return $this->order->get_meta($property);
    }

    public function get_billing_ie() {
        return $this->order->get_meta('_billing_ie');
    }

    public function get_shipping_number() {
        return $this->order->get_meta('_shipping_number');
    }

    public function get_shipping_neighborhood() {
        return $this->order->get_meta('_shipping_neighborhood');
    }

    public function get_billing_cellphone() {
        return $this->order->get_meta('_billing_cellphone');
    }
}
