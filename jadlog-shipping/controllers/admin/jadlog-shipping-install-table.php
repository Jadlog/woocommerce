<?php
/* Exit if accessed directly */
if (!defined('ABSPATH'))
    exit;

/**
 * Install table.
 *
 * @access public
 * @return void
 */
function install_table() 
{
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $time = time();
    $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_jadlog (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `order_id` varchar(255) NOT NULL,
            `shipping_method` varchar(255) DEFAULT NULL,
            `pudo_id` varchar(255) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `address` varchar(255) DEFAULT NULL,
            `postcode` varchar(30) DEFAULT NULL,
            `status` varchar(255) DEFAULT NULL,
            `erro` varchar(255) DEFAULT NULL,
            `date_creation` datetime DEFAULT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARSET=utf8";
    $wpdb->query($sql);
    
}

/**
 * Uninstall table.
 *
 * @access public
 * @return void
 */
function uninstall_table() 
{
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    $sql = "DROP TABLE IF EXISTS {$wpdb->prefix}woocommerce_jadlog";
    $wpdb->query($sql);
}