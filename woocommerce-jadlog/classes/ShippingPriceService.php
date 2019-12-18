<?php

class ShippingPriceService {

    public function __construct() {
        $this->url        = 'http://www.jadlog.com.br/embarcador/api/frete/valor'; //get_option('wc_settings_tab_jadlog_url_simulador_frete');
        $this->key        = get_option('wc_settings_tab_jadlog_key_embarcador');
        $this->cepori     = get_option('wc_settings_tab_jadlog_shipper_cep');
        $this->cnpj       = get_option('wc_settings_tab_jadlog_shipper_cnpj_cpf');
        $this->frap       = 'N'; //get_option('wc_settings_tab_jadlog_frap');
        $this->conta      = get_option('wc_settings_tab_jadlog_conta_corrente');
        $this->contrato   = get_option('wc_settings_tab_jadlog_contrato');
        $this->modalidade = get_option('wc_settings_tab_jadlog_modalidade');
        $this->tpentrega  = 'D'; //get_option('wc_settings_tab_jadlog_tipo_entrega');
        $this->tpseguro   = 'N'; //get_option('wc_settings_tab_jadlog_tipo_seguro');
        $this->vlcoleta   = null;   //get_option('wc_settings_tab_jadlog_valor_coleta');
    }

    public function estimate($declared_value, $dest_zipcode, $weight) {
        $dest_zipcode = str_replace("-", "", $dest_zipcode);
        $request = array(
            'frete' => array(array(
                'cepori'      => $this->cepori,
                'cepdes'      => $dest_zipcode,
                'frap'        => $this->frap,
                'peso'        => $weight,
                'cnpj'        => $this->cnpj,
                'conta'       => $this->conta,
                'contrato'    => $this->contrato,
                'modalidade'  => (int)$this->modalidade,
                'tpentrega'   => $this->tpentrega,
                'tpseguro'    => $this->tpseguro,
                'vldeclarado' => $declared_value,
                'vlcoleta'    => $this->vlcoleta,
            )));
        $params = array(
            'timeout'  => 120,
            'blocking' => true,
            'headers'  => array(
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => $this->key),
            'body'     => json_encode($request),
            'cookies'  => array()
        );

        $response = wp_remote_post($this->url, $params);
        error_log('In '.__FUNCTION__.'(), $response = '.var_export($response, true));

        $result = array('estimated_value' => null, 'estimated_time' => null);
        if (is_wp_error($response))
            $this->log_error($response->get_error_message(), __FUNCTION__);
        else {
            $response_body = json_decode($response['body'], true);
            if (isset($response_body['erro']))
                $this->log_error($response_body['erro'], __FUNCTION__);
            elseif (isset($response_body['error']))
                $this->log_error($response_body['error'], __FUNCTION__);
            elseif (isset($response_body['frete'][0]['erro']))
                $this->log_error($response_body['frete'][0]['erro'], __FUNCTION__);
            else {
                $result['estimated_value'] = $response_body['frete'][0]['vltotal'];
                $result['estimated_time']  = $response_body['frete'][0]['prazo'];
            }
        }
        return $result;
    }

    private function log_error($error, $function) {
        $error_message = $error['id'].' '.$error['descricao'];
        error_log('ERROR in '.$function.'(): '.$error_message);
    }
}
