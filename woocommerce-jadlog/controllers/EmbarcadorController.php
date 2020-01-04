<?php

/* Load WordPress engine */
$wp_root_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
require( $wp_root_path . '/wp-load.php' );

/* Load Class Embarcador */
include_once('../classes/EmbarcadorService.php');

$embarcador = new EmbarcadorService($_POST['id']);

$dfe = array(
    'tp_documento' => $_POST['tp_documento'],
    'nr_doc'       => $_POST['nr_doc'],
    'serie'        => $_POST['serie'],
    'valor'        => $_POST['valor'],
    'danfe_cte'    => $_POST['danfe_cte'],
    'cfop'         => $_POST['cfop']);
$response = $embarcador->create($dfe);

echo 'success' . $response;
