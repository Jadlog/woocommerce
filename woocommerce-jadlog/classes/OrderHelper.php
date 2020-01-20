<?php
namespace WooCommerce\Jadlog\Classes;

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
        return $this->order->get_date_created()->date_i18n('d/m/Y H:i:s');
    }

    public function get_cpf_or_cnpj() {
        $property = $this->is_legal_entity() ?  '_billing_cnpj': '_billing_cpf';
        return $this->order->get_meta($property);
    }

    private function is_legal_entity() {
        return $this->order->get_meta('_billing_persontype') != 1;
    }

    public function get_billing_ie() {
        return $this->is_legal_entity() ? $this->order->get_meta('_billing_ie') : null;
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

    public function get_items_names() {
        $names = array();
        foreach ($this->order->get_items() as $item) {
            $names[] = $item->get_name();
        }
        return join(' | ', $names);
    }
}
