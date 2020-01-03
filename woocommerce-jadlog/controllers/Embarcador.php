<?php

/* Load WordPress engine */
$wp_root_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
require( $wp_root_path . '/wp-load.php' );

/* Load Class Embarcador */
include_once('../classes/EmbarcadorService.php');

$embarcador = new EmbarcadorService($_POST['id']);

$response = $embarcador->create();

echo 'success' . $response;
