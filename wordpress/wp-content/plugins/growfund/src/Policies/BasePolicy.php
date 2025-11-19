<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\AuthorizationException;

class BasePolicy
{
    public function __call($method, $args)
    {
        if (growfund_user()->is_admin()) {
            return true;
        }

        $method = str_replace('authorize_', '', $method);

        if (method_exists($this, $method)) {
            return $this->{$method}(...$args);
        }

        throw new AuthorizationException(esc_html__('Invalid policy method', 'growfund'));
    }
}
