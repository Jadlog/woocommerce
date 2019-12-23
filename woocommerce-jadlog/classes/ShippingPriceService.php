<?php

class ShippingPriceService {

    public function __construct($modalidade) {
        include_once("Modalidade.php");

        $this->url        = get_option('wc_settings_tab_jadlog_url_simulador_frete');
        $this->key        = get_option('wc_settings_tab_jadlog_key_embarcador');
        $this->cepori     = get_option('wc_settings_tab_jadlog_shipper_cep');
        $this->cnpj       = get_option('wc_settings_tab_jadlog_shipper_cnpj_cpf');
        $this->conta      = get_option('wc_settings_tab_jadlog_conta_corrente');
        $this->contrato   = get_option('wc_settings_tab_jadlog_contrato');

        $sufix = '_'.strtolower(Modalidade::TODOS[$modalidade]);
        $this->modalidade = $modalidade;
        $this->frap       = get_option('wc_settings_tab_jadlog_frap'.$sufix) == 'yes' ? 'S' : 'N';
        $this->tpentrega  = get_option('wc_settings_tab_jadlog_tipo_entrega'.$sufix);
        $this->tpseguro   = get_option('wc_settings_tab_jadlog_tipo_seguro'.$sufix);
        $this->vlcoleta   = (double)get_option('wc_settings_tab_jadlog_valor_coleta'.$sufix);
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
                'modalidade'  => $this->modalidade,
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

        $result = array('estimated_value' => null, 'estimated_time' => null);
        if (is_wp_error($response))
            $this->log_error($response->get_error_message(), __FUNCTION__, $response, $request);
        elseif ($response['response']['code'] == 500)
            $this->log_error($response['body'], __FUNCTION__, $response, $request);
        else {
            $response_body = json_decode($response['body'], true);
            if (isset($response_body['erro']))
                $this->log_error($response_body['erro'], __FUNCTION__, $response, $request);
            elseif (isset($response_body['error']))
                $this->log_error($response_body['error'], __FUNCTION__, $response, $request);
            elseif (isset($response_body['frete'][0]['erro']))
                $this->log_error($response_body['frete'][0]['erro'], __FUNCTION__, $response, $request);
            else {
                $result['estimated_value'] = $response_body['frete'][0]['vltotal'];
                $result['estimated_time']  = $response_body['frete'][0]['prazo'];
            }
        }
        return $result;
    }

    private function log_error($error, $function, $response, $request) {
        error_log('In '.$function.'(), $response = '.var_export($response, true));
        error_log('In '.$function.'(), $request body = '.var_export($request, true));
        $error_message = $error['id'].' '.$error['descricao'];
        error_log('ERROR in '.$function.'(): '.$error_message);
    }
}
