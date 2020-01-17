<?php

class PostalCodeHelper {

    public function __construct() {
        $this->url      = 'https://viacep.com.br/ws';
        $this->type     = 'json';
    }

    public function getCityName($postalcode = null) {
        $return = '';

        if ($postalcode !== null) {
            $url = $this->url . '/' . urlencode($postalcode) . '/' . $this->type;
            $response = wp_remote_get($url);
            if (is_array($response) && !is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $response_obj = json_decode($body);
                if (isset($response_obj->localidade))
                    $return = $response_obj->localidade;
            }
        }

        return $return;
    }
}
