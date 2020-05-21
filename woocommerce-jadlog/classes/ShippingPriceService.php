<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPriceService {

    public function __construct($modalidade) {
        include_once("ErrorHandler.php");
        include_once("Modalidade.php");
        include_once("Logger.php");
        include_once("ServicesHelper.php");

        $this->url        = $this->get_system_option("url_simulador_frete");
        $this->key        = $this->get_system_option("key_embarcador");
        $this->cepori     = ServicesHelper::only_digits(
            $this->get_system_option("shipper_cep"));
        $this->cnpj       = ServicesHelper::only_digits(
            $this->get_system_option("shipper_cnpj_cpf"));
        $this->conta      = $this->get_system_option("conta_corrente");
        $this->contrato   = $this->get_system_option("contrato");

        $sufix = strtolower(Modalidade::TODOS[$modalidade]);
        $this->modalidade = $modalidade;
        $this->frap       = $this->get_system_option("frap_{$sufix}") == 'yes' ? 'S' : 'N';
        $this->tpentrega  = $this->get_system_option("tipo_entrega_{$sufix}");
        $this->tpseguro   = $this->get_system_option("tipo_seguro_{$sufix}");
        $this->vlcoleta   = (double)$this->get_system_option("valor_coleta_{$sufix}");
    }

    private function get_system_option($name) {
        return get_option("wc_settings_tab_jadlog_{$name}");
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
        $error_handler = new ErrorHandler($request, $response, __METHOD__);
        $result = array('estimated_value' => null, 'estimated_time' => null);

        if ($error_handler->is_wp_error() || $error_handler->is_500())
            return $result;
        else {
            $response_body = json_decode($response['body'], true);
            if ($error_handler->is_error_set($response_body['erro'])  ||
                $error_handler->is_error_set($response_body['error']) ||
                $error_handler->is_error_set($response_body['frete'][0]['erro']))
                return $result;
            else {
                $result['estimated_value'] = $response_body['frete'][0]['vltotal'];
                $result['estimated_time']  = $response_body['frete'][0]['prazo'];
                return $result;
            }
        }
    }
}
