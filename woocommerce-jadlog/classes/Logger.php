<?php
namespace WooCommerce\Jadlog\Classes;

class Logger {

    public static function log_error($error, $function, $response = null, $request = null) {
        if (isset($response))
            self::output_error('In '.$function.'(), $response = '.var_export($response, true));
        if (isset($request))
            self::output_error('In '.$function.'(), $request body = '.var_export($request, true));
        self::output_error('ERROR in '.$function.'(): '.var_export($error, true));
        self::notify_error_tracking($error, $function, $response, $request);
    }

    private static function output_error($message) {
        if (defined('ERROR_LOG_OUTPUT_FILE') && ERROR_LOG_OUTPUT_FILE !== null)
            error_log($message."\n", 3, ERROR_LOG_OUTPUT_FILE);
        else
            error_log($message);
    }

    public static function notify_error_tracking($error, $function, $response = null, $request = null) {
        // Bugsnag wordpress plugin must be installed and active.
        // API key must be configured.
        global $bugsnagWordpress;
        if (isset($bugsnagWordpress)) {
            $message = isset($error['descricao']) ? $error['descricao'] : var_export($error, true);
            $bugsnagWordpress->notifyError($function, $message, [
                'error'    => $error,
                'request'  => $request,
                'response' => $response
            ], 'warning');
        }
    }
}
