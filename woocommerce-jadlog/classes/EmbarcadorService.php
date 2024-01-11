<?php
namespace WooCommerce\Jadlog\Classes;
use \stdClass;

class EmbarcadorService {

    const TIPOS_DOCUMENTOS = array(
        0 => 'Declaração',
        1 => 'NF',
        2 => 'NF-e',
        4 => 'CT-e'
    );

    public function __construct($jadlog_id) {
        include_once("DeliveryRepository.php");
        include_once("ErrorHandler.php");
        include_once("LocalizedNumber.php");
        include_once("Logger.php");
        include_once("Modalidade.php");
        include_once("OrderHelper.php");
        include_once("ServicesHelper.php");

        if (!empty($jadlog_id))
            $this->jadlog_delivery = DeliveryRepository::get_by_id($jadlog_id);

        $this->url_inclusao     = get_option('wc_settings_tab_jadlog_url_inclusao_pedidos');
        $this->url_cancelamento = get_option('wc_settings_tab_jadlog_url_cancelamento_pedidos');
        $this->url_consulta     = get_option('wc_settings_tab_jadlog_url_consulta_pedidos');
        $this->url_pudos        = get_option('wc_settings_tab_jadlog_url_consulta_pudos');
        $this->key              = get_option('wc_settings_tab_jadlog_key_embarcador');
        $this->codigo_cliente   = get_option('wc_settings_tab_jadlog_codigo_cliente');
        if (!empty($this->jadlog_delivery))
          $this->modalidade     = Modalidade::codigo_modalidade($this->jadlog_delivery->modalidade);
        $this->conta_corrente   = get_option('wc_settings_tab_jadlog_conta_corrente');
        $this->tipo_coleta      = get_option('wc_settings_tab_jadlog_tipo_coleta');
        $this->tipo_frete       = get_option('wc_settings_tab_jadlog_tipo_frete');
        $this->unidade_origem   = get_option('wc_settings_tab_jadlog_unidade_origem');
        $this->contrato         = get_option('wc_settings_tab_jadlog_contrato');
        $this->servico          = get_option('wc_settings_tab_jadlog_servico');
        if (!empty($this->modalidade)) {
          $sufix = '_'.strtolower(Modalidade::TODOS[$this->modalidade]);
          $this->valor_coleta   = get_option('wc_settings_tab_jadlog_valor_coleta'.$sufix);
        }
        $this->max_pudo_number  = get_option('wc_settings_tab_jadlog_qtd_pontos_pickup');

        $this->rem_nome         = get_option('wc_settings_tab_jadlog_shipper_name');
        $this->rem_cpf_cnpj     = get_option('wc_settings_tab_jadlog_shipper_cnpj_cpf');
        $this->rem_ie           = get_option('wc_settings_tab_jadlog_shipper_ie');
        $this->rem_endereco     = get_option('wc_settings_tab_jadlog_shipper_endereco');
        $this->rem_numero       = get_option('wc_settings_tab_jadlog_shipper_numero');
        $this->rem_complemento  = get_option('wc_settings_tab_jadlog_shipper_complemento');
        $this->rem_bairro       = get_option('wc_settings_tab_jadlog_shipper_bairro');
        $this->rem_cidade       = get_option('wc_settings_tab_jadlog_shipper_cidade');
        $this->rem_uf           = get_option('wc_settings_tab_jadlog_shipper_uf');
        $this->rem_cep          = get_option('wc_settings_tab_jadlog_shipper_cep');
        $this->rem_fone         = get_option('wc_settings_tab_jadlog_shipper_fone');
        $this->rem_cel          = get_option('wc_settings_tab_jadlog_shipper_cel');
        $this->rem_email        = get_option('wc_settings_tab_jadlog_shipper_email');
        $this->rem_contato      = get_option('wc_settings_tab_jadlog_shipper_contato');
    }

    /**
     * This function is used to send the post request to embarcador
     *
     * @access public
     * @return json
     */
    public function create($dfe) {
        $order = wc_get_order($this->jadlog_delivery->order_id);
        $order_helper = new OrderHelper($order);

        $request_params         = $this->build_create_request_params($order);
        $request_params->rem    = $this->build_rem();
        $request_params->des    = $this->build_des($order);
        $request_params->dfe    = array($this->build_dfe($dfe));
        $request_params->volume = array($this->build_volume($order));

        $response = wp_remote_post($this->url_inclusao, array(
            'method'   => 'POST',
            'timeout'  => 30,
            'blocking' => true,
            'headers'  => array(
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => $this->key),
            'body'     => json_encode($request_params),
            'cookies'  => array()));

        $error_handler = new ErrorHandler($request_params, $response, __METHOD__);
        if ($error_handler->is_wp_error())
            return array(
                'status' => $response->get_error_message(),
                'erro' => array());
        elseif (!$error_handler->is_http_success())
            return array(
                'status' => $response['body'],
                'erro' => array('descricao' => $response['response']['code']));
        else
            return json_decode($response['body'], true);
    }

    private function build_create_request_params($order) {
        $order_helper = new OrderHelper($order);
        $params = new stdClass();
        $params->conteudo        = substr($order_helper->get_items_names(), 0, 80);
        $params->pedido          = array($order->get_order_number());
        $params->totPeso         = floatval($this->jadlog_delivery->peso_taxado);
        $params->totValor        = floatval($this->jadlog_delivery->valor_total);
        $params->obs             = null;
        $params->modalidade      = $this->modalidade;
        $params->contaCorrente   = $this->conta_corrente;
        $params->tpColeta        = $this->tipo_coleta;
        $params->tipoFrete       = intval($this->tipo_frete);
        $params->cdUnidadeOri    = $this->unidade_origem;
        $params->cdUnidadeDes    = null;
        $params->cdPickupOri     = null;
        $params->cdPickupDes     = $this->jadlog_delivery->pudo_id;
        $params->nrContrato      = empty($this->contrato) ? null : intval($this->contrato);
        $params->servico         = intval($this->servico);
        $params->shipmentId      = null;
        $params->vlColeta        = empty($this->valor_coleta) ? null : floatval($this->valor_coleta);
        return $params;
    }

    private function build_rem() {
        $rem = new stdClass();
        $rem->nome       = $this->rem_nome;
        $rem->cnpjCpf    = ServicesHelper::only_digits($this->rem_cpf_cnpj);
        $rem->ie         = $this->rem_ie;
        $rem->endereco   = $this->rem_endereco;
        $rem->numero     = $this->rem_numero;
        $rem->compl      = $this->rem_complemento;
        $rem->bairro     = $this->rem_bairro;
        $rem->cidade     = $this->rem_cidade;
        $rem->uf         = $this->rem_uf;
        $rem->cep        = ServicesHelper::only_digits($this->rem_cep);
        $rem->fone       = $this->rem_fone;
        $rem->cel        = $this->rem_cel;
        $rem->email      = $this->rem_email;
        $rem->contato    = $this->rem_contato;
        return $rem;
    }

    private function build_des($order) {
        $order_helper = new OrderHelper($order);
        $des = new stdClass();
        $des->nome     = $order->get_formatted_shipping_full_name();
        $des->cnpjCpf  = ServicesHelper::only_digits($order_helper->get_cpf_or_cnpj());
        $des->ie       = $order_helper->get_billing_ie();
        $des->endereco = $order->get_shipping_address_1();
        $des->numero   = $order_helper->get_shipping_number();
        $des->compl    = $order->get_shipping_address_2();
        $des->bairro   = $order_helper->get_shipping_neighborhood();
        $des->cidade   = $order->get_shipping_city();
        $des->uf       = $order->get_shipping_state();
        $des->cep      = ServicesHelper::only_digits($order->get_shipping_postcode());
        $des->fone     = $order->get_billing_phone();
        $des->cel      = $order_helper->get_billing_cellphone();
        $des->email    = $order->get_billing_email();
        $des->contato  = $order->get_formatted_billing_full_name();
        return $des;
    }

    private function build_dfe($params) {
        $dfe = new stdClass();
        $dfe->danfeCte    = $params['danfe_cte'];
        $dfe->nrDoc       = $params['nr_doc'];
        $dfe->serie       = $params['serie'];
        $dfe->cfop        = $params['cfop'];
        $dfe->tpDocumento = $params['tp_documento'];
        $dfe->valor       = LocalizedNumber::to_float($params['valor']);
        return $dfe;
    }

    private function build_volume($order) {
        $volume = new stdClass();
        $volume->altura        = null;
        $volume->comprimento   = null;
        $volume->largura       = null;
        $volume->peso          = floatval($this->jadlog_delivery->peso_taxado);
        $volume->identificador = $order->get_order_number();
        return $volume;
    }

    public function cancel($shipment_id) {
        $request_params = new stdClass();
        $request_params->shipmentId = $shipment_id;

        $response = wp_remote_post($this->url_cancelamento, array(
            'method'   => 'POST',
            'timeout'  => 30,
            'blocking' => true,
            'headers'  => array(
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => $this->key),
            'body'     => json_encode($request_params),
            'cookies'  => array()));

        $error_handler = new ErrorHandler($request_params, $response, __METHOD__);
        if ($error_handler->is_wp_error())
            return array(
                'status' => $response->get_error_message(),
                'erro' => array());
        elseif (!$error_handler->is_http_success())
            return array(
                'status' => $response['body'],
                'erro' => array('descricao' => $response['response']['code']));
        else
            return json_decode($response['body'], true);
    }

    public function get($shipment_id) {
        $shipment = new stdClass();
        $shipment->shipmentId = $shipment_id;

        // PARA TESTES APENAS.
        // Usar esta consulta caso a coleta tenha sido vinculada a um CT-e manualmente, para efeito de testes.
        // Neste caso, a consulta pelo shipmentId pode não funcionar se o CT-e estiver associado a outro shipmentId.
        // $shipment->codigo = '120822394'; // ou 120822062

        $request_params = new stdClass();
        $request_params->consulta = array($shipment);

        $response = wp_remote_post($this->url_consulta, array(
            'method'   => 'POST',
            'timeout'  => 30,
            'blocking' => true,
            'headers'  => array(
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => $this->key),
            'body'     => json_encode($request_params),
            'cookies'  => array()));

        $error_handler = new ErrorHandler($request_params, $response, __METHOD__);
        if ($error_handler->is_wp_error())
            return array(
                'status' => $response->get_error_message(),
                'erro' => array());
        elseif (!$error_handler->is_http_success())
            return array(
                'status' => $response['body'],
                'erro' => array('descricao' => $response['response']['code']));
        else
            return json_decode($response['body'], true);
    }

    public function get_pudos($zip_code) {

        $url = $this->url_pudos."/".urlencode($zip_code);

        $response = wp_remote_post($url, array(
            'method'   => 'POST',
            'timeout'  => 30,
            'blocking' => true,
            'headers'  => array(
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => $this->key),
            'cookies'  => array()));

        $error_handler = new ErrorHandler(null, $response, __METHOD__);
        if ($error_handler->is_wp_error())
            return array(
                'status' => $response->get_error_message(),
                'erro'   => array());
        elseif (!$error_handler->is_http_success())
            return array(
                'status' => $response['body'],
                'erro'   => array('descricao' => $response['response']['code']));
        else
            return json_decode($response['body'], true);
    }
}
