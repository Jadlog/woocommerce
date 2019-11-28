<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    function jadlogShippingInit(){
        if ( ! class_exists( 'WC_Jadlog_Shipping_Method' ) ) {

            /**
             * Class WC_MelhorEnvioShippingMethod
             */
            class WC_Jadlog_Shipping_Method extends WC_Shipping_Method {

                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'melhorenvio';
                    $this->method_title       = __( 'Melhor Envio', 'melhorenvio' );
                    $this->method_description = __( 'Metodo de envio utilzando a Melhor Envio.', 'melhorenvio' );

                    $this->init();

                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Melhor Envio', 'melhorenvio' );

                    include_once('class-mypudo.php');
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param array $package
                 * @return void
                 */
                public function calculate_shipping($package = array()) {

                    global $woocommerce;

                    $JDgetPudos = new JadLogMyPudo();

                    $pudos = $JDgetPudos->getPudos($woocommerce->customer->get_shipping_postcode());

                    if (isset($pudos['PUDO_ITEMS']['PUDO_ITEM'])) {
                        if (isset($pudos['PUDO_ITEMS']['PUDO_ITEM']['PUDO_ID'])) {
                            $_SESSION[$pudos['PUDO_ITEMS']['PUDO_ITEM']['PUDO_ID']]['latitude'] = $pudos['PUDO_ITEMS']['PUDO_ITEM']['LATITUDE'];
                            $_SESSION[$pudos['PUDO_ITEMS']['PUDO_ITEM']['PUDO_ID']]['longitude'] = $pudos['PUDO_ITEMS']['PUDO_ITEM']['LONGITUDE'];
                            $_SESSION[$pudos['PUDO_ITEMS']['PUDO_ITEM']['PUDO_ID']]['address'] = $pudos['PUDO_ITEMS']['PUDO_ITEM']['ADDRESS1'] . ' - ' . $pudos['PUDO_ITEMS']['PUDO_ITEM']['STREETNUM'] . ' - Cep ' . $pudos['PUDO_ITEMS']['PUDO_ITEM']['ZIPCODE']. ' - ' . $pudos['PUDO_ITEMS']['PUDO_ITEM']['CITY'];
                            $_SESSION[$pudos['PUDO_ITEMS']['PUDO_ITEM']['PUDO_ID']]['time'] = $pudos['PUDO_ITEMS']['PUDO_ITEM']['OPENING_HOURS_ITEMS'];
                            $rate = array(
                                'id' => $pudos['PUDO_ITEMS']['PUDO_ITEM']['PUDO_ID'],
                                'label' => 'Jadlog - Pickup - ' . $pudos['PUDO_ITEMS']['PUDO_ITEM']['NAME'],
                                'cost' => 0,
                                'meta_data' => [
                                    'id_pudo' => $pudos['PUDO_ITEMS']['PUDO_ITEM']['PUDO_ID'],
                                    'name_pudo' => $pudos['PUDO_ITEMS']['PUDO_ITEM']['NAME'],
                                    'address_pudo' => $pudos['PUDO_ITEMS']['PUDO_ITEM']['ADDRESS1'] . ' - ' .
                                        $pudos['PUDO_ITEMS']['PUDO_ITEM']['STREETNUM'] . ' - ' .
                                        $pudos['PUDO_ITEMS']['PUDO_ITEM']['CITY'],
                                    'zipcode_pudo'     => $pudos['PUDO_ITEMS']['PUDO_ITEM']['ZIPCODE'],
                                ],
                            );
                            $this->add_rate($rate);
                        } else {
                            $count = 0;
                            foreach ($pudos['PUDO_ITEMS']['PUDO_ITEM'] as $key => $value ) {
                                $count++;
                                $_SESSION[$value['PUDO_ID']]['latitude'] = $value['LATITUDE'];
                                $_SESSION[$value['PUDO_ID']]['longitude'] = $value['LONGITUDE'];
                                $_SESSION[$value['PUDO_ID']]['address'] = $value['ADDRESS1'] . ' - ' . $value['STREETNUM'] . ' - Cep ' . $value['ZIPCODE']. ' - ' . $value['CITY'];
                                $_SESSION[$value['PUDO_ID']]['time'] = $value['OPENING_HOURS_ITEMS'];
                                $rate = array(
                                    'id' => $value['PUDO_ID'],
                                    'label' => 'Jadlog - Pickup - ' . $value['NAME'],
                                    'cost' => jadlog_get_pudo_price(jadlog_getPackage($package)->preco,$value,jadlog_getPackage($package)->peso_cubado),
                                    'meta_data' => [
                                        'id_pudo' => $value['PUDO_ID'],
                                        'name_pudo' => $value['NAME'],
                                        'address_pudo' => $value['ADDRESS1'] . ' - ' . $value['STREETNUM'] . ' - ' .
                                                          $value['CITY'],
                                        'zipcode_pudo'     => $value['ZIPCODE'],
                                    ],
                                );
                                $this->add_rate($rate);
                            }
                        }
                    }

                }

            }

        }

    }

    function jadlog_getPackage($package){
        $volume =0;
        $weight =0;
        $total  =0;
        $pacote = new stdClass();
        foreach ($package['contents'] as $item){
            $width = wc_get_product($item['product_id'])->get_width();
            $height = wc_get_product($item['product_id'])->get_height();
            $length = wc_get_product($item['product_id'])->get_length();
            $weight = $weight + wc_get_product($item['product_id'])->get_weight()  * $item['quantity'];
            $valor = wc_get_product($item['product_id'])->get_price() * $item['quantity'];
            $volume  = $volume +  (float) ($width * $length * $height) * $item['quantity'];
            $total =+ $valor ;
        }
        $pacote->volume = (float) $volume/6000;
        $pacote->peso = $weight;
        $pacote->preco = $valor;
        $pacote->peso_cubado = $pacote->volume;
        if($pacote->peso > $pacote->peso_cubado) {
            $pacote->peso_cubado = $pacote->peso;
        }
        error_log( 'In ' . __FUNCTION__ . '(), $pacote = ' . var_export( $pacote, true ) );

        return $pacote;
    }

    function getShipPrice($vldec,$cepdest,$peso){
        $cepdest = str_replace("-","",$cepdest);
        $curl = curl_init();
        $url = "http://www.jadlog.com.br/JadlogEdiWs/services/ValorFreteBean?method=valorar&vModalidade=40&Password=".get_option("wc_settings_tab_jadlog_senha")."&vSeguro=N&vVlDec=".$vldec."&vVlColeta=&vCepOrig=".get_option("wc_settings_tab_jadlog_shipper_cep")."&vCepDest=".$cepdest."&vPeso=".$peso."&vFrap=N&vEntrega=D&vCnpj=".get_option("wc_settings_tab_jadlog_shipper_cnpj_cpf");

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "postman-token: cb80c09a-e569-be29-ea50-22054e786a98"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        error_log( 'In ' . __FUNCTION__ . '(), $url = ' . var_export( $url, true ) );
        error_log( 'In ' . __FUNCTION__ . '(), $response = ' . var_export( $response, true ) );
        error_log( 'In ' . __FUNCTION__ . '(), $err = ' . var_export( $err, true ) );
        curl_close($curl);
        $resposta = convertXmlToJson($response,"Jadlog_Valor_Frete");
        $resposta = json_decode($resposta);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $resposta->response->code;
        }
    }

    function convertXmlToJson($xml, $type)
    {
        $p = xml_parser_create();
        xml_parse_into_struct($p, $xml, $vals, $index);
        xml_parser_free($p);
        $xml = simplexml_load_string($vals[3]['value']);

        $xml = (array) $xml->{$type};

        $response = ["status" => false, "message" => "Falha ao coletar dados"];

        if (isset($xml["ND"]))
            $response = [
                "response" => $xml["ND"],
                "status" => true,
            ];

        if (isset($xml["Retorno"]) && $xml["Retorno"] < 0)
            $response= [
                "status" => false,
                "error" => [
                    "code" => (isset($xml["Retorno"]) ? $xml["Retorno"] : 9999),
                    "message" => (isset($xml["Mensagem"]) ? $xml["Mensagem"] : "Não retornou dados."),
                ]
            ];

        if (isset($xml["Retorno"]) && $xml["Retorno"] > 0)
            $response= [
                "status" => true,
                "response" => [
                    "code" => $xml["Retorno"],
                    "message" => $xml["Mensagem"],
                ]

            ];

        return json_encode($response);
    }

    function jadlog_get_pudo_price($valor,$pudo,$peso){

        return getShipPrice($valor,$pudo['ZIPCODE'],$peso);
    }




    function jadlog_save_pudos ($order_id) {

        global $wpdb;
        $table =  $wpdb->prefix . 'woocommerce_jadlog';
        $order = new WC_Order( $order_id );
        $shipping = explode('-', $order->get_shipping_method());

        foreach ($order->get_shipping_methods() as $key => $value) {
            $shipping_method_data = $order->get_item_meta_array($key);
        }

        foreach ($shipping_method_data as $key => $value) {
            if ($value->key == 'id_pudo') {
                $id_pudo = $value->value;
            }
            if ($value->key == 'address_pudo') {
                $address = $value->value;
            }
            if ($value->key == 'name_pudo') {
                $name = $value->value;
            }
            if ($value->key == 'zipcode_pudo') {
                $zipcode = $value->value;
            }
        }

        if (count($shipping) >= 3):
            $wpdb->insert($table, array(
                'order_id' => $order_id,
                'shipping_method' => 'Jadlog - Pickup',
                'pudo_id' => $id_pudo,
                'name' => $name,
                'address' => $address,
                'postcode' => $zipcode,
                'status' => 'Pendente',
                'date_creation' => $order->get_date_created()
            ));
        endif;

    }

    add_action( 'woocommerce_shipping_init', 'jadlogShippingInit' );

    add_action( 'woocommerce_checkout_order_processed', 'jadlog_save_pudos' );

    function add_jadlog_shipping_method( $methods ) {
        $methods['WC_Jadlog_Shipping_Method'] = 'WC_Jadlog_Shipping_Method';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_jadlog_shipping_method');

}