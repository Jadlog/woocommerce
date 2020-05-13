<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPriceService {

    public function __construct($modalidade) {
        include_once("Modalidade.php");
        include_once("Logger.php");
        include_once("ServicesHelper.php");

        $this->url        = get_option('wc_settings_tab_jadlog_url_simulador_frete');
        $this->key        = get_option('wc_settings_tab_jadlog_key_embarcador');
        $this->cepori     = ServicesHelper::only_digits(get_option('wc_settings_tab_jadlog_shipper_cep'));
        $this->cnpj       = ServicesHelper::only_digits(get_option('wc_settings_tab_jadlog_shipper_cnpj_cpf'));
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
        $request = array(
            'frete' => array(array(
                'cepori'      => ServicesHelper::only_digits($this->cepori),
                'cepdes'      => ServicesHelper::only_digits($dest_zipcode),
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
            Logger::log_error($response->get_error_message(), __METHOD__, $response, $request);
        elseif ($response['response']['code'] == 500)
            Logger::log_error($response['body'], __METHOD__, $response, $request);
        else {
            $response_body = json_decode($response['body'], true);
            if (isset($response_body['erro']))
                Logger::log_error($response_body['erro'], __METHOD__, $response, $request);
            elseif (isset($response_body['error']))
                Logger::log_error($response_body['error'], __METHOD__, $response, $request);
            elseif (isset($response_body['frete'][0]['erro']))
                Logger::log_error($response_body['frete'][0]['erro'], __METHOD__, $response, $request);
            else {
                $result['estimated_value'] = $response_body['frete'][0]['vltotal'];
                $result['estimated_time']  = $response_body['frete'][0]['prazo'];
            }
        }
        return $result;
    }
}
