<?php
namespace WooCommerce\Jadlog\Classes;

class ErrorHandlerTest extends \WP_UnitTestCase {

    private $request_body, $options;

    public function setUp() {
        parent::setUp();
        include_once("ErrorHandler.php");
        $this->request_body = array('body' => 'request_body');
        $this->options = array('logger_class' => ErrorHandlerTest\LoggerMock::class);
    }

    public function test_is_wp_error() {
        $response = new \WP_Error('fake error', 'This is my error');
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertTrue($subject->is_wp_error());

        $logger = ErrorHandlerTest\LoggerMock::get_attributes();
        $this->assertEquals(
            array(
                'message'      => 'This is my error',
                'function'     => __FUNCTION__,
                'response'     => $response,
                'request_body' => $this->request_body),
            $logger);

        $response = array();
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertFalse($subject->is_wp_error());
    }

    public function test_is_500_returns_true_for_500_http_responses() {
        $response = array(
            'response' => array('code' => '500'),
            'body' => 'error message');
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertTrue($subject->is_500());

        $logger = ErrorHandlerTest\LoggerMock::get_attributes();
        $this->assertEquals(
            array(
                'message'      => 'error message',
                'function'     => __FUNCTION__,
                'response'     => $response,
                'request_body' => $this->request_body),
            $logger);
    }

    public function test_is_500_returns_false_for_other_http_responses() {
        $response = array('response' => array('code' => 404));
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertFalse($subject->is_500());

        $response = array('response' => array('code' => null));
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertFalse($subject->is_500());

        $response = array('response' => 500);
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertFalse($subject->is_500());

        $response = array('response' => null);
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertFalse($subject->is_500());

        $response = null;
        $subject = new ErrorHandler(
            $this->request_body, $response, __FUNCTION__, $this->options);
        $this->assertFalse($subject->is_500());
    }

    public function test_is_error_set() {
        $subject = new ErrorHandler(
            $this->request_body, 'response', __FUNCTION__, $this->options);
        $this->assertTrue($subject->is_error_set(''));
        $this->assertTrue($subject->is_error_set(0));
        $this->assertTrue($subject->is_error_set(array()));
        $this->assertTrue($subject->is_error_set('error message'));

        $logger = ErrorHandlerTest\LoggerMock::get_attributes();
        $this->assertEquals(
            array(
                'message'      => 'error message',
                'function'     => __FUNCTION__,
                'response'     => 'response',
                'request_body' => $this->request_body),
            $logger);

        $subject = new ErrorHandler(
            $this->request_body, 'response', __FUNCTION__, $this->options);
        $this->assertFalse($subject->is_error_set(null));
    }
}


namespace WooCommerce\Jadlog\Classes\ErrorHandlerTest;

class LoggerMock {
    private static $attributes;

    public static function log_error($message, $function, $response = null, $request_body = null) {
        self::$attributes = array(
            'message'      => $message,
            'function'     => $function,
            'response'     => $response,
            'request_body' => $request_body
        );
    }

    public static function get_attributes() {
        return self::$attributes;
    }
}
