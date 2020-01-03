<?php

class DeliveryRepository {
    const TABLE_NAME     = 'woocommerce_jadlog';
    const INITIAL_STATUS = 'Pendente';

    public static function create($order, $values) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::TABLE_NAME;

        $values = array_merge(
            array(
                'order_id'      => $order->get_id(),
                'status'        => self::INITIAL_STATUS,
                'date_creation' => strval($order->get_date_created())),
            $values);

        $wpdb->insert($table_name, $values);
    }

    public static function get_all() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::TABLE_NAME;

        $result = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY order_id DESC");
        return $result;
    }

    public static function get_by_id($id) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::TABLE_NAME;

        $result = $wpdb->get_results("SELECT * FROM {$table_name} WHERE ID = {$id}");
        return $result[0];
    }
}
