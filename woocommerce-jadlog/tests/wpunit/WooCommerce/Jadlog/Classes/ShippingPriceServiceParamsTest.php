<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPriceServiceParamsTest extends \Codeception\TestCase\WPTestCase
{
    private $options = array(
        'wc_settings_tab_jadlog_url_simulador_frete',
        'wc_settings_tab_jadlog_key_embarcador',
        'wc_settings_tab_jadlog_shipper_cep',
        'wc_settings_tab_jadlog_shipper_cnpj_cpf',
        'wc_settings_tab_jadlog_conta_corrente',
        'wc_settings_tab_jadlog_contrato',
        'wc_settings_tab_jadlog_frap_com',
        'wc_settings_tab_jadlog_tipo_entrega_com',
        'wc_settings_tab_jadlog_tipo_seguro_com',
        'wc_settings_tab_jadlog_valor_coleta_com');

    public function setUp(): void {
        parent::setUp();

        add_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post', 10, 3);
        foreach ($this->options as $option)
            add_filter("pre_option_{$option}", __CLASS__.'::mock_get_option', 10, 3);
    }

    public function tearDown(): void {
        parent::tearDown();

        remove_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post');
        foreach ($this->options as $option)
            remove_filter("pre_option_{$option}", __CLASS__.'::mock_get_option');
    }

    private static $url, $request_data;
    public static function mock_wp_remote_post($preempt, $parsed_args, $url) {
        self::$url          = $url;
        self::$request_data = $parsed_args;
        return new \WP_Error('error');
    }

    public static function mock_get_option($pre_option, $option, $default) {
        switch ($option) {
        case 'wc_settings_tab_jadlog_shipper_cep':
            return '12.000-999';
        case 'wc_settings_tab_jadlog_shipper_cnpj_cpf':
            return '01.222.333/0001-99';
        case 'wc_settings_tab_jadlog_frap_com':
            return 'yes';
        case 'wc_settings_tab_jadlog_valor_coleta_com':
            return '5.40';
        default:
            return strtoupper($option);
        }
    }

    public function test_estimate_with_correct_params() {
        $subject = new ShippingPriceService(Modalidade::COD_COM);
        $subject->estimate(100, '98.000-111', 3.1);

        $this->assertEquals('WC_SETTINGS_TAB_JADLOG_URL_SIMULADOR_FRETE', self::$url);
        $expected_params = array(
            'frete' => array(array(
                'cepori'      => '12000999',
                'cepdes'      => '98000111',
                'frap'        => 'S',
                'peso'        => 3.1,
                'cnpj'        => '01222333000199',
                'conta'       => 'WC_SETTINGS_TAB_JADLOG_CONTA_CORRENTE',
                'contrato'    => 'WC_SETTINGS_TAB_JADLOG_CONTRATO',
                'modalidade'  => 9, //COM
                'tpentrega'   => 'WC_SETTINGS_TAB_JADLOG_TIPO_ENTREGA_COM',
                'tpseguro'    => 'WC_SETTINGS_TAB_JADLOG_TIPO_SEGURO_COM',
                'vldeclarado' => 100.0,
                'vlcoleta'    => 5.4)));
        $this->assertEquals(json_encode($expected_params), self::$request_data['body']);
    }
}

