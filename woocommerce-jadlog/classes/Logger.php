<?php
namespace WooCommerce\Jadlog\Classes;

class Logger {

    public static function log_error($error, $function, $response = null, $request = null) {
        if (isset($response))
            error_log('In '.$function.'(), $response = '.var_export($response, true));
        if (isset($request))
            error_log('In '.$function.'(), $request body = '.var_export($request, true));
        error_log('ERROR in '.$function.'(): '.var_export($error, true));
    }
}
