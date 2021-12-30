<?php

namespace WooCommerce\Jadlog\Controllers;

/* Load WordPress engine */
$wp_root_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
require($wp_root_path . '/wp-load.php');

if (!current_user_can('administrator')) {
    wp_die(__('You are not allowed to access this part of the site'));
}

use WooCommerce\Jadlog\Classes\DeliveryRepository;
use WooCommerce\Jadlog\Classes\EmbarcadorService;
use WooCommerce\Jadlog\Classes\Logger;
use WooCommerce\Jadlog\Classes\OrderHelper;

include_once('../classes/DeliveryRepository.php');
include_once('../classes/EmbarcadorService.php');
include_once('../classes/Logger.php');

$jadlog_id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
$embarcador = new EmbarcadorService($jadlog_id);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $delivery = $embarcador->jadlog_delivery;
        $order_helper = new OrderHelper($delivery->order_id);
        $order = $order_helper->get_order();
        if ($order === null)
            wp_die(__('order not found!', 'jadlog'));
        $raw_des_address = $order_helper->order->get_address('shipping');
        $is_pickup = $delivery->modalidade == 'PICKUP';
        /* para incluir a logo do cliente, realizar upload de uma imagem com o título: jadlog-etiqueta-logo-cliente, e opcionalmente um pequeno texto na legenda */
        $jadlog_etiqueta_logo_cliente = get_page_by_title('jadlog-etiqueta-logo-cliente', OBJECT, 'attachment');
        $cli_logo = wp_get_attachment_url($jadlog_etiqueta_logo_cliente->ID);
        $cli_text = $jadlog_etiqueta_logo_cliente->post_excerpt;

        $codigo_rastreio = $delivery->codigo_inclusao;
        $order_id = $order->get_order_number();
        $des_full_name = $order->get_formatted_shipping_full_name();
        $des_phone = $order->get_billing_phone();
        if ($is_pickup) {
            $des_address_line_1 = $delivery->pudo_name . ' - ' . $delivery->pudo_id;
            $des_address_line_2 = $delivery->pudo_address;
            $des_address_line_3 = '';
        } else {
            $des_address_line_1 = $raw_des_address['address_1'];
            if (!empty($raw_des_address['number']))
                $des_address_line_1 .= ', ' . $raw_des_address['number'];
            if (!empty($raw_des_address['address_2']))
                $des_address_line_1 .= ' - ' . $raw_des_address['address_2'];
            $des_address_line_2 = $raw_des_address['neighborhood'];
            $des_address_line_3 = $raw_des_address['postcode'] . ' ' . $des_city = $raw_des_address['city'] . '/' . $raw_des_address['state'];
        }

        if ($is_pickup) {
            $rem_full_name = $embarcador->rem_nome . ' - UNIDADE ORIGEM ' . $embarcador->unidade_origem;
        } else {
            $rem_full_name = $embarcador->rem_nome;
        }
        $rem_phone = $embarcador->rem_fone;
        $rem_address_line_1 = $embarcador->rem_endereco;
        if (!empty($embarcador->rem_numero))
            $rem_address_line_1 .= ', ' . $embarcador->rem_numero;
        if (!empty($embarcador->rem_complemento))
            $rem_address_line_1 .= ' - ' . $embarcador->rem_complemento;
        $rem_address_line_2 = $embarcador->rem_bairro;
        $rem_address_line_3 = $embarcador->rem_cep . ' ' . $embarcador->rem_cidade . '/' . $embarcador->rem_uf;
        ?><!DOCTYPE html>
        <html lang="pt-br">
            <style>
                @page {
                    size: A4;
                    margin: 3mm;
                    padding: 0
                }
            </style>
            <script type="text/javascript" src="<?= JADLOG_ROOT_URL; ?>/assets/js/JsBarcode.all.min.js"></script>
            <body style="line-height: 1;padding: 0 !important;border: 0;vertical-align: baseline;width: 21cm !important;">
                <div style="margin: 0 !important;padding: 2mm !important;border: 0;float: left;width: 10cm !important;height: 14.55cm !important;">
                    <div style="margin: 0;padding: 0;border: 2px solid #000;height: 14.55cm !important;">
                        <div style="margin: 0;padding: 0;border: 0;">
                            <div style="margin: 0;padding: 5px 10px;border: 0;height: 85px;">
                                <div style="margin: 0;padding: 0;border: 0;position: relative;width: 33.33%;height: 100%;display: inline-block;float: left;">
                                    <img src="<?= JADLOG_ROOT_URL; ?>/assets/img/jadlog_label.png" style="border-style: none;margin: 0;padding: 0;border: 0;width: <?= $is_pickup ? '102px' : '100%'; ?>;position: absolute;top: 50%;left: 50%;-webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);" />
                                    <?php if ($is_pickup) { ?>
                                        <span style="margin: 0;padding: 2px 24px;border: 0;font-size: 14pt !important;vertical-align: baseline;font-family: Arial, serif;position: absolute;bottom: 1px;left: 50%;-webkit-transform: translateX(-50%);transform: translateX(-50%);background-color: black;color: white;-webkit-print-color-adjust: exact;color-adjust: exact;">Pickup</span>
                                    <?php }; ?>
                                </div>
                                <div style="margin: 0 auto;padding: 0;border: 0;position: relative;width: 33.33%;height: 100%;display: inline-block;">
                                    <div style="padding: 0px;overflow: auto;width: 126px;margin: 0;border: 0;font-size: 10pt;vertical-align: baseline;font-family: Arial, serif;position: absolute;top: 50%;left: 50%;-webkit-transform: translate(-50%, -50%) scale(0.7) !important;transform: translate(-50%, -50%) scale(0.7) !important;">
                                    </div>
                                </div>
                                <div style="margin: 0;padding: 0;border: 0;position: relative;width: 33.33%;height: 100%;display: inline-block;float: right;">
                                    <?php if (!empty($cli_logo)) { ?>
                                        <img src="<?= $cli_logo; ?>" style="border-style: none;margin: 0;padding: 0;border: 0;font-size: 10pt;vertical-align: baseline;font-family: Arial, serif;width: 100%;position: absolute;top: 50%;left: 50%;-webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);" />
                                        <span style="margin: 0;padding: 0;border: 0;font-size: 8pt !important;vertical-align: baseline;font-family: Arial, serif;position: absolute;bottom: 1px;left: 50%;-webkit-transform: translateX(-50%);transform: translateX(-50%);"><?= htmlentities($cli_text); ?></span>
                                    <?php }; ?>
                                </div>
                            </div>
                        </div>
                        <div style="height: 5px;"></div>
                        <div style="margin: 0 20px;padding: 0;border: 0;font-size: 9pt;vertical-align: baseline;font-family: Arial, serif;width: 100%;line-height: 1;float: left;text-align: left;">
                            <span style="margin: 0;padding: 0;border: 0;height: 16px;display: block;">PEDIDO: <strong><?= htmlentities($order_id); ?></strong></span>
                        </div>
                        <div style="height: 5px;"></div>
                        <div style="margin: 0;padding: 0;border: 0;position: relative;">
                            <div style="margin: 0 20px;padding: 0;border: 0;position: relative;bottom: 0;right: 10px;width: 355px;height: 50px">
                                <div style="text-align: center;font-family: Arial, serif;font-size: 14pt !important;"><strong><?= htmlentities($codigo_rastreio); ?></strong></div>
                                <div style="text-align: center;"><svg class="barcode" data-format="CODE128" data-height="30" data-width="2" data-displayValue="false" data-marginLeft="20" data-marginRight="20" data-marginTop="1" data-marginBottom="1" data-value="<?= htmlentities($codigo_rastreio); ?>"></svg></div>
                            </div>
                        </div>
                        <div style="height: 20px;"></div>
                        <div style="margin: 0 10px;padding: 3px 0;border: 0;">
                            <div style="margin: 0;padding: 0;border: 0;font-size: 10pt;  vertical-align: baseline;font-family: Arial, serif;position: relative;">
                                Recebedor:<span style="margin: 0;padding: 0 !important;border: 0;font-size: 8pt;  vertical-align: baseline;font-family: Arial, serif;min-height: 15px;white-space: normal;margin-bottom: 2px !important;line-height: 14px;text-align: left;position: absolute;right: 0;">_______________________________________________</span>
                            </div>
                            <div style="height: 8px;"></div>
                            <div style="margin: 0;padding: 0;border: 0;font-size: 10pt;vertical-align: baseline;font-family: Arial, serif;position: relative;">
                                Assinatura:<span style="margin: 0;padding: 0 !important;border: 0;font-size: 8pt;vertical-align: baseline;font-family: Arial, serif;min-height: 15px;white-space: normal;margin-bottom: 2px !important;line-height: 14px;text-align: left;">____________________</span>
                                Documento:<span style="margin: 0;padding: 0 !important;border: 0;font-size: 8pt;vertical-align: baseline;font-family: Arial, serif;min-height: 15px;white-space: normal;margin-bottom: 2px !important;line-height: 14px;text-align: left;position: absolute;right: 0;">______________</span>
                            </div>
                        </div>
                        <div style="margin: 5px 0;padding: 0 5px;border: 0;position: relative;border-top: 2px solid #000;border-bottom: 2px solid #000;">
                            <div style="margin: 0;padding: 0;border: 0;height: 30px;">
                                <span style="margin: 0;padding: 4px 14px;border: 0;font-size: 14pt;vertical-align: baseline;font-family: Arial, serif;margin-left: -5px;background: #000;color: #fff;font-weight: 700;position: absolute;-webkit-print-color-adjust: exact;color-adjust: exact;"><strong>DESTINATÁRIO</strong></span>
                            </div>
                            <div style="margin: 0;padding: 0 10px;border: 0;">
                                <p style="height: 10px;padding: 0; margin: 0;"></p>
                                <p style="margin: 0;padding: 0 !important;border: 0;font-size: 12pt;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;padding-right: 60px;">
                                    <?= htmlentities($des_full_name); ?>
                                </p>
                                <p style="margin: 0;padding: 0 !important;border: 0;font-size: 12pt;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;padding-right: 60px;">
                                    <strong><?= htmlentities($des_phone); ?></strong>
                                </p>
                                <p style="height: 10px;padding: 0; margin: 0;"></p>
                                <p style="margin: 0;padding: 0 !important;border: 0;font-size: 12pt;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;padding-right: 60px;">
                                    <?= htmlentities($des_address_line_1); ?>
                                </p>
                                <p style="margin: 0;padding: 0 !important;border: 0;font-size: 12pt;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;padding-right: 60px;">
                                    <?= htmlentities($des_address_line_2); ?>
                                </p>
                                <p style="margin: 0;padding: 0 !important;border: 0;font-size: 12pt;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;padding-right: 60px;">
                                    <?= htmlentities($des_address_line_3); ?>
                                </p>
                                <p style="height: 10px;padding: 0; margin: 0;"></p>
                            </div>
                        </div>
                        <div  style="margin: 5px 0;padding: 0 10px;border: 0;position: relative;">
                            <p style="margin: 0;padding: 0 !important;border: 0;font-size: 10pt !important;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;">
                                <strong>Remetente</strong>
                            </p>
                            <p style="margin: 0;padding: 0 !important;border: 0;font-size: 10pt !important;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;">
                                <?= htmlentities($rem_full_name); ?>
                            </p>
                            <p style="margin: 0;padding: 0 !important;border: 0;font-size: 10pt !important;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;">
                                <strong><?= htmlentities($rem_phone); ?></strong>
                            </p>
                            <p style="margin: 0;padding: 0 !important;border: 0;font-size: 10pt !important;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;">
                                <?= htmlentities($rem_address_line_1); ?>
                            </p>
                            <p style="margin: 0;padding: 0 !important;border: 0;font-size: 10pt !important;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;">
                                <?= htmlentities($rem_address_line_2); ?>
                            </p>
                            <p style="margin: 0;padding: 0 !important;border: 0;font-size: 10pt !important;vertical-align: baseline;font-family: Arial, serif;white-space: normal;text-transform: uppercase;min-height: 15px;margin-bottom: 2px !important;xline-height: 14px;text-align: left;">
                                <?= htmlentities($rem_address_line_3); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                (function () {
                    JsBarcode(".barcode").init();
                })();
            </script>
        </body>
        </html>
        <?php
        break;
}
