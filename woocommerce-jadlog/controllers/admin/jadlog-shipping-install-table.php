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
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');

    $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_jadlog (
            `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `order_id`         VARCHAR(255)  NOT NULL,
            `modalidade`       VARCHAR(255)  DEFAULT NULL,
            `valor_total`      decimal(10,2) DEFAULT NULL,
            `peso_taxado`      decimal(10,2) DEFAULT NULL,
            `pudo_id`          VARCHAR(255)  DEFAULT NULL,
            `pudo_name`        VARCHAR(255)  DEFAULT NULL,
            `pudo_address`     VARCHAR(511)  DEFAULT NULL,
            `status`           VARCHAR(255)  DEFAULT NULL,
            `erro`             VARCHAR(2000) DEFAULT NULL,
            `dfe_tp_documento` INT           DEFAULT NULL,
            `dfe_nr_doc`       VARCHAR(255)  DEFAULT NULL,
            `dfe_serie`        VARCHAR(255)  DEFAULT NULL,
            `dfe_valor`        DECIMAL(10,2) DEFAULT NULL,
            `dfe_danfe_cte`    VARCHAR(255)  DEFAULT NULL,
            `dfe_cfop`         VARCHAR(255)  DEFAULT NULL,
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
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');

    $sql = "DROP TABLE IF EXISTS {$wpdb->prefix}woocommerce_jadlog";
    $wpdb->query($sql);
}
