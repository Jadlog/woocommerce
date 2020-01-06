<?php

/* Load WordPress engine */
$wp_root_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
require( $wp_root_path . '/wp-load.php' );

include_once('../classes/DeliveryRepository.php');
include_once('../classes/EmbarcadorService.php');
include_once('../classes/Logger.php');

$jadlog_id  = $_POST['id'];
$embarcador = new EmbarcadorService($jadlog_id);

$dfe = array(
    'tp_documento' => $_POST['tp_documento'],
    'nr_doc'       => $_POST['nr_doc'],
    'serie'        => $_POST['serie'],
    'valor'        => $_POST['valor'],
    'danfe_cte'    => $_POST['danfe_cte'],
    'cfop'         => $_POST['cfop']);
$response = $embarcador->create($dfe);

if (isset($response['erro'])) {
    Logger::log_error($response['erro'], __FUNCTION__, $response);
    DeliveryRepository::update($jadlog_id, array(
        'status' => $response['status'],
        'erro'   => $response['erro']['descricao'].' ('.$response['erro']['id'].')'
    ));
}
else {
    DeliveryRepository::update($jadlog_id, array(
        'status'          => $response['status'],
        'codigo_inclusao' => $response['codigo'],
        'shipment_id'     => $response['shipmentId']
    ));
}
echo $response;
