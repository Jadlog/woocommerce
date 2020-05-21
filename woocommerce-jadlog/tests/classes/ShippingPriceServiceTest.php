<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPriceServiceTest extends \WP_UnitTestCase {

    private $subject;

    public function setUp() {
        parent::setUp();
        include_once("Modalidade.php");
        include_once("ShippingPriceService.php");
        add_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post', 10, 2);

        $this->subject = new ShippingPriceService(Modalidade::COD_COM);
    }

    private static $response, $request_data;
    public static function mock_wp_remote_post($url, $data) {
        self::$request_data = $data;
        return self::$response;
    }

    public function tearDown() {
        parent::tearDown();
        remove_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post');
    }

    public function test_estimate_with_success() {
        self::$response = array('body' => '{"frete":[{"vltotal":101.99,"prazo":5,"erro":null}],"erro":null,"error":null}');
        $this->assertEquals(
            array('estimated_value' => 101.99, 'estimated_time' => 5),
            $this->subject->estimate(100.0, '10999000', 2));
    }

    public function test_estimate_with_error() {
        self::$response = new \WP_Error('error', 'Forced error');
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response = array(
            'response' => array('code' => '500'),
            'body'     => 'Internal server error');
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response = array('body' => '{"frete":[{"vltotal":101.99,"prazo":5,"erro":null}],"erro":"Unknown error","error":null}');
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response = array('body' => '{"frete":[{"vltotal":101.99,"prazo":5,"erro":null}],"erro":null,"error":"Unknown error"}');
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response = array('body' => '{"frete":[{"vltotal":101.99,"prazo":5,"erro":"Unknown error"}],"erro":null,"error":null}');
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));
    }
}

