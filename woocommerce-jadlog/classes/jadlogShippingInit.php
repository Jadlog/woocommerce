<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    function jadlogShippingInit() {
        if ( ! class_exists( 'WC_Jadlog_Shipping_Method' ) ) {

            class WC_Jadlog_Shipping_Method extends WC_Shipping_Method {

                const METHOD_ID = "JADLOG";

                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = self::METHOD_ID;
                    $this->method_title       = __('Jadlog', 'jadlog');
                    // $this->method_description = __('Jadlog', 'jadlog');

                    $this->init();

                    $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('Jadlog', 'jadlog');

                    include_once('jadlog-mypudo.php');
                    include_once('Delivery.php');
                    include_once('Modalidade.php');
                    include_once('ShippingPriceService.php');
                    include_once('ShippingPackage.php');
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

                    $this->pickup_points_number      = get_option('wc_settings_tab_jadlog_qtd_pontos_pickup');
                    $this->modalidade_pickup_ativa   = get_option('wc_settings_tab_jadlog_modalidade_pickup')   == 'yes';
                    $this->modalidade_expresso_ativa = get_option('wc_settings_tab_jadlog_modalidade_expresso') == 'yes';

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

                    $postcode = $woocommerce->customer->get_shipping_postcode();
                    if (empty($postcode))
                        return;

                    if ($this->modalidade_expresso_ativa) {
                        $shipping_package = new ShippingPackage($package, Modalidade::COD_EXPRESSO);
                        $valor_total = $shipping_package->get_price();
                        $peso_taxado = $shipping_package->get_effective_weight();

                        $estimated_values = $this->jadlog_get_express_price($valor_total, $postcode, $peso_taxado);
                        $time = isset($estimated_values['estimated_time']) ? ' - '.$estimated_values['estimated_time'].' dias úteis' : '';
                        $label = __('Jadlog Expresso', 'jadlog').$time;

                        $rate = array(
                            // 'id'    => $pudo_id,
                            'label' => $label,
                            'cost'  => $estimated_values['estimated_value'],
                            'taxes' => true,
                            'meta_data' => [
                                'modalidade'  => Modalidade::LABEL_EXPRESSO,
                                'valor_total' => $valor_total,
                                'peso_taxado' => $peso_taxado
                            ],
                        );
                        $this->add_rate($rate);
                    }

                    if ($this->modalidade_pickup_ativa) {
                        $shipping_package = new ShippingPackage($package, Modalidade::COD_PICKUP);
                        $valor_total = $shipping_package->get_price();
                        $peso_taxado = $shipping_package->get_effective_weight();

                        $jadlogMyPudo = new JadLogMyPudo();
                        $pudos        = $jadlogMyPudo->getPudos($postcode);

                        if (isset($pudos['PUDO_ITEMS']['PUDO_ITEM'])) {
                            $count = 0;
                            foreach ($pudos['PUDO_ITEMS']['PUDO_ITEM'] as $key => $pudo_item ) {
                                $pudo_id = $pudo_item['PUDO_ID'];
                                $address = $pudo_item['ADDRESS1'].', '.$pudo_item['STREETNUM'].' '.join(" ", $pudo_item['ADDRESS2']).' - '.$pudo_item['ADDRESS3'].
                                    ' - '.$pudo_item['CITY'].' - '.$pudo_item['ZIPCODE'];
                                $_SESSION[$pudo_id]['latitude']  = $pudo_item['LATITUDE'];
                                $_SESSION[$pudo_id]['longitude'] = $pudo_item['LONGITUDE'];
                                $_SESSION[$pudo_id]['address']   = $address;
                                $_SESSION[$pudo_id]['time']      = $pudo_item['OPENING_HOURS_ITEMS'];
                                $distance = round(intval($pudo_item['DISTANCE']) / 1000.0, 1);
                                $estimated_values = $this->jadlog_get_pudo_price($valor_total, $pudo_item, $peso_taxado);
                                $time = isset($estimated_values['estimated_time']) ? ' - '.$estimated_values['estimated_time'].' dias úteis' : '';
                                $label = __('Retire no ponto Pickup Jadlog', 'jadlog').' '.$pudo_item['NAME'].' - '.
                                    $address.' ('.number_format($distance, 1, ',', '.').' km)'.$time;
                                $rate = array(
                                    'id'    => $pudo_id,
                                    'label' => $label,
                                    'cost'  => $estimated_values['estimated_value'],
                                    'taxes' => true,
                                    'meta_data' => [
                                        'modalidade'   => Modalidade::LABEL_PICKUP,
                                        'valor_total'  => $valor_total,
                                        'peso_taxado'  => $peso_taxado,
                                        'pudo_id'      => $pudo_item['PUDO_ID'],
                                        'pudo_name'    => $pudo_item['NAME'],
                                        'pudo_address' => $address
                                    ],
                                );
                                $this->add_rate($rate);

                                if (++$count >= $this->pickup_points_number)
                                    break;
                            }
                        }
                    }
                }

                private function jadlog_get_pudo_price($valor, $pudo, $peso) {
                    $service = new ShippingPriceService(Modalidade::COD_PICKUP);
                    $result = $service->estimate($valor, $pudo['ZIPCODE'], $peso);
                    return $result;
                }

                private function jadlog_get_express_price($valor, $zipcode, $peso) {
                    $service = new ShippingPriceService(Modalidade::COD_EXPRESSO);
                    $result = $service->estimate($valor, $zipcode, $peso);
                    return $result;
                }

            } //end of WC_Jadlog_Shipping_Method
        }
    }

    add_action( 'woocommerce_shipping_init', 'jadlogShippingInit' );


    function save_jadlog_order($order_id) {
        $order = wc_get_order($order_id);
        $delivery = new Delivery($order);
        $delivery->create();
    }

    add_action('woocommerce_checkout_order_processed', 'save_jadlog_order');


    function add_jadlog_shipping_method($methods) {
        $methods['WC_Jadlog_Shipping_Method'] = 'WC_Jadlog_Shipping_Method';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_jadlog_shipping_method');
}
