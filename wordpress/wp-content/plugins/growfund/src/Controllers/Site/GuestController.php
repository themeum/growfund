<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

/**
 * Guest Controller
 * @since 1.0.0
 */
class GuestController
{
    public function show()
    {
        return growfund_renderer()->get_html('dashboard.app', ['as_guest' => true]);
    }
}
