<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use WooCommerce\Jadlog\Classes\Delivery;
use WooCommerce\Jadlog\Classes\EmbarcadorService;
use WooCommerce\Jadlog\Classes\Modalidade;
use WooCommerce\Jadlog\Classes\PudoInfo;
use WooCommerce\Jadlog\Classes\ShippingPackage;
use WooCommerce\Jadlog\Classes\ShippingPrice;
use WooCommerce\Jadlog\Classes\ShippingPriceService;

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    function wc_jadlog_jadlogShippingInit() {
        if (! class_exists('WC_Jadlog_Shipping_Method')) {

            class WC_Jadlog_Shipping_Method extends WC_Shipping_Method {

                const METHOD_ID = "JADLOG";

                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct($instance_id = 0) {
                    $this->id          = self::METHOD_ID;
                    $this->instance_id = absint($instance_id);
                    $this->method_title       = 'Jadlog';
                    $this->method_description =
                        'Modalidades Package, Expresso e Pickup.<br/>'.
                        '<a href="http://www.jadlog.com.br" target="_blank">jadlog.com.br</a> - '.
                        'Uma empresa DPDgroup. Sua encomenda no melhor caminho.';

                    $this->enabled  = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
                    $this->title    = isset($this->settings['title']) ? $this->settings['title'] : __('Jadlog', 'jadlog');
                    $this->supports = array('shipping-zones');

                    $this->init();

                    include_once('Delivery.php');
                    include_once('EmbarcadorService.php');
                    include_once('Modalidade.php');
                    include_once('PudoInfo.php');
                    include_once('ShippingPackage.php');
                    include_once('ShippingPrice.php');
                    include_once('ShippingPriceService.php');
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

                    $this->jadlog_pickup_points_number     = get_option('wc_settings_tab_jadlog_qtd_pontos_pickup');
                    $this->jadlog_modalidade_com_ativa     = get_option('wc_settings_tab_jadlog_modalidade_com')     == 'yes';
                    $this->jadlog_modalidade_package_ativa = get_option('wc_settings_tab_jadlog_modalidade_package') == 'yes';
                    $this->jadlog_modalidade_pickup_ativa  = get_option('wc_settings_tab_jadlog_modalidade_pickup')  == 'yes';

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                /**
                 * This function is used to calculate the shipping cost.
                 * Within this function we can check for weights, dimensions and other parameters.
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

                    if ($this->jadlog_modalidade_com_ativa)
                        $this->jadlog_add_com_rate($package, $postcode);

                    if ($this->jadlog_modalidade_package_ativa)
                        $this->jadlog_add_package_rate($package, $postcode);

                    if ($this->jadlog_modalidade_pickup_ativa)
                        $this->jadlog_add_pickup_rates($package, $postcode);
                }

                private function jadlog_add_com_rate($package, $postcode) {
                    $shipping_package = new ShippingPackage($package, Modalidade::COD_COM);
                    $shipping_price   = new ShippingPrice($postcode, $shipping_package);
                    $cost             = $shipping_price->get_estimated_value();

                    if (!is_null($cost)) {
                        $rate = array(
                            'id'    => self::METHOD_ID.'_COM',
                            'label' => $this->jadlog_build_com_label($shipping_price),
                            'cost'  => $cost,
                            'taxes' => true,
                            'meta_data' => [
                                'modalidade'  => Modalidade::LABEL_COM,
                                'valor_total' => $shipping_package->get_price(),
                                'peso_taxado' => $shipping_package->get_effective_weight()
                            ],
                        );
                        $this->add_rate($rate);
                    }
                }

                private function jadlog_add_package_rate($package, $postcode) {
                    $shipping_package = new ShippingPackage($package, Modalidade::COD_PACKAGE);
                    $shipping_price   = new ShippingPrice($postcode, $shipping_package);
                    $cost             = $shipping_price->get_estimated_value();

                    if (!is_null($cost)) {
                        $rate = array(
                            'id'    => self::METHOD_ID.'_PACKAGE',
                            'label' => $this->jadlog_build_package_label($shipping_price),
                            'cost'  => $cost,
                            'taxes' => true,
                            'meta_data' => [
                                'modalidade'  => Modalidade::LABEL_PACKAGE,
                                'valor_total' => $shipping_package->get_price(),
                                'peso_taxado' => $shipping_package->get_effective_weight()
                            ],
                        );
                        $this->add_rate($rate);
                    }
                }

                private function jadlog_add_pickup_rates($package, $postcode) {
                    $shipping_package = new ShippingPackage($package, Modalidade::COD_PICKUP);
                    $embarcador       = new EmbarcadorService(null);
                    $pudos_result     = $embarcador->get_pudos($postcode);
                    $pudos_data       = (isset($pudos_result) && is_array($pudos_result['pudos'])) ? $pudos_result['pudos'] : [];

                    $count = 0;
                    foreach ($pudos_data as $pudo_data) {
                        $pudo           = new PudoInfo($pudo_data);
                        $shipping_price = new ShippingPrice($pudo->get_postcode(), $shipping_package);
                        $cost           = $shipping_price->get_estimated_value();

                        if ($pudo->nao_esta_ativo() || is_null($cost))
                            continue;
                        
                        if (!session_id()) {
                            session_start(['read_and_close' => true,]);
                        }
                        
                        $_SESSION[$pudo->get_id()] = $this->jadlog_build_session_array($pudo);

                        $rate = array(
                            'id'    => self::METHOD_ID.'_PUDO_'.$pudo->get_id(),
                            'label' => $this->jadlog_build_pickup_label($shipping_price, $pudo),
                            'cost'  => $cost,
                            'taxes' => true,
                            'meta_data' => array(
                                'modalidade'   => Modalidade::LABEL_PICKUP,
                                'valor_total'  => $shipping_package->get_price(),
                                'peso_taxado'  => $shipping_package->get_effective_weight(),
                                'pudo_id'      => $pudo->get_id(),
                                'pudo_name'    => $pudo->get_name(),
                                'pudo_address' => $pudo->get_formatted_address()
                            )
                        );
                        $this->add_rate($rate);

                        if (++$count >= $this->jadlog_pickup_points_number)
                            break;
                    }
                }

                private function jadlog_build_session_array($pudo) {
                    return array(
                        'latitude'  => $pudo->get_latitude(),
                        'longitude' => $pudo->get_longitude(),
                        'address'   => $pudo->get_formatted_address(),
                        'time'      => $pudo->get_openning_hours()
                    );
                }

                private function jadlog_build_com_label($shipping_price) {
                    return __('Jadlog Expresso', 'jadlog').$this->jadlog_insert_if_present(
                        $shipping_price->get_estimated_time(),
                        ' - $1 '.__('dias úteis', 'jadlog'),
                        ' - $1 '.__('dia útil', 'jadlog'));
                }

                private function jadlog_build_package_label($shipping_price) {
                    return __('Jadlog Package', 'jadlog').$this->jadlog_insert_if_present(
                        $shipping_price->get_estimated_time(),
                        ' - $1 '.__('dias úteis', 'jadlog'),
                        ' - $1 '.__('dia útil', 'jadlog'));
                }

                private function jadlog_build_pickup_label($shipping_price, $pudo) {
                    return __('Retire no ponto Pickup Jadlog', 'jadlog').' '.
                        $pudo->get_formatted_name_and_address().
                        $this->jadlog_insert_if_present(
                            $shipping_price->get_estimated_time(),
                            ' - $1 '.__('dias úteis', 'jadlog'),
                            ' - $1 '.__('dia útil', 'jadlog'));
                }

                private function jadlog_insert_if_present($text, $plural_phrase, $singular_phrase = '') {
                    if (empty($text))
                        return '';
                    else {
                        $phrase = is_numeric($text) && intval($text) == 1 ? $singular_phrase : $plural_phrase;
                        return str_replace('$1', $text, $phrase);
                    }
                }

            } //end of WC_Jadlog_Shipping_Method
        }
    }

    add_action('woocommerce_shipping_init', 'wc_jadlog_jadlogShippingInit');


    function wc_jadlog_save_order($order_id) {
        include_once('Delivery.php');
        $order = wc_get_order($order_id);
        $delivery = new Delivery($order);
        $delivery->create();
    }

    add_action('woocommerce_checkout_order_processed', 'wc_jadlog_save_order');


    function wc_jadlog_add_shipping_method($methods) {
        $methods[WC_Jadlog_Shipping_Method::METHOD_ID] = 'WC_Jadlog_Shipping_Method';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'wc_jadlog_add_shipping_method');
}
