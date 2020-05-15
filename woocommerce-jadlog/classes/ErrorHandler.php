<?php
namespace WooCommerce\Jadlog\Classes;

class ErrorHandler {

    public function __construct($request_body, $response, $method, $options = array()) {
        $this->_request_body = $request_body;
        $this->_response     = $response;
        $this->_method       = $method;
        $this->_logger_class = $options['logger_class'] ?? Logger;
    }

    public function is_wp_error() {
        if (is_wp_error($this->_response)) {
            $this->_logger_class::log_error($this->_response->get_error_message(), $this->_method, $this->_response, $this->_request_body);
            return true;
        }
        else
            return false;
    }

    public function is_500() {
        if (isset($this->_response['response']) &&
            isset($this->_response['response']['code']) &&
            $this->_response['response']['code'] == 500) {
            $message = isset($this->_response['body']) ?
                $this->_response['body'] :
                var_export($this->_response, true);
            $this->_logger_class::log_error($message, $this->_method, $this->_response, $this->_request_body);
            return true;
        }
        else
            return false;
    }

    public function is_error_set($error) {
        if (isset($error)) {
            $this->_logger_class::log_error($error, $this->_method, $this->_response, $this->_request_body);
            return true;
        }
        else
            return false;
    }
}
