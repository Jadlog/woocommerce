<?php

class DeliveryRepository {
    const INITIAL_STATUS = 'Pendente';

    public static function create($order, $values) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_jadlog';

        $values = array_merge(
            array(
                'order_id'      => $order->get_id(),
                'status'        => self::INITIAL_STATUS,
                'date_creation' => strval($order->get_date_created())),
            $values);

        $wpdb->insert($table_name, $values);
    }
}
