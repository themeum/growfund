<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\NotFoundException;
use Exception;

class SiteExceptionHandler
{
    public static function handle(Exception $exception)
    {
        if (growfund_is_dev_mode() && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) { 
            growfund_error_log($exception->getMessage() . ' in ' . $exception->getFile() . ' at ' . $exception->getLine());
		}
            
        if ($exception instanceof NotFoundException) {
            return growfund_redirect(home_url());
        }

        growfund_redirect(home_url());
    }
}
