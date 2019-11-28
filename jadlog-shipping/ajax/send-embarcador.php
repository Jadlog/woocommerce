<?php
/**
 * Created by PhpStorm.
 * User: quinho
 * Date: 31/10/17
 * Time: 17:50
 */

/* Load WordPress engine */
$wp_root_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
require( $wp_root_path . '/wp-load.php' );

/* Load Class Embarcador */
include_once('../classes/class-embarcador.php');

$embarcador = new JadLogEmbarcador($_POST['jadlog_id'], $_POST['pudo_id']);

$response = $embarcador->postRequestEmbarcador();

echo 'success' . $response;