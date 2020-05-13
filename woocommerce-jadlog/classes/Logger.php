<?php
namespace WooCommerce\Jadlog\Classes;

class Logger {

    public static function log_error($error, $function, $response = null, $request = null) {
        if (isset($response))
            error_log('In '.$function.'(), $response = '.var_export($response, true));
        if (isset($request))
            error_log('In '.$function.'(), $request body = '.var_export($request, true));
        error_log('ERROR in '.$function.'(): '.var_export($error, true));
        self::notify_error_tracking($error, $function, $response, $request);
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
