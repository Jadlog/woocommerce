<?php

class Delivery {

    public function __construct($order) {
        $this->order = $order;
    }

    public function create() {
        global $wpdb;
        $table =  $wpdb->prefix . 'woocommerce_jadlog';

        foreach ($this->order->get_shipping_methods() as $shipping_method) {
            if ($shipping_method->get_method_id() == WC_Jadlog_Shipping_Method::METHOD_ID) {
                $meta_data = array_reduce(
                    $shipping_method->get_formatted_meta_data(),
                    function($acc, $item) {
                        $acc[$item->key] = $item->value;
                        return $acc;
                    },
                    array());

                $modalidade = $meta_data['modalidade'];
                switch ($modalidade) {
                case Modalidade::LABEL_EXPRESSO:
                    $id_pudo = null;
                    $name    = $this->order->get_formatted_shipping_full_name();
                    $address = $this->format_address($this->order->get_address('shipping'));
                    $zipcode = $this->order->get_shipping_postcode();
                    break;
                case Modalidade::LABEL_PICKUP:
                    $id_pudo = $meta_data['id_pudo'];
                    $name    = $meta_data['name_pudo'];
                    $address = $meta_data['address_pudo'];
                    $zipcode = $meta_data['zipcode_pudo'];
                    break;
                }

                $wpdb->insert($table, array(
                    'order_id'        => $this->order->get_id(),
                    'shipping_method' => $modalidade,
                    'pudo_id'         => $id_pudo,
                    'name'            => $name,
                    'address'         => $address,
                    'postcode'        => $zipcode,
                    'status'          => 'Pendente',
                    'date_creation'   => strval($this->order->get_date_created())));
            }
        }
    }

    private function format_address($raw_address) {
        $address = $raw_address['address_1'].", ".$raw_address['number'];
        foreach (array('address_2', 'neighborhood', 'city', 'state', 'postcode') as $key) {
            $field = $raw_address[$key];
            if (!empty($field))
                $address = $address.' - '.$field;
        }
        return $address;
    }
}
