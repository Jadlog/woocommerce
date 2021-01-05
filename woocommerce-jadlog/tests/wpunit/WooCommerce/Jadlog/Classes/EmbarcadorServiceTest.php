<?php
namespace WooCommerce\Jadlog\Classes;

class EmbarcadorServiceTest extends \Codeception\TestCase\WPTestCase
{
    private $subject;

    public function setUp(): void {
        parent::setUp();
        add_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post', 10, 2);

        $this->subject = new EmbarcadorService(null);
    }

    public function tearDown(): void {
        parent::tearDown();
        remove_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post');
    }

    private static $response, $request_data;
    public static function mock_wp_remote_post($url, $data) {
        self::$request_data = $data;
        return self::$response;
    }

    public function test_get_pudos_with_success() {
        self::$response = array(
            'response' => array('code' => 200),
            'body' => '{"result":"success"}');
        $this->assertEquals(
            ['result' => 'success'],
            $this->subject->get_pudos('12300999'));
    }

    public function test_get_pudos_with_error() {
        self::$response = new \WP_Error('error', 'Forced error');
        $this->assertEquals(
            array('status' => 'Forced error', 'erro' => array()),
            $this->subject->get_pudos('12300999'));

        self::$response = array(
            'response' => array('code' => '404'),
            'body'     => 'Resource not found');
        $this->assertEquals(
            array(
                'status' => 'Resource not found',
                'erro'   => array('descricao' => '404')),
            $this->subject->get_pudos('12300999'));
    }

    public function test_get_with_success() {
        self::$response = array(
            'response' => array('code' => 204),
            'body' => '{"result":"success"}');
        $this->assertEquals(['result' => 'success'], $this->subject->get(1234));
        $this->assertEquals(
            '{"consulta":[{"shipmentId":1234}]}',
            self::$request_data['body']);
    }

    public function test_get_with_error() {
        self::$response = new \WP_Error('error', 'Forced error');
        $this->assertEquals(
            array('status' => 'Forced error', 'erro' => array()),
            $this->subject->get(0));

        self::$response = array(
            'response' => array('code' => '404'),
            'body'     => 'Resource not found');
        $this->assertEquals(
            array(
                'status' => 'Resource not found',
                'erro'   => array('descricao' => '404')),
            $this->subject->get(0));
    }

    public function test_cancel_with_success() {
        self::$response = array(
            'response' => array('code' => 200),
            'body' => '{"result":"success"}');
        $this->assertEquals(['result' => 'success'], $this->subject->cancel(9));
        $this->assertEquals('{"shipmentId":9}', self::$request_data['body']);
    }

    public function test_cancel_with_error() {
        self::$response = new \WP_Error('error', 'Forced error');
        $this->assertEquals(
            array('status' => 'Forced error', 'erro' => array()),
            $this->subject->cancel(9));

        self::$response = array(
            'response' => array('code' => '400'),
            'body'     => 'Bad request');
        $this->assertEquals(
            array(
                'status' => 'Bad request',
                'erro'   => array('descricao' => '400')),
            $this->subject->cancel(9));
    }
}

