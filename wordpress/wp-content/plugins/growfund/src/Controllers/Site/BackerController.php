<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

class BackerController
{
    public function show()
    {
        return growfund_renderer()->get_html('dashboard.app');
    }
}
