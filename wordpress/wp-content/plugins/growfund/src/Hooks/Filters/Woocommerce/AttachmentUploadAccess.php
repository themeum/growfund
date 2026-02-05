<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class AttachmentUploadAccess extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_PREVENT_ADMIN_ACCESS;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function get_priority()
    {
        return 99;
    }

    public function handle(...$args)
    {
        list($prevent_access) = $args;
        
        $request_uri = growfund_input_server('REQUEST_URI', '');

        if (strpos($request_uri, 'async-upload') !== false) {
            return false;
        }

        return $prevent_access;
    }
}
