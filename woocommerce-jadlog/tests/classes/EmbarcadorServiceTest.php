<?php
namespace WooCommerce\Jadlog\Classes;

class EmbarcadorServiceTest extends \WP_UnitTestCase {

    private $subject;

    public function setUp() {
        parent::setUp();
        include_once("EmbarcadorService.php");
        include_once("Logger.php");
        add_filter('pre_http_request', __CLASS__.'::mock_wp_remote_post', 10, 2);

        $this->subject = new EmbarcadorService(null);
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

    public function test_get_pudos_with_success() {
        self::$response = array('body' => '{"result":"success"}');
        $this->assertEquals(['result' => 'success'], $this->subject->get_pudos('12300999'));
    }

    public function test_get_pudos_with_error() {
        self::$response = new \WP_Error('error', 'Forced error');
        $this->assertEquals(
            array('status' => 'Forced error', 'erro' => array()),
            $this->subject->get_pudos('12300999'));

        self::$response = array(
            'response' => array('code' => '500'),
            'body'     => 'Internal server error');
        $this->assertEquals(
            array(
                'status' => 'Internal server error',
                'erro'   => array('descricao' => '500')),
            $this->subject->get_pudos('12300999'));
    }

    public function test_get_with_success() {
        self::$response = array('body' => '{"result":"success"}');
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
            'response' => array('code' => '500'),
            'body'     => 'Internal server error');
        $this->assertEquals(
            array(
                'status' => 'Internal server error',
                'erro'   => array('descricao' => '500')),
            $this->subject->get(0));
    }

    public function test_cancel_with_success() {
        self::$response = array('body' => '{"result":"success"}');
        $this->assertEquals(['result' => 'success'], $this->subject->cancel(9));
        $this->assertEquals('{"shipmentId":9}', self::$request_data['body']);
    }

    public function test_cancel_with_error() {
        self::$response = new \WP_Error('error', 'Forced error');
        $this->assertEquals(
            array('status' => 'Forced error', 'erro' => array()),
            $this->subject->cancel(9));

        self::$response = array(
            'response' => array('code' => '500'),
            'body'     => 'Internal server error');
        $this->assertEquals(
            array(
                'status' => 'Internal server error',
                'erro'   => array('descricao' => '500')),
            $this->subject->cancel(9));
    }
}

