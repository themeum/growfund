<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Utils;


/**
 * Guest Controller
 * @since 1.0.0
 */
class GuestController
{
    public function show()
    {
        return growfund_renderer()->get_html('dashboard.app', ['as_guest' => Utils::is_public_route()]);
    }
}
