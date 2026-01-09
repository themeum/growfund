<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\NotFoundException;
use Exception;

class SiteExceptionHandler
{
    public static function handle(Exception $exception)
    {
        if ($exception instanceof NotFoundException) {
            return growfund_redirect(home_url());
        }

        growfund_redirect(home_url());
    }
}
