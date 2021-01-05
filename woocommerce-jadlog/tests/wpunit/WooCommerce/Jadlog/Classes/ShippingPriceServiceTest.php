<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPriceServiceTest extends \Codeception\TestCase\WPTestCase
{
    private $subject;

    public function setUp(): void {
        parent::setUp();
        add_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post', 10, 3);

        $this->subject = new ShippingPriceService(Modalidade::COD_COM);
        self::$response = array('response' => array('code' => 200), 'body' => null);
    }

    public function tearDown(): void {
        parent::tearDown();
        remove_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post');
    }

    private static $response, $request_data;
    public static function mock_wp_remote_post($preempt, $parsed_args, $url) {
        self::$request_data = $parsed_args;
        return self::$response;
    }

    public function test_estimate_with_success() {
        self::$response['body'] = '{"frete":[{"vltotal":101.99,"prazo":5,"erro":null}],"erro":null,"error":null}';
        $this->assertEquals(
            array('estimated_value' => 101.99, 'estimated_time' => 5),
            $this->subject->estimate(100.0, '10999000', 2));
    }

    public function test_estimate_with_error() {
        self::$response['body'] = '{"frete":[{"vltotal":101.99,"prazo":5,"erro":null}],"erro":"Unknown error","error":null}';
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response['body'] = '{"frete":[{"vltotal":101.99,"prazo":5,"erro":null}],"erro":null,"error":"Unknown error"}';
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response['body'] = '{"frete":[{"vltotal":101.99,"prazo":5,"erro":"Unknown error"}],"erro":null,"error":null}';
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response['response']['code'] = '404';
        self::$response['body']             = '{"frete":[{"vltotal":101.99,"prazo":5,"erro":null}],"erro":null,"error":null}';
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));

        self::$response = new \WP_Error('error', 'Forced error');
        $this->assertEquals(
            array('estimated_value' => null, 'estimated_time' => null),
            $this->subject->estimate(100.0, '10999000', 2));
    }
}

