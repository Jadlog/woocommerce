<?php
namespace WooCommerce\Jadlog\Controllers;

/* Load WordPress engine */
$wp_root_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
require($wp_root_path.'/wp-load.php');

use WooCommerce\Jadlog\Classes\DeliveryRepository;
use WooCommerce\Jadlog\Classes\EmbarcadorService;
use WooCommerce\Jadlog\Classes\Logger;
include_once('../classes/DeliveryRepository.php');
include_once('../classes/EmbarcadorService.php');
include_once('../classes/Logger.php');

$jadlog_id  = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
$embarcador = new EmbarcadorService($jadlog_id);

switch ($_SERVER['REQUEST_METHOD']) {
case 'POST':
    $dfe = array(
        'tp_documento' => $_POST['tp_documento'],
        'nr_doc'       => $_POST['nr_doc'],
        'serie'        => $_POST['serie'],
        'valor'        => $_POST['valor'],
        'danfe_cte'    => $_POST['danfe_cte'],
        'cfop'         => $_POST['cfop']);
    $response = $embarcador->create($dfe);

    if (isset($response['erro'])) {
        Logger::log_error($response['erro'], __METHOD__, $response);
        DeliveryRepository::update($jadlog_id, array(
            'status' => $response['status'],
            'erro'   => $response['erro']['descricao'].' ('.$response['erro']['id'].')'
        ));
        http_response_code(400);
    }
    else {
        DeliveryRepository::update($jadlog_id, array(
            'status'          => $response['status'],
            'codigo_inclusao' => $response['codigo'],
            'shipment_id'     => $response['shipmentId']
        ));
        http_response_code(200);
    }

    header('Content-type: application/json');
    echo json_encode($response);
    break;

case 'DELETE':
    $shipment_id = $_GET['shipment_id'];
    $response = $embarcador->cancel($shipment_id);

    if (isset($response['erro'])) {
        Logger::log_error($response['erro'], __METHOD__, $response);
        http_response_code(400);
    }
    else {
        DeliveryRepository::update($jadlog_id, array(
            'status' => DeliveryRepository::CANCELED_STATUS
        ));
        http_response_code(200);
    }

    header('Content-type: application/json');
    echo json_encode($response);
    break;

case 'GET':
    $shipment_id = $_GET['shipment_id'];
    $response = $embarcador->get($shipment_id);

    if (isset($response['consulta'][0]['error'])) {
        Logger::log_error($response, __METHOD__, $response);
        http_response_code(400);
    }
    else
        http_response_code(200);

    header('Content-type: application/json');
    echo json_encode($response);
    break;
}
