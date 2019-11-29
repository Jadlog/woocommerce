<?php
/**
 * Created by PhpStorm.
 * User: quinho
 * Date: 26/10/17
 * Time: 14:54
 */
/**
 * Class MelhorEnvioGetCep
 */
class MelhorEnvioGetCep {

    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->url      = 'https://viacep.com.br/ws/';
        $this->type     = 'json';
    }

    /**
     * This function is used to get the city name.
     *
     * @access public
     * @param array $postalcode
     * @return void
     */
    public function getCityName($postalcode = null) {

        if ($postalcode === null) {
            return '';
        }

        $url = $this->url . $postalcode . '/' . $this->type;
        $response = wp_remote_get($url);
        $body     = wp_remote_retrieve_body($response);

        return json_decode($body)->localidade;

    }

}