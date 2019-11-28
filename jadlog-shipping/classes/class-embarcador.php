<?php
/**
 * Created by PhpStorm.
 * User: quinho
 * Date: 26/10/17
 * Time: 14:54
 */
/**
 * Class JadLogMyPudo
 */
class JadLogEmbarcador {

    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct($jadlog_id, $pudo_id) {
        global $wpdb;

        $this->table = $wpdb->prefix . 'woocommerce_jadlog';

        $this->jadlog_id        = $jadlog_id;
        $this->pudo_id          = $pudo_id;

        $this->url              = get_option('wc_settings_tab_jadlog_url_embarcador');
        $this->key              = get_option('wc_settings_tab_jadlog_key_embarcador');
        $this->codigo_cliente   = get_option('wc_settings_tab_jadlog_codigo_cliente');
        $this->modalidade       = get_option('wc_settings_tab_jadlog_modalidade');
        $this->conta_corrente   = get_option('wc_settings_tab_jadlog_conta_corrente');
        $this->tipo_coleta      = get_option('wc_settings_tab_jadlog_tipo_coleta');
        $this->tipo_frete       = get_option('wc_settings_tab_jadlog_tipo_frete');
        $this->unidade_origem   = get_option('wc_settings_tab_jadlog_unidade_origem');
        $this->contrato         = get_option('wc_settings_tab_jadlog_contrato');
        $this->servico          = get_option('wc_settings_tab_jadlog_servico');

        $this->rem_nome         = get_option('wc_settings_tab_jadlog_shipper_name');
        $this->rem_cpf_cnpj     = get_option('wc_settings_tab_jadlog_shipper_cnpj_cpf');
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
    public function postRequestEmbarcador()  {

        global $wpdb;

        $order_id = $wpdb->get_col("SELECT ORDER_ID FROM {$this->table}
                                     WHERE id = {$this->jadlog_id}");

        $order = new WC_Order( $order_id[0] );

        $jadlog_request = new stdClass();
        $jadlog_request->codCliente      = $this->codigo_cliente;
        $jadlog_request->conteudo        = 'PLUGIN JADLOG';
        $jadlog_request->pedido          = $order->get_order_number();
        $jadlog_request->totPeso         = 1;
        $jadlog_request->totValor        = $order->get_total();
        $jadlog_request->obs             = null;
        $jadlog_request->modalidade      = $this->modalidade;
        $jadlog_request->contaCorrente   = $this->conta_corrente;
        $jadlog_request->centroCusto     = null;
        $jadlog_request->tpColeta        = $this->tipo_coleta;
        $jadlog_request->cdPickupOri     = null;
        $jadlog_request->cdPickupDes     = $this->pudo_id;
        $jadlog_request->tipoFrete       = $this->tipo_frete;
        $jadlog_request->cdUnidadeOri    = $this->unidade_origem;
        $jadlog_request->cdUnidadeDes    = null;
        $jadlog_request->nrContrato      = $this->contrato;
        $jadlog_request->servico         = $this->servico;
        $jadlog_request->shipmentId      = null;

        $jadlog_request->rem = new stdClass();
        $jadlog_request->rem->nome       = $this->rem_nome;
        $jadlog_request->rem->cnpjCpf    = $this->rem_cpf_cnpj;
        $jadlog_request->rem->ie         = null;
        $jadlog_request->rem->endereco   = $this->rem_endereco;
        $jadlog_request->rem->numero     = $this->rem_numero;
        $jadlog_request->rem->compl      = $this->rem_complemento;
        $jadlog_request->rem->bairro     = $this->rem_bairro;
        $jadlog_request->rem->cidade     = $this->rem_cidade;
        $jadlog_request->rem->uf         = $this->rem_uf;
        $jadlog_request->rem->cep        = $this->rem_cep;
        $jadlog_request->rem->fone       = $this->rem_fone;
        $jadlog_request->rem->cel        = $this->rem_cel;
        $jadlog_request->rem->email      = $this->rem_email;
        $jadlog_request->rem->contato    = $this->rem_contato;

        $jadlog_request->des = new stdClass();
        $jadlog_request->des->nome         = $order->get_formatted_billing_full_name();
        $jadlog_request->des->cnpjCpf      = $order->get_meta( '_billing_persontype' ) == 1 ? str_replace('.', '', str_replace('-', '', $order->get_meta( '_billing_cpf' ))) : str_replace('.', '', str_replace('-', '', str_replace('/', '', $order->get_meta( '_billing_cnpj' ))));
        $jadlog_request->des->ie           = null;
        $jadlog_request->des->endereco     = $order->get_billing_address_1();
        $jadlog_request->des->numero       = $order->get_meta( '_billing_number' );
        $jadlog_request->des->compl        = null;
        $jadlog_request->des->bairro       = $order->get_meta( '_billing_neighborhood' );
        $jadlog_request->des->cidade       = $order->get_shipping_city();
        $jadlog_request->des->uf           = $order->get_billing_state();
        $jadlog_request->des->cep          = str_replace('-', '', $order->get_billing_postcode());
        $jadlog_request->des->fone         = $order->get_billing_phone();
        $jadlog_request->des->cel          = $order->get_meta( '_billing_cellphone' );
        $jadlog_request->des->email        = $order->get_billing_email();
        $jadlog_request->des->contato      = $order->get_formatted_billing_full_name();

        $jadlog_request->volume = new stdClass();
        $jadlog_request->volume->altura         = 10;
        $jadlog_request->volume->comprimento    = 10;
        $jadlog_request->volume->largura        = 10;
        $jadlog_request->volume->peso           = 1.0;
        $jadlog_request->volume->identificador  = '242424887';
        $jadlog_request->volume->lacre          = null;

        $jadlog_request->dfe = new stdClass();
        $jadlog_request->dfe->danfeCte          = null;
        $jadlog_request->dfe->valor             = 20.2;
        $jadlog_request->dfe->nrDoc             = 'DECLARACAO';
        $jadlog_request->dfe->serie             = null;
        $jadlog_request->dfe->cfop              = '6909';
        $jadlog_request->dfe->tpDocumento       = 2;

        $response = wp_remote_post( $this->url, array(
                'method' => 'POST',
                'timeout' => 500,
                'blocking' => true,
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => $this->key],
                'body' => json_encode($jadlog_request),
                'cookies' => array()
            )
        );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return $error_message;
        } else {
            $this->_saveStatus($response['body']);
            return $response['body'];
        }

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