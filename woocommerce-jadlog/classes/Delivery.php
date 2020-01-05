<?php

class Delivery {

    public function __construct($order) {
        include_once("DeliveryRepository.php");
        $this->order = $order;
    }

    public function create() {
        foreach ($this->order->get_shipping_methods() as $shipping_method) {
            if ($shipping_method->get_method_id() == WC_Jadlog_Shipping_Method::METHOD_ID) {
                $meta_data = array_reduce(
                    $shipping_method->get_formatted_meta_data(),
                    function($acc, $item) {
                        $acc[$item->key] = $item->value;
                        return $acc;
                    },
                    array());

                DeliveryRepository::create(
                    $this->order,
                    array(
                        'modalidade'   => $meta_data['modalidade'],
                        'pudo_id'      => $meta_data['pudo_id'],
                        'pudo_name'    => $meta_data['pudo_name'],
                        'pudo_address' => $meta_data['pudo_address']
                    ));
            }
        }
    }

}
