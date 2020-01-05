<?php
class EmbarcadorService {

    const TIPOS_DOCUMENTOS = array(
        0 => 'Declaração',
        1 => 'NF',
        2 => 'NF-e',
        4 => 'CT-e'
    );

    public function __construct($jadlog_id) {
        include_once("DeliveryRepository.php");
        include_once("Modalidade.php");
        include_once("OrderHelper.php");

        global $wpdb;
        $this->table = $wpdb->prefix . 'woocommerce_jadlog';

        $this->jadlog_delivery = DeliveryRepository::get_by_id($jadlog_id);

        $this->url              = get_option('wc_settings_tab_jadlog_url_embarcador');
        $this->key              = get_option('wc_settings_tab_jadlog_key_embarcador');
        $this->codigo_cliente   = get_option('wc_settings_tab_jadlog_codigo_cliente');
        $this->modalidade       = Modalidade::codigo_modalidade($this->jadlog_delivery->modalidade);
        $this->conta_corrente   = get_option('wc_settings_tab_jadlog_conta_corrente');
        $this->tipo_coleta      = get_option('wc_settings_tab_jadlog_tipo_coleta');
        $this->tipo_frete       = get_option('wc_settings_tab_jadlog_tipo_frete');
        $this->unidade_origem   = get_option('wc_settings_tab_jadlog_unidade_origem');
        $this->contrato         = get_option('wc_settings_tab_jadlog_contrato');
        $this->servico          = get_option('wc_settings_tab_jadlog_servico');

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

        //calculo do peso cubado
        $total_volume = 0.0;
        $total_weight = 0.0;
        $total  = 0.0;
        foreach( $order->get_items() as $item_id => $product_item ) {
            $quantity = $product_item->get_quantity(); // get quantity
            $product = $product_item->get_product(); // get the WC_Product object
            $product_weight = $product->get_weight(); // get the product weight
            // Add the line item weight to the total weight calculation
            $total_weight += floatval( $product_weight * $quantity );

            //valor dos produtos
            $valor = $product->get_price() * $quantity;
            $total += $valor;

            // dimensions
            $width = $product->get_width();
            $height = $product->get_height();
            $length = $product->get_length();
            $total_volume = $total_volume + (float) ($width * $length * $height * $quantity);
        }
        $peso_cubado = (float) $total_volume/6000;
        if($total_weight > $peso_cubado) {
            $peso_cubado = $total_weight;
        }


        $jadlog_request = new stdClass();
        $jadlog_request->codCliente      = $this->codigo_cliente;
        $jadlog_request->conteudo        = 'PLUGIN JADLOG';
        $jadlog_request->pedido          = $order->get_order_number();
        $jadlog_request->totPeso         = $peso_cubado;
        $jadlog_request->totValor        = $total; //$order->get_total();
        $jadlog_request->obs             = null;
        $jadlog_request->modalidade      = $this->modalidade;
        $jadlog_request->contaCorrente   = $this->conta_corrente;
        $jadlog_request->centroCusto     = null;
        $jadlog_request->tpColeta        = $this->tipo_coleta;
        $jadlog_request->cdPickupOri     = null;
        $jadlog_request->cdPickupDes     = $this->jadlog_delivery->pudo_id;
        $jadlog_request->tipoFrete       = $this->tipo_frete;
        $jadlog_request->cdUnidadeOri    = $this->unidade_origem;
        $jadlog_request->cdUnidadeDes    = null;
        $jadlog_request->nrContrato      = $this->contrato;
        $jadlog_request->servico         = $this->servico;
        $jadlog_request->shipmentId      = null;

        $jadlog_request->rem = $this->build_rem();

        $jadlog_request->des = $this->build_des($order);

        $jadlog_request->volume = new stdClass();
        $jadlog_request->volume->altura         = pow($total_volume, 1/3);
        $jadlog_request->volume->comprimento    = pow($total_volume, 1/3);
        $jadlog_request->volume->largura        = pow($total_volume, 1/3);
        $jadlog_request->volume->peso           = $peso_cubado;
        $jadlog_request->volume->identificador  = $order->get_order_number();
        $jadlog_request->volume->lacre          = null;

        $jadlog_request->dfe = $this->build_dfe($dfe);

        error_log('embarcador/coleta body: '.var_export($jadlog_request, true));
        error_log(var_export($order->get_data(), true));
        return;

        $response = wp_remote_post( $this->url, array(
                'method' => 'POST',
                'timeout' => 500,
                'blocking' => true,
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => $this->key],
                'body' => json_encode($jadlog_request),
                'cookies' => array()
            )
        );
        error_log( 'In ' . __FUNCTION__ . '(), $jadlog_request = ' . var_export( $jadlog_request, true ) );
        error_log( 'In ' . __FUNCTION__ . '(), $response = ' . var_export( $response, true ) );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return $error_message;
        } else {
            //TODO: save shipment id
            $this->_saveStatus($response['body']);
            return $response['body'];
        }

    }

    private function build_rem() {
        $rem = new stdClass();
        $rem->nome       = $this->rem_nome;
        $rem->cnpjCpf    = $this->only_digits($this->rem_cpf_cnpj);
        $rem->ie         = $this->rem_ie;
        $rem->endereco   = $this->rem_endereco;
        $rem->numero     = $this->rem_numero;
        $rem->compl      = $this->rem_complemento;
        $rem->bairro     = $this->rem_bairro;
        $rem->cidade     = $this->rem_cidade;
        $rem->uf         = $this->rem_uf;
        $rem->cep        = $this->only_digits($this->rem_cep);
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
        $des->cnpjCpf  = $this->only_digits($order_helper->get_cpf_or_cnpj());
        $des->ie       = $order_helper->get_billing_ie();
        $des->endereco = $order->get_shipping_address_1();
        $des->numero   = $order_helper->get_shipping_number();
        $des->compl    = $order->get_shipping_address_2();
        $des->bairro   = $order_helper->get_shipping_neighborhood();
        $des->cidade   = $order->get_shipping_city();
        $des->uf       = $order->get_shipping_state();
        $des->cep      = $this->only_digits($order->get_shipping_postcode());
        $des->fone     = $order->get_billing_phone();
        $des->cel      = $order_helper->get_billing_cellphone();
        $des->email    = $order->get_billing_email();
        $des->contato  = $order->get_formatted_billing_full_name();
        return $des;
    }

    private function build_dfe($params) {
        $dfe = new stdClass();
        $dfe->danfeCte    = $params['danfe_cte'];
        $dfe->valor       = $params['valor'];
        $dfe->nrDoc       = $params['nr_doc'];
        $dfe->serie       = $params['serie'];
        $dfe->cfop        = $params['cfop'];
        $dfe->tpDocumento = $params['tp_documento'];
        return $dfe;
    }

    private function only_digits($string) {
        return preg_replace('/[^0-9]/', '', $string);
    }

    /**
     * This function is used to send the post request to embarcador
     *
     * @access public
     * @return json
     */
    public function _saveStatus($response) {

        global $wpdb;

        $response = json_decode($response);

        if (isset($response->erro)) {
            $wpdb->update( $this->table, [
                'status' => $response->status,
                'erro' => $response->erro->descricao
            ], [
                'id' => $this->jadlog_id
            ]);
        } else {
            $wpdb->update( $this->table, [
                'status' => $response->status,
                'erro' => ''
            ], [
                'id' => $this->jadlog_id
            ]);
        }

    }

}
